<?php

namespace Cspray\AnnotatedContainer;

use JsonSerializable;

/**
 * A ContainerDefinitionSerializer that will format a ContainerDefinition into a JSON string.
 */
final class JsonContainerDefinitionSerializer implements ContainerDefinitionSerializer {

    /**
     * Returns a JSON object that specifies the various definitions that make up this ContainerDefinition.
     *
     * It is not advised to rely on the precise format of the returned JSON string. Instead, you should use the
     * serialized string in the JsonContainerDefinitionSerializer::deserialize method to create a corresponding
     * ContainerDefinition.
     *
     * @param ContainerDefinition $containerDefinition
     * @param ContainerDefinitionSerializerOptions|null $options
     * @return string
     */
    public function serialize(ContainerDefinition $containerDefinition, ContainerDefinitionSerializerOptions $options = null) : string {
        $compiledServiceDefinitions = [];
        $addCompiledServiceDefinition = function(string $key, ServiceDefinition $serviceDefinition) use(&$compiledServiceDefinitions, &$addCompiledServiceDefinition) : void {
            if (!isset($compiledServiceDefinitions[$key])) {
                $compiledServiceDefinitions[$key] = [
                    'name' => is_null($serviceDefinition->getName()) ? null : $this->convertAnnotationValueToJson($serviceDefinition->getName()),
                    'type' => $serviceDefinition->getType(),
                    'profiles' => $this->convertAnnotationValueToJson($serviceDefinition->getProfiles()),
                    'isAbstract' => $serviceDefinition->isAbstract(),
                    'isConcrete' => $serviceDefinition->isConcrete(),
                    'isShared' => $serviceDefinition->isShared()
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
                'type' => $servicePrepareDefinition->getService()->getType(),
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
                'value' => $this->convertAnnotationValueToJson($injectScalarDefinition->getValue())
            ];
        }

        $injectServiceDefinitions = [];
        foreach ($containerDefinition->getInjectServiceDefinitions() as $injectServiceDefinition) {
            $injectServiceDefinitions[] = [
                'type' => $injectServiceDefinition->getService()->getType(),
                'method' => $injectServiceDefinition->getMethod(),
                'paramName' => $injectServiceDefinition->getParamName(),
                'paramType' => $injectServiceDefinition->getParamType(),
                'value' => $this->convertAnnotationValueToJson($injectServiceDefinition->getInjectedService())
            ];
        }

        $serviceDelegateDefinitions = [];
        foreach ($containerDefinition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            $serviceDelegateDefinitions[] = [
                'delegateType' => $serviceDelegateDefinition->getDelegateType(),
                'delegateMethod' => $serviceDelegateDefinition->getDelegateMethod(),
                'serviceType' => $serviceDelegateDefinition->getServiceType()->getType()
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

    /**
     * Parses a JSON object returned from JsonContainerDefinitionSerializer::serialize to create a ContainerDefinition.
     *
     * @param string $serializedDefinition
     * @return ContainerDefinition
     * @throws Exception\DefinitionBuilderException
     */
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

        $containerDefinitionBuilder = ContainerDefinitionBuilder::newDefinition();
        foreach ($data['sharedServiceDefinitions'] as $serviceHash) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($serviceDefinitions[$serviceHash]);
        }

        foreach ($data['aliasDefinitions'] as $aliasDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($serviceDefinitions[$aliasDefinition['original']])->withConcrete($serviceDefinitions[$aliasDefinition['alias']])->build()
            );
        }

        foreach ($data['servicePrepareDefinitions'] as $servicePrepareDefinition) {
            $service = $serviceDefinitions[md5($servicePrepareDefinition['type'])];
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition(
                ServicePrepareDefinitionBuilder::forMethod($service, $servicePrepareDefinition['method'])->build()
            );
        }

        foreach ($data['injectScalarDefinitions'] as $useScalarDefinition) {
            $service = $serviceDefinitions[md5($useScalarDefinition['type'])];
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectScalarDefinition(
                InjectScalarDefinitionBuilder::forMethod($service, $useScalarDefinition['method'])
                    ->withParam(ScalarType::String, $useScalarDefinition['paramName'])
                    ->withValue($this->convertJsonToAnnotationValue($useScalarDefinition['value']))
                    ->build()
            );
        }

        foreach ($data['injectServiceDefinitions'] as $injectServiceDefinition) {
            $targetService = $serviceDefinitions[md5($injectServiceDefinition['type'])];
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectServiceDefinition(
                InjectServiceDefinitionBuilder::forMethod($targetService, $injectServiceDefinition['method'])
                    ->withParam($injectServiceDefinition['paramType'], $injectServiceDefinition['paramName'])
                    ->withInjectedService($this->convertJsonToAnnotationValue($injectServiceDefinition['value']))
                    ->build()
            );
        }

        foreach ($data['serviceDelegateDefinitions'] as $serviceDelegateDefinition) {
            $service = $serviceDefinitions[md5($serviceDelegateDefinition['serviceType'])];
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition(
                ServiceDelegateDefinitionBuilder::forService($service)
                    ->withDelegateMethod($serviceDelegateDefinition['delegateType'], $serviceDelegateDefinition['delegateMethod'])
                    ->build()
            );
        }

        return $containerDefinitionBuilder->build();
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
            /** @var ServiceDefinitionBuilder $serviceDefinitionBuilder */
            $serviceDefinitionBuilder = ServiceDefinitionBuilder::$factoryMethod($type);
            $serviceDefinitionBuilder = $serviceDefinitionBuilder->withProfiles($this->convertJsonToAnnotationValue($compiledServiceDefinition['profiles']));

            if (!is_null($compiledServiceDefinition['name'])) {
                $serviceDefinitionBuilder = $serviceDefinitionBuilder->withName($this->convertJsonToAnnotationValue($compiledServiceDefinition['name']));
            }

            if ($compiledServiceDefinition['isShared']) {
                $serviceDefinitionBuilder = $serviceDefinitionBuilder->withShared();
            } else {
                $serviceDefinitionBuilder = $serviceDefinitionBuilder->withNotShared();
            }

            $serviceDefinitionCacheMap[$serviceHash] = $serviceDefinitionBuilder->build();
        }

        return $serviceDefinitionCacheMap[$serviceHash];
    }

    private function convertAnnotationValueToJson(AnnotationValue $annotationValue) : JsonSerializable {
        return new class($annotationValue) implements JsonSerializable {

            public function __construct(private AnnotationValue $annotationValue) {}

            public function jsonSerialize(): array {
                return $this->getJsonForAnnotationValue($this->annotationValue);
            }

            private function getJsonForAnnotationValue(AnnotationValue $annotationValue) : array {
                return ['annotationValue' => serialize($annotationValue)];
            }
        };
    }

    private function convertJsonToAnnotationValue(array $json) : AnnotationValue|CollectionAnnotationValue {
        return unserialize($json['annotationValue']);
    }

}