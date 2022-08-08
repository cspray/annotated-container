<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidBootstrappingConfigurationException;
use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointContainerDefinitionBuilderContextConsumer;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;

use Psr\Log\LoggerInterface;
use function libxml_use_internal_errors;

final class XmlBootstrappingConfiguration implements BootstrappingConfiguration {

    /**
     * @var list<string>
     */
    private readonly array $directories;
    private readonly ?ContainerDefinitionBuilderContextConsumer $contextConsumer;
    private readonly ?string $cacheDir;

    /**
     * @var list<ParameterStore>
     */
    private readonly array $parameterStores;

    public function __construct(
        private readonly string $xmlFile,
        private readonly ?ParameterStoreFactory $parameterStoreFactory = null,
        private readonly ?ContainerDefinitionBuilderContextConsumerFactory $consumerFactory = null
    ) {
        try{
            $schemaFile = dirname(__DIR__) . '/annotated-container.xsd';
            $dom = new DOMDocument();
            $dom->load($this->xmlFile);
            libxml_use_internal_errors(true);
            if (!$dom->schemaValidate($schemaFile)) {
                throw new InvalidBootstrappingConfigurationException(
                    sprintf('Configuration file %s does not validate against the appropriate schema.', $this->xmlFile)
                );
            }

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('ac', 'https://annotated-container.cspray.io/schema/annotated-container.xsd');
            $scanDirectoriesNodes = $xpath->query('/ac:annotatedContainer/ac:scanDirectories/ac:source/ac:dir');
            $scanDirectories = [];
            /** @var DOMNode $scanDirectory */
            foreach ($scanDirectoriesNodes as $scanDirectory) {
                $scanDirectories[] = $scanDirectory->textContent;
            }

            $contextConsumerNodes = $xpath->query('/ac:annotatedContainer/ac:containerDefinitionBuilderContextConsumer/text()');
            $contextConsumer = null;
            if (count($contextConsumerNodes) === 1) {
                $contextConsumerTextNode = $contextConsumerNodes[0];
                $consumerClassType = trim($contextConsumerTextNode->nodeValue);
                if (!class_exists($consumerClassType) ||
                    !is_subclass_of($consumerClassType, ContainerDefinitionBuilderContextConsumer::class)) {
                    throw new InvalidBootstrappingConfigurationException(sprintf(
                        'All entries in containerDefinitionBuilderContextConsumers must be classes that implement %s',
                        ContainerDefinitionBuilderContextConsumer::class
                    ));
                }
                if (isset($this->consumerFactory)) {
                    $contextConsumer = $this->consumerFactory->createConsumer($consumerClassType);
                } else{
                    $contextConsumer = new $consumerClassType();
                }
            }

            $parameterStores = [];
            $parameterStoreNodes = $xpath->query('/ac:annotatedContainer/ac:parameterStores/ac:fqcn/text()');
            if ($parameterStoreNodes instanceof DOMNodeList) {
                foreach ($parameterStoreNodes as $parameterStoreNode) {
                    assert(isset($parameterStoreNode->nodeValue));
                    $parameterStoreType = trim($parameterStoreNode->nodeValue);
                    if (!class_exists($parameterStoreType) || !is_subclass_of($parameterStoreType, ParameterStore::class)) {
                        throw new InvalidBootstrappingConfigurationException(sprintf(
                            'All entries in parameterStores must be classes that implement %s',
                            ParameterStore::class
                        )) ;
                    }
                    if (isset($this->parameterStoreFactory)) {
                        $parameterStore = $this->parameterStoreFactory->createParameterStore($parameterStoreType);
                    } else {
                        $parameterStore = new $parameterStoreType();
                    }
                    $parameterStores[] = $parameterStore;
                }
            }

            /** @var DOMNodeList $cacheDirNodes */
            $cacheDirNodes = $xpath->query('/ac:annotatedContainer/ac:cacheDir');
            $cache = null;
            if (count($cacheDirNodes) === 1) {
                $cache = $cacheDirNodes[0]->textContent;
            }

            $this->directories = $scanDirectories;
            $this->contextConsumer = $contextConsumer;
            $this->cacheDir = $cache;
            $this->parameterStores = $parameterStores;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors(false);
        }
    }

    public function getScanDirectories() : array {
        return $this->directories;
    }

    #[SingleEntrypointContainerDefinitionBuilderContextConsumer]
    public function getContainerDefinitionConsumer() : ?ContainerDefinitionBuilderContextConsumer {
        return $this->contextConsumer;
    }

    /**
     * @return list<ParameterStore>
     */
    public function getParameterStores() : array {
        return $this->parameterStores;
    }

    public function getCacheDirectory() : ?string {
        return $this->cacheDir;
    }

    public function getLogger() : ?LoggerInterface {
        return null;
    }
}