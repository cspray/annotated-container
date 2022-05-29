<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;
use Cspray\AnnotatedContainer\ServiceDelegateDefinition;
use Cspray\AnnotatedContainerFixture\Fixtures;
use ReflectionMethod;
use function Cspray\Typiphy\objectType;

class ServiceDelegateConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::ServiceDelegate, new ReflectionMethod(
            Fixtures::delegatedService()->serviceFactory()->getName(),
            'createService'
        ));
    }

    public function testGetServiceDelegateDefinitionInstance() {
        $this->assertInstanceOf(ServiceDelegateDefinition::class, $this->definition);
    }

    public function testGetDelegateTypeIsServiceFactory() {
        $this->assertSame(Fixtures::delegatedService()->serviceFactory(), $this->definition->getDelegateType());
    }

    public function testGetDelegateMethodIsCorrect() {
        $this->assertSame('createService', $this->definition->getDelegateMethod());
    }

    public function testGetServiceType() {
        $this->assertSame(Fixtures::delegatedService()->serviceInterface(), $this->definition->getServiceType());
    }


}