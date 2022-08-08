<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Auryn\InjectionException;
use Auryn\Injector;
use Cspray\AnnotatedContainer\ActiveProfiles;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\AutowireableFactory;
use Cspray\AnnotatedContainer\AutowireableParameter;
use Cspray\AnnotatedContainer\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\InvalidDefinitionException;
use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\AnnotatedContainer\Exception\ServiceNotFoundException;
use Cspray\AnnotatedContainer\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use Cspray\Typiphy\ObjectType;
use UnitEnum;
use function Cspray\Typiphy\objectType;

// @codeCoverageIgnoreStart
if (!class_exists(Injector::class)) {
    throw new \RuntimeException("To enable the AurynContainerFactory please install rdlowrey/auryn!");
}
// @codeCoverageIgnoreEnd

/**
 * A ContainerFactory that utilizes the rdlowrey/auryn Container as its backing implementation.
 */
final class AurynContainerFactory extends AbstractContainerFactory implements ContainerFactory {

    /**
     * Returns a PSR ContainerInterface that uses an Auryn\Injector to create services.
     *
     * Because Auryn does not provide a PSR compatible Container we wrap the injector in an anonymous class that
     * implements the PSR ContainerInterface. Auryn has the capacity to recursively autowire Services at time of
     * construction and does not necessarily need to have the Service defined ahead of time if the constructor
     * dependencies can be reliably determined. This fact makes the has() method for this particular Container a little
     * tricky in that a service could be successfully constructed but if we don't have something specifically defined
     * stating how to construct some aspect of it we can't reliably determine whether or not the Container "has" the
     * Service.
     *
     * This limitation should be short-lived as the Auryn Injector is being migrated to a new organization and codebase.
     * Once that migration has been completed a new ContainerFactory using that implementation will be used and this
     * implementation will be deprecated.
     */
    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        $activeProfiles = is_null($containerFactoryOptions) ? ['default'] : $containerFactoryOptions->getActiveProfiles();
        $nameTypeMap = [];
        try {
            $this->logCreatingContainer(objectType(Injector::class), $activeProfiles);
            $injector = $this->createInjector(
                new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles),
                $nameTypeMap
            );
            return $this->getAnnotatedContainer($injector, $nameTypeMap, $this->getActiveProfilesService($activeProfiles));
        } catch (InvalidDefinitionException $exception) {
            throw new ContainerException($exception->getMessage(), previous: $exception);
        }
    }

    private function getAnnotatedContainer(
        Injector $injector,
        array $nameTypeMap,
        ActiveProfiles $activeProfiles
    ) : AnnotatedContainer {
        return new class($injector, $nameTypeMap, $activeProfiles) implements AnnotatedContainer {

            public function __construct(
                private readonly Injector $injector,
                private readonly array $nameTypeMap,
                ActiveProfiles $activeProfiles
            ) {
                $this->injector->delegate(AutowireableFactory::class, fn() => $this);
                $this->injector->delegate(ActiveProfiles::class, fn() => $activeProfiles);
            }

            public function get(string $id) {
                try {
                    if (!$this->has($id)) {
                        throw new ServiceNotFoundException(sprintf(
                            'The service "%s" could not be found in this container.',
                            $id
                        ));
                    }

                    if (isset($this->nameTypeMap[$id])) {
                        $id = $this->nameTypeMap[$id]->getName();
                    }
                    return $this->injector->make($id);
                } catch (InjectionException $injectionException) {
                    throw new ContainerException(
                        sprintf('An error was encountered creating %s', $id),
                        previous: $injectionException
                    );
                }
            }

            public function has(string $id): bool {
                if (isset($this->nameTypeMap[$id])) {
                    return true;
                }

                $anyDefined = 0;
                foreach ($this->injector->inspect($id) as $definitions) {
                    $anyDefined += count($definitions);
                }
                return $anyDefined > 0;
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                return $this->injector->make(
                    $classType,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            public function getBackingContainer() : Injector {
                return $this->injector;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->injector->execute(
                    $callable,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            private function convertAutowireableParameterSet(AutowireableParameterSet $parameters = null) : array {
                $params = [];
                if (!is_null($parameters)) {
                    /** @var AutowireableParameter $parameter */
                    foreach ($parameters as $parameter) {
                        $name = $parameter->isServiceIdentifier() ? $parameter->getName() : ':' . $parameter->getName();
                        $params[$name] = $parameter->isServiceIdentifier() ? $parameter->getValue()->getName() : $parameter->getValue();
                    }
                }
                return $params;
            }
        };
    }

    private function createInjector(ContainerDefinition $containerDefinition, array &$nameTypeMap) : Injector {
        $injector = new Injector();

        $servicePrepareDefinitions = $containerDefinition->getServicePrepareDefinitions();
        $serviceDelegateDefinitions = $containerDefinition->getServiceDelegateDefinitions();

        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $injector->share($serviceDefinition->getType()->getName());
            $this->logServiceShared($serviceDefinition);
            $name = $serviceDefinition->getName();
            if (!is_null($name)) {
                $nameTypeMap[$name] = $serviceDefinition->getType();
                $this->logServiceNamed($serviceDefinition);
            }
        }

        foreach ($containerDefinition->getConfigurationDefinitions() as $configurationDefinition) {
            $injector->share($configurationDefinition->getClass()->getName());
            $this->logConfigurationShared($configurationDefinition);
            $name = $configurationDefinition->getName();
            if (!is_null($name)) {
                $nameTypeMap[$name] = $configurationDefinition->getClass();
                $this->logConfigurationNamed($configurationDefinition);
            }
            $injectPropertyMap = [];
            foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
                if ($injectDefinition->getTargetIdentifier()->isMethodParameter() ||
                    $injectDefinition->getTargetIdentifier()->getClass() !== $configurationDefinition->getClass()) {
                    continue;
                }

                $this->logInjectingProperty($injectDefinition);

                $value = $injectDefinition->getValue();
                $storeName = $injectDefinition->getStoreName();
                if ($storeName !== null) {
                    $store = $this->getParameterStore($storeName);
                    if ($store === null) {
                        throw new InvalidParameterException(sprintf(
                            'The ParameterStore "%s" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.',
                            $storeName
                        ));
                    }
                    $value = $store->fetch($injectDefinition->getType(), $value);
                }

                $injectPropertyMap[$injectDefinition->getTargetIdentifier()->getName()] = $value;

            }

            $injector->delegate($configurationDefinition->getClass()->getName(), function() use ($injectPropertyMap, $configurationDefinition) {
                /** @var class-string $configurationClass */
                $configurationClass = $configurationDefinition->getClass()->getName();
                $configReflection = (new \ReflectionClass($configurationClass));
                $configInstance = $configReflection->newInstanceWithoutConstructor();
                foreach ($injectPropertyMap as $prop => $value) {
                    $reflectionProperty = $configReflection->getProperty($prop);
                    $reflectionProperty->setValue($configInstance, $value);
                }
                return $configInstance;
            });
        }

        // We need to keep track of which abstract types we have aliased
        // It is possible that there are multiple alias definitions for the
        // abstract service. In that case we only want to attempt a resolution
        // one time. Attempting to resolve the abstract class many times over
        // could have untindended consequences and is a waste of cycles
        $aliasedTypes = [];
        $aliasDefinitions = $containerDefinition->getAliasDefinitions();
        foreach ($aliasDefinitions as $aliasDefinition) {
            if (!in_array($aliasDefinition->getAbstractService(), $aliasedTypes)) {
                $resolution = $this->aliasDefinitionResolver->resolveAlias(
                    $containerDefinition, $aliasDefinition->getAbstractService()
                );
                $this->logAliasingService($resolution, $aliasDefinition->getAbstractService());

                $aliasDefinition = $resolution->getAliasDefinition();
                if (isset($aliasDefinition)) {
                    $injector->alias(
                        $aliasDefinition->getAbstractService()->getName(),
                        $aliasDefinition->getConcreteService()->getName()
                    );
                }
            }
        }
        unset($aliasedTypes);

        $definitionMap = $this->mapInjectDefinitions($containerDefinition, $nameTypeMap);
        foreach ($definitionMap as $service => $methods) {
            if (array_key_exists('__construct', $methods)) {
                $injector->define($service, $methods['__construct']);
            }
        }

        $preparedTypes = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            $type = $servicePrepareDefinition->getService();
            if (!in_array($type, $preparedTypes)) {
                $injector->prepare(
                    $type->getName(),
                    function(object $object) use($servicePrepareDefinitions, $servicePrepareDefinition, $injector, $type, $definitionMap) {
                        $methods = $this->mapTypesServicePrepares($type, $servicePrepareDefinitions);
                        foreach ($methods as $method) {
                            $params = $definitionMap[$type->getName()][$method] ?? [];
                            $injector->execute([$object, $method], $params);
                        }
                    }
                );
                $this->logServicePrepare($servicePrepareDefinition);
                $preparedTypes[] = $type;
            }
        }
        unset($preparedTypes);

        foreach ($serviceDelegateDefinitions as $serviceDelegateDefinition) {
            $injector->delegate(
                $serviceDelegateDefinition->getServiceType()->getName(),
                [$serviceDelegateDefinition->getDelegateType()->getName(), $serviceDelegateDefinition->getDelegateMethod()]
            );
            $this->logServiceDelegate($serviceDelegateDefinition);
        }

        return $injector;
    }

    private function mapInjectDefinitions(ContainerDefinition $containerDefinition, array $nameTypeMap) : array {
        $definitionMap = [];
        foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
            $method = $injectDefinition->getTargetIdentifier()->getMethodName();
            if (is_null($method)) {
                continue;
            }

            $serviceType = $injectDefinition->getTargetIdentifier()->getClass()->getName();
            if (!isset($definitionMap[$serviceType])) {
                $definitionMap[$serviceType] = [];
            }

            if (!isset($definitionMap[$serviceType][$method])) {
                $definitionMap[$serviceType][$method] = [];
            }

            $value = $injectDefinition->getValue();
            if ($injectDefinition->getType() instanceof ObjectType && !is_a($injectDefinition->getType()->getName(), UnitEnum::class, true)) {
                $key = $injectDefinition->getTargetIdentifier()->getName();
                if (isset($nameTypeMap[$value])) {
                    $value = $nameTypeMap[$value]->getName();
                }
            } else {
                $key = ':' . $injectDefinition->getTargetIdentifier()->getName();
            }

            $storeName = $injectDefinition->getStoreName();
            if (!is_null($storeName)) {
                $parameterStore = $this->getParameterStore($storeName);
                if (is_null($parameterStore)) {
                    throw new InvalidParameterException(sprintf(
                        'The ParameterStore "%s" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.',
                        $storeName
                    ));
                }
                $value = $parameterStore->fetch($injectDefinition->getType(), $value);
            }
            $definitionMap[$serviceType][$method][$key] = $value;
            $this->logInjectingMethodParameter($injectDefinition);
        }
        return $definitionMap;
    }

    private function mapTypesServicePrepares(ObjectType $type, array $servicePreparesDefinition) : array {
        $methods = [];
        /** @var ServicePrepareDefinition $servicePrepareDefinition */
        foreach ($servicePreparesDefinition as $servicePrepareDefinition) {
            if ($servicePrepareDefinition->getService() === $type) {
                $methods[] = $servicePrepareDefinition->getMethod();
            }
        }
        return $methods;
    }

}