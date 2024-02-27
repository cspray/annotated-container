<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameter;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Profiles;
use DI\Container;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\Typiphy\ObjectType;
use DI\ContainerBuilder;
use UnitEnum;
use function Cspray\Typiphy\objectType;
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

    protected function getBackingContainerType() : ObjectType {
        return objectType(Container::class);
    }

    protected function getContainerFactoryState() : ContainerFactoryState {
        return new PhpDiContainerFactoryState();
    }

    protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition) : void {
        assert($state instanceof PhpDiContainerFactoryState);
        $serviceType = $definition->getType()->getName();
        $state->addService($serviceType);
        $state->autowireService($serviceType);
        $key = $serviceType;
        $name = $definition->getName();
        if ($name !== null) {
            $state->addService($name);
            $state->referenceService($name, $definition->getType()->getName());
            $key = $name;
        }
        $state->setServiceKey($serviceType, $key);
    }

    protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution) : void {
        assert($state instanceof PhpDiContainerFactoryState);
        $aliasDefinition = $resolution->getAliasDefinition();
        if ($aliasDefinition !== null) {
            $state->referenceService(
                $state->getServiceKey($aliasDefinition->getAbstractService()->getName()),
                $aliasDefinition->getConcreteService()->getName()
            );
        }
    }

    public function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition) : void {
        assert($state instanceof PhpDiContainerFactoryState);
        $serviceName = $definition->getServiceType()->getName();
        $state->factoryService($serviceName, static fn(Container $container) => $container->call(
            [$definition->getDelegateType()->getName(), $definition->getDelegateMethod()]
        ));
    }

    public function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition) : void {
        assert($state instanceof PhpDiContainerFactoryState);

        $state->addServicePrepare($definition->getService()->getName(), $definition->getMethod());
    }

    public function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition) : void {
        assert($state instanceof PhpDiContainerFactoryState);
        $targetClass = $definition->getTargetIdentifier()->getClass()->getName();
        if ($definition->getTargetIdentifier()->isMethodParameter()) {
            $method = $definition->getTargetIdentifier()->getMethodName();
            $parameter = $definition->getTargetIdentifier()->getName();

            $value = $definition->getValue();
            $store = $definition->getStoreName();
            if ($store !== null) {
                $parameterStore = $this->getParameterStore($store);
                if ($parameterStore === null) {
                    throw ParameterStoreNotFound::fromParameterStoreNotAddedToContainerFactory($store);
                }
                $value = $parameterStore->fetch($definition->getType(), $value);
            }

            $param = $definition->getType() instanceof ObjectType && !is_a($definition->getType()->getName(), UnitEnum::class, true)
                ? get($value)
                : $value;

            $state->addMethodInject($targetClass, $method, $parameter, $param);
        } else {
            $property = $definition->getTargetIdentifier()->getName();

            $value = $definition->getValue();
            $store = $definition->getStoreName();
            if ($store !== null) {
                $parameterStore = $this->getParameterStore($store);
                if ($parameterStore === null) {
                    throw ParameterStoreNotFound::fromParameterStoreNotAddedToContainerFactory($store);
                }
                $value = $parameterStore->fetch($definition->getType(), $value);
            }

            $param = $definition->getType() instanceof ObjectType && !is_a($definition->getType()->getName(), UnitEnum::class, true)
                ? get($value)
                : $value;

            $state->addPropertyInject($targetClass, $property, $param);
        }

    }

    public function handleConfigurationDefinition(ContainerFactoryState $state, ConfigurationDefinition $definition) : void {
        assert($state instanceof PhpDiContainerFactoryState);
        $configName = is_null($definition->getName()) ? $definition->getClass()->getName() : $definition->getName();
        $state->addService($definition->getClass()->getName());
        if (!is_null($definition->getName())) {
            $state->addService($definition->getName());
            $state->referenceService($definition->getName(), $definition->getClass()->getName());
        }
        assert(!is_null($configName));
        $state->autowireService($definition->getClass()->getName());
    }

    protected function createAnnotatedContainer(ContainerFactoryState $state, Profiles $activeProfiles) : AnnotatedContainer {
        assert($state instanceof PhpDiContainerFactoryState);
        $containerBuilder = new ContainerBuilder();

        $definitions = $state->getDefinitions();

        foreach ($state->getMethodInject() as $service => $methods) {
            foreach ($methods as $method => $params) {
                if ($method === '__construct') {
                    foreach ($params as $param => $value) {
                        $definitions[$service]->constructorParameter($param, $value);
                    }
                }
            }
        }

        foreach ($state->getPropertyInject() as $service => $properties) {
            foreach ($properties as $property => $value) {
                $definitions[$service]->property($property, $value);
            }
        }

        $servicePrepareDefinitions = [];
        foreach ($state->getServicePrepares() as $service => $methods) {
            $servicePrepareDefinitions[$service] = decorate(static function (object $service, Container $container) use($state, $methods) {
                foreach ($methods as $method) {
                    $params = $state->parametersForMethod($service::class, $method);
                    $container->call([$service, $method], $params);
                }
                return $service;
            });
        }

        $containerBuilder->addDefinitions($definitions);
        $containerBuilder->addDefinitions($servicePrepareDefinitions);

        return new class($containerBuilder->build(), $state->getServices(), $activeProfiles) implements AnnotatedContainer {

            public function __construct(
                private readonly Container $container,
                private readonly array $serviceTypes,
                Profiles $activeProfiles
            ) {
                $this->container->set(AutowireableFactory::class, $this);
                $this->container->set(AutowireableInvoker::class, $this);
                $this->container->set(Profiles::class, $activeProfiles);
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
}