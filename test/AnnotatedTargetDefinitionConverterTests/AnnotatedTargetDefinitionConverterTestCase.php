<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainer\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

abstract class AnnotatedTargetDefinitionConverterTestCase extends TestCase {

    protected ServiceDefinition|ServiceDelegateDefinition|ServicePrepareDefinition|InjectDefinition $definition;

    protected function setUp() : void {
        $subject = new DefaultAnnotatedTargetDefinitionConverter();
        $this->definition = $subject->convert($this->getSubjectTarget());
    }

    protected function getAnnotatedTarget(AttributeType $attributeType, ReflectionClass|ReflectionMethod|ReflectionParameter $reflection, int $attributeIndex = 0) : AnnotatedTarget {
        return new class($attributeType, $reflection, $attributeIndex) implements AnnotatedTarget {

            public function __construct(
                private readonly AttributeType $attributeType,
                private readonly ReflectionClass|ReflectionMethod|ReflectionParameter $reflection,
                private readonly int $attributeIndex
            ) {}

            public function getTargetReflection(): ReflectionClass|ReflectionMethod|ReflectionParameter {
                return $this->reflection;
            }

            public function getAttributeReflection() : ReflectionAttribute {
                return $this->reflection->getAttributes($this->attributeType->value)[$this->attributeIndex];
            }

            public function getAttributeInstance(): object {
                return $this->getAttributeReflection()->newInstance();
            }
        };
    }

    abstract protected function getSubjectTarget() : AnnotatedTarget;

}