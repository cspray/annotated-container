<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\ConfigurationDefinition;
use Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainer\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

abstract class AnnotatedTargetDefinitionConverterTestCase extends TestCase {

    protected ServiceDefinition|ServiceDelegateDefinition|ServicePrepareDefinition|InjectDefinition|ConfigurationDefinition $definition;

    protected function setUp() : void {
        $subject = new DefaultAnnotatedTargetDefinitionConverter();
        $this->definition = $subject->convert($this->getSubjectTarget());
    }

    protected function getAnnotatedTarget(AttributeType $attributeType, ReflectionClass|ReflectionMethod|ReflectionParameter|ReflectionProperty $reflection, int $attributeIndex = 0) : AnnotatedTarget {
        return new class($attributeType, $reflection, $attributeIndex) implements AnnotatedTarget {

            public function __construct(
                private readonly AttributeType $attributeType,
                private readonly ReflectionClass|ReflectionMethod|ReflectionParameter|ReflectionProperty $reflection,
                private readonly int $attributeIndex
            ) {}

            public function getTargetReflection(): ReflectionClass|ReflectionMethod|ReflectionParameter|ReflectionProperty {
                return $this->reflection;
            }

            public function getAttributeReflection() : ReflectionAttribute {
                return $this->reflection->getAttributes($this->attributeType->value, ReflectionAttribute::IS_INSTANCEOF)[$this->attributeIndex];
            }

            public function getAttributeInstance(): Service|ServicePrepare|ServiceDelegate|Configuration|Inject {
                return $this->getAttributeReflection()->newInstance();
            }
        };
    }

    abstract protected function getSubjectTarget() : AnnotatedTarget;

}