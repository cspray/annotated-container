<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameter;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use DI\Container;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\Typiphy\ObjectType;
use DI\ContainerBuilder;
use DI\Definition\Helper\AutowireDefinitionHelper;
use UnitEnum;
use function Cspray\Typiphy\objectType;
use function DI\autowire;
use function DI\decorate;
use function DI\get;

// @codeCoverageIgnoreStart
if (!class_exists(Container::class)) {
    throw new \RuntimeException("To enable the PhpDiContainerFactory please install php-di/php-di 7+!");
}
// @codeCoverageIgnoreEnd


/**
 * A ContainerFactory that utilizes the php-di/php-di library.
 */
final class PhpDiContainerFactory extends AbstractContainerFactory implements ContainerFactory {

    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        $this->setLoggerFromOptions($containerFactoryOptions);
        $activeProfiles = $containerFactoryOptions?->getActiveProfiles() ?? ['default'];

        try {
            $this->logCreatingContainer(objectType(Container::class), $activeProfiles);
            $this->logServicesNotMatchingProfiles($containerDefinition, $activeProfiles);
            $container =  $this->createDiContainer($containerDefinition, $activeProfiles);
            $this->logFinishedCreatingContainer(objectType(Container::class), $activeProfiles);
            return $container;
        } catch (InvalidAlias $exception) {
            throw ContainerException::fromCaughtThrowable($exception);
        }
    }

    private function createDiContainer(ContainerDefinition $containerDefinition, array $activeProfiles) : AnnotatedContainer {
        $containerBuilder = new ContainerBuilder();
        $definitions = [];
        // We have to maintain a set of known services to let our Container comply with PSR-11
        $serviceTypes = [AutowireableFactory::class, ActiveProfiles::class, AutowireableInvoker::class];
        $definitions[ActiveProfiles::class] = function() : ActiveProfiles {
            return $this->getActiveProfilesService();
        };

        $containerDefinition = new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles);
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $serviceTypes[] = $serviceDefinition->getType()->getName();
            $definitions[$serviceDefinition->getType()->getName()] = autowire();
            $this->logServiceShared($serviceDefinition);
            $name = $serviceDefinition->getName();
            if (!is_null($name)) {
                $serviceTypes[] = $name;
                $definitions[$name] = get($serviceDefinition->getType()->getName());
                $this->logServiceNamed($serviceDefinition);
            }
        }

        foreach ($containerDefinition->getConfigurationDefinitions() as $configurationDefinition) {
            $configName = is_null($configurationDefinition->getName()) ? $configurationDefinition->getClass()->getName() : $configurationDefinition->getName();
            $serviceTypes[] = $configurationDefinition->getClass()->getName();
            $this->logConfigurationShared($configurationDefinition);
            if (!is_null($configurationDefinition->getName())) {
                $serviceTypes[] = $configurationDefinition->getName();
                $this->logConfigurationNamed($configurationDefinition);
            }
            assert(!is_null($configName));
            $definitions[$configName] = autowire($configurationDefinition->getClass()->getName());
        }

        $methodInjectMap = $this->mapMethodInjectDefinitions($containerDefinition);
        foreach ($methodInjectMap as $service => $methods) {
            if (!isset($definitions[$service])) {
                $definitions[$service] = autowire();
            }
            assert($definitions[$service] instanceof AutowireDefinitionHelper);
            // This covers constructor injection, setting injection for service prepare methods happens with service prepare configuration
            foreach ($methods as $methodName => $params) {
                if ($methodName === '__construct') {
                    foreach ($params as $param => $value) {
                        $definitions[$service]->constructorParameter($param, $value);
                    }
                }
            }
        }

        $propertyInjectMap = $this->mapPropertyInjectDefinitions($containerDefinition);
        foreach ($propertyInjectMap as $service => $properties) {
            if (!isset($definitions[$service])) {
                $definitions[$service] = autowire();
            }
            assert($definitions[$service] instanceof AutowireDefinitionHelper);
            foreach ($properties as $property => $value) {
                $definitions[$service]->property($property, $value);
            }
        }

        $aliasedTypes = [];
        $aliasDefinitions = $containerDefinition->getAliasDefinitions();
        foreach ($aliasDefinitions as $aliasDefinition) {
            if (!in_array($aliasDefinition->getAbstractService()->getName(), $aliasedTypes)) {
                $resolution = $this->aliasDefinitionResolver->resolveAlias(
                    $containerDefinition, $aliasDefinition->getAbstractService()
                );
                $this->logAliasingService($resolution, $aliasDefinition->getAbstractService());

                $aliasDefinition = $resolution->getAliasDefinition();
                if ($aliasDefinition !== null) {
                    $abstractDefinition = $this->getServiceDefinition($containerDefinition, $aliasDefinition->getAbstractService());
                    assert(!is_null($abstractDefinition));

                    $abstractName = is_null($abstractDefinition->getName()) ? $abstractDefinition->getType()->getName() : $abstractDefinition->getName();
                    assert(!is_null($abstractName));

                    $definitions[$abstractName] = get($aliasDefinition->getConcreteService()->getName());
                }
            }
        }


        foreach ($containerDefinition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            $serviceName = $serviceDelegateDefinition->getServiceType()->getName();
            $definitions[$serviceName] = function(Container $container) use($serviceDelegateDefinition) : mixed {
                return $container->call([$serviceDelegateDefinition->getDelegateType()->getName(), $serviceDelegateDefinition->getDelegateMethod()]);
            };
            $this->logServiceDelegate($serviceDelegateDefinition);
        }

        $servicePrepareDefinitions = [];
        $servicePrepareMap = $this->mapServicePrepareDefinitions($containerDefinition, $methodInjectMap);
        foreach ($servicePrepareMap as $service => $methodParams) {
            if (!isset($definitions[$service])) {
                $definitions[$service] = autowire();
            }

            $servicePrepareDefinitions[$service] = decorate(function(object $service, Container $container) use($methodParams) : object {
                foreach ($methodParams as $method => $params) {
                    $container->call([$service, $method], $params);
                }
                return $service;
            });
        }

        $containerBuilder->addDefinitions($definitions);
        $containerBuilder->addDefinitions($servicePrepareDefinitions);
        $container = $containerBuilder->build();
        return new class($container, $serviceTypes) implements AnnotatedContainer {

            public function __construct(
                private readonly Container $container,
                private readonly array $serviceTypes
            ) {
                $this->container->set(AutowireableFactory::class, $this);
                $this->container->set(AutowireableInvoker::class, $this);
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                return $this->container->make(
                    $classType,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            public function get(string $id) {
                if (!$this->has($id)) {
                    throw ServiceNotFound::fromServiceNotInContainer($id);
                }
                return $this->container->get($id);
            }

            public function has(string $id) : bool {
                return in_array($id, $this->serviceTypes);
            }

            public function getBackingContainer() : Container {
                return $this->container;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->container->call(
                    $callable,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            private function convertAutowireableParameterSet(AutowireableParameterSet $parameters = null) : array {
                $params = [];
                if (!is_null($parameters)) {
                    /** @var AutowireableParameter $parameter */
                    foreach ($parameters as $parameter) {
                        $params[$parameter->getName()] = $parameter->isServiceIdentifier() ? get($parameter->getValue()->getName()) : $parameter->getValue();
                    }
                }
                return $params;
            }
        };
    }

    private function mapMethodInjectDefinitions(ContainerDefinition $containerDefinition) : array {
        $map = [];
        foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
            if ($injectDefinition->getTargetIdentifier()->isClassProperty()) {
                continue;
            }

            $className = $injectDefinition->getTargetIdentifier()->getClass()->getName();
            $methodName = $injectDefinition->getTargetIdentifier()->getMethodName();
            if (!isset($map[$className])) {
                $map[$className] = [];
            }
            assert(!is_null($methodName));

            if (!isset($map[$className][$methodName])) {
                $map[$className][$methodName] = [];
            }

            $injectStore = $injectDefinition->getStoreName();
            $value = $injectDefinition->getValue();
            if ($injectStore !== null) {
                $parameterStore = $this->getParameterStore($injectStore);
                if ($parameterStore === null) {
                    throw ParameterStoreNotFound::fromParameterStoreNotAddedToContainerFactory($injectStore);
                }

                $value = $parameterStore->fetch($injectDefinition->getType(), $value);
            }

            $param = $injectDefinition->getType() instanceof ObjectType && !is_a($injectDefinition->getType()->getName(), UnitEnum::class, true)
                ? get($injectDefinition->getValue())
                : $value;
            $map[$className][$methodName][$injectDefinition->getTargetIdentifier()->getName()] = $param;
            $this->logInjectingMethodParameter($injectDefinition);
        }
        return $map;
    }

    private function mapPropertyInjectDefinitions(ContainerDefinition $containerDefinition) : array {
        $map = [];
        foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
            if ($injectDefinition->getTargetIdentifier()->isMethodParameter()) {
                continue;
            }

            $className = $injectDefinition->getTargetIdentifier()->getClass()->getName();
            $propertyName = $injectDefinition->getTargetIdentifier()->getName();
            if (!isset($map[$className])) {
                $map[$className] = [];
            }

            $injectStore = $injectDefinition->getStoreName();
            $value = $injectDefinition->getValue();
            if (!is_null($injectStore)) {
                $parameterStore = $this->getParameterStore($injectStore);
                if ($parameterStore === null) {
                    throw ParameterStoreNotFound::fromParameterStoreNotAddedToContainerFactory($injectStore);
                }

                $value = $parameterStore->fetch($injectDefinition->getType(), $value);
            }

            $map[$className][$propertyName] = $value;
            $this->logInjectingProperty($injectDefinition);
        }
        return $map;
    }

    private function mapServicePrepareDefinitions(ContainerDefinition $containerDefinition, array $methodInjectMap) : array {
        $map = [];
        foreach ($containerDefinition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
            if (!isset($map[$servicePrepareDefinition->getService()->getName()])) {
                $map[$servicePrepareDefinition->getService()->getName()] = [];
            }

            $map[$servicePrepareDefinition->getService()->getName()][$servicePrepareDefinition->getMethod()] = $methodInjectMap[$servicePrepareDefinition->getService()->getName()][$servicePrepareDefinition->getMethod()] ?? [];
            $this->logServicePrepare($servicePrepareDefinition);
        }

        return $map;
    }

    private function getServiceDefinition(ContainerDefinition $containerDefinition, ObjectType $objectType) : ?ServiceDefinition {
        return array_reduce(
            $containerDefinition->getServiceDefinitions(),
            fn($carry, $item) : ?ServiceDefinition => $item->getType() === $objectType ? $item : $carry
        );
    }
}