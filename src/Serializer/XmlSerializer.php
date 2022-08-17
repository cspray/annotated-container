<?php

namespace Cspray\AnnotatedContainer\Serializer;

use Cspray\AnnotatedContainer\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\ConfigurationDefinitionBuilder;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidDefinitionException;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\SerializerInjectValueParser;
use Cspray\AnnotatedContainer\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\ServicePrepareDefinitionBuilder;
use DI\Container;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Exception as PhpException;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

final class XmlSerializer implements ContainerDefinitionSerializer {

    private const XML_SCHEMA = 'https://annotated-container.cspray.io/schema/annotated-container-definition.xsd';

    private const ROOT_ELEMENT = 'annotatedContainerDefinition';

    private readonly SerializerInjectValueParser $injectValueParser;

    public function __construct() {
        $this->injectValueParser = new SerializerInjectValueParser();
    }

    public function serialize(ContainerDefinition $containerDefinition) : string {
        $dom = new DOMDocument(encoding: 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElementNS(self::XML_SCHEMA, self::ROOT_ELEMENT);
        $root->setAttribute('version', AnnotatedContainerVersion::getVersion());

        $dom->appendChild($root);

        $this->addServiceDefinitionsToDom($root, $containerDefinition);
        $this->addAliasDefinitionsToDom($root, $containerDefinition);
        $this->addConfigurationDefinitionsToDom($root, $containerDefinition);
        $this->addServicePrepareDefinitionsToDom($root, $containerDefinition);
        $this->addServiceDelegateDefinitionsToDom($root, $containerDefinition);
        $this->addInjectDefinitionsToDom($root, $containerDefinition);

        $schemaPath = dirname(__DIR__, 2) . '/annotated-container-definition.xsd';
        $dom->schemaValidate($schemaPath);
        return $dom->saveXML();
    }

    private function addServiceDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;

        assert($dom instanceof DOMDocument);

        $root->appendChild(
            $serviceDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'serviceDefinitions')
        );

        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $serviceDefinitionsNode->appendChild(
                $serviceDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'serviceDefinition')
            );

            if ($serviceDefinition->isPrimary()) {
                $serviceDefinitionNode->setAttribute('isPrimary', 'true');
            }

            $serviceDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'type', $serviceDefinition->getType()->getName())
            );
            $serviceDefinitionNode->appendChild(
                $nameNode = $dom->createElementNS(self::XML_SCHEMA, 'name')
            );

            $name = $serviceDefinition->getName();
            if ($name !== null) {
                $nameNode->nodeValue = $name;
            }

            $serviceDefinitionNode->appendChild(
                $profilesNode = $dom->createElementNS(self::XML_SCHEMA, 'profiles')
            );

            foreach ($serviceDefinition->getProfiles() as $profile) {
                $profilesNode->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'profile', $profile)
                );
            }

            $serviceDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'concreteOrAbstract', $serviceDefinition->isConcrete() ? 'Concrete' : 'Abstract')
            );
        }

    }

    private function addAliasDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;
        assert($dom instanceof DOMDocument);

        $root->appendChild(
            $aliasDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'aliasDefinitions')
        );

        foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
            $aliasDefinitionsNode->appendChild(
                $aliasDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'aliasDefinition')
            );

            $aliasDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'abstractService', $aliasDefinition->getAbstractService()->getName())
            );
            $aliasDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'concreteService', $aliasDefinition->getConcreteService()->getName())
            );
        }
    }

    private function addConfigurationDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;
        assert($dom instanceof DOMDocument);

        $root->appendChild(
            $configurationDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'configurationDefinitions')
        );

        foreach ($containerDefinition->getConfigurationDefinitions() as $configurationDefinition) {
            $configurationDefinitionsNode->appendChild(
                $configurationDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'configurationDefinition')
            );

            $configurationDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'type', $configurationDefinition->getClass()->getName())
            );

            $configurationDefinitionNode->appendChild(
                $nameNode = $dom->createElementNS(self::XML_SCHEMA, 'name')
            );

            $name = $configurationDefinition->getName();
            if ($name !== null) {
                $nameNode->nodeValue = $name;
            }
        }
    }

    private function addServicePrepareDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;
        assert($dom instanceof DOMDocument);
        $root->appendChild(
            $servicePrepareDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'servicePrepareDefinitions')
        );

        foreach ($containerDefinition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
            $servicePrepareDefinitionsNode->appendChild(
                $servicePrepareDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'servicePrepareDefinition')
            );

            $servicePrepareDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'type', $servicePrepareDefinition->getService()->getName())
            );

            $servicePrepareDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'method', $servicePrepareDefinition->getMethod())
            );
        }
    }

    private function addServiceDelegateDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;
        assert($dom instanceof DOMDocument);

        $root->appendChild(
            $serviceDelegateDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'serviceDelegateDefinitions')
        );

        foreach ($containerDefinition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            $serviceDelegateDefinitionsNode->appendChild(
                $serviceDelegateDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'serviceDelegateDefinition')
            );

            $serviceDelegateDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'service', $serviceDelegateDefinition->getServiceType()->getName())
            );
            $serviceDelegateDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'delegateType', $serviceDelegateDefinition->getDelegateType()->getName())
            );
            $serviceDelegateDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'delegateMethod', $serviceDelegateDefinition->getDelegateMethod())
            );
        }
    }

    private function addInjectDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;
        assert($dom instanceof DOMDocument);

        $root->appendChild(
            $injectDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'injectDefinitions')
        );

        foreach ($containerDefinition->getInjectDefinitions() as $injectDefinition) {
            try{
                $serializedValue = serialize($injectDefinition->getValue());
            } catch(PhpException $exception) {
                throw new InvalidDefinitionException(
                    'An InjectDefinition with a value that cannot be serialized was provided.',
                    previous: $exception
                );
            }

            $dom = $root->ownerDocument;
            assert($dom instanceof DOMDocument);

            $injectDefinitionsNode->appendChild(
                $injectDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'injectDefinition')
            );

            $injectDefinitionNode->appendChild(
                $targetNode = $dom->createElementNS(self::XML_SCHEMA, 'target')
            );

            if ($injectDefinition->getTargetIdentifier()->isMethodParameter()) {
                $this->addMethodParameterInjectDefinitionToDom($targetNode, $injectDefinition);
            } else {
                $this->addClassPropertyInjectDefinitionToDom($targetNode, $injectDefinition);
            }

            $injectDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'valueType', $injectDefinition->getType()->getName())
            );

            $injectDefinitionNode->appendChild(
                $valueNode = $dom->createElementNS(self::XML_SCHEMA, 'value')
            );

            $valueNode->appendChild(
                $dom->createCDATASection(base64_encode($serializedValue))
            );

            $injectDefinitionNode->appendChild(
                $profilesNode = $dom->createElementNS(self::XML_SCHEMA, 'profiles')
            );

            foreach ($injectDefinition->getProfiles() as $profile) {
                $profilesNode->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'profile', $profile)
                );
            }

            $injectDefinitionNode->appendChild(
                $storeNode = $dom->createElementNS(self::XML_SCHEMA, 'store')
            );

            $store = $injectDefinition->getStoreName();
            if ($store !== null) {
                $storeNode->nodeValue = $store;
            }
        }
    }

    private function addMethodParameterInjectDefinitionToDom(DOMElement $root, InjectDefinition $injectDefinition) : void {
        $dom = $root->ownerDocument;
        assert($dom instanceof DOMDocument);

        $root->appendChild(
            $classMethodNode = $dom->createElementNS(self::XML_SCHEMA, 'classMethod')
        );

        $classMethodNode->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'class', $injectDefinition->getTargetIdentifier()->getClass()->getName())
        );

        $methodName = $injectDefinition->getTargetIdentifier()->getMethodName();
        assert($methodName !== null);
        $classMethodNode->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'method', $methodName)
        );

        $classMethodNode->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'parameter', $injectDefinition->getTargetIdentifier()->getName())
        );
    }

    private function addClassPropertyInjectDefinitionToDom(DOMElement $root, InjectDefinition $injectDefinition) {
        $dom = $root->ownerDocument;
        assert($dom instanceof DOMDocument);

        $root->appendChild(
            $classPropertyNode = $dom->createElementNS(self::XML_SCHEMA, 'classProperty')
        );

        $classPropertyNode->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'class', $injectDefinition->getTargetIdentifier()->getClass()->getName())
        );

        $classPropertyNode->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'property', $injectDefinition->getTargetIdentifier()->getName())
        );
    }

    public function deserialize(string $serializedDefinition) : ContainerDefinition {
        $dom = new DOMDocument(encoding: 'UTF-8');
        $dom->loadXML($serializedDefinition);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('cd', self::XML_SCHEMA);

        $builder = ContainerDefinitionBuilder::newDefinition();

        $builder = $this->addServiceDefinitionsToBuilder($builder, $xpath);
        $builder = $this->addAliasDefinitionsToBuilder($builder, $xpath);
        $builder = $this->addServicePrepareDefinitionsToBuilder($builder, $xpath);
        $builder = $this->addServiceDelegateDefinitionsToBuilder($builder, $xpath);
        $builder = $this->addInjectDefinitionsToBuilder($builder, $xpath);
        $builder = $this->addConfigurationDefinitionsToBuilder($builder, $xpath);

        return $builder->build();
    }

    private function addServiceDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $serviceDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:serviceDefinitions/cd:serviceDefinition');
        assert($serviceDefinitions instanceof DOMNodeList);

        foreach ($serviceDefinitions as $serviceDefinition) {
            $type = objectType(
                $xpath->query('cd:type/text()', $serviceDefinition)[0]->nodeValue
            );

            $concreteOrAbstract = $xpath->query('cd:concreteOrAbstract/text()', $serviceDefinition)[0]->nodeValue;
            $isPrimary = $xpath->query('@isPrimary', $serviceDefinition)[0]?->value;
            if ($concreteOrAbstract === 'Concrete') {
                $serviceBuilder = ServiceDefinitionBuilder::forConcrete($type, $isPrimary === 'true');
            } else {
                $serviceBuilder = ServiceDefinitionBuilder::forAbstract($type);
            }

            $name = $xpath->query('cd:name/text()', $serviceDefinition)[0]?->nodeValue;
            if ($name !== null) {
                $serviceBuilder = $serviceBuilder->withName($name);
            }

            $profiles = $xpath->query('cd:profiles/cd:profile', $serviceDefinition);
            $serviceProfiles = [];
            foreach ($profiles as $profile) {
                assert($profile instanceof DOMElement);
                $serviceProfiles[] = $profile->nodeValue;
            }

            $serviceBuilder = $serviceBuilder->withProfiles($serviceProfiles);

            $builder = $builder->withServiceDefinition($serviceBuilder->build());
        }

        return $builder;
    }

    private function addAliasDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $aliasDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:aliasDefinitions/cd:aliasDefinition');
        assert($aliasDefinitions instanceof DOMNodeList);

        foreach ($aliasDefinitions as $aliasDefinition) {
            $abstract = $xpath->query('cd:abstractService/text()', $aliasDefinition)[0]->nodeValue;
            $concrete = $xpath->query('cd:concreteService/text()', $aliasDefinition)[0]->nodeValue;
            $builder = $builder->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract(objectType($abstract))->withConcrete(objectType($concrete))->build()
            );
        }

        return $builder;
    }

    private function addServicePrepareDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $prepareDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:servicePrepareDefinitions/cd:servicePrepareDefinition');
        assert($prepareDefinitions instanceof DOMNodeList);

        foreach ($prepareDefinitions as $prepareDefinition) {
            $service = $xpath->query('cd:type/text()', $prepareDefinition)[0]->nodeValue;
            $method = $xpath->query('cd:method/text()', $prepareDefinition)[0]->nodeValue;
            $builder = $builder->withServicePrepareDefinition(
                ServicePrepareDefinitionBuilder::forMethod(objectType($service), $method)->build()
            );
        }

        return $builder;
    }

    private function addServiceDelegateDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $delegateDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:serviceDelegateDefinitions/cd:serviceDelegateDefinition');
        assert($delegateDefinitions instanceof DOMNodeList);

        foreach ($delegateDefinitions as $delegateDefinition) {
            $service = $xpath->query('cd:service/text()', $delegateDefinition)[0]->nodeValue;
            $delegateType = $xpath->query('cd:delegateType/text()', $delegateDefinition)[0]->nodeValue;
            $delegateMethod = $xpath->query('cd:delegateMethod/text()', $delegateDefinition)[0]->nodeValue;

            $builder = $builder->withServiceDelegateDefinition(
                ServiceDelegateDefinitionBuilder::forService(objectType($service))
                    ->withDelegateMethod(objectType($delegateType), $delegateMethod)
                    ->build()

            );
        }

        return $builder;
    }

    private function addInjectDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $injectDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:injectDefinitions/cd:injectDefinition');
        assert($injectDefinitions instanceof DOMNodeList);

        foreach ($injectDefinitions as $injectDefinition) {
            $valueType = $xpath->query('cd:valueType/text()', $injectDefinition)[0]->nodeValue;
            $store = $xpath->query('cd:store/text()', $injectDefinition)[0]?->nodeValue;
            $profiles = $xpath->query('cd:profiles/cd:profile/text()', $injectDefinition);
            $encodedSerializedValue = $xpath->query('cd:value/text()', $injectDefinition)[0]->nodeValue;
            $serializedValue = base64_decode($encodedSerializedValue);
            $value = unserialize($serializedValue);
            $valueType = $this->injectValueParser->convertStringToType($valueType);

            $hasClassMethod = $xpath->query('cd:target/cd:classMethod', $injectDefinition)->count() === 1;
            if ($hasClassMethod) {
                $type = $xpath->query('cd:target/cd:classMethod/cd:class/text()', $injectDefinition)[0]->nodeValue;
                $methodName = $xpath->query('cd:target/cd:classMethod/cd:method/text()', $injectDefinition)[0]->nodeValue;
                $parameter = $xpath->query('cd:target/cd:classMethod/cd:parameter/text()', $injectDefinition)[0]->nodeValue;
                $injectBuilder = InjectDefinitionBuilder::forService(objectType($type))
                    ->withMethod($methodName, $valueType, $parameter);
            } else {
                $type = $xpath->query('cd:target/cd:classProperty/cd:class/text()', $injectDefinition)[0]->nodeValue;
                $name = $xpath->query('cd:target/cd:classProperty/cd:property/text()', $injectDefinition)[0]->nodeValue;
                $injectBuilder = InjectDefinitionBuilder::forService(objectType($type))
                    ->withProperty($valueType, $name);
            }

            $injectBuilder = $injectBuilder->withValue($value);

            $injectProfiles = [];
            foreach ($profiles as $profile) {
                $injectProfiles[] = $profile->nodeValue;
            }

            $injectBuilder = $injectBuilder->withProfiles(...$injectProfiles);

            if ($store !== null) {
                $injectBuilder = $injectBuilder->withStore($store);
            }

            $builder = $builder->withInjectDefinition($injectBuilder->build());
        }

        return $builder;
    }

    private function addConfigurationDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $configurationDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:configurationDefinitions/cd:configurationDefinition');
        assert($configurationDefinitions instanceof DOMNodeList);

        foreach ($configurationDefinitions as $configurationDefinition) {
            $type = $xpath->query('cd:type/text()', $configurationDefinition)[0]->nodeValue;
            $name = $xpath->query('cd:name/text()', $configurationDefinition)[0]?->nodeValue;
            $configBuilder = ConfigurationDefinitionBuilder::forClass(objectType($type));
            if ($name !== null) {
                $configBuilder = $configBuilder->withName($name);
            }

            $builder = $builder->withConfigurationDefinition(
                $configBuilder->build()
            );
        }

        return $builder;
    }
}