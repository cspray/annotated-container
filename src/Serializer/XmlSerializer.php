<?php

namespace Cspray\AnnotatedContainer\Serializer;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidDefinitionException;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\ServiceDefinitionBuilder;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Exception as PhpException;
use function Cspray\Typiphy\objectType;

final class XmlSerializer implements ContainerDefinitionSerializer {

    private const XML_SCHEMA = 'https://annotated-container.cspray.io/schema/annotated-container-definition.xsd';

    private const ROOT_ELEMENT = 'annotatedContainerDefinition';

    public function serialize(ContainerDefinition $containerDefinition) : string {
        $dom = new DOMDocument(encoding: 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElementNS(self::XML_SCHEMA, self::ROOT_ELEMENT);
        $root->setAttribute('version', AnnotatedContainerVersion::getVersion());

        $dom->appendChild($root);

        $this->addServiceDefinitions($root, $containerDefinition);
        $this->addAliasDefinitions($root, $containerDefinition);
        $this->addConfigurationDefinitions($root, $containerDefinition);
        $this->addServicePrepareDefinitions($root, $containerDefinition);
        $this->addServiceDelegateDefinitions($root, $containerDefinition);
        $this->addInjectDefinitions($root, $containerDefinition);

        $schemaPath = dirname(__DIR__, 2) . '/annotated-container-definition.xsd';
        $dom->schemaValidate($schemaPath);
        return $dom->saveXML();
    }

    private function addServiceDefinitions(DOMElement $root, ContainerDefinition $containerDefinition) : void {
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

    private function addAliasDefinitions(DOMElement $root, ContainerDefinition $containerDefinition) : void {
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

    private function addConfigurationDefinitions(DOMElement $root, ContainerDefinition $containerDefinition) : void {
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

    private function addServicePrepareDefinitions(DOMElement $root, ContainerDefinition $containerDefinition) : void {
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

    private function addServiceDelegateDefinitions(DOMElement $root, ContainerDefinition $containerDefinition) : void {
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

    private function addInjectDefinitions(DOMElement $root, ContainerDefinition $containerDefinition) : void {
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
                $this->addMethodParameterInjectDefinition($targetNode, $injectDefinition);
            } else {
                $this->addClassPropertyInjectDefinition($targetNode, $injectDefinition);
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

    private function addMethodParameterInjectDefinition(DOMElement $root, InjectDefinition $injectDefinition) : void {
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

    private function addClassPropertyInjectDefinition(DOMElement $root, InjectDefinition $injectDefinition) {
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

        $serviceDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:serviceDefinitions/cd:serviceDefinition');
        assert($serviceDefinitions instanceof DOMNodeList);

        $builder = ContainerDefinitionBuilder::newDefinition();

        foreach ($serviceDefinitions as $serviceDefinition) {
            $type = objectType(
                $xpath->query('cd:type/text()', $serviceDefinition)[0]->nodeValue
            );

            $builder = $builder->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete($type)->build()
            );
        }


        return $builder->build();
    }
}