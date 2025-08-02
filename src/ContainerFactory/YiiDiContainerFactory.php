<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameter;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\Typiphy\ObjectType;
use RuntimeException;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Di\Reference\TagReference;
use Yiisoft\Injector\Injector;
use function assert;
use function Cspray\Typiphy\objectType;

// @codeCoverageIgnoreStart
// phpcs:disable
if (!class_exists(Container::class)) {
    throw new RuntimeException("To enable the YiiDiContainerFactory please install yiisoft/di 1.4+!");
}

if (!class_exists(Injector::class)) {
    throw new RuntimeException("To enable the YiiDiContainerFactory please install yiisoft/injector 1.2+!");
}
// phpcs:enable
// @codeCoverageIgnoreEnd


final class YiiDiContainerFactory extends AbstractContainerFactory implements ContainerFactory {

    protected function getBackingContainerType(): ObjectType {
        return objectType(Container::class);
    }

    protected function getContainerFactoryState(ContainerDefinition $containerDefinition): ContainerFactoryState {
        return new YiiDiContainerFactoryState($containerDefinition);
    }

    protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition): void {
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

    protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution): void {
        assert($state instanceof YiiDiContainerFactoryState);
        $definition = $resolution->getAliasDefinition();
        if ($definition !== null) {
            $state->addAlias($definition->getAbstractService()->getName(), $definition->getConcreteService()->getName());
        }
    }

    protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition): void {
        assert($state instanceof YiiDiContainerFactoryState);
        $state->addServiceDelegate(
            $definition->getServiceType()->getName(),
            $definition->getDelegateType()->getName(),
            $definition->getDelegateMethod()
        );
    }

    protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition): void {
        assert($state instanceof YiiDiContainerFactoryState);
        $state->addServicePrepare($definition->getService()->getName(), $definition->getMethod());
    }

    /**
     * @throws ParameterStoreNotFound
     */
    protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition): void {
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

    protected function handleConfigurationDefinition(ContainerFactoryState $state, ConfigurationDefinition $definition): void {
        assert($state instanceof YiiDiContainerFactoryState);
        $state->addConcreteService($definition->getClass()->getName());
        $name = $definition->getName();
        if ($name !== null) {
            $state->addNamedService($name, $definition->getClass()->getName());
        }
    }

    protected function createAnnotatedContainer(ContainerFactoryState $state, ActiveProfiles $activeProfiles): AnnotatedContainer {
        assert($state instanceof YiiDiContainerFactoryState);

        $state->addInstance(ActiveProfiles::class, $activeProfiles);

        return new class ($state) implements AnnotatedContainer {
            private readonly Container $container;
            private readonly Injector $injector;

            public function __construct(YiiDiContainerFactoryState $state) {
                $state->addInstance(AutowireableFactory::class, $this);
                $state->addInstance(AutowireableInvoker::class, $this);

                $config = ContainerConfig::create()
                    ->withDefinitions($state->createDefinitions())
                    ->withStrictMode()
                    ->withValidate(false);

                $this->container = new Container($config);
                $this->injector = (new Injector($this->container))->withCacheReflections();

                $servicesWithReadOnlyProperties = $this->container->get(TagReference::id(YiiDiContainerFactoryState::TAG_INJECT_READ_ONLY_PROPERTIES));

                foreach ($servicesWithReadOnlyProperties as $service) {
                    $properties = $state->getReadOnlyPropertyInjectsForService($service::class);
                    /**
                     * @var \ReflectionProperty $reflectionProperty
                     * @var mixed $def
                     */
                    foreach ($properties as [$reflectionProperty, $def]) {
                        $value = $def instanceof ContainerReference ? $this->container->get($def->type->getName()) : $def;
                        $reflectionProperty->setValue($service, $value);
                    }
                }
            }

            public function getBackingContainer(): object {
                return $this->container;
            }

            public function make(string $classType, ?AutowireableParameterSet $parameters = null): object {
                return $this->injector->make($classType, $this->convertAutowireableParameterSet($parameters));
            }

            public function invoke(callable $callable, ?AutowireableParameterSet $parameters = null): mixed {
                return $this->injector->invoke($callable, $this->convertAutowireableParameterSet($parameters));
            }

            public function get(string $id) {
                if (!$this->has($id)) {
                    throw ServiceNotFound::fromServiceNotInContainer($id);
                }
                return $this->container->get($id);
            }

            public function has(string $id): bool {
                return $this->container->has($id);
            }

            private function convertAutowireableParameterSet(?AutowireableParameterSet $parameters = null): array {
                $params = [];
                if (!is_null($parameters)) {
                    /** @var AutowireableParameter $parameter */
                    foreach ($parameters as $parameter) {
                        $name = $parameter->getName();
                        if ($parameter->isServiceIdentifier()) {
                            $serviceIdentifier = $parameter->getValue()->getName();
                            $value = $this->container->has($serviceIdentifier) ? $this->container->get($serviceIdentifier) : $this->make($serviceIdentifier);
                        } else {
                            $value = $parameter->getValue();
                        }
                        $params[$name] = $value;
                    }
                }
                return $params;
            }
        };
    }
}
