<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Auryn\Injector;

// @codeCoverageIgnoreStart
if (!class_exists(Injector::class)) {
    throw new \RuntimeException("To enable the PhpDiContainerFactory please install php-di/php-di 7+!");
}
// @codeCoverageIgnoreEnd

use Cspray\AnnotatedContainer\AliasDefinition;
use Cspray\AnnotatedContainer\AutowireableFactory;
use Cspray\AnnotatedContainer\AutowireableParameterSet;
use Cspray\AnnotatedContainer\HasBackingContainer;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\EnvironmentParameterStore;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\AnnotatedContainer\Exception\ServiceNotFoundException;
use Cspray\AnnotatedContainer\ParameterStore;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\Typiphy\ObjectType;
use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\decorate;
use function DI\get;

class PhpDiContainerFactory implements ContainerFactory {

    /**
     * @var ParameterStore[]
     */
    private array $parameterStores = [];

    public function __construct() {
        $this->addParameterStore(new EnvironmentParameterStore());
    }

    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : ContainerInterface&AutowireableFactory&HasBackingContainer {
        $activeProfiles = $containerFactoryOptions?->getActiveProfiles() ?? [];
        if (empty($activeProfiles)) {
            $activeProfiles[] = 'default';
        }
        $containerBuilder = new ContainerBuilder();
        $definitions = [];
        $serviceTypes = [AutowireableFactory::class];
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $serviceTypes[] = $serviceDefinition->getType()->getName();
            if (!is_null($serviceDefinition->getName())) {
                $serviceTypes[] = $serviceDefinition->getName();
            }
        }

        $aliasedTypes = [];
        $aliasDefinitions = $containerDefinition->getAliasDefinitions();
        foreach ($aliasDefinitions as $aliasDefinition) {
            $concreteDefinition = $this->getServiceDefinition($containerDefinition, $aliasDefinition->getConcreteService());
            if (is_null($concreteDefinition)) {
                throw new ContainerException(sprintf(
                    'An AliasDefinition is defined with a concrete type %s that is not a registered #[Service].',
                    $aliasDefinition->getConcreteService()->getName()
                ));
            }
            if (!in_array($aliasDefinition->getAbstractService()->getName(), $aliasedTypes)) {
                $typeAliasDefinitions = $this->mapTypesAliasDefinitions($containerDefinition, $aliasDefinition->getAbstractService(), $aliasDefinitions, $activeProfiles);
                $aliasDefinition = null;
                if (count($typeAliasDefinitions) === 1) {
                    $aliasDefinition = $typeAliasDefinitions[0];
                } else {
                    /** @var AliasDefinition $typeAliasDefinition */
                    foreach ($typeAliasDefinitions as $typeAliasDefinition) {
                        if ($this->getServiceDefinition($containerDefinition, $typeAliasDefinition->getConcreteService())->isPrimary()) {
                            $aliasDefinition = $typeAliasDefinition;
                            break;
                        }
                    }
                }

                if (isset($aliasDefinition)) {
                    $abstractDefinition = $this->getServiceDefinition($containerDefinition, $aliasDefinition->getAbstractService());
                    $abstractName = is_null($abstractDefinition->getName()) ? $abstractDefinition->getType()->getName() : $abstractDefinition->getName();
                    $definitions[$abstractName] = autowire($aliasDefinition->getConcreteService()->getName());
                }
            }
        }

        foreach ($containerDefinition->getConfigurationDefinitions() as $configurationDefinition) {
            $configName = is_null($configurationDefinition->getName()) ? $configurationDefinition->getClass()->getName() : $configurationDefinition->getName();
            $serviceTypes[] = $configurationDefinition->getClass()->getName();
            if (!is_null($configurationDefinition->getName())) {
                $serviceTypes[] = $configurationDefinition->getName();
            }
            $definitions[$configName] = autowire($configurationDefinition->getClass()->getName());
        }

        $methodInjectMap = $this->mapMethodInjectDefinitions($containerDefinition, $activeProfiles);
        foreach ($methodInjectMap as $service => $methods) {
            if (!isset($definitions[$service])) {
                $definitions[$service] = autowire();
            }
            foreach ($methods as $methodName => $params) {
                if ($methodName === '__construct') {
                    foreach ($params as $param => $value) {
                        $definitions[$service]->constructorParameter($param, $value);
                    }
                }
            }
        }

        $propertyInjectMap = $this->mapPropertyInjectDefinitions($containerDefinition, $activeProfiles);
        foreach ($propertyInjectMap as $service => $properties) {
            if (!isset($definitions[$service])) {
                $definitions[$service] = autowire();
            }
            foreach ($properties as $property => $value) {
                $definitions[$service]->property($property, $value);
            }
        }

        foreach ($containerDefinition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            $serviceName = $serviceDelegateDefinition->getServiceType()->getName();
            $definitions[$serviceName] = function(Container $container) use($serviceDelegateDefinition) {
                return $container->call([$serviceDelegateDefinition->getDelegateType()->getName(), $serviceDelegateDefinition->getDelegateMethod()]);
            };
        }

        $servicePrepareDefinitions = [];
        $servicePrepareMap = $this->mapServicePrepareDefinitions($containerDefinition, $methodInjectMap);
        foreach ($servicePrepareMap as $service => $methodParams) {
            if (!isset($definitions[$service])) {
                $definitions[$service] = autowire();
            }

            $servicePrepareDefinitions[$service] = decorate(function($service, Container $container) use($methodParams) {
                foreach ($methodParams as $method => $params) {
                    $container->call([$service, $method], $params);
                }
                return $service;
            });
        }

