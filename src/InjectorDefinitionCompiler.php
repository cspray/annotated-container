<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Cspray\AnnotatedInjector\Interrogator\ServiceDefinitionInterrogator;
use Cspray\AnnotatedInjector\Interrogator\ServiceSetupDefinitionInterrogator;
use Cspray\AnnotatedInjector\Visitor\ServiceDefinitionVisitor;
use Cspray\AnnotatedInjector\Visitor\ServiceSetupDefinitionVisitor;
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

class InjectorDefinitionCompiler {

    private Parser $parser;
    private NodeTraverserInterface $nodeTraverser;

    public function __construct() {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
    }

    public function compileDirectory(string $dir, string $environment) : InjectorDefinition {
        $rawServiceDefinitions = [];
        $rawServiceSetupDefinitions = [];
        /** @var Node $node */
        foreach ($this->gatherDefinitions($dir) as $rawDefinition) {
            if ($rawDefinition['definitionType'] === ServiceDefinition::class) {
                $rawServiceDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === ServiceSetupDefinition::class) {
                $rawServiceSetupDefinitions[] = $rawDefinition;
            }
        }
        $serviceDefinitionInterrogator = new ServiceDefinitionInterrogator(
            $environment,
            ...$this->marshalRawServiceDefinitions($rawServiceDefinitions)
        );
        return $this->interrogateDefinitions(
            $serviceDefinitionInterrogator,
            new ServiceSetupDefinitionInterrogator(
                $serviceDefinitionInterrogator,
                ...$this->marshalRawServiceSetupDefinitions($rawServiceSetupDefinitions)
            )
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

    private function marshalRawServiceSetupDefinitions(array $rawServiceSetupDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawServiceSetupDefinitions as $rawServiceSetupDefinition) {
            $marshaledDefinitions[] = new ServiceSetupDefinition(
                $rawServiceSetupDefinition['type'],
                $rawServiceSetupDefinition['method']
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

            $serviceDefinitionVisitor = new ServiceDefinitionVisitor();
            $serviceSetupDefinitionVisitor = new ServiceSetupDefinitionVisitor();

            $this->nodeTraverser->addVisitor(new NameResolver());
            $this->nodeTraverser->addVisitor(new NodeConnectingVisitor());
            $this->nodeTraverser->addVisitor($serviceDefinitionVisitor);
            $this->nodeTraverser->addVisitor($serviceSetupDefinitionVisitor);
            $this->nodeTraverser->traverse($statements);

            yield from $serviceDefinitionVisitor->getServiceDefinitions();
            yield from $serviceSetupDefinitionVisitor->getServiceSetupDefinitions();
        }
    }

    private function interrogateDefinitions(
        ServiceDefinitionInterrogator $serviceDefinitionInterrogator,
        ServiceSetupDefinitionInterrogator $serviceSetupDefinitionInterrogator
    ) : InjectorDefinition {
        $services = iterator_to_array($serviceDefinitionInterrogator->gatherSharedServices());
        $aliases = iterator_to_array($serviceDefinitionInterrogator->gatherAliases());
        $setupMethods = iterator_to_array($serviceSetupDefinitionInterrogator->gatherServiceSetup());


        return new class($services, $aliases, $setupMethods) implements InjectorDefinition {

            public function __construct(
                private array $services,
                private array $aliases,
                private array $setupMethods
            ) {}

            public function getSharedServiceDefinitions() : array {
                return $this->services;
            }

            public function getAliasDefinitions() : array {
                return $this->aliases;
            }

            public function getServiceSetup() : array {
                return $this->setupMethods;
            }

            public function jsonSerialize() {
                return [];
            }
        };
    }

}