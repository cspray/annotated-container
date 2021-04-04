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
            $injector->prepare($servicePrepareDefinition->getType(), function($object) use($servicePrepareDefinition, $injector) {
                $method = $servicePrepareDefinition->getMethod();
                $injector->execute([$object, $method]);
            });
        }

        return $injector;
    }

    static public function fromSerializedServiceDefinition(string $serviceDefinitionJson) : Injector {

    }

}