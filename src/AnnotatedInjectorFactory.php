<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Auryn\Injector;

/**
 * Wires together an Injector from an InjectorDefinition or a JSON serialization of an InjectorDefinition.
 *
 * @package Cspray\AnnotatedInjector
 */
final class AnnotatedInjectorFactory {

    private function __construct() {}

    static public function fromInjectorDefinition(InjectorDefinition $injectorDefinition) : Injector {
        $injector = new Injector();
        $defineScalarDefinitions = $injectorDefinition->getDefineScalarDefinitions();

        foreach ($injectorDefinition->getSharedServiceDefinitions() as $serviceDefinition) {
            $injector->share($serviceDefinition->getType());
        }

        foreach ($injectorDefinition->getAliasDefinitions() as $aliasDefinition) {
            $injector->alias(
                $aliasDefinition->getOriginalServiceDefinition()->getType(),
                $aliasDefinition->getAliasServiceDefinition()->getType()
            );
        }

        foreach ($injectorDefinition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
            $injector->prepare($servicePrepareDefinition->getType(), function($object) use($servicePrepareDefinition, $injector, $defineScalarDefinitions) {
                $method = $servicePrepareDefinition->getMethod();
                $args = self::mapTypesArgMap($servicePrepareDefinition->getType(), $method, $defineScalarDefinitions);
                $injector->execute([$object, $method], $args);
            });
        }

        $definedTypes = [];
        /** @var DefineScalarDefinition $defineScalarDefinition */
        foreach ($defineScalarDefinitions as $defineScalarDefinition) {
            if (!in_array($defineScalarDefinition->getType(), $definedTypes)) {
                $args = self::mapTypesArgMap($defineScalarDefinition->getType(), '__construct', $defineScalarDefinitions);
                $injector->define($defineScalarDefinition->getType(), $args);
                $definedTypes[] = $defineScalarDefinition->getType();
            }
        }

        return $injector;
    }

    private static function mapTypesArgMap(string $type, string $method, array $defineScalarDefinitions) : array {
        $args = [];
        /** @var DefineScalarDefinition $defineScalarDefinition */
        foreach ($defineScalarDefinitions as $defineScalarDefinition) {
            if ($defineScalarDefinition->getType() === $type && $defineScalarDefinition->getMethod() === $method) {
                $value = $defineScalarDefinition->getValue();
                $constRegex = '/^\!const\((.+)\)$/';
                if (is_string($value) && preg_match($constRegex, $value, $constMatches) === 1) {
                    $value = constant($constMatches[1]);
                }
                $args[':' . $defineScalarDefinition->getParamName()] = $value;
            }
        }
        return $args;
    }

    static public function fromSerializedServiceDefinition(string $serviceDefinitionJson) : Injector {

    }

}