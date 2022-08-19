<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumerFactory;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\Internal\CompositeLogger;
use Cspray\AnnotatedContainer\Internal\FileLogger;
use Cspray\AnnotatedContainer\Internal\StdoutLogger;
use Cspray\AnnotatedContainer\ParameterStore;
use Cspray\AnnotatedContainer\ParameterStoreFactory;
use DateTimeImmutable;
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
    private readonly ?LoggerInterface $logger;
    private readonly array $excludedProfiles;

    /**
     * @var list<ParameterStore>
     */
    private readonly array $parameterStores;

    public function __construct(
        private readonly string $xmlFile,
        private readonly BootstrappingDirectoryResolver $directoryResolver,
        private readonly ?ParameterStoreFactory $parameterStoreFactory = null,
        private readonly ?ContainerDefinitionBuilderContextConsumerFactory $consumerFactory = null
    ) {
        try{
            $schemaFile = dirname(__DIR__, 2) . '/annotated-container.xsd';
            $dom = new DOMDocument();
            $dom->load($this->xmlFile);
            libxml_use_internal_errors(true);
            if (!$dom->schemaValidate($schemaFile)) {
                throw InvalidBootstrapConfiguration::fromFileDoesNotValidateSchema($this->xmlFile);
            }

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('ac', 'https://annotated-container.cspray.io/schema/annotated-container.xsd');
            $scanDirectoriesNodes = $xpath->query('/ac:annotatedContainer/ac:scanDirectories/ac:source/ac:dir');
            $scanDirectories = [];
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
                    throw InvalidBootstrapConfiguration::fromConfiguredContainerDefinitionConsumerWrongType();
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
                        throw InvalidBootstrapConfiguration::fromConfiguredParameterStoreWrongType();
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

            $loggingFileNodes = $xpath->query('/ac:annotatedContainer/ac:logging/ac:file/text()');
            $loggingStdoutNodes = $xpath->query('/ac:annotatedContainer/ac:logging/ac:stdout');
            $logger = null;

            $hasLoggingFile = $loggingFileNodes instanceof DOMNodeList && count($loggingFileNodes) === 1;
            $hasStdoutFile = $loggingStdoutNodes instanceof DOMNodeList && count($loggingStdoutNodes) === 1;

            $dateTimeProvider = fn() : DateTimeImmutable => new DateTimeImmutable();

            if ($hasLoggingFile && $hasStdoutFile) {
                $loggingFilePath = $this->directoryResolver->getLogPath($loggingFileNodes[0]->nodeValue);
                $fileLogger = new FileLogger($dateTimeProvider, $loggingFilePath);
                $stdoutLogger = new StdoutLogger($dateTimeProvider);
                $logger = new CompositeLogger($fileLogger, $stdoutLogger);
            } else if ($hasLoggingFile) {
                $loggingFilePath = $this->directoryResolver->getLogPath($loggingFileNodes[0]->nodeValue);
                $logger = new FileLogger($dateTimeProvider, $loggingFilePath);
            } else if ($hasStdoutFile) {
                $logger = new StdoutLogger($dateTimeProvider);
            }

            $excludedProfilesNodes = $xpath->query('/ac:annotatedContainer/ac:logging/ac:exclude/ac:profile/text()');
            $excludedProfiles = [];

            if ($excludedProfilesNodes instanceof DOMNodeList) {
                foreach ($excludedProfilesNodes as $node) {
                    $excludedProfiles[] = $node->nodeValue;
                }
            }

            $this->directories = $scanDirectories;
            $this->contextConsumer = $contextConsumer;
            $this->cacheDir = $cache;
            $this->parameterStores = $parameterStores;
            $this->logger = $logger;
            $this->excludedProfiles = $excludedProfiles;
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
        return $this->logger;
    }

    public function getLoggingExcludedProfiles() : array {
        return $this->excludedProfiles;
    }
}