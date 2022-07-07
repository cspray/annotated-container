<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainerFixture\InjectConstructorServices\TypeUnionInjectService;
use Cspray\Typiphy\Internal\NamedType;
use Cspray\Typiphy\Internal\NamedTypeUnion;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use JsonSerializable;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\boolType;
use function Cspray\Typiphy\callableType;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\iterableType;
use function Cspray\Typiphy\mixedType;
use function Cspray\Typiphy\nullType;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;
use function Cspray\Typiphy\typeIntersect;
use function Cspray\Typiphy\typeUnion;
use function Cspray\Typiphy\voidType;
use function DI\string;

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
                    'isConcrete' => $serviceDefinition->isConcrete()
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

        $injectDefinitions = [];
        foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
            $injectDefinitions[] = [
                'injectTargetType' => $injectDefinition->getTargetIdentifier()->getClass()->getName(),
                'injectTargetMethod' => $injectDefinition->getTargetIdentifier()->getMethodName(),
                'injectTargetName' => $injectDefinition->getTargetIdentifier()->getName(),
                'type' => $injectDefinition->getType()->getName(),
                'value' => $injectDefinition->getValue(),
                'profiles' => $injectDefinition->getProfiles(),
                'storeName' => $injectDefinition->getStoreName()
            ];
        }

        return json_encode([
            'compiledServiceDefinitions' => $compiledServiceDefinitions,
            'sharedServiceDefinitions' => $serviceDefinitions,
            'aliasDefinitions' => $aliasDefinitions,
            'servicePrepareDefinitions' => $servicePrepareDefinitions,
            'serviceDelegateDefinitions' => $serviceDelegateDefinitions,
            'injectDefinitions' => $injectDefinitions
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

        foreach ($data['injectDefinitions'] as $injectDefinition) {
            $injectBuilder = InjectDefinitionBuilder::forService(objectType($injectDefinition['injectTargetType']));

            $type = $this->convertStringToType($injectDefinition['type']);

            if (is_null($injectDefinition['injectTargetMethod'])) {
                $injectBuilder = $injectBuilder->withProperty(
                    new NamedType($injectDefinition['type']),
                    $injectDefinition['injectTargetName']
                );
            } else {
                $injectBuilder = $injectBuilder->withMethod(
                    $injectDefinition['injectTargetMethod'],
                    $type,
                    $injectDefinition['injectTargetName']
                );
            }

            $injectBuilder = $injectBuilder->withValue($injectDefinition['value'])
                ->withProfiles(...$injectDefinition['profiles']);

            if (!is_null($injectDefinition['storeName'])) {
                $injectBuilder = $injectBuilder->withStore($injectDefinition['storeName']);
            }

            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectDefinition($injectBuilder->build());
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

            $serviceDefinitionCacheMap[$serviceHash] = $serviceDefinitionBuilder->build();
        }

        return $serviceDefinitionCacheMap[$serviceHash];
    }

    private function convertStringToType(string $rawType) : Type|TypeUnion|TypeIntersect {
        if (str_contains($rawType, '|')) {
            $types = [];
            foreach (explode('|', $rawType) as $unionType) {
                $types[] = $this->convertStringToType($unionType);
            }
            $type = typeUnion(...$types);
        } else if (str_contains($rawType, '&')) {
            $types = [];
            foreach (explode('&', $rawType) as $intersectType) {
                $types[] = $this->convertStringToType($intersectType);
            }
            $type = typeIntersect(...$types);
        } else {
            $type = match($rawType) {
                'string' => stringType(),
                'int' => intType(),
                'float' => floatType(),
                'bool' => boolType(),
                'array' => arrayType(),
                'mixed' => mixedType(),
                'iterable' => iterableType(),
                'null' => nullType(),
                'void' => voidType(),
                'callable' => callableType(),
                default => objectType($rawType)
            };
        }

        return $type;
    }


}