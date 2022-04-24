<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use FilesystemIterator;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

final class PhpParserAnnotatedTargetCompiler implements AnnotatedTargetCompiler {

    private readonly Parser $parser;
    private readonly NodeTraverser $nodeTraverser;

    public function __construct() {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
        $this->nodeTraverser->addVisitor(new NameResolver());
        $this->nodeTraverser->addVisitor(new NodeConnectingVisitor());
    }

    public function compile(array $dirs, AnnotatedTargetConsumer $consumer) : void {
        $this->nodeTraverser->addVisitor($visitor = $this->getVisitor($consumer));
        foreach ($dirs as $dir) {
            $dirIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::KEY_AS_PATHNAME |
                    FilesystemIterator::CURRENT_AS_FILEINFO |
                    FilesystemIterator::SKIP_DOTS
                )
            );
            foreach ($dirIterator as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $statements = $this->parser->parse(file_get_contents($file->getPathname()));
                $this->nodeTraverser->traverse($statements);
            }
        }
        $this->nodeTraverser->removeVisitor($visitor);
    }

    private function getVisitor(AnnotatedTargetConsumer $consumer) : NodeVisitor {
        return new class($consumer) extends NodeVisitorAbstract {

            private readonly AnnotatedTargetConsumer $consumer;

            public function __construct(AnnotatedTargetConsumer $consumer) {
                $this->consumer = $consumer;
            }

            public function leaveNode(Node $node) {
                if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
                    $attributes = $this->findAttributes(Service::class, ...$node->attrGroups);
                    if (!empty($attributes)) {
                        $this->consumer->consume($this->getAnnotatedService($node));
                    }
                } else if ($node instanceof Node\Stmt\ClassMethod) {
                    foreach ([AttributeType::ServicePrepare, AttributeType::ServiceDelegate] as $attributeType) {
                        $attributes = $this->findAttributes($attributeType->value, ...$node->attrGroups);
                        if (!empty($attributes)) {
                            $this->consumer->consume($this->getAnnotatedReflectionMethod($node, $attributeType));
                        }
                    }
                }
            }

            /**
             * @param string $attributeType
             * @param AttributeGroup ...$attributeGroups
             * @return Attribute[]
             */
            private function findAttributes(string $attributeType, AttributeGroup... $attributeGroups) : array {
                $attributes = [];
                foreach ($attributeGroups as $attributeGroup) {
                    foreach ($attributeGroup->attrs as $attribute) {
                        if ($attribute->name->toString() === $attributeType) {
                            $attributes[] = $attribute;
                        }
                    }
                }

                return $attributes;
            }

            private function getAnnotatedService(Node\Stmt\Class_|Node\Stmt\Interface_ $node) : AnnotatedTarget {
                return new class($node->namespacedName->toString(), 0) implements AnnotatedTarget {

                    private ReflectionClass $targetReflection;
                    private ReflectionAttribute $attributeReflection;
                    private object $attributeInstance;

                    public function __construct(
                        private readonly string $targetType,
                        private readonly int $attributeIndex
                    ) {}

                    public function getTargetType() : AnnotatedTargetType {
                        return AnnotatedTargetType::ClassTarget;
                    }

                    public function getTargetReflection() : ReflectionClass {
                        if (!isset($this->targetReflection)) {
                            $this->targetReflection = new ReflectionClass($this->targetType);
                        }
                        return $this->targetReflection;
                    }

                    public function getAttributeReflection() : ReflectionAttribute {
                        if (!isset($this->attributeReflection)) {
                            $this->attributeReflection = $this->getTargetReflection()->getAttributes(Service::class)[$this->attributeIndex];
                        }
                        return $this->attributeReflection;
                    }

                    public function getAttributeInstance() : object {
                        if (!isset($this->attributeInstance)) {
                            $this->attributeInstance = $this->getAttributeReflection()->newInstance();
                        }
                        return $this->attributeInstance;
                    }
                };
            }

            private function getAnnotatedReflectionMethod(Node\Stmt\ClassMethod $node, AttributeType $attributeType) : AnnotatedTarget {
                $classType = $node->getAttribute('parent')->namespacedName->toString();
                $method = $node->name->toString();
                return new class($classType, $method, $attributeType) implements AnnotatedTarget {

                    private ReflectionMethod $targetReflection;
                    private ReflectionAttribute $attributeReflection;
                    private object $attributeInstance;

                    public function __construct(
                        private readonly string $classType,
                        private readonly string $method,
                        private readonly AttributeType $attributeType
                    ) {}

                    public function getTargetType() : AnnotatedTargetType {
                        return AnnotatedTargetType::MethodTarget;
                    }

                    public function getTargetReflection() : ReflectionMethod {
                        if (!isset($this->targetReflection)) {
                            $this->targetReflection = new ReflectionMethod(sprintf('%s::%s', $this->classType, $this->method));
                        }
                        return $this->targetReflection;
                    }

                    public function getAttributeReflection() : ReflectionAttribute {
                        if (!isset($this->attributeReflection)) {
                            $this->attributeReflection = $this->getTargetReflection()->getAttributes($this->attributeType->value)[0];
                        }
                        return $this->attributeReflection;
                    }

                    public function getAttributeInstance() : object {
                        if (!isset($this->attributeInstance)) {
                            $this->attributeInstance = $this->getAttributeReflection()->newInstance();
                        }
                        return $this->attributeInstance;
                    }
                };
            }
        };
    }
}