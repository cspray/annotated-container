<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

class JsonContainerDefinitionSerializerTest extends TestCase {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private JsonContainerDefinitionSerializer $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        $this->subject = new JsonContainerDefinitionSerializer();
    }

    /** ======================================== Serialization Testing ==============================================*/

    public function testSerializeSingleConcreteServiceHasCompiledServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())
                ->build()
        );

        $expectedFooImplementation = [
            'name' => null,
            'type' => Fixtures::singleConcreteService()->fooImplementation()->getName(),
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ];
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(1, $actual['compiledServiceDefinitions']);

        $this->assertArrayHasKey(md5(Fixtures::singleConcreteService()->fooImplementation()->getName()), $actual['compiledServiceDefinitions']);
        $this->assertEquals($expectedFooImplementation, $actual['compiledServiceDefinitions'][md5(Fixtures::singleConcreteService()->fooImplementation()->getName())]);
    }

    public function testSerializeSimpleServicesHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())
                ->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertCount(2, $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(Fixtures::implicitAliasedServices()->fooInterface()->getName()), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(Fixtures::implicitAliasedServices()->fooImplementation()->getName()), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeSimpleServicesHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())
                ->build());
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertCount(1, $actual['aliasDefinitions']);
        $this->assertContains([
            'original' => md5(Fixtures::implicitAliasedServices()->fooInterface()->getName()),
            'alias' => md5(Fixtures::implicitAliasedServices()->fooImplementation()->getName())
        ], $actual['aliasDefinitions']);
    }

    public function testSerializeSingleConcreteServiceHasEmptyServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeSingleConcreteServiceHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeNoServicePrepareDefinitionsHasEmptyServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeNoServiceDelegateDefinitionsHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeServiceDelegateHasServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::delegatedService()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        $this->assertArrayHasKey('serviceDelegateDefinitions', $actual);
        $this->assertCount(1, $actual['serviceDelegateDefinitions']);
        $this->assertContains([
            'delegateType' => Fixtures::delegatedService()->serviceFactory()->getName(),
            'delegateMethod' => 'createService',
            'serviceType' => Fixtures::delegatedService()->serviceInterface()->getName()
        ], $actual['serviceDelegateDefinitions']);
    }

    public function testSerializingContainerDefinitionIncludesProfiles() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::profileResolvedServices()->getPath())->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        $this->assertContains([
            'name' => null,
            'type' => Fixtures::profileResolvedServices()->devImplementation()->getName(),
            'profiles' => ['dev'],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ], $json['compiledServiceDefinitions']);
        $this->assertContains([
            'name' => null,
            'type' => Fixtures::profileResolvedServices()->testImplementation()->getName(),
            'profiles' => ['test'],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ], $json['compiledServiceDefinitions']);
        $this->assertContains([
            'name' => null,
            'type' => Fixtures::profileResolvedServices()->prodImplementation()->getName(),
            'profiles' => ['prod'],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => true
        ], $json['compiledServiceDefinitions']);
    }

    public function testSerializeNamedServicesHasName() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::namedServices()->getPath())->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        $this->assertContains([
            'name' => 'foo',
            'type' => Fixtures::namedServices()->fooInterface()->getName(),
            'profiles' => ['default'],
            'isAbstract' => true,
            'isConcrete' => false,
            'isShared' => true
        ], $json['compiledServiceDefinitions']);
    }

    public function testSerializeNonSharedService() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::nonSharedServices()->getPath())->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        $this->assertContains([
            'name' => null,
            'type' => Fixtures::nonSharedServices()->fooImplementation()->getName(),
            'profiles' => ['default'],
            'isAbstract' => false,
            'isConcrete' => true,
            'isShared' => false
        ], $json['compiledServiceDefinitions']);
    }

    /** ======================================== Deserialization Testing ==============================================*/

    public function serializeDeserializeSerializeDirs() : array {
        return [
            [Fixtures::singleConcreteService()->getPath()],
            [Fixtures::delegatedService()->getPath()],
            [Fixtures::interfacePrepareServices()->getPath()],
            [Fixtures::profileResolvedServices()->getPath()],
            [Fixtures::abstractClassAliasedService()->getPath()],
            [Fixtures::namedServices()->getPath()],
            [Fixtures::nonSharedServices()->getPath()]
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
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::nonSharedServices()->getPath())->build()
        );

        $json = $serializer->serialize($containerDefinition);
        $subject = $serializer->deserialize($json);

        $this->assertCount(1, $subject->getServiceDefinitions());
        $serviceDefinition = $subject->getServiceDefinitions()[0];
        $this->assertFalse($serviceDefinition->isShared());
    }


}


