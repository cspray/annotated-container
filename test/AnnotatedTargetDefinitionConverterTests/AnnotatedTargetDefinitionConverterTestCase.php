<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\AnnotatedTargetType;
use Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainer\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

abstract class AnnotatedTargetDefinitionConverterTestCase extends TestCase {

    protected ServiceDefinition|ServiceDelegateDefinition|ServicePrepareDefinition $definition;

    protected function setUp(): void {
        $subject = new DefaultAnnotatedTargetDefinitionConverter();
        $this->definition = $subject->convert($this->getSubjectTarget());
    }

    protected function getAnnotatedTarget(AttributeType $attributeType, ReflectionClass|ReflectionMethod|ReflectionParameter $reflection) : AnnotatedTarget {
        return new class($attributeType, $reflection) implements AnnotatedTarget {

            public function __construct(
                private readonly AttributeType $attributeType,
                private readonly ReflectionClass|ReflectionMethod|ReflectionParameter $reflection
            ) {}

            public function getTargetType(): AnnotatedTargetType {
                return AnnotatedTargetType::ClassTarget;
            }

            public function getTargetReflection(): ReflectionClass|ReflectionClassConstant|ReflectionProperty|ReflectionMethod|ReflectionParameter|ReflectionFunction {
                return $this->reflection;
            }

            public function getAttributeReflection() : ReflectionAttribute {
                return $this->reflection->getAttributes($this->attributeType->value)[0];
            }

            public function getAttributeInstance(): object {
                return $this->getAttributeReflection()->newInstance();
            }
        };
    }

    abstract protected function getSubjectTarget() : AnnotatedTarget;

}