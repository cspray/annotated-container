<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Auryn\InjectionException;
use Auryn\Injector;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\Typiphy\ObjectType;
use Psr\Container\ContainerInterface;

/**
 * Creates a PSR Container from a ContainerDefinition backed by an Auryn\Injector.
 */
final class AurynContainerFactory implements ContainerFactory {

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

        $aliasedTypes = [];
        $aliasDefinitions = $containerDefinition->getAliasDefinitions();
        foreach ($aliasDefinitions as $aliasDefinition) {
            if (!in_array($aliasDefinition->getAbstractService(), $aliasedTypes)) {
                $typeAliasDefinitions = self::mapTypesAliasDefinitions($containerDefinition, $aliasDefinition->getAbstractService(), $aliasDefinitions, $activeProfiles);
                $aliasDefinition = null;
                if (count($typeAliasDefinitions) === 1) {
                    $aliasDefinition = $typeAliasDefinitions[0];
                } else {
                    /** @var AliasDefinition $typeAliasDefinition */
                    foreach ($typeAliasDefinitions as $typeAliasDefinition) {
                        if (self::isServicePrimary($containerDefinition, $typeAliasDefinition->getConcreteService())) {
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

        $preparedTypes = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            $type = $servicePrepareDefinition->getService();
            if (!in_array($type, $preparedTypes)) {
                $injector->prepare($type, function($object) use($servicePrepareDefinitions, $servicePrepareDefinition, $injector, $type, $activeProfiles) {
                    $methods = self::mapTypesServicePrepares($type, $servicePrepareDefinitions);
                    foreach ($methods as $method) {
                        $injector->execute([$object, $method]);
                    }
                });
                $preparedTypes[] = $type;
            }
        }

        foreach ($serviceDelegateDefinitions as $serviceDelegateDefinition) {
            $injector->delegate(
                $serviceDelegateDefinition->getServiceType()->getName(),
                [$serviceDelegateDefinition->getDelegateType()->getName(), $serviceDelegateDefinition->getDelegateMethod()]
            );
        }

        return $injector;
    }

    static private function mapTypesServicePrepares(ObjectType $type, array $servicePreparesDefinition) : array {
        $methods = [];
        /** @var ServicePrepareDefinition $servicePrepareDefinition */
        foreach ($servicePreparesDefinition as $servicePrepareDefinition) {
            if ($servicePrepareDefinition->getService() === $type) {
                $methods[] = $servicePrepareDefinition->getMethod();
            }
        }
        return $methods;
    }

    static private function mapTypesAliasDefinitions(ContainerDefinition $containerDefinition, ObjectType $serviceDefinition, array $aliasDefinitions, array $activeProfiles) : array {
        $aliases = [];
        /** @var AliasDefinition $aliasDefinition */
        foreach ($aliasDefinitions as $aliasDefinition) {
            $concreteProfiles = self::getProfilesForService($containerDefinition, $aliasDefinition->getConcreteService());
            if (empty($concreteProfiles)) {
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

    static private function getProfilesForService(ContainerDefinition $containerDefinition, ObjectType $objectType) : array {
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType() === $objectType) {
                return $serviceDefinition->getProfiles();
            }
        }
        return [];
    }

    static private function isServicePrimary(ContainerDefinition $containerDefinition, ObjectType $objectType) : bool {
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType() === $objectType) {
                return $serviceDefinition->isPrimary();
            }
        }
        return false;
    }

}