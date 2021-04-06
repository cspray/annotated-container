<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Cspray\AnnotatedInjector\Interrogator\DefineScalarDefinitionInterrogator;
use Cspray\AnnotatedInjector\Interrogator\DefineServiceDefinitionInterrogator;
use Cspray\AnnotatedInjector\Interrogator\ServiceDefinitionInterrogator;
use Cspray\AnnotatedInjector\Interrogator\ServicePrepareDefinitionInterrogator;
use Cspray\AnnotatedInjector\Visitor\DefineScalarDefinitionVisitor;
use Cspray\AnnotatedInjector\Visitor\DefineServiceDefinitionVisitor;
use Cspray\AnnotatedInjector\Visitor\ServiceDefinitionVisitor;
use Cspray\AnnotatedInjector\Visitor\ServicePrepareDefinitionVisitor;
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
        $rawDefineScalarDefinitions = [];
        $rawDefineServiceDefinitions = [];
        /** @var Node $node */
        foreach ($this->gatherDefinitions($dirs) as $rawDefinition) {
            if ($rawDefinition['definitionType'] === ServiceDefinition::class) {
                $rawServiceDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === ServicePrepareDefinition::class) {
                $rawServicePrepareDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === DefineScalarDefinition::class) {
                $rawDefineScalarDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === DefineServiceDefinition::class) {
                $rawDefineServiceDefinitions[] = $rawDefinition;
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
        $defineScalarInterrogator = new DefineScalarDefinitionInterrogator(
            ...$this->marshalRawDefineScalarDefinitions($rawDefineScalarDefinitions)
        );
        $defineServiceInterrogator = new DefineServiceDefinitionInterrogator(
            ...$this->marshalRawDefineServiceDefinitions($rawDefineServiceDefinitions)
        );

        return $this->interrogateDefinitions(
            $serviceDefinitionInterrogator,
            $servicePrepareInterrogator,
            $defineScalarInterrogator,
            $defineServiceInterrogator
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

    private function marshalRawDefineScalarDefinitions(array $rawDefineScalarDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawDefineScalarDefinitions as $rawDefineScalarDefinition) {
            $marshaledDefinitions[] = new DefineScalarDefinition(
                $rawDefineScalarDefinition['type'],
                $rawDefineScalarDefinition['method'],
                $rawDefineScalarDefinition['param'],
                $rawDefineScalarDefinition['paramType'],
                $rawDefineScalarDefinition['value'],
                $rawDefineScalarDefinition['isPlainValue'],
                $rawDefineScalarDefinition['isEnvironmentVar']
            );
        }
        return $marshaledDefinitions;
    }

    private function marshalRawDefineServiceDefinitions(array $rawDefineServiceDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawDefineServiceDefinitions as $rawDefineServiceDefinition) {
            $marshaledDefinitions[] = new DefineServiceDefinition(
                $rawDefineServiceDefinition['type'],
                $rawDefineServiceDefinition['method'],
                $rawDefineServiceDefinition['param'],
                $rawDefineServiceDefinition['paramType'],
                $rawDefineServiceDefinition['value']
            );
        }
        return $marshaledDefinitions;
    }

    private function gatherDefinitions(string $dir) : Generator {
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
            if ($file->isDir()) {
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
            $defineScalarDefinitionVisitor = new DefineScalarDefinitionVisitor();
            $defineServiceDefinitionVisitor = new DefineServiceDefinitionVisitor();

            $this->nodeTraverser->addVisitor($serviceDefinitionVisitor);
            $this->nodeTraverser->addVisitor($servicePrepareDefinitionVisitor);
            $this->nodeTraverser->addVisitor($defineScalarDefinitionVisitor);
            $this->nodeTraverser->addVisitor($defineServiceDefinitionVisitor);
            $this->nodeTraverser->traverse($statements);

            $this->nodeTraverser->removeVisitor($serviceDefinitionVisitor);
            $this->nodeTraverser->removeVisitor($servicePrepareDefinitionVisitor);
            $this->nodeTraverser->removeVisitor($defineScalarDefinitionVisitor);
            $this->nodeTraverser->removeVisitor($defineServiceDefinitionVisitor);

            yield from $serviceDefinitionVisitor->getServiceDefinitions();
            yield from $servicePrepareDefinitionVisitor->getServicePrepareDefinitions();
            yield from $defineScalarDefinitionVisitor->getDefineScalarDefinitions();
            yield from $defineServiceDefinitionVisitor->getDefineServiceDefinitions();
        }
    }

    private function interrogateDefinitions(
        ServiceDefinitionInterrogator $serviceDefinitionInterrogator,
        ServicePrepareDefinitionInterrogator $servicePrepareDefinitionInterrogator,
        DefineScalarDefinitionInterrogator $defineScalarDefinitionInterrogator,
        DefineServiceDefinitionInterrogator $defineServiceDefinitionInterrogator
    ) : InjectorDefinition {
        $services = iterator_to_array($serviceDefinitionInterrogator->gatherSharedServices());
        $aliases = iterator_to_array($serviceDefinitionInterrogator->gatherAliases());
        $setupMethods = iterator_to_array($servicePrepareDefinitionInterrogator->gatherServicePrepare());
        $defineScalars = iterator_to_array($defineScalarDefinitionInterrogator->gatherDefineScalarDefinitions());
        $defineServices = iterator_to_array($defineServiceDefinitionInterrogator->gatherDefineServiceDefinitions());

        return new class($services, $aliases, $setupMethods, $defineScalars, $defineServices) implements InjectorDefinition {

            public function __construct(
                private array $services,
                private array $aliases,
                private array $setupMethods,
                private array $defineScalarDefinitions,
                private array $defineServiceDefinitions
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

            public function getDefineScalarDefinitions() : array {
                return $this->defineScalarDefinitions;
            }

            public function getDefineServiceDefinitions() : array {
                return $this->defineServiceDefinitions;
            }

            public function jsonSerialize() {
                return [];
            }
        };
    }

}