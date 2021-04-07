<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Cspray\AnnotatedInjector\Internal\Interrogator\UseScalarDefinitionInterrogator;
use Cspray\AnnotatedInjector\Internal\Interrogator\UseServiceDefinitionInterrogator;
use Cspray\AnnotatedInjector\Internal\Interrogator\ServiceDefinitionInterrogator;
use Cspray\AnnotatedInjector\Internal\Interrogator\ServicePrepareDefinitionInterrogator;
use Cspray\AnnotatedInjector\Internal\Visitor\UseScalarDefinitionVisitor;
use Cspray\AnnotatedInjector\Internal\Visitor\UseServiceDefinitionVisitor;
use Cspray\AnnotatedInjector\Internal\Visitor\ServiceDefinitionVisitor;
use Cspray\AnnotatedInjector\Internal\Visitor\ServicePrepareDefinitionVisitor;
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

/**
 * @package Cspray\AnnotatedInjector
 */
final class PhpParserInjectorDefinitionCompiler implements InjectorDefinitionCompiler {

    private Parser $parser;
    private NodeTraverserInterface $nodeTraverser;

    public function __construct() {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
    }

    public function compileDirectory(string $environment, array|string $dirs) : InjectorDefinition {
        $rawServiceDefinitions = [];
        $rawServicePrepareDefinitions = [];
        $rawUseScalarDefinitions = [];
        $rawUseServiceDefinitions = [];
        if (is_string($dirs)) {
            $dirs = [$dirs];
        }
        /** @var Node $node */
        foreach ($this->gatherDefinitions($dirs) as $rawDefinition) {
            if ($rawDefinition['definitionType'] === ServiceDefinition::class) {
                $rawServiceDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === ServicePrepareDefinition::class) {
                $rawServicePrepareDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === UseScalarDefinition::class) {
                $rawUseScalarDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === UseServiceDefinition::class) {
                $rawUseServiceDefinitions[] = $rawDefinition;
            }
        }
        $serviceDefinitionInterrogator = new ServiceDefinitionInterrogator(
            $environment,
            ...$this->marshalRawServiceDefinitions($rawServiceDefinitions)
        );
        $servicePrepareInterrogator = new ServicePrepareDefinitionInterrogator(
            $serviceDefinitionInterrogator,
            ...$this->marshalRawServicePrepareDefinitions($rawServicePrepareDefinitions)
        );
        $UseScalarInterrogator = new UseScalarDefinitionInterrogator(
            ...$this->marshalRawUseScalarDefinitions($rawUseScalarDefinitions)
        );
        $UseServiceInterrogator = new UseServiceDefinitionInterrogator(
            ...$this->marshalRawUseServiceDefinitions($rawUseServiceDefinitions)
        );

        return $this->interrogateDefinitions(
            $serviceDefinitionInterrogator,
            $servicePrepareInterrogator,
            $UseScalarInterrogator,
            $UseServiceInterrogator
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
                $rawServiceDefinition['environments'],
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
                    $rawServiceDefinition['environments'],
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
            $marshaledDefinitions[] = new UseScalarDefinition(
                $rawUseScalarDefinition['type'],
                $rawUseScalarDefinition['method'],
                $rawUseScalarDefinition['param'],
                $rawUseScalarDefinition['paramType'],
                $rawUseScalarDefinition['value'],
                $rawUseScalarDefinition['isPlainValue'],
                $rawUseScalarDefinition['isEnvironmentVar']
            );
        }
        return $marshaledDefinitions;
    }

    private function marshalRawUseServiceDefinitions(array $rawUseServiceDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawUseServiceDefinitions as $rawUseServiceDefinition) {
            $marshaledDefinitions[] = new UseServiceDefinition(
                $rawUseServiceDefinition['type'],
                $rawUseServiceDefinition['method'],
                $rawUseServiceDefinition['param'],
                $rawUseServiceDefinition['paramType'],
                $rawUseServiceDefinition['value']
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

            /** @var \SplFileInfo $file */
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
                $UseScalarDefinitionVisitor = new UseScalarDefinitionVisitor();
                $UseServiceDefinitionVisitor = new UseServiceDefinitionVisitor();

                $this->nodeTraverser->addVisitor($serviceDefinitionVisitor);
                $this->nodeTraverser->addVisitor($servicePrepareDefinitionVisitor);
                $this->nodeTraverser->addVisitor($UseScalarDefinitionVisitor);
                $this->nodeTraverser->addVisitor($UseServiceDefinitionVisitor);
                $this->nodeTraverser->traverse($statements);

                $this->nodeTraverser->removeVisitor($serviceDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($servicePrepareDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($UseScalarDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($UseServiceDefinitionVisitor);

                yield from $serviceDefinitionVisitor->getServiceDefinitions();
                yield from $servicePrepareDefinitionVisitor->getServicePrepareDefinitions();
                yield from $UseScalarDefinitionVisitor->getUseScalarDefinitions();
                yield from $UseServiceDefinitionVisitor->getUseServiceDefinitions();
            }
        }
    }

    private function interrogateDefinitions(
        ServiceDefinitionInterrogator $serviceDefinitionInterrogator,
        ServicePrepareDefinitionInterrogator $servicePrepareDefinitionInterrogator,
        UseScalarDefinitionInterrogator $UseScalarDefinitionInterrogator,
        UseServiceDefinitionInterrogator $UseServiceDefinitionInterrogator
    ) : InjectorDefinition {
        $services = iterator_to_array($serviceDefinitionInterrogator->gatherSharedServices());
        $aliases = iterator_to_array($serviceDefinitionInterrogator->gatherAliases());
        $setupMethods = iterator_to_array($servicePrepareDefinitionInterrogator->gatherServicePrepare());
        $UseScalars = iterator_to_array($UseScalarDefinitionInterrogator->gatherUseScalarDefinitions());
        $UseServices = iterator_to_array($UseServiceDefinitionInterrogator->gatherUseServiceDefinitions());

        return new class($services, $aliases, $setupMethods, $UseScalars, $UseServices) implements InjectorDefinition {

            public function __construct(
                private array $services,
                private array $aliases,
                private array $setupMethods,
                private array $UseScalarDefinitions,
                private array $UseServiceDefinitions
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

            public function getUseScalarDefinitions() : array {
                return $this->UseScalarDefinitions;
            }

            public function getUseServiceDefinitions() : array {
                return $this->UseServiceDefinitions;
            }

            public function jsonSerialize() {
                return [];
            }
        };
    }

}