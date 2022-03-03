<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Auryn\InjectionException;
use Auryn\Injector;
use Cspray\AnnotatedContainer\Exception\ContainerException;
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
     * @return ContainerInterface
     */
    public function createContainer(ContainerDefinition $containerDefinition) : ContainerInterface {
        return new class($this->createInjector($containerDefinition)) implements ContainerInterface {

            private Injector $injector;

            public function __construct(Injector $injector) {
                $this->injector = $injector;
            }

            public function get(string $id) {
                try {
                    return $this->injector->make($id);
                } catch (InjectionException $injectionException) {
                    throw new ContainerException(
                        sprintf('An error was encountered creating %s', $id),
                        previous: $injectionException
                    );
                }
            }

            public function has(string $id): bool {
                $anyDefined = 0;
                foreach ($this->injector->inspect($id) as $definitions) {
                    $anyDefined += count($definitions);
                }
                return $anyDefined > 0;
            }
        };
    }

    private function createInjector(ContainerDefinition $containerDefinition) : Injector {
        $injector = new Injector();
        $servicePrepareDefinitions = $containerDefinition->getServicePrepareDefinitions();
        $useServiceDefinitions = $containerDefinition->getInjectServiceDefinitions();
        $useScalarDefinitions = $containerDefinition->getInjectScalarDefinitions();
        $serviceDelegateDefinitions = $containerDefinition->getServiceDelegateDefinitions();

        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $injector->share($serviceDefinition->getType());
        }

        $aliasedTypes = [];
        $aliasDefinitions = $containerDefinition->getAliasDefinitions();
        foreach ($aliasDefinitions as $aliasDefinition) {
            if (!in_array($aliasDefinition->getAbstractService(), $aliasedTypes)) {
                // We are intentionally taking the stance that if there are more than 1 alias possible that it is up
                // to the developer to properly instantiate the Service. The caller could presume to provide a specific
                // parameter to the make() call or could potentially have another piece of code that interacts with the
                // Injector to define these kind of parameters
                $typeAliasDefinitions = self::mapTypesAliasDefinitions($aliasDefinition->getAbstractService()->getType(), $aliasDefinitions);
                if (count($typeAliasDefinitions) === 1) {
                    $injector->alias(
                        $typeAliasDefinitions[0]->getAbstractService()->getType(),
                        $typeAliasDefinitions[0]->getConcreteService()->getType()
                    );
                }
            }
        }

        $preparedTypes = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            $type = $servicePrepareDefinition->getService()->getType();
            if (!in_array($type, $preparedTypes)) {
                $injector->prepare($type, function($object) use($servicePrepareDefinitions, $servicePrepareDefinition, $injector, $useScalarDefinitions, $useServiceDefinitions, $type) {
                    $methods = self::mapTypesServicePrepares($type, $servicePrepareDefinitions);
                    foreach ($methods as $method) {
                        $scalarArgs = self::mapTypesScalarArgs($type, $method, $useScalarDefinitions);
                        $serviceArgs = self::mapTypesServiceArgs($type, $method, $useServiceDefinitions);
                        $injector->execute([$object, $method], array_merge([], $scalarArgs, $serviceArgs));
                    }
                });
                $preparedTypes[] = $type;
            }
        }

        $typeArgsMap = [];
        foreach ($useScalarDefinitions as $useScalarDefinition) {
            $type = $useScalarDefinition->getService()->getType();
            if (!isset($typeArgsMap[$type])) {
                $typeArgsMap[$type] = self::mapTypesScalarArgs($type, '__construct', $useScalarDefinitions);
            }
        }

        foreach ($useServiceDefinitions as $useServiceDefinition) {
            $type = $useServiceDefinition->getService()->getType();
            $defineArgs = self::mapTypesServiceArgs($type, '__construct', $useServiceDefinitions);
            if (isset($typeArgsMap[$type])) {
                $typeArgsMap[$type] = array_merge($typeArgsMap[$type], $defineArgs);
            } else {
                $typeArgsMap[$type] = $defineArgs;
            }
        }

        foreach ($typeArgsMap as $type => $args) {
            $injector->define($type, $args);
        }

        foreach ($serviceDelegateDefinitions as $serviceDelegateDefinition) {
            $injector->delegate(
                $serviceDelegateDefinition->getServiceType()->getType(),
                [$serviceDelegateDefinition->getDelegateType(), $serviceDelegateDefinition->getDelegateMethod()]
            );
        }

        return $injector;
    }

    private static function mapTypesScalarArgs(string $type, string $method, array $UseScalarDefinitions) : array {
        $args = [];
        /** @var InjectScalarDefinition $UseScalarDefinition */
        foreach ($UseScalarDefinitions as $UseScalarDefinition) {
            if ($UseScalarDefinition->getService()->getType() === $type && $UseScalarDefinition->getMethod() === $method) {
                $value = $UseScalarDefinition->getValue();
                $constRegex = '/^\!const\((.+)\)$/';
                $envRegex = '/^\!env\((.+)\)$/';
                if (is_string($value) && preg_match($constRegex, $value, $constMatches) === 1) {
                    $value = constant($constMatches[1]);
                } else if (is_string($value) && preg_match($envRegex, $value, $envMatches) === 1) {
                    $value = getenv($envMatches[1]);
                }
                $args[':' . $UseScalarDefinition->getParamName()] = $value;
            }
        }
        return $args;
    }

    private static function mapTypesServiceArgs(string $type, string $method, array $UseServiceDefinitions) : array {
        $args = [];
        /** @var InjectServiceDefinition $UseServiceDefinition */
        foreach ($UseServiceDefinitions as $UseServiceDefinition) {
            if ($UseServiceDefinition->getService()->getType() === $type && $UseServiceDefinition->getMethod() === $method) {
                $args[$UseServiceDefinition->getParamName()] = $UseServiceDefinition->getInjectedService()->getType();
            }
        }
        return $args;
    }

    static private function mapTypesServicePrepares(string $type, array $servicePreparesDefinition) : array {
        $methods = [];
        /** @var ServicePrepareDefinition $servicePrepareDefinition */
        foreach ($servicePreparesDefinition as $servicePrepareDefinition) {
            if ($servicePrepareDefinition->getService()->getType() === $type) {
                $methods[] = $servicePrepareDefinition->getMethod();
            }
        }
        return $methods;
    }

    static private function mapTypesAliasDefinitions(string $type, array $aliasDefinitions) : array {
        $aliases = [];
        /** @var AliasDefinition $aliasDefinition */
        foreach ($aliasDefinitions as $aliasDefinition) {
            if ($aliasDefinition->getAbstractService()->getType() === $type) {
                $aliases[] = $aliasDefinition;
            }
        }
        return $aliases;
    }

}