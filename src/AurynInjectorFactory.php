<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Auryn\Injector;
use Psr\Container\ContainerInterface;

/**
 * Wires together an Injector from an ContainerDefinition or a JSON serialization of an ContainerDefinition.
 *
 * @package Cspray\AnnotatedContainer
 */
final class AurynInjectorFactory implements ContainerFactory {

    public function createContainer(ContainerDefinition $containerDefinition) : ContainerInterface {
        throw new \RuntimeException('Not Yet Implemented. This method will be implemented in a future commit.');
    }

    public function createInjector(ContainerDefinition $containerDefinition) : Injector {
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
            if (!in_array($servicePrepareDefinition->getType(), $preparedTypes)) {
                $injector->prepare($servicePrepareDefinition->getType(), function($object) use($servicePrepareDefinitions, $servicePrepareDefinition, $injector, $useScalarDefinitions, $useServiceDefinitions) {
                    $methods = self::mapTypesServicePrepares($servicePrepareDefinition->getType(), $servicePrepareDefinitions);
                    foreach ($methods as $method) {
                        $scalarArgs = self::mapTypesScalarArgs($servicePrepareDefinition->getType(), $method, $useScalarDefinitions);
                        $serviceArgs = self::mapTypesServiceArgs($servicePrepareDefinition->getType(), $method, $useServiceDefinitions);
                        $injector->execute([$object, $method], array_merge([], $scalarArgs, $serviceArgs));
                    }
                });
                $preparedTypes[] = $servicePrepareDefinition->getType();
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
                $serviceDelegateDefinition->getServiceType(),
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
            if ($servicePrepareDefinition->getType() === $type) {
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