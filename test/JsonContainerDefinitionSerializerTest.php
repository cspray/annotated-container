<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\AbstractSharedServices;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\MultipleSimpleServices;
use PHPUnit\Framework\TestCase;

class JsonContainerDefinitionSerializerTest extends TestCase {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private JsonContainerDefinitionSerializer $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new AnnotatedTargetContainerDefinitionCompiler(
            new StaticAnalysisAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        $this->subject = new JsonContainerDefinitionSerializer();
    }

    /** ======================================== Serialization Testing ==============================================*/

    public function testSerializeSimpleServicesHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')
                ->build()
        );

        $expectedFooInterface = [
            'name' => null,
            'type' => SimpleServices\FooInterface::class,
            'profiles' => [],
            'isAbstract' => true,
            'isConcrete' => false,
            'isShared' => true
        ];
        $expectedFooImplementation = [
            'name' => null,
            'type' => SimpleServices\FooImplementation::class,
            'profiles' => [],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
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
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')
            ->build());
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(2, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleServices\FooInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleServices\FooImplementation::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeSimpleServicesHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')
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
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeSimpleServicesHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeMultipleSimpleServicesHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleSimpleServices')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $expectedFooInterface = [
            'name' => null,
            'type' => MultipleSimpleServices\FooInterface::class,
            'profiles' => [],
            'isAbstract' => true,
            'isConcrete' => false,
            'isShared' => true
        ];
        $expectedFooImplementation = [
            'name' => null,
            'type' => MultipleSimpleServices\FooImplementation::class,
            'profiles' => [],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ];

        $expectedBarInterface = [
            'name' => null,
            'type' => MultipleSimpleServices\BarInterface::class,
            'profiles' => [],
            'isAbstract' => true,
            'isConcrete' => false,
            'isShared' => true
        ];
        $expectedBarImplementation = [
            'name' => null,
            'type' => MultipleSimpleServices\BarImplementation::class,
            'profiles' => [],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
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
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleSimpleServices')->build()
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
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleSimpleServices')->build()
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
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleSimpleServices')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeMultipleSimpleServicesHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleSimpleServices')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeAbstractSharedServicesHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/AbstractSharedServices')->build()
        );

        $expectedAbstractFoo = [
            'name' => null,
            'type' => AbstractSharedServices\AbstractFoo::class,
            'profiles' => [],
            'isAbstract' => true,
            'isConcrete' => false,
            'isShared' => true
        ];
        $expectedFooImplementation = [
            'name' => null,
            'type' => AbstractSharedServices\FooImplementation::class,
            'profiles' => [],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ];

        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(2, $actual['compiledServiceDefinitions']);

        $this->assertArrayHasKey(md5(AbstractSharedServices\AbstractFoo::class), $actual['compiledServiceDefinitions']);
        $this->assertArrayHasKey(md5(AbstractSharedServices\FooImplementation::class), $actual['compiledServiceDefinitions']);

        $this->assertEquals($expectedAbstractFoo, $actual['compiledServiceDefinitions'][md5(AbstractSharedServices\AbstractFoo::class)]);
        $this->assertEquals($expectedFooImplementation, $actual['compiledServiceDefinitions'][md5(AbstractSharedServices\FooImplementation::class)]);
    }

    public function testSerializeAbstractSharedServicesHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/AbstractSharedServices')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(2, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(AbstractSharedServices\AbstractFoo::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(AbstractSharedServices\FooImplementation::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeAbstractSharedServicesHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/AbstractSharedServices')->build()
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
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/AbstractSharedServices')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeAbstractSharedServicesHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/AbstractSharedServices')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeInterfaceServicePrepareHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InterfaceServicePrepare')->build()
        );

        $expectedFooInterface = [
            'name' => null,
            'type' => InterfaceServicePrepare\FooInterface::class,
            'profiles' => [],
            'isAbstract' => true,
            'isConcrete' => false,
            'isShared' => true
        ];
        $expectedFooImplementation = [
            'name' => null,
            'type' => InterfaceServicePrepare\FooImplementation::class,
            'profiles' => [],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
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
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InterfaceServicePrepare')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(2, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(InterfaceServicePrepare\FooInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(InterfaceServicePrepare\FooImplementation::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeInterfaceServicePrepareHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InterfaceServicePrepare')->build()
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
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InterfaceServicePrepare')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertCount(1, $actual['servicePrepareDefinitions']);
        $this->assertContains([
            'type' => InterfaceServicePrepare\FooInterface::class,
            'method' => 'setBar'
        ], $actual['servicePrepareDefinitions']);
    }

    public function testSerializeInterfaceServicePrepareHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InterfaceServicePrepare')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeServiceDelegateHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ServiceDelegate')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(2, $actual['compiledServiceDefinitions']);

        $expectedServiceInterface = [
            'name' => null,
            'type' => ServiceDelegate\ServiceInterface::class,
            'profiles' => [],
            'isAbstract' => true,
            'isConcrete' => false,
            'isShared' => true
        ];
        $this->assertArrayHasKey(md5(ServiceDelegate\ServiceInterface::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedServiceInterface, $actual['compiledServiceDefinitions'][md5(ServiceDelegate\ServiceInterface::class)]);

        $expectedFooService = [
            'name' => null,
            'type' => ServiceDelegate\FooService::class,
            'profiles' => [],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ];
        $this->assertArrayHasKey(md5(ServiceDelegate\FooService::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedFooService, $actual['compiledServiceDefinitions'][md5(ServiceDelegate\FooService::class)]);
    }

    public function testSerializeServiceDelegateHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ServiceDelegate')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(2, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(ServiceDelegate\ServiceInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(ServiceDelegate\FooService::class), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeServiceDelegateHasNoAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ServiceDelegate')->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertEmpty($actual['aliasDefinitions']);
    }

    public function testSerializeServiceDelegateHasServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ServiceDelegate')->build()
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

    public function testSerializingContainerDefinitionIncludesProfiles() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ProfileResolvedServices')->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        $this->assertContains([
            'name' => null,
            'type' => DummyApps\ProfileResolvedServices\DevFooImplementation::class,
            'profiles' => ['dev'],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ], $json['compiledServiceDefinitions']);
        $this->assertContains([
            'name' => null,
            'type' => DummyApps\ProfileResolvedServices\TestFooImplementation::class,
            'profiles' => ['test'],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ], $json['compiledServiceDefinitions']);
        $this->assertContains([
            'name' => null,
            'type' => DummyApps\ProfileResolvedServices\ProdFooImplementation::class,
            'profiles' => ['prod'],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ], $json['compiledServiceDefinitions']);
    }

    public function testSerializeNamedServicesHasName() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/NamedService')->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        $this->assertContains([
            'name' => 'foo',
            'type' => DummyApps\NamedService\FooInterface::class,
            'profiles' => [],
            'isAbstract' => true,
            'isConcrete' => false,
            'isShared' => true
        ], $json['compiledServiceDefinitions']);
    }

    public function testSerializeNonSharedService() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/NonSharedService')->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        $this->assertContains([
            'name' => null,
            'type' => DummyApps\NonSharedService\FooImplementation::class,
            'profiles' => [],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => false
        ], $json['compiledServiceDefinitions']);
    }

    /** ======================================== Deserialization Testing ==============================================*/

    public function serializeDeserializeSerializeDirs() : array {
        return [
            [DummyAppUtils::getRootDir() . '/SimpleServices'],
            [DummyAppUtils::getRootDir() . '/ServiceDelegate'],
            [DummyAppUtils::getRootDir() . '/InterfaceServicePrepare'],
            [DummyAppUtils::getRootDir() . '/ProfileResolvedServices'],
            [DummyAppUtils::getRootDir() . '/AbstractSharedServices'],
            [DummyAppUtils::getRootDir() . '/NamedService'],
            [DummyAppUtils::getRootDir() . '/NonSharedService']
        ];
    }

    /**
     * @dataProvider serializeDeserializeSerializeDirs
     * @return void
     */
    public function testSerializingDeserializedContainerDefinitionIsCompatible(string $dir) {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition1 = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)->build()
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

    public function testDeserializeNonSharedService() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/NonSharedService')->build()
        );

        $json = $serializer->serialize($containerDefinition);
        $subject = $serializer->deserialize($json);

        $this->assertCount(1, $subject->getServiceDefinitions());
        $serviceDefinition = $subject->getServiceDefinitions()[0];
        $this->assertFalse($serviceDefinition->isShared());
    }


}


