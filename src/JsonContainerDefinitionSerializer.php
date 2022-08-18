<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Internal\Objects;
use Cspray\AnnotatedContainer\Internal\SerializerInjectValueParser;
use Cspray\AnnotatedContainer\Internal\SerializerServiceDefinitionCache;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use phpDocumentor\Reflection\DocBlock\Serializer;
use ReflectionEnum;
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

/**
 * A ContainerDefinitionSerializer that will format a ContainerDefinition into a JSON string.
 *
 * @psalm-type JsonSerializedServiceDefinition = array{
 *     name: ?string,
 *     type: string,
 *     profiles: list<string>,
 *     isAbstract: bool,
 *     isConcrete: bool
 * }
 * @psalm-type JsonSerializedContainerDefinitionArray = array{
 *     compiledServiceDefinitions: array<string, JsonSerializedServiceDefinition>,
 *     sharedServiceDefinitions: list<string>,
 *     configurationDefinitions: list<array{type: string, name: ?string}>,
 *     aliasDefinitions: list<array{original: string, alias: string}>,
 *     servicePrepareDefinitions: list<array{type: string, method: string}>,
 *     serviceDelegateDefinitions: list<array{delegateType: string, delegateMethod: string, serviceType: string}>,
 *     injectDefinitions: list<array{
 *         injectTargetType: string,
 *         injectTargetMethod: ?string,
 *         injectTargetName: string,
 *         type: string,
 *         value: mixed,
 *         profiles: list<string>,
 *         storeName: ?string
 *     }>
 * }
 * @deprecated This class is designated to be removed in 2.0
 */
final class JsonContainerDefinitionSerializer implements ContainerDefinitionSerializer {

    private readonly SerializerInjectValueParser $parser;

