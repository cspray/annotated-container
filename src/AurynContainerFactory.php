<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Auryn\InjectionException;
use Auryn\Injector;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\Typiphy\ObjectType;
use Psr\Container\ContainerInterface;

/**
 * Creates a PSR Container from a ContainerDefinition backed by an Auryn\Injector.
 */
final class AurynContainerFactory implements ContainerFactory {

    /**
     * @var ParameterStore[]
     */
    private array $parameterStores = [];

    public function __construct() {
        $this->addParameterStore(new EnvironmentParameterStore());
    }

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
     *
     * @param ContainerDefinition $containerDefinition
     * @param ContainerFactoryOptions|null $containerFactoryOptions
     * @return ContainerInterface
     */
    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : ContainerInterface {
        $activeProfiles = is_null($containerFactoryOptions) ? ['default'] : $containerFactoryOptions->getActiveProfiles();
        $nameTypeMap = [];
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if (!is_null($serviceDefinition->getName())) {
                $nameTypeMap[$serviceDefinition->getName()] = $serviceDefinition->getType();
            }
        }

        return new class($this->createInjector($containerDefinition, $activeProfiles), $nameTypeMap) implements ContainerInterface {

            public function __construct(private readonly Injector $injector, private readonly array $nameTypeMap) {}

            public function get(string $id) {
                try {
                    if (isset($this->nameTypeMap[$id])) {
                        $id = $this->nameTypeMap[$id];
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
        };
    }

    private function createInjector(ContainerDefinition $containerDefinition, array $activeProfiles) : Injector {
        $injector = new Injector();
        $servicePrepareDefinitions = $containerDefinition->getServicePrepareDefinitions();
        $serviceDelegateDefinitions = $containerDefinition->getServiceDelegateDefinitions();

        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $injector->share($serviceDefinition->getType()->getName());
        }

        foreach ($containerDefinition->getConfigurationDefinitions() as $configurationDefinition) {
            $injector->share($configurationDefinition->getClass()->getName());
            $injector->delegate($configurationDefinition->getClass()->getName(), function() use ($containerDefinition, $configurationDefinition, $activeProfiles) {
                $configReflection = (new \ReflectionClass($configurationDefinition->getClass()->getName()));
                $configInstance = $configReflection->newInstanceWithoutConstructor();
                foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
                    $injectProfiles = $injectDefinition->getProfiles();
                    if (empty($injectProfiles)) {
                        $injectProfiles[] = 'default';
                    }
                    if ($injectDefinition->getTargetIdentifier()->isMethodParameter() ||
                        $injectDefinition->getTargetIdentifier()->getClass() !== $configurationDefinition->getClass() ||
                        empty(array_intersect($activeProfiles, $injectProfiles))) {
                        continue;
                    }

                    $reflectionProperty = $configReflection->getProperty($injectDefinition->getTargetIdentifier()->getName());
                    $value = $injectDefinition->getValue();
                    if (!is_null($injectDefinition->getStoreName())) {
                        $value = $this->parameterStores[$injectDefinition->getStoreName()]->fetch($injectDefinition->getType(), $value);
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
                    $injector->alias(
                        $aliasDefinition->getAbstractService()->getName(),
                        $aliasDefinition->getConcreteService()->getName()
                    );
                }
            }
        }
        unset($aliasedTypes);

        $definitionMap = $this->mapInjectDefinitions($containerDefinition, $activeProfiles);
        foreach ($definitionMap as $service => $methods) {
            if (array_key_exists('__construct', $methods)) {
                $injector->define($service, $methods['__construct']);
            }
        }

        $preparedTypes = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            $type = $servicePrepareDefinition->getService();
            if (!in_array($type, $preparedTypes)) {
                $injector->prepare($type, function($object) use($servicePrepareDefinitions, $servicePrepareDefinition, $injector, $type, $activeProfiles, $definitionMap) {
                    $methods = $this->mapTypesServicePrepares($type, $servicePrepareDefinitions);
                    foreach ($methods as $method) {
                        $params = $definitionMap[$type->getName()][$method] ?? [];
                        $injector->execute([$object, $method], $params);
                    }
                });
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

    private function mapInjectDefinitions(ContainerDefinition $containerDefinition, array $activeProfiles) : array {
        $definitionMap = [];
        foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
            $injectProfiles = empty($injectDefinition->getProfiles()) ? ['default'] : $injectDefinition->getProfiles();
            if (empty(array_intersect($activeProfiles, $injectProfiles))) {
                continue;
            }

            $serviceType = $injectDefinition->getTargetIdentifier()->getClass()->getName();
            if (!isset($definitionMap[$serviceType])) {
                $definitionMap[$serviceType] = [];
            }

            $method = $injectDefinition->getTargetIdentifier()->getMethodName();
            if (!isset($definitionMap[$serviceType][$method])) {
                $definitionMap[$serviceType][$method] = [];
            }

            if ($injectDefinition->getType() instanceof ObjectType) {
                $key = $injectDefinition->getTargetIdentifier()->getName();
            } else {
                $key = ':' . $injectDefinition->getTargetIdentifier()->getName();
            }
            $value = $injectDefinition->getValue();
            if (!is_null($injectDefinition->getStoreName())) {
                $parameterStore = $this->parameterStores[$injectDefinition->getStoreName()] ?? null;
                if (is_null($parameterStore)) {
                    throw new InvalidParameterException(sprintf(
                        'The ParameterStore "%s" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.',
                        $injectDefinition->getStoreName()
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

    private function getServiceDefinition(ContainerDefinition $containerDefinition, ObjectType $objectType) : ?ServiceDefinition {
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType() === $objectType) {
                return $serviceDefinition;
            }
        }

        return null;
    }

}