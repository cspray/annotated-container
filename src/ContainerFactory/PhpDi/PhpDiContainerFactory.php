<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\PhpDi;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\AbstractContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerFactoryState;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerReference;
use Cspray\AnnotatedContainer\ContainerFactory\State\InjectParameterValue;
use Cspray\AnnotatedContainer\ContainerFactory\State\InjectParameterValueProvider;
use Cspray\AnnotatedContainer\ContainerFactory\State\ParameterResolver;
use Cspray\AnnotatedContainer\ContainerFactory\State\ServiceCollectorReference;
use Cspray\AnnotatedContainer\ContainerFactory\State\ValueFetchedFromParameterStore;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Profiles;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Reference;
use function DI\autowire;
use function DI\decorate;
use function DI\factory;
use function DI\get;

// @codeCoverageIgnoreStart
// phpcs:disable
if (!class_exists(Container::class)) {
    throw new \RuntimeException("To enable the PhpDiContainerFactory please install php-di/php-di 7+!");
}
// phpcs:enable
// @codeCoverageIgnoreEnd


/**
 * A ContainerFactory that utilizes the php-di/php-di library.
 *
 * @extends AbstractContainerFactory<ContainerBuilder, Container>
 */
final class PhpDiContainerFactory extends AbstractContainerFactory implements ContainerFactory {

    protected function createAnnotatedContainer(ContainerFactoryState $state) : AnnotatedContainer {
        $containerBuilder = new ContainerBuilder();
        $parameterResolver = new ParameterResolver($this->injectParameterValueProvider());

        $definitions = [];
        $servicePrepareDefinitions = [];
        foreach ($state->serviceDefinitions() as $serviceDefinition) {
            $serviceDelegateDefinition = $state->serviceDelegateDefinitionForServiceDefinition($serviceDefinition);

            if ($serviceDelegateDefinition === null) {
                $definitions[$serviceDefinition->type()->name()] = autowire();

                foreach ($parameterResolver->resolveParametersForServiceConstructor($containerBuilder, $state, $serviceDefinition) as $param => $value) {
                    $definitions[$serviceDefinition->type()->name()]->constructorParameter($param, $value);
                }
            } else {
                $definitions[$serviceDefinition->type()->name()] = function (Container $container) use($state, $serviceDelegateDefinition, $parameterResolver) : object {
                     return $container->call(
                         [$serviceDelegateDefinition->classMethod()->class()->name(), $serviceDelegateDefinition->classMethod()->methodName()],
                         $parameterResolver->resolveParametersForServiceDelegate($container, $state, $serviceDelegateDefinition),
                     );
                };
            }

            $name = $serviceDefinition->name();
            if ($name !== null) {
                $definitions[$name] = get($serviceDefinition->type()->name());
            }

            if ($serviceDefinition->isAbstract()) {
                $alias = $state->resolveAliasDefinitionForAbstractService($serviceDefinition);
                if ($alias !== null) {
                    $definitions[$serviceDefinition->type()->name()] = get($alias->name());
                }
            }

            $servicePrepares = $state->servicePrepareDefinitionsForServiceDefinition($serviceDefinition);
            if ($servicePrepares !== []) {
                $servicePrepareDefinitions[$serviceDefinition->type()->name()] = decorate(function (object $object, Container $container) use($servicePrepares, $state, $parameterResolver) : mixed {
                    foreach ($servicePrepares as $servicePrepare) {
                        $container->call(
                            [$object, $servicePrepare->classMethod()->methodName()],
                            $parameterResolver->resolveParametersForServicePrepare($container, $state, $servicePrepare),
                        );
                    }

                    return $object;
                });
            }
        }

        $containerBuilder->addDefinitions($definitions);
        $containerBuilder->addDefinitions($servicePrepareDefinitions);

        return new class($containerBuilder->build(), $state, array_values(array_keys($definitions))) implements AnnotatedContainer {

            private readonly array $knownServices;

            public function __construct(
                private readonly Container $container,
                ContainerFactoryState $state,
                array $knownServices
            ) {
                $this->container->set(AutowireableFactory::class, $this);
                $this->container->set(AutowireableInvoker::class, $this);
                $this->container->set(Profiles::class, $state->activeProfiles());

                $knownServices[] = AutowireableFactory::class;
                $knownServices[] = AutowireableInvoker::class;
                $knownServices[] = Profiles::class;

                $this->knownServices = $knownServices;
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                $object = $this->container->make(
                    $classType,
                    $this->convertAutowireableParameterSet($parameters)
                );
                assert($object instanceof $classType);

                return $object;
            }

            /**
             * @psalm-template T of object
             * @psalm-param class-string<T>|non-empty-string $id
             * @psalm-return ($id is class-string<T> ? T : mixed)
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
                return in_array($id, $this->knownServices, true);
            }

            public function backingContainer() : Container {
                return $this->container;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->container->call(
                    $callable,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            /**
             * @param AutowireableParameterSet|null $parameters
             * @return array<non-empty-string, Reference|mixed>
             */
            private function convertAutowireableParameterSet(AutowireableParameterSet $parameters = null) : array {
                /** @var array<non-empty-string, Reference|mixed> $params */
                $params = [];
                if (!is_null($parameters)) {
                    foreach ($parameters as $parameter) {
                        /** @var Reference|mixed $value */
                        $value = $parameter->value();
                        if ($parameter->isServiceIdentifier()) {
                            $value = get($value->name());
                        }

                        $params[$parameter->name()] = $value;
                    }
                }
                return $params;
            }
        };
    }

    protected function injectParameterValueProvider() : InjectParameterValueProvider {
        return new class implements InjectParameterValueProvider {

            public function resolveInjectParameterValue(object $container, ContainerFactoryState $state, InjectDefinition $injectDefinition) : InjectParameterValue {
                $value = $injectDefinition->value();

                if ($value instanceof ContainerReference) {
                    $value = get($value->name);
                } elseif ($value instanceof ServiceCollectorReference) {
                    $value = factory(fn(Container $container) : mixed => $state->serviceCollectorReferenceToListOfServices(
                        $value,
                        $injectDefinition,
                        $container->get(...),
                    ));
                } elseif ($value instanceof ValueFetchedFromParameterStore) {
                    $value = factory($value->get(...));
                }

                return new InjectParameterValue($injectDefinition->classMethodParameter()->parameterName(), $value);
            }
        };
    }
}
