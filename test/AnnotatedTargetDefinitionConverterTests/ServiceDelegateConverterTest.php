<?php

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;
use Cspray\AnnotatedContainer\ServiceDelegateDefinition;
use ReflectionMethod;
use function Cspray\Typiphy\objectType;

class ServiceDelegateConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::ServiceDelegate, new ReflectionMethod(
            DummyApps\ServiceDelegate\ServiceFactory::class,
            'createService'
        ));
    }

    public function testGetServiceDelegateDefinitionInstance() {
        $this->assertInstanceOf(ServiceDelegateDefinition::class, $this->definition);
    }

    public function testGetDelegateTypeIsServiceFactory() {
        $this->assertSame(objectType(DummyApps\ServiceDelegate\ServiceFactory::class), $this->definition->getDelegateType());
    }

    public function testGetDelegateMethodIsCorrect() {
        $this->assertSame('createService', $this->definition->getDelegateMethod());
    }

    public function testGetServiceType() {
        $this->assertSame(objectType(DummyApps\ServiceDelegate\ServiceInterface::class), $this->definition->getServiceType());
    }


}