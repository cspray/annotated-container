<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Cspray\AnnotatedInjector\Interrogator\ServiceDefinitionInterrogator;
use Cspray\AnnotatedInjector\Interrogator\ServiceSetupDefinitionInterrogator;
use Cspray\AnnotatedInjector\Visitor\ServiceDefinitionVisitor;
use Cspray\AnnotatedInjector\Visitor\ServiceSetupDefinitionVisitor;
use Generator;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;

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
            $implementServiceDefinitions = [];
            foreach ($rawServiceDefinition['implements'] as $implementedType) {
                foreach ($rawServiceDefinitions as $_rawServiceDefinition) {
                    if ($_rawServiceDefinition['isInterface'] &&                    // only care about interface services because we're marshalling class implements
                        $implementedType === $_rawServiceDefinition['type'] &&      // actually make sure that the type implemented by the class is the type we're looking at
                        $implementedType !== $rawServiceDefinition['type']          // make sure it isn't the outer type being looked at
                    ) {
                        $implementServiceDefinitions[] = new ServiceDefinition(
                            $_rawServiceDefinition['type'],
                            $_rawServiceDefinition['environments'],
                            [],
                            true
                        );
                    }
                }
            }

            $marshaledDefinitions[] = new ServiceDefinition(
                $rawServiceDefinition['type'],
                $rawServiceDefinition['environments'],
                $implementServiceDefinitions,
                $rawServiceDefinition['isInterface']
            );
        }

        return $marshaledDefinitions;
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
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            $fileContents = file_get_contents($file);
            $statements = $this->parser->parse($fileContents);

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