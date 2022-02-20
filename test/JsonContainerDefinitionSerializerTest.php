<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\AbstractSharedServices;
use Cspray\AnnotatedContainer\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseScalar;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseService;
use Cspray\AnnotatedContainer\DummyApps\MultipleSimpleServices;
use PHPUnit\Framework\TestCase;

class JsonContainerDefinitionSerializerTest extends TestCase {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private JsonContainerDefinitionSerializer $subject;

    protected function setUp(): void {
        parent::setUp();
        $this->containerDefinitionCompiler = new PhpParserContainerDefinitionCompiler();
        $this->subject = new JsonContainerDefinitionSerializer();
    }

    /** ======================================== Serialization Testing ==============================================*/

    public function testSerializeSimpleServicesHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleServices')
                ->withProfiles('default')
                ->build()
        );

        $expectedFooInterface = [
            'type' => SimpleServices\FooInterface::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => true,
            'isConcrete' => false
        ];
        $expectedFooImplementation = [
            'type' => SimpleServices\FooImplementation::class,
            'implementedServices' => [md5(SimpleServices\FooInterface::class)],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(2, $actual['compiledServiceDefinitions']);

        $this->assertArrayHasKey(md5(SimpleServices\FooInterface::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedFooInterface, $actual['compiledServiceDefinitions'][md5(SimpleServices\FooInterface::class)]);

        $this->assertArrayHasKey(md5(SimpleServices\FooImplementation::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedFooImplementation, $actual['compiledServiceDefinitions'][md5(SimpleServices\FooImplementation::class)]);
    }

    public function testSerializeSimpleServicesHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleServices')
            ->withProfiles('default')
            ->build());
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(2, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleServices\FooInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleServices\FooImplementation::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeSimpleServicesHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleServices')
            ->withProfiles('default')
            ->build());
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertCount(1, $actual['aliasDefinitions']);
        $this->assertContains([
            'original' => md5(SimpleServices\FooInterface::class),
            'alias' => md5(SimpleServices\FooImplementation::class)
        ], $actual['aliasDefinitions']);
    }

    public function testSerializeSimpleServicesHasEmptyServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeSimpleServicesHasEmptyInjectServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleServices')->withProfiles('default')->build());
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectServiceDefinitions', $actual);
        $this->assertEmpty($actual['injectServiceDefinitions']);
    }

    public function testSerializeSimpleServicesHasEmptyInjectScalarDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectScalarDefinitions', $actual);
        $this->assertEmpty($actual['injectScalarDefinitions']);
    }

    public function testSerializeSimpleServicesHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeMultipleSimpleServicesHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/MultipleSimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $expectedFooInterface = [
            'type' => MultipleSimpleServices\FooInterface::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => true,
            'isConcrete' => false
        ];
        $expectedFooImplementation = [
            'type' => MultipleSimpleServices\FooImplementation::class,
            'implementedServices' => [md5(MultipleSimpleServices\FooInterface::class)],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];

        $expectedBarInterface = [
            'type' => MultipleSimpleServices\BarInterface::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => true,
            'isConcrete' => false
        ];
        $expectedBarImplementation = [
            'type' => MultipleSimpleServices\BarImplementation::class,
            'implementedServices' => [md5(MultipleSimpleServices\BarInterface::class)],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(4, $actual['compiledServiceDefinitions']);

        $this->assertArrayHasKey(md5(MultipleSimpleServices\FooInterface::class), $actual['compiledServiceDefinitions']);
        $this->assertArrayHasKey(md5(MultipleSimpleServices\FooImplementation::class), $actual['compiledServiceDefinitions']);
        $this->assertArrayHasKey(md5(MultipleSimpleServices\BarInterface::class), $actual['compiledServiceDefinitions']);
        $this->assertArrayHasKey(md5(MultipleSimpleServices\BarImplementation::class), $actual['compiledServiceDefinitions']);

        $this->assertEquals($expectedFooInterface, $actual['compiledServiceDefinitions'][md5(MultipleSimpleServices\FooInterface::class)]);
        $this->assertEquals($expectedFooImplementation, $actual['compiledServiceDefinitions'][md5(MultipleSimpleServices\FooImplementation::class)]);
        $this->assertEquals($expectedBarInterface, $actual['compiledServiceDefinitions'][md5(MultipleSimpleServices\BarInterface::class)]);
        $this->assertEquals($expectedBarImplementation, $actual['compiledServiceDefinitions'][md5(MultipleSimpleServices\BarImplementation::class)]);
    }

    public function testSerializeMultipleSimpleServicesHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/MultipleSimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(4, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(MultipleSimpleServices\FooInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(MultipleSimpleServices\FooImplementation::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(MultipleSimpleServices\BarInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(MultipleSimpleServices\BarImplementation::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeMultipleServicesHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/MultipleSimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertCount(2, $actual['aliasDefinitions']);
        $this->assertContains([
            'original' => md5(MultipleSimpleServices\BarInterface::class),
            'alias' => md5(MultipleSimpleServices\BarImplementation::class),
        ], $actual['aliasDefinitions']);
        $this->assertContains([
            'original' => md5(MultipleSimpleServices\FooInterface::class),
            'alias' => md5(MultipleSimpleServices\FooImplementation::class)
        ], $actual['aliasDefinitions']);
    }

    public function testSerializeMultipleSimpleServicesHasEmptyServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/MultipleSimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeMultipleSimpleServicesHasEmptyInjectServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/MultipleSimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectServiceDefinitions', $actual);
        $this->assertEmpty($actual['injectServiceDefinitions']);
    }

    public function testSerializeMultipleSimpleServicesHasEmptyInjectScalarDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/MultipleSimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectScalarDefinitions', $actual);
        $this->assertEmpty($actual['injectScalarDefinitions']);
    }

    public function testSerializeMultipleSimpleServicesHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/MultipleSimpleServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeAbstractSharedServicesHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/AbstractSharedServices')->withProfiles('default')->build()
        );

        $expectedAbstractFoo = [
            'type' => AbstractSharedServices\AbstractFoo::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => true,
            'isConcrete' => false
        ];
        $expectedFooImplementation = [
            'type' => AbstractSharedServices\FooImplementation::class,
            'implementedServices' => [md5(AbstractSharedServices\AbstractFoo::class)],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];

        $actual = json_decode($this->subject->serialize($containerDefinition), 2);

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(2, $actual['compiledServiceDefinitions']);

        $this->assertArrayHasKey(md5(AbstractSharedServices\AbstractFoo::class), $actual['compiledServiceDefinitions']);
        $this->assertArrayHasKey(md5(AbstractSharedServices\FooImplementation::class), $actual['compiledServiceDefinitions']);

        $this->assertEquals($expectedAbstractFoo, $actual['compiledServiceDefinitions'][md5(AbstractSharedServices\AbstractFoo::class)]);
        $this->assertEquals($expectedFooImplementation, $actual['compiledServiceDefinitions'][md5(AbstractSharedServices\FooImplementation::class)]);
    }

    public function testSerializeAbstractSharedServicesHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/AbstractSharedServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(2, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(AbstractSharedServices\AbstractFoo::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(AbstractSharedServices\FooImplementation::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeAbstractSharedServicesHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/AbstractSharedServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertCount(1, $actual['aliasDefinitions']);
        $this->assertContains([
            'original' => md5(AbstractSharedServices\AbstractFoo::class),
            'alias' => md5(AbstractSharedServices\FooImplementation::class)
        ], $actual['aliasDefinitions']);
    }

    public function testSerializeAbstractSharedServicesHasEmptyServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/AbstractSharedServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeAbstractSharedServicesHasEmptyInjectServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/AbstractSharedServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectServiceDefinitions', $actual);
        $this->assertEmpty($actual['injectServiceDefinitions']);
    }

    public function testSerializeAbstractSharedServicesHasEmptyInjectScalarDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/AbstractSharedServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectScalarDefinitions', $actual);
        $this->assertEmpty($actual['injectScalarDefinitions']);
    }

    public function testSerializeAbstractSharedServicesHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/AbstractSharedServices')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeInterfaceServicePrepareHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InterfaceServicePrepare')->withProfiles('default')->build()
        );

        $expectedFooInterface = [
            'type' => InterfaceServicePrepare\FooInterface::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => true,
            'isConcrete' => false
        ];
        $expectedFooImplementation = [
            'type' => InterfaceServicePrepare\FooImplementation::class,
            'implementedServices' => [md5(InterfaceServicePrepare\FooInterface::class)],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(2, $actual['compiledServiceDefinitions']);

        $this->assertArrayHasKey(md5(InterfaceServicePrepare\FooInterface::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedFooInterface, $actual['compiledServiceDefinitions'][md5(InterfaceServicePrepare\FooInterface::class)]);

        $this->assertArrayHasKey(md5(InterfaceServicePrepare\FooImplementation::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedFooImplementation, $actual['compiledServiceDefinitions'][md5(InterfaceServicePrepare\FooImplementation::class)]);
    }

    public function testSerializeInterfaceServicePrepareHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InterfaceServicePrepare')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(2, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(InterfaceServicePrepare\FooInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(InterfaceServicePrepare\FooImplementation::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeInterfaceServicePrepareHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InterfaceServicePrepare')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertCount(1, $actual['aliasDefinitions']);
        $this->assertContains([
            'original' => md5(InterfaceServicePrepare\FooInterface::class),
            'alias' => md5(InterfaceServicePrepare\FooImplementation::class)
        ], $actual['aliasDefinitions']);
    }

    public function testSerializeInterfaceServicePrepareHasServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InterfaceServicePrepare')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertCount(1, $actual['servicePrepareDefinitions']);
        $this->assertContains([
            'type' => InterfaceServicePrepare\FooInterface::class,
            'method' => 'setBar'
        ], $actual['servicePrepareDefinitions']);
    }

    public function testSerializeInterfaceServicePrepareHasEmptyInjectServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InterfaceServicePrepare')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectServiceDefinitions', $actual);
        $this->assertEmpty($actual['injectServiceDefinitions']);
    }

    public function testSerializeInterfaceServicePrepareHasEmptyInjectScalarDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InterfaceServicePrepare')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectScalarDefinitions', $actual);
        $this->assertEmpty($actual['injectScalarDefinitions']);
    }

    public function testSerializeInterfaceServicePrepareHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InterfaceServicePrepare')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeSimpleInjectScalarHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalar')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);
        $this->assertIsArray($actual);

        $expectedFooImplementation = [
            'type' => SimpleUseScalar\FooImplementation::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(1, $actual['compiledServiceDefinitions']);

        $this->assertArrayHasKey(md5(SimpleUseScalar\FooImplementation::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedFooImplementation, $actual['compiledServiceDefinitions'][md5(SimpleUseScalar\FooImplementation::class)]);
    }

    public function testSerializeSimpleInjectScalarHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalar')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(1, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleUseScalar\FooImplementation::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeSimpleInjectScalarHasNoAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalar')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertEmpty($actual['aliasDefinitions']);
    }

    public function testSerializeSimpleInjectScalarHasNoServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalar')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeSimpleInjectScalarHasInjectScalarDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalar')->withProfiles('default')->build());
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectScalarDefinitions', $actual);
        $this->assertCount(5, $actual['injectScalarDefinitions']);
        $this->assertContains([
            'type' => SimpleUseScalar\FooImplementation::class,
            'method' => '__construct',
            'paramName' => 'stringParam',
            'paramType' => 'string',
            'value' => 'string param test value'
        ], $actual['injectScalarDefinitions']);
        $this->assertContains([
            'type' => SimpleUseScalar\FooImplementation::class,
            'method' => '__construct',
            'paramName' => 'intParam',
            'paramType' => 'int',
            'value' => 42
        ], $actual['injectScalarDefinitions']);
        $this->assertContains([
            'type' => SimpleUseScalar\FooImplementation::class,
            'method' => '__construct',
            'paramName' => 'floatParam',
            'paramType' => 'float',
            'value' => 42
        ], $actual['injectScalarDefinitions']);
        $this->assertContains([
            'type' => SimpleUseScalar\FooImplementation::class,
            'method' => '__construct',
            'paramName' => 'boolParam',
            'paramType' => 'bool',
            'value' => true
        ], $actual['injectScalarDefinitions']);
        $this->assertContains([
            'type' => SimpleUseScalar\FooImplementation::class,
            'method' => '__construct',
            'paramName' => 'arrayParam',
            'paramType' => 'array',
            'value' => [
                ['a', 'b', 'c'],
                [1, 2, 3],
                [1.1, 2.1, 3.1],
                [true, false, true],
                [['a', 'b', 'c'], [1, 2, 3], [1.1, 2.1, 3.1], [true, false, true]]
            ]
        ], $actual['injectScalarDefinitions']);
    }

    public function testSerializeSimpleUseScalarHasNoInjectServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalar')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectServiceDefinitions', $actual);
        $this->assertEmpty($actual['injectServiceDefinitions']);
    }

    public function testSerializeSimpleUseScalarHasNoServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalar')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeSimpleUseServiceHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(6, $actual['compiledServiceDefinitions']);

        $expectedBarImplementation = [
            'type' => SimpleUseService\BarImplementation::class,
            'implementedServices' => [md5(SimpleUseService\FooInterface::class)],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];
        $this->assertArrayHasKey(md5(SimpleUseService\BarImplementation::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedBarImplementation, $actual['compiledServiceDefinitions'][md5(SimpleUseService\BarImplementation::class)]);

        $expectedBazImplementation = [
            'type' => SimpleUseService\BazImplementation::class,
            'implementedServices' => [md5(SimpleUseService\FooInterface::class)],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];
        $this->assertArrayHasKey(md5(SimpleUseService\BazImplementation::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedBazImplementation, $actual['compiledServiceDefinitions'][md5(SimpleUseService\BazImplementation::class)]);

        $expectedConstructorInjection = [
            'type' => SimpleUseService\ConstructorInjection::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];
        $this->assertArrayHasKey(md5(SimpleUseService\ConstructorInjection::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedConstructorInjection, $actual['compiledServiceDefinitions'][md5(SimpleUseService\ConstructorInjection::class)]);

        $expectedFooInterface = [
            'type' => SimpleUseService\FooInterface::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => true,
            'isConcrete' => false
        ];
        $this->assertArrayHasKey(md5(SimpleUseService\FooInterface::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedFooInterface, $actual['compiledServiceDefinitions'][md5(SimpleUseService\FooInterface::class)]);

        $expectedSetterInjection = [
            'type' => SimpleUseService\SetterInjection::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];
        $this->assertArrayHasKey(md5(SimpleUseService\SetterInjection::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedSetterInjection, $actual['compiledServiceDefinitions'][md5(SimpleUseService\SetterInjection::class)]);

        $expectedQuxImplementation = [
            'type' => SimpleUseService\QuxImplementation::class,
            'implementedServices' => [md5(SimpleUseService\FooInterface::class)],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];
        $this->assertArrayHasKey(md5(SimpleUseService\QuxImplementation::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedQuxImplementation, $actual['compiledServiceDefinitions'][md5(SimpleUseService\QuxImplementation::class)]);
    }

    public function testSerializeSimpleUseServiceHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(6, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleUseService\BarImplementation::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleUseService\BazImplementation::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleUseService\ConstructorInjection::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleUseService\FooInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleUseService\QuxImplementation::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleUseService\SetterInjection::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeSimpleUseServiceHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertCount(3, $actual['aliasDefinitions']);
        $this->assertContains([
            'original' => md5(SimpleUseService\FooInterface::class),
            'alias' => md5(SimpleUseService\BarImplementation::class)
        ], $actual['aliasDefinitions']);
        $this->assertContains([
            'original' => md5(SimpleUseService\FooInterface::class),
            'alias' => md5(SimpleUseService\BazImplementation::class)
        ], $actual['aliasDefinitions']);
        $this->assertContains([
            'original' => md5(SimpleUseService\FooInterface::class),
            'alias' => md5(SimpleUseService\QuxImplementation::class)
        ], $actual['aliasDefinitions']);
    }

    public function testSerializeSimpleUseServiceHasServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertCount(3, $actual['servicePrepareDefinitions']);
        $this->assertContains([
            'type' => SimpleUseService\SetterInjection::class,
            'method' => 'setBaz'
        ], $actual['servicePrepareDefinitions']);
        $this->assertContains([
            'type' => SimpleUseService\SetterInjection::class,
            'method' => 'setBar'
        ], $actual['servicePrepareDefinitions']);
        $this->assertContains([
            'type' => SimpleUseService\SetterInjection::class,
            'method' => 'setQux'
        ], $actual['servicePrepareDefinitions']);
    }

    public function testSerializeSimpleUseServiceHasNoInjectScalarDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectScalarDefinitions', $actual);
        $this->assertEmpty($actual['injectScalarDefinitions']);
    }

    public function testSerializeSimpleUseServiceHasInjectServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectServiceDefinitions', $actual);
        $this->assertCount(6, $actual['injectServiceDefinitions']);
        $this->assertContains([
            'type' => SimpleUseService\ConstructorInjection::class,
            'method' => '__construct',
            'paramName' => 'bar',
            'paramType' => SimpleUseService\FooInterface::class,
            'value' => SimpleUseService\BarImplementation::class
        ], $actual['injectServiceDefinitions']);
        $this->assertContains([
            'type' => SimpleUseService\ConstructorInjection::class,
            'method' => '__construct',
            'paramName' => 'baz',
            'paramType' => SimpleUseService\FooInterface::class,
            'value' => SimpleUseService\BazImplementation::class
        ], $actual['injectServiceDefinitions']);
        $this->assertContains([
            'type' => SimpleUseService\ConstructorInjection::class,
            'method' => '__construct',
            'paramName' => 'qux',
            'paramType' => SimpleUseService\FooInterface::class,
            'value' => SimpleUseService\QuxImplementation::class
        ], $actual['injectServiceDefinitions']);
        $this->assertContains([
            'type' => DummyApps\SimpleUseService\SetterInjection::class,
            'method' => 'setBaz',
            'paramName' => 'foo',
            'paramType' => SimpleUseService\FooInterface::class,
            'value' => SimpleUseService\BazImplementation::class
        ], $actual['injectServiceDefinitions']);
        $this->assertContains([
            'type' => DummyApps\SimpleUseService\SetterInjection::class,
            'method' => 'setBar',
            'paramName' => 'foo',
            'paramType' => SimpleUseService\FooInterface::class,
            'value' => SimpleUseService\BarImplementation::class
        ], $actual['injectServiceDefinitions']);
        $this->assertContains([
            'type' => DummyApps\SimpleUseService\SetterInjection::class,
            'method' => 'setQux',
            'paramName' => 'foo',
            'paramType' => SimpleUseService\FooInterface::class,
            'value' => SimpleUseService\QuxImplementation::class
        ], $actual['injectServiceDefinitions']);
    }

    public function testSerializeSimpleUseServiceHasNoServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeServiceDelegateHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/ServiceDelegate')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(2, $actual['compiledServiceDefinitions']);

        $expectedServiceInterface = [
            'type' => ServiceDelegate\ServiceInterface::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => true,
            'isConcrete' => false
        ];
        $this->assertArrayHasKey(md5(ServiceDelegate\ServiceInterface::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedServiceInterface, $actual['compiledServiceDefinitions'][md5(ServiceDelegate\ServiceInterface::class)]);

        $expectedFooService = [
            'type' => ServiceDelegate\FooService::class,
            'implementedServices' => [],
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true
        ];
        $this->assertArrayHasKey(md5(ServiceDelegate\FooService::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedFooService, $actual['compiledServiceDefinitions'][md5(ServiceDelegate\FooService::class)]);
    }

    public function testSerializeServiceDelegateHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/ServiceDelegate')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(2, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(ServiceDelegate\ServiceInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(ServiceDelegate\FooService::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeServiceDelegateHasNoAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/ServiceDelegate')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertEmpty($actual['aliasDefinitions']);
    }

    public function testSerializeServiceDelegateHasNoInjectScalarDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/ServiceDelegate')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectScalarDefinitions', $actual);
        $this->assertEmpty($actual['injectScalarDefinitions']);
    }

    public function testSerializeServiceDelegateHasNoInjectServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/ServiceDelegate')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('injectServiceDefinitions', $actual);
        $this->assertEmpty($actual['injectServiceDefinitions']);
    }

    public function testSerializeServiceDelegateHasServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/ServiceDelegate')->withProfiles('default')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertCount(1, $actual['serviceDelegateDefinitions']);
        $this->assertContains([
            'delegateType' => ServiceDelegate\ServiceFactory::class,
            'delegateMethod' => 'createService',
            'serviceType' => ServiceDelegate\ServiceInterface::class
        ], $actual['serviceDelegateDefinitions']);
    }

    /** ======================================== Deserialization Testing ==============================================*/

    public function testDeserializeSimpleServices() {
        $serializer = new JsonContainerDefinitionSerializer();
        $json = $serializer->serialize($this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleServices')->withProfiles('default')->build()
        ));
        $injectorDefinition = $serializer->deserialize($json);

        $injector = (new AurynInjectorFactory())->createInjector($injectorDefinition);

        $foo1 = $injector->make(SimpleServices\FooInterface::class);

        $this->assertInstanceOf(SimpleServices\FooImplementation::class, $foo1);

        $foo2 = $injector->make(SimpleServices\FooInterface::class);

        $this->assertSame($foo1, $foo2);
    }

    public function testDeserializeInterfaceServicePrepare() {
        $serializer = new JsonContainerDefinitionSerializer();
        $json = $serializer->serialize($this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InterfaceServicePrepare')->withProfiles('default')->build()
        ));
        $injectorDefinition = $serializer->deserialize($json);

        $injector = (new AurynInjectorFactory())->createInjector($injectorDefinition);

        $foo = $injector->make(InterfaceServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InterfaceServicePrepare\FooImplementation::class, $foo);

        $this->assertSame(1, $foo->getBarCounter());
    }

    public function testDeserializeSimpleUseScalar() {
        $serializer = new JsonContainerDefinitionSerializer();
        $json = $serializer->serialize($this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalar')->withProfiles('default')->build()
        ));
        $injectorDefinition = $serializer->deserialize($json);

        $injector = (new AurynInjectorFactory())->createInjector($injectorDefinition);

        $foo = $injector->make(SimpleUseScalar\FooImplementation::class);

        $this->assertSame('string param test value', $foo->stringParam);
        $this->assertSame(42, $foo->intParam);
        $this->assertSame(42.0, $foo->floatParam);
        $this->assertTrue($foo->boolParam);
        $this->assertSame([
            ['a', 'b', 'c'],
            [1, 2, 3],
            [1.1, 2.1, 3.1],
            [true, false, true],
            [['a', 'b', 'c'], [1, 2, 3], [1.1, 2.1, 3.1], [true, false, true]]
        ], $foo->arrayParam);
    }

    public function testDeserializeSimpleUseService() {
        $serializer = new JsonContainerDefinitionSerializer();
        $json = $serializer->serialize($this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')->withProfiles('default')->build()
        ));
        $injectorDefinition = $serializer->deserialize($json);

        $injector = (new AurynInjectorFactory())->createInjector($injectorDefinition);

        $constructorInjection = $injector->make(SimpleUseService\ConstructorInjection::class);

        $this->assertSame(
            $injector->make(SimpleUseService\BarImplementation::class),
            $constructorInjection->bar
        );
        $this->assertSame(
            $injector->make(SimpleUseService\BazImplementation::class),
            $constructorInjection->baz
        );
        $this->assertSame(
            $injector->make(SimpleUseService\QuxImplementation::class),
            $constructorInjection->qux
        );
    }

    public function testDeserializeServiceDelegate() {
        $serializer = new JsonContainerDefinitionSerializer();
        $json = $serializer->serialize($this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/ServiceDelegate')->withProfiles('default')->build()
        ));
        $injectorDefinition = $serializer->deserialize($json);

        $injector = (new AurynInjectorFactory())->createInjector($injectorDefinition);

        $service = $injector->make(ServiceDelegate\ServiceInterface::class);

        $this->assertSame('From ServiceFactory From FooService', $service->getValue());
    }

    public function serializeDeserializeSerializeDirs() : array {
        return [
            [__DIR__ . '/DummyApps/ProfileResolvedServices'],
            [__DIR__ . '/DummyApps/AbstractSharedServices']
        ];
    }

    /**
     * @dataProvider serializeDeserializeSerializeDirs
     * @return void
     */
    public function testSerializingDeserializedContainerDefinitionIsCompatible(string $dir) {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition1 = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)->withProfiles('default')->build()
        );

        // Ensure that serialize -> deserialize -> serialize results in compatible container definition with original compilation
        $json = $serializer->serialize($containerDefinition1);
        $json2 = $serializer->serialize($serializer->deserialize($json));
        $containerDefinition2 = $serializer->deserialize($json2);

        $this->assertJsonStringEqualsJsonString(
            $serializer->serialize($containerDefinition1),
            $serializer->serialize($containerDefinition2)
        );
    }

}