        $containerBuilder->addDefinitions($definitions);
        $containerBuilder->addDefinitions($servicePrepareDefinitions);
        $container = $containerBuilder->build();
        return new class($container, $serviceTypes) implements ContainerInterface, AutowireableFactory, HasBackingContainer {

            public function __construct(private readonly Container $container, private readonly array $serviceTypes) {
                $this->container->set(AutowireableFactory::class, $this);
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                $params = [];
                if (!is_null($parameters)) {
                    /** @var AutowireableParameter $parameter */
                    foreach ($parameters as $parameter) {
                        $params[$parameter->getName()] = $parameter->isServiceIdentifier() ? get($parameter->getValue()->getName()) : $parameter->getValue();
                    }
                }
                return $this->container->make($classType, $params);
            }

            public function get(string $id) {
                if (!$this->has($id)) {
                    throw new ServiceNotFoundException(sprintf(
                        'The service "%s" could not be found in this container.',
                        $id
                    ));
                }
                return $this->container->get($id);
            }

            public function has(string $id) : bool {
                return in_array($id, $this->serviceTypes);
            }

            public function getBackingContainer() : Container {
                return $this->container;
            }
        };
    }

    private function mapMethodInjectDefinitions(ContainerDefinition $containerDefinition, array $activeProfiles) : array {
        $map = [];
        foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
            if ($injectDefinition->getTargetIdentifier()->isClassProperty()) {
                continue;
            }

            $injectProfiles = $injectDefinition->getProfiles();
            if (empty($injectProfiles)) {
                $injectProfiles[] = 'default';
            }
            if (empty(array_intersect($activeProfiles, $injectProfiles))) {
                continue;
            }
            $className = $injectDefinition->getTargetIdentifier()->getClass()->getName();
            $methodName = $injectDefinition->getTargetIdentifier()->getMethodName();
            if (!isset($map[$className])) {
                $map[$className] = [];
            }

            if (!isset($map[$className][$methodName])) {
                $map[$className][$methodName] = [];
            }

            $injectStore = $injectDefinition->getStoreName();
            $value = $injectDefinition->getValue();
            if (!is_null($injectStore)) {
                if (!isset($this->parameterStores[$injectStore])) {
                    throw new InvalidParameterException(sprintf(
                        'The ParameterStore "%s" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.',
                        $injectStore
                    ));
                }

                $value = $this->parameterStores[$injectStore]->fetch($injectDefinition->getType(), $value);
            }

            $param = $injectDefinition->getType() instanceof ObjectType ? get($injectDefinition->getValue()) : $value;
            $map[$className][$methodName][$injectDefinition->getTargetIdentifier()->getName()] = $param;
        }
        return $map;
    }

    private function mapPropertyInjectDefinitions(ContainerDefinition $containerDefinition, array $activeProfiles) : array {
        $map = [];
        foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
            if ($injectDefinition->getTargetIdentifier()->isMethodParameter()) {
                continue;
            }
            $injectProfiles = $injectDefinition->getProfiles();
            if (empty($injectProfiles)) {
                $injectProfiles[] = 'default';
            }
            if (empty(array_intersect($activeProfiles, $injectProfiles)) ) {
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
                if (!isset($this->parameterStores[$injectStore])) {
                    throw new InvalidParameterException(sprintf(
                        'The ParameterStore "%s" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.',
                        $injectStore
                    ));
                }

                $value = $this->parameterStores[$injectStore]->fetch($injectDefinition->getType(), $value);
            }

            $map[$className][$propertyName] = $value;
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
        }

        return $map;
    }

    private function getServiceDefinition(ContainerDefinition $containerDefinition, ObjectType $objectType) : ?ServiceDefinition {
        return array_reduce(
            $containerDefinition->getServiceDefinitions(),
            fn($carry, $item) => $item->getType() === $objectType ? $item : $carry
        );
    }

    private function mapTypesAliasDefinitions(ContainerDefinition $containerDefinition, ObjectType $serviceDefinition, array $aliasDefinitions, array $activeProfiles) : array {
        $aliases = [];
        /** @var AliasDefinition $aliasDefinition */
        foreach ($aliasDefinitions as $aliasDefinition) {
            $concreteProfiles = $this->getServiceDefinition($containerDefinition, $aliasDefinition->getConcreteService())?->getProfiles() ?? false;
            if ($concreteProfiles === false) {
                throw new ContainerException(sprintf(
                    'An AliasDefinition is defined with a concrete type %s that is not a registered #[Service].',
                    $aliasDefinition->getConcreteService()->getName()
                ));
            } else if (empty($concreteProfiles)) {
                $concreteProfiles[] = 'default';
            }
            foreach ($activeProfiles as $activeProfile) {
                if (in_array($activeProfile, $concreteProfiles) && $aliasDefinition->getAbstractService() === $serviceDefinition) {
                    $aliases[] = $aliasDefinition;
                }
            }
        }
        return $aliases;
    }

    public function addParameterStore(ParameterStore $parameterStore) : void {
        $this->parameterStores[$parameterStore->getName()] = $parameterStore;
    }
}