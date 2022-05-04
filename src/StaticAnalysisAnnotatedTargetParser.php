<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\Inject;
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
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

final class StaticAnalysisAnnotatedTargetParser implements AnnotatedTargetParser {

    private readonly Parser $parser;
    private readonly NodeTraverser $nodeTraverser;

    public function __construct() {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
        $this->nodeTraverser->addVisitor(new NameResolver());
        $this->nodeTraverser->addVisitor(new NodeConnectingVisitor());
    }

    public function parse(array $dirs) : Generator {
        $this->nodeTraverser->addVisitor($visitor = $this->getVisitor());
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

        yield from $visitor->getTargets();
    }

    private function getVisitor() : NodeVisitor {
        return new class extends NodeVisitorAbstract {

            private array $targets = [];

            public function getTargets() : array {
                return $this->targets;
            }

            public function leaveNode(Node $node) {
                if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
                    $attributes = $this->findAttributes(Service::class, ...$node->attrGroups);
                    if (!empty($attributes)) {
                        $this->targets[] = $this->getAnnotatedService($node);
                    }
                } else if ($node instanceof Node\Stmt\ClassMethod) {
                    foreach ([AttributeType::ServicePrepare, AttributeType::ServiceDelegate] as $attributeType) {
                        $attributes = $this->findAttributes($attributeType->value, ...$node->attrGroups);
                        if (!empty($attributes)) {
                            $this->targets[] = $this->getAnnotatedReflectionMethod($node, $attributeType);
                        }
                    }
                } else if ($node instanceof Node\Param) {
                    $attributes = $this->findAttributes(Inject::class, ...$node->attrGroups);
                    foreach ($attributes as $index => $attribute) {
                        $this->targets[] = $this->getAnnotatedInject($node, $index);
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

            private function getAnnotatedInject(Node\Param $node, int $index) : AnnotatedTarget {
                $classType = $node->getAttribute('parent')->getAttribute('parent')->namespacedName->toString();
                $reflection = (new ReflectionClass($classType))->getMethod($node->getAttribute('parent')->name->toString());
                $reflectionParameter = null;
                foreach ($reflection->getParameters() as $parameter) {
                    if ($parameter->getName() === $node->var->name) {
                        $reflectionParameter = $parameter;
                        break;
                    }
                }
                return new class($reflectionParameter, $index) implements AnnotatedTarget {

                    public function __construct(
                        private readonly ReflectionParameter $targetReflection,
                        private readonly int $index
                    ) {}

                    public function getTargetReflection(): ReflectionParameter {
                        return $this->targetReflection;
                    }

                    public function getAttributeReflection(): ReflectionAttribute {
                        return $this->getTargetReflection()->getAttributes(Inject::class)[$this->index];
                    }

                    public function getAttributeInstance(): object {
                        return $this->getAttributeReflection()->newInstance();
                    }
                };
            }
        };
    }
}