    public function __construct() {
        $this->parser = new SerializerInjectValueParser();
    }

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
        $addCompiledServiceDefinition = function(string $key, ServiceDefinition $serviceDefinition) use(&$compiledServiceDefinitions) : void {
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

        $configurationDefinitions = [];
        foreach ($containerDefinition->getConfigurationDefinitions() as $configurationDefinition) {
            $configurationDefinitions[] = [
                'type' => $configurationDefinition->getClass()->getName(),
                'name' => $configurationDefinition->getName()
            ];
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

        $parseValue = function(mixed $value) use(&$parseValue) : mixed {
            if (is_object($value) && Objects::isEnum($value::class)) {
                $parsedValue = $value->name;
            } else if (is_array($value)) {
                $parsedValue = [];
                foreach ($value as $key => $val) {
                    $rawType = is_object($val) ? $val::class : gettype($val);
                    $parsedValue[$key] = [
                        'type' => $this->parser->convertStringToType($rawType)->getName(),
                        'value' => $parseValue($val)
                    ];
                }
            } else {
                $parsedValue = $value;
            }

            return $parsedValue;
        };

        $injectDefinitions = [];
        foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
            $injectDefinitions[] = [
                'injectTargetType' => $injectDefinition->getTargetIdentifier()->getClass()->getName(),
                'injectTargetMethod' => $injectDefinition->getTargetIdentifier()->getMethodName(),
                'injectTargetName' => $injectDefinition->getTargetIdentifier()->getName(),
                'type' => $injectDefinition->getType()->getName(),
                'value' => $parseValue($injectDefinition->getValue()),
                'profiles' => $injectDefinition->getProfiles(),
                'storeName' => $injectDefinition->getStoreName()
            ];
        }

        return json_encode([
            'compiledServiceDefinitions' => $compiledServiceDefinitions,
            'sharedServiceDefinitions' => $serviceDefinitions,
            'configurationDefinitions' => $configurationDefinitions,
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
        /** @var JsonSerializedContainerDefinitionArray $data */
        $data = json_decode($serializedDefinition, true);

        $serviceDefinitions = new SerializerServiceDefinitionCache();
        foreach ($data['compiledServiceDefinitions'] as $serviceHash => $compiledServiceDefinition) {
            // getDeserializeServiceDefinition is a recursive function that could result in multiple service definitions
            // being added with one call if the passed type implements or extends a service that hasn't been parsed yet
            // checking to see if the hash has already been added will prevent already added values from being added
            // multiple times
            if (!$serviceDefinitions->has($serviceHash)) {
                $serviceDefinitions->add(
                    $serviceHash,
                    $this->getDeserializeServiceDefinition(
                        $data['compiledServiceDefinitions'],
                        $serviceDefinitions,
                        $compiledServiceDefinition['type']
                    )
                );
            }
        }

        $containerDefinitionBuilder = ContainerDefinitionBuilder::newDefinition();
        foreach ($data['sharedServiceDefinitions'] as $serviceHash) {
            $service = $serviceDefinitions->get($serviceHash);
            assert($service !== null);
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($service);
        }

        foreach ($data['configurationDefinitions'] as $configurationDefinition) {
            $configurationDefinitionBuilder = ConfigurationDefinitionBuilder::forClass(objectType($configurationDefinition['type']));
            if ($configurationDefinition['name'] !== null) {
                $configurationDefinitionBuilder = $configurationDefinitionBuilder->withName($configurationDefinition['name']);
            }
            $containerDefinitionBuilder = $containerDefinitionBuilder->withConfigurationDefinition(
                $configurationDefinitionBuilder->build()
            );
        }

        foreach ($data['aliasDefinitions'] as $aliasDefinition) {
            $abstractService = $serviceDefinitions->get($aliasDefinition['original']);
            assert($abstractService !== null);
            $concreteService = $serviceDefinitions->get($aliasDefinition['alias']);
            assert($concreteService !== null);

            $containerDefinitionBuilder = $containerDefinitionBuilder->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($abstractService->getType())
                    ->withConcrete($concreteService->getType())
                    ->build()
            );
        }

        foreach ($data['servicePrepareDefinitions'] as $servicePrepareDefinition) {
            $service = $serviceDefinitions->get(md5($servicePrepareDefinition['type']));
            assert($service !== null);
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition(
                ServicePrepareDefinitionBuilder::forMethod($service->getType(), $servicePrepareDefinition['method'])->build()
            );
        }

        foreach ($data['serviceDelegateDefinitions'] as $serviceDelegateDefinition) {
            $service = $serviceDefinitions->get(md5($serviceDelegateDefinition['serviceType']));
            assert($service !== null);
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition(
                ServiceDelegateDefinitionBuilder::forService($service->getType())
                    ->withDelegateMethod(objectType($serviceDelegateDefinition['delegateType']), $serviceDelegateDefinition['delegateMethod'])
                    ->build()
            );
        }

        foreach ($data['injectDefinitions'] as $injectDefinition) {
            $injectBuilder = InjectDefinitionBuilder::forService(objectType($injectDefinition['injectTargetType']));

            $type = $this->parser->convertStringToType($injectDefinition['type']);

            if (is_null($injectDefinition['injectTargetMethod'])) {
                $injectBuilder = $injectBuilder->withProperty(
                    $type,
                    $injectDefinition['injectTargetName']
                );
            } else {
                $injectBuilder = $injectBuilder->withMethod(
                    $injectDefinition['injectTargetMethod'],
                    $type,
                    $injectDefinition['injectTargetName']
                );
            }


            $injectBuilder = $injectBuilder->withValue($this->parser->parse($type, $injectDefinition['value']))
                ->withProfiles(...$injectDefinition['profiles']);

            if (!is_null($injectDefinition['storeName'])) {
                $injectBuilder = $injectBuilder->withStore($injectDefinition['storeName']);
            }

            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectDefinition($injectBuilder->build());
        }

        return $containerDefinitionBuilder->build();
    }

    /**
     * @param array<string, JsonSerializedServiceDefinition> $compiledServiceDefinitions
     * @param SerializerServiceDefinitionCache $cache
     * @param string $type
     * @return ServiceDefinition
     */
    private function getDeserializeServiceDefinition(array $compiledServiceDefinitions, SerializerServiceDefinitionCache $cache, string $type) : ServiceDefinition {
        $serviceHash = md5($type);
        if (!$cache->has($serviceHash)) {
            $compiledServiceDefinition = $compiledServiceDefinitions[$serviceHash];
            if ($compiledServiceDefinition['isAbstract']) {
                $factoryMethod = 'forAbstract';
            } else {
                $factoryMethod = 'forConcrete';
            }
            /** @var ServiceDefinitionBuilder $serviceDefinitionBuilder */
            $serviceDefinitionBuilder = ServiceDefinitionBuilder::$factoryMethod(objectType($type));
            $serviceDefinitionBuilder = $serviceDefinitionBuilder->withProfiles($compiledServiceDefinition['profiles']);

            if ($compiledServiceDefinition['name'] !== null) {
                $serviceDefinitionBuilder = $serviceDefinitionBuilder->withName($compiledServiceDefinition['name']);
            }

            $service = $serviceDefinitionBuilder->build();
            $cache->add($serviceHash, $service);
        }

        $service = $cache->get($serviceHash);
        assert($service !== null);
        return $service;
    }



}