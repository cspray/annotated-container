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

                $compiledServiceDefinitions[$key] = [
                    'type' => $serviceDefinition->getType(),
                    'implementedServices' => $implementedServices,
                    'profiles' => $serviceDefinition->getProfiles(),
                    'isAbstract' => $serviceDefinition->isAbstract(),
                    'isConcrete' => $serviceDefinition->isConcrete(),
                ];
            }
        };
        $serviceDefinitions = [];
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $key = md5($serviceDefinition->getType());
            $addCompiledServiceDefinition($key, $serviceDefinition);
            $serviceDefinitions[] = $key;
        }

        $aliasDefinitions = [];
        foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
            $originalKey = md5($aliasDefinition->getAbstractService()->getType());
            $addCompiledServiceDefinition($originalKey, $aliasDefinition->getAbstractService());
            $aliasKey = md5($aliasDefinition->getConcreteService()->getType());
            $addCompiledServiceDefinition($aliasKey, $aliasDefinition->getConcreteService());
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
        foreach ($containerDefinition->getInjectScalarDefinitions() as $injectScalarDefinition) {
            $injectScalarDefinitions[] = [
                'type' => $injectScalarDefinition->getService()->getType(),
                'method' => $injectScalarDefinition->getMethod(),
                'paramName' => $injectScalarDefinition->getParamName(),
                'paramType' => strtolower($injectScalarDefinition->getParamType()->name),
                'value' => $injectScalarDefinition->getValue()
            ];
        }

        $injectServiceDefinitions = [];
        foreach ($containerDefinition->getInjectServiceDefinitions() as $injectServiceDefinition) {
            $injectServiceDefinitions[] = [
                'type' => $injectServiceDefinition->getService()->getType(),
                'method' => $injectServiceDefinition->getMethod(),
                'paramName' => $injectServiceDefinition->getParamName(),
                'paramType' => $injectServiceDefinition->getParamType(),
                'value' => $injectServiceDefinition->getInjectedService()->getType()
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
            $aliasDefinitions[] = AliasDefinitionBuilder::forAbstract($serviceDefinitions[$aliasDefinition['original']])->withConcrete($serviceDefinitions[$aliasDefinition['alias']])->build();
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
            $service = $serviceDefinitions[md5($useScalarDefinition['type'])];
            $useScalarDefinitions[] = InjectScalarDefinitionBuilder::forMethod($service, $useScalarDefinition['method'])
                ->withParam(ScalarType::String, $useScalarDefinition['paramName'])
                ->withValue($useScalarDefinition['value'])
                ->build();
        }

        $useServiceDefinitions = [];
        foreach ($data['injectServiceDefinitions'] as $useServiceDefinition) {
            $targetService = $serviceDefinitions[md5($useServiceDefinition['type'])];
            $injectService = $serviceDefinitions[md5($useServiceDefinition['value'])];
            $useServiceDefinitions[] = InjectServiceDefinitionBuilder::forMethod($targetService, $useServiceDefinition['method'])
                ->withParam($useServiceDefinition['paramType'], $useServiceDefinition['paramName'])
                ->withInjectedService($injectService)
                ->build();
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

            public function getServiceDefinitions(): array {
                return $this->sharedServiceDefinitions;
            }

            public function getAliasDefinitions(): array {
                return $this->aliasDefinitions;
            }

            public function getServicePrepareDefinitions(): array {
                return $this->servicePrepareDefinitions;
            }

            public function getInjectScalarDefinitions(): array {
                return $this->useScalarDefinitions;
            }

            public function getInjectServiceDefinitions(): array {
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
            if ($compiledServiceDefinition['isAbstract']) {
                $factoryMethod = 'forAbstract';
            } else {
                $factoryMethod = 'forConcrete';
            }
            $serviceDefinitionBuilder = ServiceDefinitionBuilder::$factoryMethod($type)->withProfiles(...$compiledServiceDefinition['profiles']);

            foreach ($compiledServiceDefinition['implementedServices'] as $implementedServiceHash) {
                $implementedType = $compiledServiceDefinitions[$implementedServiceHash]['type'];
                $serviceDefinitionBuilder = $serviceDefinitionBuilder->withImplementedService(
                    $this->getDeserializeServiceDefinition($compiledServiceDefinitions, $serviceDefinitionCacheMap, $implementedType)
                );
            }

            $serviceDefinitionCacheMap[$serviceHash] = $serviceDefinitionBuilder->build();
        }

        return $serviceDefinitionCacheMap[$serviceHash];
    }

}