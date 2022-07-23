<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Auryn\InjectionException;
use Auryn\Injector;
use Cspray\AnnotatedContainer\ActiveProfiles;
use Cspray\AnnotatedContainer\AliasDefinition;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\AutowireableFactory;
use Cspray\AnnotatedContainer\AutowireableInvoker;
use Cspray\AnnotatedContainer\AutowireableParameter;
use Cspray\AnnotatedContainer\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\EnvironmentParameterStore;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\InvalidDefinitionException;
use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\AnnotatedContainer\Exception\ServiceNotFoundException;
use Cspray\AnnotatedContainer\HasBackingContainer;
use Cspray\AnnotatedContainer\ParameterStore;
use Cspray\AnnotatedContainer\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\Typiphy\ObjectType;
use Psr\Container\ContainerInterface;

// @codeCoverageIgnoreStart
if (!class_exists(Injector::class)) {
    throw new \RuntimeException("To enable the AurynContainerFactory please install rdlowrey/auryn!");
}
// @codeCoverageIgnoreEnd

/**
 * A ContainerFactory that utilizes the rdlowrey/auryn Container as its backing implementation.
 */
final class AurynContainerFactory implements ContainerFactory {

    /**
     * @var ParameterStore[]
     */
    private array $parameterStores = [];

    public function __construct() {
        // Injecting environment variables is something we have supported since early versions.
        // We don't require adding this parameter store explicitly to continue providing this functionality
        // without the end-user having to change how they construct their ContainerFactory.
        $this->addParameterStore(new EnvironmentParameterStore());
    }

    /**
     * Add a custom ParameterStore, allowing you to Inject arbitrary values into your Services.
     *
     * @param ParameterStore $parameterStore
     * @return void
     * @see Inject
     */
    public function addParameterStore(ParameterStore $parameterStore): void {
        $this->parameterStores[$parameterStore->getName()] = $parameterStore;
    }

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
            $injector = $this->createInjector(
                new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles),
                $nameTypeMap
            );
            $activeProfiles = new class($activeProfiles) implements ActiveProfiles {

                public function __construct(
                    private readonly array $profiles
                ) {}

                public function getProfiles() : array {
                    return $this->profiles;
                }

                public function isActive(string $profile) : bool {
                    return in_array($profile, $this->profiles);
                }
            };

            return $this->getAnnotatedContainer($injector, $nameTypeMap, $activeProfiles);
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
        // We need to keep a nameTypeMap because Auryn does not support arbitrarily named services out of the box

        $servicePrepareDefinitions = $containerDefinition->getServicePrepareDefinitions();
        $serviceDelegateDefinitions = $containerDefinition->getServiceDelegateDefinitions();

        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $injector->share($serviceDefinition->getType()->getName());
            $name = $serviceDefinition->getName();
            if (!is_null($name)) {
                $nameTypeMap[$name] = $serviceDefinition->getType();
            }
        }

        foreach ($containerDefinition->getConfigurationDefinitions() as $configurationDefinition) {
            $injector->share($configurationDefinition->getClass()->getName());
            $name = $configurationDefinition->getName();
            if (!is_null($name)) {
                $nameTypeMap[$name] = $configurationDefinition->getClass();
            }
            $injector->delegate($configurationDefinition->getClass()->getName(), function() use ($containerDefinition, $configurationDefinition) {
                /** @var class-string $configurationClass */
                $configurationClass = $configurationDefinition->getClass()->getName();
                $configReflection = (new \ReflectionClass($configurationClass));
                $configInstance = $configReflection->newInstanceWithoutConstructor();
                foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
                    if ($injectDefinition->getTargetIdentifier()->isMethodParameter() ||
                        $injectDefinition->getTargetIdentifier()->getClass() !== $configurationDefinition->getClass()) {
                        continue;
                    }

                    $reflectionProperty = $configReflection->getProperty($injectDefinition->getTargetIdentifier()->getName());
                    $value = $injectDefinition->getValue();
                    $storeName = $injectDefinition->getStoreName();
                    if (!is_null($storeName)) {
                        $value = $this->parameterStores[$storeName]->fetch($injectDefinition->getType(), $value);
                    }
                    $reflectionProperty->setValue($configInstance, $value);
                }
                return $configInstance;
            });
        }

        $aliasedTypes = [];
        $aliasDefinitions = $containerDefinition->getAliasDefinitions();
        foreach ($aliasDefinitions as $aliasDefinition) {
            if (!in_array($aliasDefinition->getAbstractService(), $aliasedTypes)) {
                $typeAliasDefinitions = $this->mapTypesAliasDefinitions($containerDefinition, $aliasDefinition->getAbstractService(), $aliasDefinitions);
                $aliasDefinition = null;
                if (count($typeAliasDefinitions) === 1) {
                    $aliasDefinition = $typeAliasDefinitions[0];
                } else {
                    /** @var AliasDefinition $typeAliasDefinition */
                    foreach ($typeAliasDefinitions as $typeAliasDefinition) {
                        if ($this->getServiceDefinition($containerDefinition, $typeAliasDefinition->getConcreteService())?->isPrimary()) {
                            $aliasDefinition = $typeAliasDefinition;
                            break;
                        }
                    }
                }

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
                $preparedTypes[] = $type;
            }
        }
        unset($preparedTypes);

        foreach ($serviceDelegateDefinitions as $serviceDelegateDefinition) {
            $injector->delegate(
                $serviceDelegateDefinition->getServiceType()->getName(),
                [$serviceDelegateDefinition->getDelegateType()->getName(), $serviceDelegateDefinition->getDelegateMethod()]
            );
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
            if ($injectDefinition->getType() instanceof ObjectType) {
                $key = $injectDefinition->getTargetIdentifier()->getName();
                if (isset($nameTypeMap[$value])) {
                    $value = $nameTypeMap[$value]->getName();
                }
            } else {
                $key = ':' . $injectDefinition->getTargetIdentifier()->getName();
            }

            $storeName = $injectDefinition->getStoreName();
            if (!is_null($storeName)) {
                $parameterStore = $this->parameterStores[$storeName] ?? null;
                if (is_null($parameterStore)) {
                    throw new InvalidParameterException(sprintf(
                        'The ParameterStore "%s" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.',
                        $storeName
                    ));
                }
                $value = $parameterStore->fetch($injectDefinition->getType(), $value);
            }
            $definitionMap[$serviceType][$method][$key] = $value;
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

    private function mapTypesAliasDefinitions(ContainerDefinition $containerDefinition, ObjectType $serviceDefinition, array $aliasDefinitions) : array {
        $aliases = [];
        /** @var AliasDefinition $aliasDefinition */
        foreach ($aliasDefinitions as $aliasDefinition) {
            if ($aliasDefinition->getAbstractService() === $serviceDefinition) {
                $aliases[] = $aliasDefinition;
            }
        }
        return $aliases;
    }

    private function getServiceDefinition(ContainerDefinition $containerDefinition, ObjectType $objectType) : ?ServiceDefinition {
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType() === $objectType) {
                return $serviceDefinition;
            }
        }

        return null;
    }

}