<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\Illuminate;

use Closure;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\AbstractContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerFactoryState;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerReference;
use Cspray\AnnotatedContainer\ContainerFactory\State\InjectParameterValue;
use Cspray\AnnotatedContainer\ContainerFactory\State\InjectParameterValueProvider;
use Cspray\AnnotatedContainer\ContainerFactory\State\ParameterResolver;
use Cspray\AnnotatedContainer\ContainerFactory\State\ServiceCollectorReference;
use Cspray\AnnotatedContainer\ContainerFactory\State\ValueFetchedFromParameterStore;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Reflection\Type;
use Illuminate\Contracts\Container\Container;
use function Cspray\AnnotatedContainer\Reflection\types;

// @codeCoverageIgnoreStart
// phpcs:disable
if (!interface_exists(Container::class)) {
    throw new \RuntimeException("To enable the IlluminateContainerFactory please install illuminate/container 10+!");
}
// phpcs:enable
// @codeCoverageIgnoreEnd

/**
 * @extends AbstractContainerFactory<Container, Container>
 */
final class IlluminateContainerFactory extends AbstractContainerFactory {

    protected function createAnnotatedContainer(ContainerFactoryState $state) : AnnotatedContainer {
        $container = new \Illuminate\Container\Container();
        $parameterResolver = new ParameterResolver($this->injectParameterValueProvider());

        (new IlluminateContainerBinder(
            $container,
            $state,
            $parameterResolver
        ))->bindDependencies();

        return new class($container) implements AnnotatedContainer {

            public function __construct(
                private readonly Container $container,
            ) {
                $this->container->instance(AutowireableFactory::class, $this);
                $this->container->instance(AutowireableInvoker::class, $this);
            }

            public function backingContainer() : Container {
                return $this->container;
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                $object = $this->container->make($classType, $this->resolvedParameters($parameters));
                assert($object instanceof $classType);
                return $object;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->container->call($callable, $this->resolvedParameters($parameters));
            }

            /**
             * @return array<non-empty-string, mixed>
             */
            private function resolvedParameters(?AutowireableParameterSet $parameters) : array {
                /** @var array<non-empty-string, mixed> $params */
                $params = [];
                if ($parameters !== null) {
                    foreach ($parameters as $parameter) {
                        if ($parameter->isServiceIdentifier()) {
                            $parameterValue = $parameter->value();
                            assert($parameterValue instanceof Type);

                            /** @psalm-var mixed $value */
                            $value = $this->container->get($parameterValue->name());
                        } else {
                            /** @psalm-var mixed $value */
                            $value = $parameter->value();
                        }

                        $params[$parameter->name()] = $value;
                    }
                }

                return $params;
            }

            /**
             * @template T
             * @param class-string<T>|non-empty-string $id
             * @return ($id is class-string<T> ? T : mixed)
             */
            public function get(string $id) {
                if (!$this->has($id)) {
                    throw ServiceNotFound::fromServiceNotInContainer($id);
                }

                /** @var T|mixed $object */
                $object = $this->container->get($id);
                return $object;
            }

            public function has(string $id) : bool {
                return $this->container->has($id);
            }
        };
    }

    protected function injectParameterValueProvider() : InjectParameterValueProvider {
        return new class implements InjectParameterValueProvider {

            public function resolveInjectParameterValue(object $container, ContainerFactoryState $state, InjectDefinition $injectDefinition) : InjectParameterValue {
                $key = sprintf('%s', $injectDefinition->classMethodParameter()->parameterName());
                if ($injectDefinition->classMethodParameter()->methodName() === '__construct') {
                    $key = '$' . $key;
                }
                $value = $injectDefinition->value();
                if ($value instanceof ContainerReference) {
                    $key = $injectDefinition->classMethodParameter()->type()->name();
                    $value = fn() => $container->get($value->name);
                } elseif ($value instanceof ServiceCollectorReference) {
                    if (!$value->collectionType->equals(types()->array())) {
                        $key = $value->collectionType->name();
                    }
                    $value = fn() => $state->serviceCollectorReferenceToListOfServices($value, $injectDefinition, $container->get(...));
                } else {
                    $value = fn() : mixed => $value instanceof ValueFetchedFromParameterStore ? $value->get() : $value;
                }

                return new InjectParameterValue($key, $value);
            }
        };
    }
}
