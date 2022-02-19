<?php

namespace Cspray\AnnotatedContainer;

final class JsonContainerDefinitionSerializer implements ContainerDefinitionSerializer {

    public function serialize(ContainerDefinition $containerDefinition, ContainerDefinitionSerializerOptions $options = null) : string {
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
                    'profiles' => $serviceDefinition->getProfiles(),
                    'isInterface' => $serviceDefinition->isInterface(),
                    'isClass' => $serviceDefinition->isClass(),
                    'isAbstract' => $serviceDefinition->isAbstract()
                ];
            }
        };
        $serviceDefinitions = [];
        foreach ($containerDefinition->getSharedServiceDefinitions() as $serviceDefinition) {
            $key = md5($serviceDefinition->getType());
            $addCompiledServiceDefinition($key, $serviceDefinition);
            $serviceDefinitions[] = $key;
        }

        $aliasDefinitions = [];
        foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
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
        foreach ($containerDefinition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
            $servicePrepareDefinitions[] = [
                'type' => $servicePrepareDefinition->getType(),
                'method' => $servicePrepareDefinition->getMethod()
            ];
        }

        $injectScalarDefinitions = [];
        foreach ($containerDefinition->getUseScalarDefinitions() as $injectScalarDefinition) {
            $injectScalarDefinitions[] = [
                'type' => $injectScalarDefinition->getType(),
                'method' => $injectScalarDefinition->getMethod(),
                'paramName' => $injectScalarDefinition->getParamName(),
                'paramType' => $injectScalarDefinition->getParamType(),
                'value' => $injectScalarDefinition->getValue()
            ];
        }

        $injectServiceDefinitions = [];
        foreach ($containerDefinition->getUseServiceDefinitions() as $injectServiceDefinition) {
            $injectServiceDefinitions[] = [
                'type' => $injectServiceDefinition->getType(),
                'method' => $injectServiceDefinition->getMethod(),
                'paramName' => $injectServiceDefinition->getParamName(),
                'paramType' => $injectServiceDefinition->getParamType(),
                'value' => $injectServiceDefinition->getValue()
            ];
        }

        $serviceDelegateDefinitions = [];
        foreach ($containerDefinition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            $serviceDelegateDefinitions[] = [
                'delegateType' => $serviceDelegateDefinition->getDelegateType(),
                'delegateMethod' => $serviceDelegateDefinition->getDelegateMethod(),
                'serviceType' => $serviceDelegateDefinition->getServiceType()
            ];
        }

        $options ??= new ContainerDefinitionSerializerOptions();
        $flags = 0;
        if ($options->isPrettyFormatted()) {
            $flags |= JSON_PRETTY_PRINT;
        }
        return json_encode([
            'compiledServiceDefinitions' => $compiledServiceDefinitions,
            'sharedServiceDefinitions' => $serviceDefinitions,
            'aliasDefinitions' => $aliasDefinitions,
            'servicePrepareDefinitions' => $servicePrepareDefinitions,
            'injectScalarDefinitions' => $injectScalarDefinitions,
            'injectServiceDefinitions' => $injectServiceDefinitions,
            'serviceDelegateDefinitions' => $serviceDelegateDefinitions
        ], $flags);
    }

    public function deserialize(string $serializedDefinition) : ContainerDefinition {
        $data = json_decode($serializedDefinition, true);

        $serviceDefinitions = [];
        foreach ($data['compiledServiceDefinitions'] as $serviceHash => $compiledServiceDefinition) {
            // getDeserializeServiceDefinition is a recursive function that could result in multiple service definitions
            // being added with one call if the passed type implements or extends a service that hasn't been parsed yet
            // checking to see if the hash has already been added will prevent already added values from being added
            // multiple times
            if (!isset($serviceDefinitions[$serviceHash])) {
                $serviceDefinitions[$serviceHash] = $this->getDeserializeServiceDefinition($data['compiledServiceDefinitions'], $serviceDefinitions, $compiledServiceDefinition['type']);
            }
        }

        $sharedServiceDefinitions = [];
        foreach ($data['sharedServiceDefinitions'] as $serviceHash) {
            $sharedServiceDefinitions[] = $serviceDefinitions[$serviceHash];
        }

        $aliasDefinitions = [];
        foreach ($data['aliasDefinitions'] as $aliasDefinition) {
            $aliasDefinitions[] = new AliasDefinition(
                $serviceDefinitions[$aliasDefinition['original']],
                $serviceDefinitions[$aliasDefinition['alias']]
            );
        }

        $servicePrepareDefinitions = [];
        foreach ($data['servicePrepareDefinitions'] as $servicePrepareDefinition) {
            $servicePrepareDefinitions[] = new ServicePrepareDefinition(
                $servicePrepareDefinition['type'],
                $servicePrepareDefinition['method']
            );
        }

        $useScalarDefinitions = [];
        foreach ($data['injectScalarDefinitions'] as $useScalarDefinition) {
            $useScalarDefinitions[] = new InjectScalarDefinition(
                $useScalarDefinition['type'],
                $useScalarDefinition['method'],
                $useScalarDefinition['paramName'],
                $useScalarDefinition['paramType'],
                $useScalarDefinition['value']
            );
        }

        $useServiceDefinitions = [];
        foreach ($data['injectServiceDefinitions'] as $useServiceDefinition) {
            $useServiceDefinitions[] = new InjectServiceDefinition(
                $useServiceDefinition['type'],
                $useServiceDefinition['method'],
                $useServiceDefinition['paramName'],
                $useServiceDefinition['paramType'],
                $useServiceDefinition['value']
            );
        }

        $serviceDelegateDefinitions = [];
        foreach ($data['serviceDelegateDefinitions'] as $serviceDelegateDefinition) {
            $serviceDelegateDefinitions[] = new ServiceDelegateDefinition(
                $serviceDelegateDefinition['delegateType'],
                $serviceDelegateDefinition['delegateMethod'],
                $serviceDelegateDefinition['serviceType']
            );
        }

        return new class($sharedServiceDefinitions, $aliasDefinitions, $servicePrepareDefinitions, $useScalarDefinitions, $useServiceDefinitions, $serviceDelegateDefinitions) implements ContainerDefinition {

            public function __construct(
                private array $sharedServiceDefinitions,
                private array $aliasDefinitions,
                private array $servicePrepareDefinitions,
                private array $useScalarDefinitions,
                private array $useServiceDefinitions,
                private array $serviceDelegateDefinitions
            ) {}

            public function getSharedServiceDefinitions(): array {
                return $this->sharedServiceDefinitions;
            }

            public function getAliasDefinitions(): array {
                return $this->aliasDefinitions;
            }

            public function getServicePrepareDefinitions(): array {
                return $this->servicePrepareDefinitions;
            }

            public function getUseScalarDefinitions(): array {
                return $this->useScalarDefinitions;
            }

            public function getUseServiceDefinitions(): array {
                return $this->useServiceDefinitions;
            }

            public function getServiceDelegateDefinitions(): array {
                return $this->serviceDelegateDefinitions;
            }
        };
    }

    private function getDeserializeServiceDefinition(array $compiledServiceDefinitions, array &$serviceDefinitionCacheMap, string $type) : ServiceDefinition {
        $serviceHash = md5($type);
        if (!isset($serviceDefinitionCacheMap[$serviceHash])) {
            $compiledServiceDefinition = $compiledServiceDefinitions[$serviceHash];

            $implementedServices = [];
            foreach ($compiledServiceDefinition['implementedServices'] as $implementedServiceHash) {
                $implementedType = $compiledServiceDefinitions[$implementedServiceHash]['type'];
                $implementedServices[] = $this->getDeserializeServiceDefinition($compiledServiceDefinitions, $serviceDefinitionCacheMap, $implementedType);
            }

            $extendedServices = [];
            foreach ($compiledServiceDefinition['extendedServices'] as $extendedServiceHash) {
                $extendedType = $compiledServiceDefinitions[$extendedServiceHash]['type'];
                $extendedServices[] = $this->getDeserializeServiceDefinition($compiledServiceDefinitions, $serviceDefinitionCacheMap, $extendedType);
            }

            $serviceDefinitionCacheMap[$serviceHash] = new ServiceDefinition(
                $type,
                $compiledServiceDefinition['profiles'],
                $implementedServices,
                $extendedServices,
                $compiledServiceDefinition['isInterface'],
                $compiledServiceDefinition['isAbstract']
            );
        }

        return $serviceDefinitionCacheMap[$serviceHash];
    }

}