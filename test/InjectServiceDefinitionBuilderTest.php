<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\SimpleUseService;
use PHPUnit\Framework\TestCase;

class InjectServiceDefinitionBuilderTest extends TestCase {

    public function testEmptyMethodNameThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The method for an InjectServiceDefinition must not be blank.');
        InjectServiceDefinitionBuilder::forMethod($serviceDefinition, '');
    }

    public function testEmptyParamTypeThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The param type for an InjectServiceDefinition must not be blank.');
        InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz')->withParam('', '');
    }

    public function testEmptyParamNameThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The param name for an InjectServiceDefinition must not be blank.');
        InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz')->withParam(SimpleUseService\FooInterface::class, '');
    }

    public function testWithParamImmutableBuilder() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $builder1 = InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz');
        $builder2 = $builder1->withParam(SimpleUseService\FooInterface::class, 'foo');

        $this->assertNotSame($builder1, $builder2);
    }

    public function testWithInjectedServiceImmutableBuilder() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $builder1 = InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz');
        $builder2 = $builder1->withInjectedService(ServiceDefinitionBuilder::forConcrete(SimpleUseService\FooInterface::class)->build());

        $this->assertNotSame($builder1, $builder2);
    }

    public function testBuilderWithoutParamThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('An InjectServiceDefinitionBuilder must have a parameter defined before building.');
        InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz')->build();
    }

    public function testBuilderWithoutInjectedServiceThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('An InjectServiceDefinitionBuilder must have an injected service defined before building.');
        InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz')->withParam(SimpleUseService\FooInterface::class, 'foo')->build();
    }

    public function testBuildHasServiceType() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $injectServiceDefinition = InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz')
            ->withParam(SimpleUseService\FooInterface::class, 'foo')
            ->withInjectedService(ServiceDefinitionBuilder::forConcrete(SimpleUseService\BazImplementation::class)->build())
            ->build();

        $this->assertSame($serviceDefinition, $injectServiceDefinition->getService());
    }

    public function testBuildHasMethod() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $injectServiceDefinition = InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz')
            ->withParam(SimpleUseService\FooInterface::class, 'foo')
            ->withInjectedService(ServiceDefinitionBuilder::forConcrete(SimpleUseService\BazImplementation::class)->build())
            ->build();

        $this->assertSame('setBaz', $injectServiceDefinition->getMethod());
    }

    public function testBuildHasParamType() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $injectServiceDefinition = InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz')
            ->withParam(SimpleUseService\FooInterface::class, 'foo')
            ->withInjectedService(ServiceDefinitionBuilder::forConcrete(SimpleUseService\BazImplementation::class)->build())
            ->build();

        $this->assertSame(SimpleUseService\FooInterface::class, $injectServiceDefinition->getParamType());
    }

    public function testBuildHasParamName() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $injectServiceDefinition = InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz')
            ->withParam(SimpleUseService\FooInterface::class, 'foo')
            ->withInjectedService(ServiceDefinitionBuilder::forConcrete(SimpleUseService\BazImplementation::class)->build())
            ->build();

        $this->assertSame('foo', $injectServiceDefinition->getParamName());
    }

    public function testBuildHasInjectedService() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseService\SetterInjection::class)->build();
        $injectServiceDefinition = InjectServiceDefinitionBuilder::forMethod($serviceDefinition, 'setBaz')
            ->withParam(SimpleUseService\FooInterface::class, 'foo')
            ->withInjectedService($injectedService = ServiceDefinitionBuilder::forConcrete(SimpleUseService\BazImplementation::class)->build())
            ->build();

        $this->assertSame($injectedService, $injectServiceDefinition->getInjectedService());

    }

}