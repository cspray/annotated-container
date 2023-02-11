<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Closure;
use FilesystemIterator;
use Iterator;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use stdClass;

final class VendorScanningThirdPartyInitializerProvider implements ThirdPartyInitializerProvider {

    private readonly Parser $parser;

    /**
     * @var list<class-string<ThirdPartyInitializer>>|null
     */
    private ?array $initializers = null;

    public function __construct(
        private readonly BootstrappingDirectoryResolver $resolver
    ) {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
    }

    public function getThirdPartyInitializers() : array {
        if ($this->initializers === null) {
            $this->initializers = $this->scanVendorDirectoryForInitializers();
            sort($this->initializers);
        }

        return $this->initializers;
    }

    /**
     * @return list<class-string<ThirdPartyInitializer>>
     */
    private function scanVendorDirectoryForInitializers() : array {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NodeConnectingVisitor());
        $nodeTraverser->addVisitor(new NameResolver());
        $data = new stdClass();
        $data->targets = [];
        $nodeTraverser->addVisitor($this->getVisitor(fn(string $initializer) => $data->targets[] = $initializer));

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->resolver->getVendorPath(), FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $nodes = $this->parser->parse(file_get_contents($file->getPathname()));
                if ($nodes !== null) {
                    $nodeTraverser->traverse($nodes);
                    unset($nodes);
                }
            }
        }

        return $data->targets;
    }

    private function getVisitor(Closure $callback) : NodeVisitor {
        return new class($callback) extends NodeVisitorAbstract {

            public function __construct(
                private readonly Closure $callback
            ) {}

            public function leaveNode(Node $node) {
                if ($node instanceof Node\Stmt\Class_) {
                    $className = $node->namespacedName->toString();
                    assert($className !== null);
                    $reflection = new ReflectionClass($className);
                    if ($reflection->isSubclassOf(ThirdPartyInitializer::class)) {
                        ($this->callback)($node->namespacedName?->toString());
                    }
                    unset($reflection);
                }
            }
        };
    }

}
