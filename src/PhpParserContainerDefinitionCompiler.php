<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Internal\Interrogator\ServiceDelegateDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Interrogator\InjectScalarDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Interrogator\InjectServiceDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Interrogator\ServiceDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Interrogator\ServicePrepareDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Visitor\ServiceDelegateVisitor;
use Cspray\AnnotatedContainer\Internal\Visitor\InjectScalarDefinitionVisitor;
use Cspray\AnnotatedContainer\Internal\Visitor\InjectServiceDefinitionVisitor;
use Cspray\AnnotatedContainer\Internal\Visitor\ServiceDefinitionVisitor;
use Cspray\AnnotatedContainer\Internal\Visitor\ServicePrepareDefinitionVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * @package Cspray\AnnotatedContainer
 */
final class PhpParserContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    private Parser $parser;
    private NodeTraverserInterface $nodeTraverser;

    public function __construct() {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
    }

    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition {
        $rawServiceDefinitions = [];
        $rawServicePrepareDefinitions = [];
        $rawUseScalarDefinitions = [];
        $rawUseServiceDefinitions = [];
        $rawServiceDelegateDefinitions = [];
        /** @var Node $node */
        foreach ($this->gatherDefinitions($containerDefinitionCompileOptions->getScanDirectories()) as $rawDefinition) {
            if ($rawDefinition['definitionType'] === ServiceDefinition::class) {
                $rawServiceDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === ServicePrepareDefinition::class) {
                $rawServicePrepareDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === InjectScalarDefinition::class) {
                $rawUseScalarDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === InjectServiceDefinition::class) {
                $rawUseServiceDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === ServiceDelegateDefinition::class) {
                $rawServiceDelegateDefinitions[] = $rawDefinition;
            }
        }
        $serviceDefinitionInterrogator = new ServiceDefinitionInterrogator(
            $containerDefinitionCompileOptions->getProfiles(),
            ...$this->marshalRawServiceDefinitions($rawServiceDefinitions)
        );
        $servicePrepareInterrogator = new ServicePrepareDefinitionInterrogator(
            $serviceDefinitionInterrogator,
            ...$this->marshalRawServicePrepareDefinitions($rawServicePrepareDefinitions)
        );
        $useScalarInterrogator = new InjectScalarDefinitionInterrogator(
            ...$this->marshalRawUseScalarDefinitions($rawUseScalarDefinitions)
        );
        $useServiceInterrogator = new InjectServiceDefinitionInterrogator(
            ...$this->marshalRawUseServiceDefinitions($rawUseServiceDefinitions)
        );
        $serviceDelegateInterrogator = new ServiceDelegateDefinitionInterrogator(
            ...$this->marshalRawServiceDelegateDefinitions($rawServiceDelegateDefinitions)
        );

        return $this->interrogateDefinitions(
            $serviceDefinitionInterrogator,
            $servicePrepareInterrogator,
            $useScalarInterrogator,
            $useServiceInterrogator,
            $serviceDelegateInterrogator
        );
    }

    private function marshalRawServiceDefinitions(array $rawServiceDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawServiceDefinitions as $rawServiceDefinition) {
            $implementServiceDefinitions = $this->marshalCollectionServiceDefinitionFromTypes(
                $rawServiceDefinitions,
                $rawServiceDefinition['implements']
            );

            $extendedServiceDefinitions = $this->marshalCollectionServiceDefinitionFromTypes(
                $rawServiceDefinitions,
                $rawServiceDefinition['extends']
            );

            $marshaledDefinitions[] = new ServiceDefinition(
                $rawServiceDefinition['type'],
                $rawServiceDefinition['profiles'],
                $implementServiceDefinitions,
                $extendedServiceDefinitions,
                $rawServiceDefinition['isInterface'],
                $rawServiceDefinition['isAbstract'],
            );
        }

        return $marshaledDefinitions;
    }

    private function marshalCollectionServiceDefinitionFromTypes(array $rawServiceDefinitions, array $targetTypes) : array {
        $collection = [];
        foreach ($targetTypes as $targetType) {
            $collection[] = $this->marshalServiceDefinitionFromType($rawServiceDefinitions, $targetType);
        }
        return $collection;
    }

    private function marshalServiceDefinitionFromType(array $rawServiceDefinitions, string $targetType) {
        $serviceDefinition = null;
        foreach ($rawServiceDefinitions as $rawServiceDefinition) {
            if ($targetType === $rawServiceDefinition['type']) {
                $serviceDefinition = new ServiceDefinition(
                    $rawServiceDefinition['type'],
                    $rawServiceDefinition['profiles'],
                    $this->marshalCollectionServiceDefinitionFromTypes($rawServiceDefinitions, $rawServiceDefinition['implements']),
                    $this->marshalCollectionServiceDefinitionFromTypes($rawServiceDefinitions, $rawServiceDefinition['extends']),
                    $rawServiceDefinition['isInterface'],
                    $rawServiceDefinition['isAbstract']
                );
            }
        }
        return $serviceDefinition;
    }

    private function marshalRawServicePrepareDefinitions(array $rawServicePrepareDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawServicePrepareDefinitions as $rawServicePrepareDefinition) {
            $marshaledDefinitions[] = new ServicePrepareDefinition(
                $rawServicePrepareDefinition['type'],
                $rawServicePrepareDefinition['method']
            );
        }
        return $marshaledDefinitions;
    }

    private function marshalRawUseScalarDefinitions(array $rawUseScalarDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawUseScalarDefinitions as $rawUseScalarDefinition) {
            $marshaledDefinitions[] = new InjectScalarDefinition(
                $rawUseScalarDefinition['type'],
                $rawUseScalarDefinition['method'],
                $rawUseScalarDefinition['param'],
                $rawUseScalarDefinition['paramType'],
                $rawUseScalarDefinition['value']
            );
        }
        return $marshaledDefinitions;
    }

    private function marshalRawUseServiceDefinitions(array $rawUseServiceDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawUseServiceDefinitions as $rawUseServiceDefinition) {
            $marshaledDefinitions[] = new InjectServiceDefinition(
                $rawUseServiceDefinition['type'],
                $rawUseServiceDefinition['method'],
                $rawUseServiceDefinition['param'],
                $rawUseServiceDefinition['paramType'],
                $rawUseServiceDefinition['value']
            );
        }
        return $marshaledDefinitions;
    }

    private function marshalRawServiceDelegateDefinitions(array $rawServiceDelegateDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawServiceDelegateDefinitions as $rawServiceDelegateDefinition) {
            $marshaledDefinitions[] = new ServiceDelegateDefinition(
                $rawServiceDelegateDefinition['delegateType'],
                $rawServiceDelegateDefinition['delegateMethod'],
                $rawServiceDelegateDefinition['serviceType']
            );
        }
        return $marshaledDefinitions;
    }

    private function gatherDefinitions(array $dirs) : Generator {
        foreach ($dirs as $dir) {
            $dirIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::KEY_AS_PATHNAME |
                    FilesystemIterator::CURRENT_AS_FILEINFO |
                    FilesystemIterator::SKIP_DOTS
                )
            );

            /** @var SplFileInfo $file */
            foreach ($dirIterator as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $statements = $this->parser->parse(file_get_contents($file->getRealPath()));

                $nameResolver = new NameResolver();
                $nodeConnectingVisitor = new NodeConnectingVisitor();
                $this->nodeTraverser->addVisitor($nameResolver);
                $this->nodeTraverser->addVisitor($nodeConnectingVisitor);
                $this->nodeTraverser->traverse($statements);

                $this->nodeTraverser->removeVisitor($nameResolver);
                $this->nodeTraverser->removeVisitor($nodeConnectingVisitor);

                $serviceDefinitionVisitor = new ServiceDefinitionVisitor();
                $servicePrepareDefinitionVisitor = new ServicePrepareDefinitionVisitor();
                $useScalarDefinitionVisitor = new InjectScalarDefinitionVisitor();
                $useServiceDefinitionVisitor = new InjectServiceDefinitionVisitor();
                $serviceDelegateDefinitionVisitor = new ServiceDelegateVisitor();

                $this->nodeTraverser->addVisitor($serviceDefinitionVisitor);
                $this->nodeTraverser->addVisitor($servicePrepareDefinitionVisitor);
                $this->nodeTraverser->addVisitor($useScalarDefinitionVisitor);
                $this->nodeTraverser->addVisitor($useServiceDefinitionVisitor);
                $this->nodeTraverser->addVisitor($serviceDelegateDefinitionVisitor);
                $this->nodeTraverser->traverse($statements);

                $this->nodeTraverser->removeVisitor($serviceDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($servicePrepareDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($useScalarDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($useServiceDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($serviceDelegateDefinitionVisitor);

                yield from $serviceDefinitionVisitor->getServiceDefinitions();
                yield from $servicePrepareDefinitionVisitor->getServicePrepareDefinitions();
                yield from $useScalarDefinitionVisitor->getUseScalarDefinitions();
                yield from $useServiceDefinitionVisitor->getUseServiceDefinitions();
                yield from $serviceDelegateDefinitionVisitor->getServiceDelegateDefinitions();
            }
        }
    }

    private function interrogateDefinitions(
        ServiceDefinitionInterrogator         $serviceDefinitionInterrogator,
        ServicePrepareDefinitionInterrogator  $servicePrepareDefinitionInterrogator,
        InjectScalarDefinitionInterrogator    $useScalarDefinitionInterrogator,
        InjectServiceDefinitionInterrogator   $useServiceDefinitionInterrogator,
        ServiceDelegateDefinitionInterrogator $serviceDelegateDefinitionInterrogator
    ) : ContainerDefinition {
        $services = iterator_to_array($serviceDefinitionInterrogator->gatherSharedServices());
        $aliases = iterator_to_array($serviceDefinitionInterrogator->gatherAliases());
        $setupMethods = iterator_to_array($servicePrepareDefinitionInterrogator->gatherServicePrepare());
        $useScalars = iterator_to_array($useScalarDefinitionInterrogator->gatherUseScalarDefinitions());
        $useServices = iterator_to_array($useServiceDefinitionInterrogator->gatherUseServiceDefinitions());
        $serviceDelegateDefinitions = iterator_to_array($serviceDelegateDefinitionInterrogator->getServiceDelegateDefinitions());

        return new class($services, $aliases, $setupMethods, $useScalars, $useServices, $serviceDelegateDefinitions) implements ContainerDefinition {

            public function __construct(
                private array $services,
                private array $aliases,
                private array $setupMethods,
                private array $useScalarDefinitions,
                private array $useServiceDefinitions,
                private array $serviceDelegateDefinitions
            ) {}

            public function getSharedServiceDefinitions() : array {
                return $this->services;
            }

            public function getAliasDefinitions() : array {
                return $this->aliases;
            }

            public function getServicePrepareDefinitions() : array {
                return $this->setupMethods;
            }

            public function getInjectScalarDefinitions() : array {
                return $this->useScalarDefinitions;
            }

            public function getInjectServiceDefinitions() : array {
                return $this->useServiceDefinitions;
            }

            public function getServiceDelegateDefinitions(): array {
                return $this->serviceDelegateDefinitions;
            }
        };
    }

}