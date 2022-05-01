<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use JsonSerializable;
use function Cspray\Typiphy\objectType;

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
     * @return string
     */
    public function serialize(ContainerDefinition $containerDefinition) : string {
        $compiledServiceDefinitions = [];
        $addCompiledServiceDefinition = function(string $key, ServiceDefinition $serviceDefinition) use(&$compiledServiceDefinitions, &$addCompiledServiceDefinition) : void {
            if (!isset($compiledServiceDefinitions[$key])) {
                $compiledServiceDefinitions[$key] = [
                    'name' => is_null($serviceDefinition->getName()) ? null : $serviceDefinition->getName(),
                    'type' => $serviceDefinition->getType()->getName(),
                    'profiles' => $serviceDefinition->getProfiles(),
                    'isAbstract' => $serviceDefinition->isAbstract(),
                    'isConcrete' => $serviceDefinition->isConcrete(),
                    'isShared' => $serviceDefinition->isShared()
                ];
            }
        };
        $serviceDefinitions = [];
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $key = md5($serviceDefinition->getType()->getName());
            $addCompiledServiceDefinition($key, $serviceDefinition);
            $serviceDefinitions[] = $key;
        }

        $aliasDefinitions = [];
        foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
            $originalKey = md5($aliasDefinition->getAbstractService()->getName());
            $aliasKey = md5($aliasDefinition->getConcreteService()->getName());
            $aliasDefinitions[] = [
                'original' => $originalKey,
                'alias' => $aliasKey
            ];
        }

        $servicePrepareDefinitions = [];
        foreach ($containerDefinition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
            $servicePrepareDefinitions[] = [
                'type' => $servicePrepareDefinition->getService()->getName(),
                'method' => $servicePrepareDefinition->getMethod()
            ];
        }

        $serviceDelegateDefinitions = [];
        foreach ($containerDefinition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            $serviceDelegateDefinitions[] = [
                'delegateType' => $serviceDelegateDefinition->getDelegateType()->getName(),
                'delegateMethod' => $serviceDelegateDefinition->getDelegateMethod(),
                'serviceType' => $serviceDelegateDefinition->getServiceType()->getName()
            ];
        }

        return json_encode([
            'compiledServiceDefinitions' => $compiledServiceDefinitions,
            'sharedServiceDefinitions' => $serviceDefinitions,
            'aliasDefinitions' => $aliasDefinitions,
            'servicePrepareDefinitions' => $servicePrepareDefinitions,
            'serviceDelegateDefinitions' => $serviceDelegateDefinitions
        ]);
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
                AliasDefinitionBuilder::forAbstract($serviceDefinitions[$aliasDefinition['original']]->getType())->withConcrete($serviceDefinitions[$aliasDefinition['alias']]->getType())->build()
            );
        }

        foreach ($data['servicePrepareDefinitions'] as $servicePrepareDefinition) {
            $service = $serviceDefinitions[md5($servicePrepareDefinition['type'])];
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition(
                ServicePrepareDefinitionBuilder::forMethod($service->getType(), $servicePrepareDefinition['method'])->build()
            );
        }

        foreach ($data['serviceDelegateDefinitions'] as $serviceDelegateDefinition) {
            $service = $serviceDefinitions[md5($serviceDelegateDefinition['serviceType'])];
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition(
                ServiceDelegateDefinitionBuilder::forService($service->getType())
                    ->withDelegateMethod(objectType($serviceDelegateDefinition['delegateType']), $serviceDelegateDefinition['delegateMethod'])
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
            $serviceDefinitionBuilder = ServiceDefinitionBuilder::$factoryMethod(objectType($type));
            $serviceDefinitionBuilder = $serviceDefinitionBuilder->withProfiles($compiledServiceDefinition['profiles']);

            if (!is_null($compiledServiceDefinition['name'])) {
                $serviceDefinitionBuilder = $serviceDefinitionBuilder->withName($compiledServiceDefinition['name']);
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


}