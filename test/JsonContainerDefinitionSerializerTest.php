<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainerFixture\ConfigurationWithArrayEnum\FooEnum;
use Cspray\AnnotatedContainerFixture\ConfigurationWithAssocArrayEnum;
use Cspray\AnnotatedContainerFixture\ConfigurationWithEnum\MyEnum;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\stringType;

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
            'isConcrete' => true
        ];
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        self::assertArrayHasKey('compiledServiceDefinitions', $actual);
        self::assertCount(1, $actual['compiledServiceDefinitions']);

        self::assertArrayHasKey(md5(Fixtures::singleConcreteService()->fooImplementation()->getName()), $actual['compiledServiceDefinitions']);
        self::assertEquals($expectedFooImplementation, $actual['compiledServiceDefinitions'][md5(Fixtures::singleConcreteService()->fooImplementation()->getName())]);
    }

    public function testSerializeSimpleServicesHasSharedServiceDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())
                ->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        self::assertArrayHasKey('sharedServiceDefinitions', $actual);
        self::assertCount(2, $actual['sharedServiceDefinitions']);
        self::assertContains(md5(Fixtures::implicitAliasedServices()->fooInterface()->getName()), $actual['sharedServiceDefinitions']);
        self::assertContains(md5(Fixtures::implicitAliasedServices()->fooImplementation()->getName()), $actual['sharedServiceDefinitions']);
    }

    public function testSerializeSimpleServicesHasAliasDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())
                ->build());
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        self::assertArrayHasKey('aliasDefinitions', $actual);
        self::assertCount(1, $actual['aliasDefinitions']);
        self::assertContains([
            'original' => md5(Fixtures::implicitAliasedServices()->fooInterface()->getName()),
            'alias' => md5(Fixtures::implicitAliasedServices()->fooImplementation()->getName())
        ], $actual['aliasDefinitions']);
    }

    public function testSerializeSingleConcreteServiceHasEmptyServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        self::assertArrayHasKey('servicePrepareDefinitions', $actual);
        self::assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeSingleConcreteServiceHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        self::assertArrayHasKey('serviceDelegateDefinitions', $actual);
        self::assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeNoServicePrepareDefinitionsHasEmptyServicePrepareDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        self::assertArrayHasKey('servicePrepareDefinitions', $actual);
        self::assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testSerializeNoServiceDelegateDefinitionsHasEmptyServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        self::assertArrayHasKey('serviceDelegateDefinitions', $actual);
        self::assertEmpty($actual['serviceDelegateDefinitions']);
    }

    public function testSerializeServiceDelegateHasServiceDelegateDefinitions() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::delegatedService()->getPath())->build()
        );
        $actual = json_decode($this->subject->serialize($containerDefinition), true);

        self::assertArrayHasKey('serviceDelegateDefinitions', $actual);
        self::assertCount(1, $actual['serviceDelegateDefinitions']);
        self::assertContains([
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
        self::assertContains([
            'name' => null,
            'type' => Fixtures::profileResolvedServices()->devImplementation()->getName(),
            'profiles' => ['dev'],
            'isAbstract' => false,
            'isConcrete' => true
        ], $json['compiledServiceDefinitions']);
        self::assertContains([
            'name' => null,
            'type' => Fixtures::profileResolvedServices()->testImplementation()->getName(),
            'profiles' => ['test'],
            'isAbstract' => false,
            'isConcrete' => true
        ], $json['compiledServiceDefinitions']);
        self::assertContains([
            'name' => null,
            'type' => Fixtures::profileResolvedServices()->prodImplementation()->getName(),
            'profiles' => ['prod'],
            'isAbstract' => false,
            'isConcrete' => true
        ], $json['compiledServiceDefinitions']);
    }

    public function testSerializeNamedServicesHasName() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::namedServices()->getPath())->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        self::assertContains([
            'name' => 'foo',
            'type' => Fixtures::namedServices()->fooInterface()->getName(),
            'profiles' => ['default'],
            'isAbstract' => true,
            'isConcrete' => false,
        ], $json['compiledServiceDefinitions']);
    }

    public function testSerializeInjectDefinitionMethod() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::injectConstructorServices()->getPath())->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        self::assertArrayHasKey('injectDefinitions', $json);
        self::assertContains([
            'injectTargetType' => Fixtures::injectConstructorServices()->injectStringService()->getName(),
            'injectTargetMethod' => '__construct',
            'injectTargetName' => 'val',
            'type' => 'string',
            'value' => 'foobar',
            'profiles' => ['default'],
            'storeName' => null
        ], $json['injectDefinitions']);
    }

    public function testSerializeInjectDefinitionProperty() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::configurationServices()->getPath())->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        self::assertArrayHasKey('injectDefinitions', $json);
        self::assertContains([
            'injectTargetType' => Fixtures::configurationServices()->myConfig()->getName(),
            'injectTargetMethod' => null,
            'injectTargetName' => 'key',
            'type' => 'string',
            'value' => 'my-api-key',
            'profiles' => ['default'],
            'storeName' => null
        ], $json['injectDefinitions']);
    }

    public function testSerializeConfigurationDefinitions() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::namedConfigurationServices()->getPath())->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        self::assertArrayHasKey('configurationDefinitions', $json);
        self::assertContains([
            'type' => Fixtures::namedConfigurationServices()->myConfig()->getName(),
            'name' => 'my-config'
        ], $json['configurationDefinitions']);
    }

    public function testSerializeWithEnumInjectDefinitions() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::configurationWithEnum()->getPath())->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        self::assertArrayHasKey('injectDefinitions', $json);
        self::assertContains([
            'injectTargetType' => Fixtures::configurationWithEnum()->configuration()->getName(),
            'injectTargetMethod' => null,
            'injectTargetName' => 'enum',
            'type' => MyEnum::class,
            'value' => 'Foo',
            'profiles' => ['default'],
            'storeName' => null
        ], $json['injectDefinitions']);
    }

    public function testSerializeWithArrayEnumInjectDefinitions() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::configurationWithArrayEnum()->getPath())->build()
        );

        $json = json_decode($serializer->serialize($containerDefinition), true);
        self::assertArrayHasKey('injectDefinitions', $json);
        self::assertContains([
            'injectTargetType' => Fixtures::configurationWithArrayEnum()->myConfiguration()->getName(),
            'injectTargetMethod' => null,
            'injectTargetName' => 'cases',
            'type' => 'array',
            'value' => [
                [
                    'type' => FooEnum::class,
                    'value' => 'Bar'
                ],
                [
                    'type' => FooEnum::class,
                    'value' => 'Qux'
                ]
            ],
            'profiles' => ['default'],
            'storeName' => null
        ], $json['injectDefinitions']);
    }

    /** ======================================== Deserialization Testing ==============================================*/

    public function serializeDeserializeSerializeDirs() : array {
        return [
            'singleConcrete' => [Fixtures::singleConcreteService()->getPath()],
            'delegatedService' => [Fixtures::delegatedService()->getPath()],
            'interfacePrepareServices' => [Fixtures::interfacePrepareServices()->getPath()],
            'profileResolvedServices' => [Fixtures::profileResolvedServices()->getPath()],
            'abstractClassAliasedService' => [Fixtures::abstractClassAliasedService()->getPath()],
            'namedServices' => [Fixtures::namedServices()->getPath()],
            'injectConstructorServices' => [Fixtures::injectConstructorServices()->getPath()],
            'configurationServices' => [Fixtures::configurationServices()->getPath()],
            'injectArrayOfNumbers' => [Fixtures::injectListOfScalarsFixture()->getPath()]
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

        self::assertJsonStringEqualsJsonString(
            $serializer->serialize($containerDefinition1),
            $serializer->serialize($containerDefinition2)
        );
    }

    public function testDeserializeInjectWithCorrectTypeUnion() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::injectConstructorServices()->getPath())->build()
        );

        $serialized = $serializer->serialize($containerDefinition);
        $subjectDefinition = $serializer->deserialize($serialized);

        $injectDefinitions = $subjectDefinition->getInjectDefinitions();

        /** @var InjectDefinition[] $typeUnionInjects */
        $typeUnionInjects = array_values(
            array_filter(
                $injectDefinitions,
                fn(InjectDefinition $injectDefinition) =>
                    $injectDefinition->getTargetIdentifier()->getClass() === Fixtures::injectConstructorServices()->injectTypeUnionService()
            )
        );

        self::assertCount(1, $typeUnionInjects);
        self::assertInstanceOf(TypeUnion::class, $typeUnionInjects[0]->getType());

        /** @var TypeUnion $type */
        $type = $typeUnionInjects[0]->getType();

        self::assertSame([
            stringType(),
            intType(),
            floatType()
        ], $type->getTypes());
    }

    public function testDeserializeInjectWithCorrectTypeIntersect() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::injectIntersectCustomStoreServices()->getPath())->build()
        );

        $serialized = $serializer->serialize($containerDefinition);
        $subjectDefinition = $serializer->deserialize($serialized);

        $injectDefinitions = $subjectDefinition->getInjectDefinitions();

        /** @var InjectDefinition[] $typeIntersectInjects */
        $typeIntersectInjects = array_values(
            array_filter(
                $injectDefinitions,
                fn(InjectDefinition $injectDefinition) =>
                    $injectDefinition->getTargetIdentifier()->getClass() === Fixtures::injectIntersectCustomStoreServices()->intersectInjector()
            )
        );

        self::assertCount(1, $typeIntersectInjects);
        self::assertInstanceOf(TypeIntersect::class, $typeIntersectInjects[0]->getType());

        $type = $typeIntersectInjects[0]->getType();

        self::assertSame([
            Fixtures::injectIntersectCustomStoreServices()->fooInterface(),
            Fixtures::injectIntersectCustomStoreServices()->barInterface()
        ], $type->getTypes());
    }

    public function testDeserializeWithEnum() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::configurationWithEnum()->getPath())->build()
        );

        $serialized = $serializer->serialize($containerDefinition);
        $subjectDefinition = $serializer->deserialize($serialized);

        $injectDefinitions = $subjectDefinition->getInjectDefinitions();

        self::assertCount(1, $injectDefinitions);
        self::assertSame(MyEnum::Foo, $injectDefinitions[0]->getValue());
    }

    public function testDeserializeWithArrayEnum() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::configurationWithArrayEnum()->getPath())->build()
        );

        $serialized = $serializer->serialize($containerDefinition);
        $subjectDefinition = $serializer->deserialize($serialized);

        $injectDefinitions = $subjectDefinition->getInjectDefinitions();

        self::assertCount(1, $injectDefinitions);
        self::assertSame([FooEnum::Bar, FooEnum::Qux], $injectDefinitions[0]->getValue());
    }

    public function testDeserializeWithAssocArrayEnum() {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::configurationWithAssocArrayEnum()->getPath())->build()
        );

        $serialized = $serializer->serialize($containerDefinition);
        $subjectDefinition = $serializer->deserialize($serialized);

        $injectDefinitions = $subjectDefinition->getInjectDefinitions();

        self::assertCount(1, $injectDefinitions);
        self::assertSame([
            'b' => ConfigurationWithAssocArrayEnum\MyEnum::B,
            'c' => ConfigurationWithAssocArrayEnum\MyEnum::C
        ], $injectDefinitions[0]->getValue());
    }

    public function testDeserializeConfigurationWithName() : void {
        $serializer = new JsonContainerDefinitionSerializer();
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::namedConfigurationServices()->getPath())->build()
        );

        $serialized = $serializer->serialize($containerDefinition);
        $subjectDefinition = $serializer->deserialize($serialized);

        $configurations = $subjectDefinition->getConfigurationDefinitions();

        self::assertCount(1, $configurations);
        self::assertSame('my-config', $configurations[0]->getName());
    }
}


