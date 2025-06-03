<?php

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
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Exception\UnsupportedOperation;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\Typiphy\ObjectType;
use RuntimeException;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Injector\Injector;
use function assert;
use function Cspray\Typiphy\objectType;

// @codeCoverageIgnoreStart
if (!class_exists(Container::class)) {
    throw new RuntimeException("To enable the YiiDiContainerFactory please install yiisoft/di 1.4+!");
}
// @codeCoverageIgnoreEnd

// @codeCoverageIgnoreStart
if (!class_exists(Injector::class)) {
    throw new RuntimeException("To enable the YiiDiContainerFactory please install yiisoft/injector 1.2+!");
}

// @codeCoverageIgnoreEnd


final class YIiDiContainerFactory extends AbstractContainerFactory implements ContainerFactory
{
    protected function getBackingContainerType(): ObjectType
    {
        return objectType(Container::class);
    }

    protected function getContainerFactoryState(): ContainerFactoryState
    {
        return new YiiDiContainerFactoryState();
    }

    protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition): void
    {
        assert($state instanceof YiiDiContainerFactoryState);
        if ($definition->isAbstract()) {
            $state->addAbstractService($definition->getType()->getName());
        } else {
            $state->addConcreteService($definition->getType()->getName());
        }
        $alias = $definition->getName();
        if ($alias !== null) {
            $state->addNamedService($alias, $definition->getType()->getName());
        }
    }

    protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution): void
    {
        assert($state instanceof YiiDiContainerFactoryState);
        $definition = $resolution->getAliasDefinition();
        if ($definition !== null) {
            $state->addAlias($definition->getAbstractService()->getName(), $definition->getConcreteService()->getName());
        }
    }

    protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition): void
    {
        // TODO: implement me
    }

    protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition): void
    {
        assert($state instanceof YiiDiContainerFactoryState);
        $state->addServicePrepare($definition->getService()->getName(), $definition->getMethod());
    }

    /**
     * @throws ParameterStoreNotFound
     */
    protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition): void
    {
        assert($state instanceof YiiDiContainerFactoryState);
        if ($definition->getTargetIdentifier()->isMethodParameter()) {
            $state->addMethodInject(
                $definition->getTargetIdentifier()->getClass()->getName(),
                $definition->getTargetIdentifier()->getMethodName(),
                $definition->getTargetIdentifier()->getName(),
                $this->getInjectDefinitionValue($definition),
            );
        } else {
            $state->addPropertyInject(
                $definition->getTargetIdentifier()->getClass()->getName(),
                $definition->getTargetIdentifier()->getName(),
                $this->getInjectDefinitionValue($definition),
            );
        }
    }

    protected function handleConfigurationDefinition(ContainerFactoryState $state, ConfigurationDefinition $definition): void
    {
        // TODO: Configuration attribute is deprecated. Should this method be implemented?
    }

    protected function createAnnotatedContainer(ContainerFactoryState $state, ActiveProfiles $activeProfiles): AnnotatedContainer
    {
        assert($state instanceof YiiDiContainerFactoryState);

        $state->addInstance(ActiveProfiles::class, $activeProfiles);

        return new readonly class ($state) implements AnnotatedContainer {
            private Container $container;
            private Injector $injector;

            public function __construct(YiiDiContainerFactoryState $state)
            {
                $state->addInstance(AutowireableFactory::class, $this);
                $state->addInstance(AutowireableInvoker::class, $this);

                $config = ContainerConfig::create()
                    ->withDefinitions($state->createDefinitions())
                    ->withStrictMode();

                $this->container = new Container($config);
                $this->injector = (new Injector($this->container))->withCacheReflections(); // TODO: check memory usage with cache reflections
            }

            public function getBackingContainer(): object
            {
                return $this->container;
            }

            public function make(string $classType, ?AutowireableParameterSet $parameters = null): object
            {
                return $this->injector->make($classType, $this->convertAutowireableParameterSet($parameters));
            }

            public function invoke(callable $callable, ?AutowireableParameterSet $parameters = null): mixed
            {
                // TODO: implement me
                throw UnsupportedOperation::fromMethodNotSupported(__METHOD__);
            }

            public function get(string $id)
            {
                if (!$this->has($id)) {
                    throw ServiceNotFound::fromServiceNotInContainer($id);
                }
                return $this->container->get($id);
            }

            public function has(string $id): bool
            {
                return $this->container->has($id);
            }

            private function convertAutowireableParameterSet(?AutowireableParameterSet $parameters = null): array
            {
                $params = [];
                if (!is_null($parameters)) {
                    /** @var AutowireableParameter $parameter */
                    foreach ($parameters as $parameter) {
                        $name = $parameter->getName();
                        $value = $parameter->isServiceIdentifier() ? $this->injector->make($parameter->getValue()->getName()) : $parameter->getValue();
                        $params[$name] = $value;
                    }
                }
                return $params;
            }
        };
    }
}
