<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\Illuminate;

use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerFactoryState;
use Cspray\AnnotatedContainer\ContainerFactory\State\ParameterResolver;
use Cspray\AnnotatedContainer\Profiles;
use Illuminate\Contracts\Container\Container;
use Closure;

final class IlluminateContainerBinder {

    public function __construct(
        private readonly Container $container,
        private readonly ContainerFactoryState $state,
        private readonly ParameterResolver $parameterResolver
    ) {
    }

    public function bindDependencies() : void {
        foreach ($this->state->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->isAbstract()) {
                $aliasedType = $this->state->resolveAliasDefinitionForAbstractService($serviceDefinition);
                if ($aliasedType !== null) {
                    $this->container->singleton($serviceDefinition->type()->name(), $aliasedType->name());
                }
            } else {
                $this->container->singleton($serviceDefinition->type()->name());
            }

            $name = $serviceDefinition->name();
            if ($name !== null) {
                $this->container->alias($serviceDefinition->type()->name(), $name);
            }
            $serviceConstructorParameters = $this->parameterResolver->resolveParametersForServiceConstructor(
                $this->container,
                $this->state,
                $serviceDefinition
            );
            foreach ($serviceConstructorParameters as $key => $value) {
                $this->container->when($serviceDefinition->type()->name())->needs($key)->give($value);
            }

            $servicePrepares = $this->state->servicePrepareDefinitionsForServiceDefinition($serviceDefinition);
            if ($servicePrepares !== []) {
                $this->container->afterResolving($serviceDefinition->type()->name(), function(object $object) use($servicePrepares) : void {
                    foreach ($servicePrepares as $servicePrepare) {
                        $this->container->call(
                            [$object, $servicePrepare->classMethod()->methodName()],
                            array_map(
                                static fn(Closure $closure) => $closure(),
                                $this->parameterResolver->resolveParametersForServicePrepare(
                                    $this->container,
                                    $this->state,
                                    $servicePrepare
                                )
                            ),
                        );
                    }
                });
            }
        }

        foreach ($this->state->serviceDelegateDefinitions() as $serviceDelegateDefinition) {
            $this->container->singleton(
                $serviceDelegateDefinition->service()->name(),
                function (Container $container) use($serviceDelegateDefinition) : object {
                    if ($serviceDelegateDefinition->classMethod()->isStatic()) {
                        $target = $serviceDelegateDefinition->classMethod()->class()->name();
                    } else {
                        $target = $container->get($serviceDelegateDefinition->classMethod()->class()->name());
                    }

                    return $container->call(
                        [$target, $serviceDelegateDefinition->classMethod()->methodName()],
                        array_map(
                            static fn(Closure $closure) => $closure(),
                            $this->parameterResolver->resolveParametersForServiceDelegate(
                                $container,
                                $this->state,
                                $serviceDelegateDefinition
                            )
                        ),
                    );
                }
            );
        }

        $this->container->instance(Profiles::class, $this->state->activeProfiles());
    }
}
