<?php

namespace Cspray\AnnotatedInjector;

use JsonSerializable;

class InjectorDefinitionSerializer {

    public function serialize(InjectorDefinition $injectorDefinition) : JsonSerializable {
        return new class($injectorDefinition) implements JsonSerializable {

            private InjectorDefinition $injectorDefinition;

            public function __construct(InjectorDefinition $injectorDefinition) {
                $this->injectorDefinition = $injectorDefinition;
            }

            public function jsonSerialize() {
                $compiledServiceDefinitions = [];
                $addCompiledServiceDefinition = function(string $key, ServiceDefinition $serviceDefinition) use(&$compiledServiceDefinitions, &$addCompiledServiceDefinition) : void {
                    if (!isset($compiledServiceDefinitions[$key])) {
                        $implementedServices = [];
                        foreach ($serviceDefinition->getImplementedServices() as $implementedService) {
                            $implementedKey = md5($implementedService->getType());
                            $addCompiledServiceDefinition($implementedKey, $implementedService);
                            $implementedServices[] = $implementedKey;
                        }

                        $extendedServices = [];
                        foreach ($serviceDefinition->getExtendedServices() as $extendedService) {
                            $extendedKey = md5($extendedService->getType());
                            $addCompiledServiceDefinition($extendedKey, $extendedService);
                            $extendedServices[] = $extendedKey;
                        }

                        $compiledServiceDefinitions[$key] = [
                            'type' => $serviceDefinition->getType(),
                            'implementedServices' => $implementedServices,
                            'extendedServices' => $extendedServices,
                            'environments' => $serviceDefinition->getEnvironments(),
                            'isInterface' => $serviceDefinition->isInterface(),
                            'isClass' => $serviceDefinition->isClass(),
                            'isAbstract' => $serviceDefinition->isAbstract()
                        ];
                    }
                };
                $serviceDefinitions = [];
                foreach ($this->injectorDefinition->getSharedServiceDefinitions() as $serviceDefinition) {
                    $key = md5($serviceDefinition->getType());
                    $addCompiledServiceDefinition($key, $serviceDefinition);
                    $serviceDefinitions[] = $key;
                }

                $aliasDefinitions = [];
                foreach ($this->injectorDefinition->getAliasDefinitions() as $aliasDefinition) {
                    $originalKey = md5($aliasDefinition->getOriginalServiceDefinition()->getType());
                    $addCompiledServiceDefinition($originalKey, $aliasDefinition->getOriginalServiceDefinition());
                    $aliasKey = md5($aliasDefinition->getAliasServiceDefinition()->getType());
                    $addCompiledServiceDefinition($aliasKey, $aliasDefinition->getAliasServiceDefinition());
                    $aliasDefinitions[] = [
                        'original' => $originalKey,
                        'alias' => $aliasKey
                    ];
                }

                $servicePrepareDefinitions = [];
                foreach ($this->injectorDefinition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
                    $servicePrepareDefinitions[] = [
                        'type' => $servicePrepareDefinition->getType(),
                        'method' => $servicePrepareDefinition->getMethod()
                    ];
                }

                $useScalarDefinitions = [];
                foreach ($this->injectorDefinition->getUseScalarDefinitions() as $useScalarDefinition) {
                    $useScalarDefinitions[] = [
                        'type' => $useScalarDefinition->getType(),
                        'method' => $useScalarDefinition->getMethod(),
                        'paramName' => $useScalarDefinition->getParamName(),
                        'paramType' => $useScalarDefinition->getParamType(),
                        'value' => $useScalarDefinition->getValue()
                    ];
                }

                $useServiceDefinitions = [];
                foreach ($this->injectorDefinition->getUseServiceDefinitions() as $useServiceDefinition) {
                    $useServiceDefinitions[] = [
                        'type' => $useServiceDefinition->getType(),
                        'method' => $useServiceDefinition->getMethod(),
                        'paramName' => $useServiceDefinition->getParamName(),
                        'paramType' => $useServiceDefinition->getParamType(),
                        'value' => $useServiceDefinition->getValue()
                    ];
                }

                $serviceDelegateDefinitions = [];
                foreach ($this->injectorDefinition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
                    $serviceDelegateDefinitions[] = [
                        'delegateType' => $serviceDelegateDefinition->getDelegateType(),
                        'delegateMethod' => $serviceDelegateDefinition->getDelegateMethod(),
                        'serviceType' => $serviceDelegateDefinition->getServiceType()
                    ];
                }

                return [
                    'compiledServiceDefinitions' => $compiledServiceDefinitions,
                    'sharedServiceDefinitions' => $serviceDefinitions,
                    'aliasDefinitions' => $aliasDefinitions,
                    'servicePrepareDefinitions' => $servicePrepareDefinitions,
                    'useScalarDefinitions' => $useScalarDefinitions,
                    'useServiceDefinitions' => $useServiceDefinitions,
                    'serviceDelegateDefinitions' => $serviceDelegateDefinitions
                ];
            }
        };
    }

    public function deserialize(string $json) : InjectorDefinition {

    }

}