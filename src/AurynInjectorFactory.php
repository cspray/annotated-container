<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Auryn\Injector;

/**
 * Wires together an Injector from an InjectorDefinition or a JSON serialization of an InjectorDefinition.
 *
 * @package Cspray\AnnotatedInjector
 */
final class AurynInjectorFactory implements InjectorFactory {

    public function createContainer(InjectorDefinition $injectorDefinition) : Injector {
        $injector = new Injector();
        $servicePrepareDefinitions = $injectorDefinition->getServicePrepareDefinitions();
        $defineServiceDefinitions = $injectorDefinition->getDefineServiceDefinitions();
        $defineScalarDefinitions = $injectorDefinition->getDefineScalarDefinitions();

        foreach ($injectorDefinition->getSharedServiceDefinitions() as $serviceDefinition) {
            $injector->share($serviceDefinition->getType());
        }

        $aliasedTypes = [];
        $aliasDefinitions = $injectorDefinition->getAliasDefinitions();
        foreach ($aliasDefinitions as $aliasDefinition) {
            if (!in_array($aliasDefinition->getOriginalServiceDefinition(), $aliasedTypes)) {
                // We are intentionally taking the stance that if there are more than 1 alias possible that it is up
                // to the developer to properly instantiate the Service. The caller could presume to provide a specific
                // parameter to the make() call or could potentially have another piece of code that interacts with the
                // Injector to define these kind of parameters
                // TODO: Determine if we want to add a strict mode that warns/fails when multiple aliases were resolved
                $typeAliasDefinitions = self::mapTypesAliasDefinitions($aliasDefinition->getOriginalServiceDefinition()->getType(), $aliasDefinitions);
                if (count($typeAliasDefinitions) === 1) {
                    $injector->alias(
                        $typeAliasDefinitions[0]->getOriginalServiceDefinition()->getType(),
                        $typeAliasDefinitions[0]->getAliasServiceDefinition()->getType()
                    );
                }
            }
        }

        $preparedTypes = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            if (!in_array($servicePrepareDefinition->getType(), $preparedTypes)) {
                $injector->prepare($servicePrepareDefinition->getType(), function($object) use($servicePrepareDefinitions, $servicePrepareDefinition, $injector, $defineScalarDefinitions, $defineServiceDefinitions) {
                    $methods = self::mapTypesServicePrepares($servicePrepareDefinition->getType(), $servicePrepareDefinitions);
                    foreach ($methods as $method) {
                        $scalarArgs = self::mapTypesScalarArgs($servicePrepareDefinition->getType(), $method, $defineScalarDefinitions);
                        $serviceArgs = self::mapTypesServiceArgs($servicePrepareDefinition->getType(), $method, $defineServiceDefinitions);
                        $injector->execute([$object, $method], array_merge([], $scalarArgs, $serviceArgs));
                    }
                });
                $preparedTypes[] = $servicePrepareDefinition->getType();
            }
        }

        $typeArgsMap = [];
        /** @var DefineScalarDefinition $defineScalarDefinition */
        foreach ($defineScalarDefinitions as $defineScalarDefinition) {
            $type = $defineScalarDefinition->getType();
            if (!isset($typeArgsMap[$type])) {
                $typeArgsMap[$type] = self::mapTypesScalarArgs($type, '__construct', $defineScalarDefinitions);
            }
        }

        /** @var DefineServiceDefinition $defineServiceDefinition */
        foreach ($defineServiceDefinitions as $defineServiceDefinition) {
            $type = $defineServiceDefinition->getType();
            $defineArgs = self::mapTypesServiceArgs($type, '__construct', $defineServiceDefinitions);
            if (isset($typeArgsMap[$type])) {
                $typeArgsMap[$type] = array_merge($typeArgsMap[$type], $defineArgs);
            } else {
                $typeArgsMap[$type] = $defineArgs;
            }
        }

        foreach ($typeArgsMap as $type => $args) {
            $injector->define($type, $args);
        }

        return $injector;
    }

    private static function mapTypesScalarArgs(string $type, string $method, array $defineScalarDefinitions) : array {
        $args = [];
        /** @var DefineScalarDefinition $defineScalarDefinition */
        foreach ($defineScalarDefinitions as $defineScalarDefinition) {
            if ($defineScalarDefinition->getType() === $type && $defineScalarDefinition->getMethod() === $method) {
                $value = $defineScalarDefinition->getValue();
                $constRegex = '/^\!const\((.+)\)$/';
                $envRegex = '/^\!env\((.+)\)$/';
                if (is_string($value) && preg_match($constRegex, $value, $constMatches) === 1) {
                    $value = constant($constMatches[1]);
                } else if (is_string($value) && preg_match($envRegex, $value, $envMatches) === 1) {
                    $value = getenv($envMatches[1]);
                }
                $args[':' . $defineScalarDefinition->getParamName()] = $value;
            }
        }
        return $args;
    }

    private static function mapTypesServiceArgs(string $type, string $method, array $defineServiceDefinitions) : array {
        $args = [];
        /** @var DefineServiceDefinition $defineServiceDefinition */
        foreach ($defineServiceDefinitions as $defineServiceDefinition) {
            if ($defineServiceDefinition->getType() === $type && $defineServiceDefinition->getMethod() === $method) {
                $args[$defineServiceDefinition->getParamName()] = $defineServiceDefinition->getValue();
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
            if ($aliasDefinition->getOriginalServiceDefinition()->getType() === $type) {
                $aliases[] = $aliasDefinition;
            }
        }
        return $aliases;
    }

}