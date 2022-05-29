<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;
use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use Cspray\AnnotatedContainerFixture\Fixtures;
use ReflectionMethod;
use function Cspray\Typiphy\objectType;

class ServicePrepareConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::ServicePrepare, new ReflectionMethod(
            Fixtures::interfacePrepareServices()->fooInterface()->getName(),
            'setBar'
        ));
    }

    public function testGetServiceDelegateDefinitionInstance() {
        $this->assertInstanceOf(ServicePrepareDefinition::class, $this->definition);
    }

    public function testGetService() {
        $this->assertSame(Fixtures::interfacePrepareServices()->fooInterface(), $this->definition->getService());
    }

    public function testGetMethodIsCorrect() {
        $this->assertSame('setBar', $this->definition->getMethod());
    }
}