<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\ContainerFactory\IlluminateContainerFactory;
use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainerFixture;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\Typiphy\Internal\NamedType;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use Illuminate\Contracts\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Cspray\AnnotatedContainer\autowiredParams;
use function Cspray\AnnotatedContainer\rawParam;
use function Cspray\AnnotatedContainer\serviceParam;
use function Cspray\Typiphy\objectType;

abstract class ContainerFactoryTestCase extends TestCase {

    private Profiles $activeProfiles;

    abstract protected function getContainerFactory() : ContainerFactory;

    abstract protected function getBackingContainerInstanceOf() : ObjectType;

    private function getContainerDefinitionCompiler() : ContainerDefinitionAnalyzer {
        return new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
        );
    }

    private function getContainer(
        string $dir,
        Profiles $profiles = null,
        ParameterStore $parameterStore = null,
    ) : AnnotatedContainer {
        $compiler = $this->getContainerDefinitionCompiler();
        $optionsBuilder = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir);
        $containerDefinition = $compiler->analyze($optionsBuilder->build());
        $containerOptions = ContainerFactoryOptionsBuilder::forProfiles($profiles ?? Profiles::fromList(['default']));

        $factory = $this->getContainerFactory();
        if ($parameterStore !== null) {
            $factory->addParameterStore($parameterStore);
        }
        return $factory->createContainer($containerDefinition, $containerOptions->build());
    }

    public function testCreateServiceNotHasThrowsException() {
        $container = $this->getContainer(Fixtures::nonAnnotatedServices()->getPath());

        self::expectException(NotFoundExceptionInterface::class);
        self::expectExceptionMessage('The service "' . Fixtures::nonAnnotatedServices()->nonAnnotatedService()->getName() . '" could not be found in this container.');
        $container->get(Fixtures::nonAnnotatedServices()->nonAnnotatedService()->getName());
    }

    public function testGetSingleConcreteService() {
        $class = Fixtures::singleConcreteService()->fooImplementation()->getName();
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());
        $subject = $container->get($class);

        self::assertInstanceOf($class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $container = $this->getContainer(Fixtures::interfacePrepareServices()->getPath());
        $subject = $container->get(Fixtures::interfacePrepareServices()->fooInterface()->getName());

        self::assertInstanceOf(Fixtures::interfacePrepareServices()->fooImplementation()->getName(), $subject);
        self::assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnContainer() {
        $container = $this->getContainer(Fixtures::injectPrepareServices()->getPath());
        $subject = $container->get(Fixtures::injectPrepareServices()->prepareInjector()->getName());

        self::assertInstanceOf(Fixtures::injectPrepareServices()->prepareInjector()->getName(), $subject);
        self::assertSame('foo', $subject->getVal());
        self::assertInstanceOf(Fixtures::injectPrepareServices()->barImplementation()->getName(), $subject->getService());
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $container = $this->getContainer(Fixtures::ambiguousAliasedServices()->getPath());

        self::expectException(ContainerExceptionInterface::class);
        $container->get(Fixtures::ambiguousAliasedServices()->fooInterface()->getName());
    }

    public function testServiceDelegate() {
        $container = $this->getContainer(Fixtures::delegatedService()->getPath());
        $service = $container->get(Fixtures::delegatedService()->serviceInterface()->getName());

        self::assertSame('From ServiceFactory From FooService', $service->getValue());
    }

    public function testHasServiceIfCompiled() {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());

        self::assertTrue($container->has(Fixtures::singleConcreteService()->fooImplementation()->getName()));
        self::assertFalse($container->has(Fixtures::ambiguousAliasedServices()->fooInterface()->getName()));
    }

    public function testMultipleServicesWithPrimary() {
        $container = $this->getContainer(Fixtures::primaryAliasedServices()->getPath());

        self::assertInstanceOf(Fixtures::primaryAliasedServices()->fooImplementation()->getName(), $container->get(Fixtures::primaryAliasedServices()->fooInterface()->getName()));
    }

    public function testProfileResolvedServices() {
        $container = $this->getContainer(Fixtures::profileResolvedServices()->getPath(), Profiles::fromList(['default', 'dev']));

        $instance = $container->get(Fixtures::profileResolvedServices()->fooInterface()->getName());

        self::assertNotNull($instance);
        self::assertInstanceOf(Fixtures::profileResolvedServices()->devImplementation()->getName(), $instance);
    }

    public function testCreateNamedService() {
        $container = $this->getContainer(Fixtures::namedServices()->getPath());

        self::assertTrue($container->has('foo'));

        $instance = $container->get('foo');

        self::assertNotNull($instance);
        self::assertInstanceOf(Fixtures::namedServices()->fooImplementation()->getName(), $instance);
    }

    public function testCreateInjectStringService() {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath());

        self::assertSame('foobar', $container->get(Fixtures::injectConstructorServices()->injectStringService()->getName())->val);
    }

    public function testConcreteAliasDefinitionDoesNotHaveServiceDefinition() {
        $abstractService = Fixtures::implicitAliasedServices()->fooInterface()->getName();
        $concreteService = Fixtures::implicitAliasedServices()->fooImplementation()->getName();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract($abstract = objectType($abstractService))->build()
            )
            ->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($abstract)->withConcrete($concrete = objectType($concreteService))->build()
            )->build();

        $this->expectException(InvalidAlias::class);
        $this->expectExceptionMessage('An AliasDefinition has a concrete type, ' . $concrete->getName() . ', that is not a registered ServiceDefinition.');
        $this->getContainerFactory()->createContainer($containerDefinition);
    }

    public function testMultipleServicePrepare() {
        $container = $this->getContainer(Fixtures::multiplePrepareServices()->getPath());

        $subject = $container->get(Fixtures::multiplePrepareServices()->fooImplementation()->getName());

        self::assertSame('foobar', $subject->getProperty());
    }

    public function testInjectServiceObjectMethodParam() {
        $container = $this->getContainer(Fixtures::injectServiceConstructorServices()->getPath());

        $subject = $container->get(Fixtures::injectServiceConstructorServices()->serviceInjector()->getName());

        self::assertInstanceOf(Fixtures::injectServiceConstructorServices()->fooImplementation()->getName(), $subject->foo);
    }

    public function testInjectEnvMethodParam() {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath());

        $subject = $container->get(Fixtures::injectConstructorServices()->injectEnvService()->getName());
        self::assertSame(getenv('USER'), $subject->user);
    }

    public function testCreateArbitraryStorePresent() {
        $parameterStore = new class implements ParameterStore {
            public function getName(): string {
                return 'test-store';
            }

            public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
                return $key . '_test_store';
            }
        };
        $container = $this->getContainer(Fixtures::injectCustomStoreServices()->getPath(), parameterStore: $parameterStore);

        $subject = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());
        self::assertSame('key_test_store', $subject->key);
    }

    public function testCreateArbitraryStoreWithUnionType() {
        $parameterStore = new class implements ParameterStore {
            public function getName() : string {
                return 'union-store';
            }

            public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
                $type = Fixtures::injectUnionCustomStoreServices()->fooImplementation()->getName();
                return new $type();
            }
        };

        $container = $this->getContainer(Fixtures::injectUnionCustomStoreServices()->getPath(), parameterStore: $parameterStore);
        $subject = $container->get(Fixtures::injectUnionCustomStoreServices()->unionInjector()->getName());

        self::assertInstanceOf(Fixtures::injectUnionCustomStoreServices()->fooImplementation()->getName(), $subject->fooOrBar);
    }

    public function testCreateArbitraryStoreWithIntersectType() {
        $parameterStore = new class implements ParameterStore {
            public function getName() : string {
                return 'intersect-store';
            }

            public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
                $type = Fixtures::injectIntersectCustomStoreServices()->fooBarImplementation()->getName();
                return new $type();
            }
        };

        $container = $this->getContainer(Fixtures::injectIntersectCustomStoreServices()->getPath(), parameterStore: $parameterStore);
        $subject = $container->get(Fixtures::injectIntersectCustomStoreServices()->intersectInjector()->getName());

        self::assertInstanceOf(Fixtures::injectIntersectCustomStoreServices()->fooBarImplementation()->getName(), $subject->fooAndBar);
    }

    public function testCreateArbitraryStoreOnServiceNotPresent() {
        self::expectException(ParameterStoreNotFound::class);
        self::expectExceptionMessage('The ParameterStore "test-store" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.');
        $this->getContainer(Fixtures::injectCustomStoreServices()->getPath());
    }

    public function profilesProvider() : array {
        return [
            ['from-prod', Profiles::fromList(['default', 'prod'])],
            ['from-test', Profiles::fromList(['default', 'test'])],
            ['from-dev', Profiles::fromList(['default', 'dev'])],
        ];
    }

    /**
     * @dataProvider profilesProvider
     */
    public function testInjectProfilesMethodParam(string $expected, Profiles $profiles)  {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath(), $profiles);
        $subject = $container->get(Fixtures::injectConstructorServices()->injectProfilesStringService()->getName());

        self::assertSame($expected, $subject->val);
    }

    public function testMakeAutowiredObject() {
        $container = $this->getContainer(Fixtures::autowireableFactoryServices()->getPath());
        $subject = $container->make(Fixtures::autowireableFactoryServices()->factoryCreatedService()->getName(), autowiredParams(rawParam('scalar', '802')));

        self::assertInstanceOf(Fixtures::autowireableFactoryServices()->fooImplementation()->getName(), $subject->foo);
        self::assertSame('802', $subject->scalar);
    }

    public function testMakeAutowiredObjectReplaceServiceTarget() {
        $container = $this->getContainer(Fixtures::autowireableFactoryServices()->getPath());
        $subject = $container->make(Fixtures::autowireableFactoryServices()->factoryCreatedService()->getName(), autowiredParams(
            rawParam('scalar', 'quarters'),
            serviceParam('foo', Fixtures::autowireableFactoryServices()->barImplementation())
        ));

        self::assertInstanceOf(Fixtures::autowireableFactoryServices()->barImplementation()->getName(), $subject->foo);
        self::assertSame('quarters', $subject->scalar);
    }

    public function testBackingContainerInstanceOf() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        self::assertInstanceOf(
            $this->getBackingContainerInstanceOf()->getName(),
            $this->getContainerFactory()->createContainer($containerDefinition)->getBackingContainer()
        );
    }

    public function testGettingAutowireableFactory() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $container = $this->getContainerFactory()->createContainer($containerDefinition);

        self::assertSame($container, $container->get(AutowireableFactory::class));
    }

    public function testGettingAutowireableInvoker() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $container = $this->getContainerFactory()->createContainer($containerDefinition);

        self::assertSame($container, $container->get(AutowireableInvoker::class));
    }

    public function testNamedServicesShared() : void {
        $container = $this->getContainer(Fixtures::injectNamedServices()->getPath());

        $namedService = $container->get('bar');
        $typedService = $container->get(Fixtures::injectNamedServices()->barImplementation()->getName());

        self::assertSame($namedService, $typedService);
    }

    public function testInjectingNamedServices() : void {
        if ($this->getBackingContainerInstanceOf()->getName() === Container::class) {
            $this->markTestSkipped(
                IlluminateContainerFactory::class . ' does not support injecting multiple named services.'
            );
        }

        $container = $this->getContainer(Fixtures::injectNamedServices()->getPath());

        /** @var AnnotatedContainerFixture\InjectNamedServices\ServiceConsumer $service */
        $service = $container->get(Fixtures::injectNamedServices()->serviceConsumer()->getName());

        self::assertInstanceOf(Fixtures::injectNamedServices()->fooImplementation()->getName(), $service->foo);
        self::assertInstanceOf(Fixtures::injectNamedServices()->barImplementation()->getName(), $service->bar);
    }

    public function testGettingProfilesImplicitlyShared() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());

        $a = $container->get(Profiles::class);
        $b = $container->get(Profiles::class);

        self::assertInstanceOf(Profiles::class, $a);
        self::assertSame($a, $b);
    }

    public function testGettingProfilesHasCorrectList() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath(), Profiles::fromList(['default', 'foo', 'bar']));

        $activeProfile = $container->get(Profiles::class);

        self::assertInstanceOf(Profiles::class, $activeProfile);
        self::assertSame(['default', 'foo', 'bar'], $activeProfile->toArray());
    }

    public function testInvokeWithImplicitAlias() : void {
        $invoker = $this->getContainer(Fixtures::implicitAliasedServices()->getPath());
        $state = new \stdClass();
        $state->foo = null;
        $callable = fn(AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface $foo) => $state->foo = $foo;

        $invoker->invoke($callable);

        self::assertInstanceOf(Fixtures::implicitAliasedServices()->fooImplementation()->getName(), $state->foo);
    }

    public function testInvokeWithAmbiguousAliasRespectsParameters() : void {
        $invoker = $this->getContainer(Fixtures::ambiguousAliasedServices()->getPath());
        $state = new \stdClass();
        $state->foo = null;
        $callable = fn(AnnotatedContainerFixture\AmbiguousAliasedServices\FooInterface $foo) => $state->foo = $foo;
        $invoker->invoke($callable, autowiredParams(serviceParam('foo', Fixtures::ambiguousAliasedServices()->quxImplementation())));

        self::assertInstanceOf(Fixtures::ambiguousAliasedServices()->quxImplementation()->getName(), $state->foo);
    }

    public function testInvokeWithScalarParameter() : void {
        $invoker = $this->getContainer(Fixtures::implicitAliasedServices()->getPath());
        $state = new \stdClass();
        $state->bar = null;
        $callable = fn(AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface $foo, string $bar) => $state->bar = $bar;

        $invoker->invoke($callable, autowiredParams(rawParam('bar', 'foobaz')));

        self::assertSame('foobaz', $state->bar);
    }

    public function testInvokeReturnsCallableReturnValue() : void {
        $invoker = $this->getContainer(Fixtures::implicitAliasedServices()->getPath());
        $callable = fn() => 'returned from fn()';

        $actual = $invoker->invoke($callable);

        self::assertSame('returned from fn()', $actual);
    }

    public function testServiceProfileNotActiveNotShared() : void {
        $container = $this->getContainer(Fixtures::profileResolvedServices()->getPath(), Profiles::fromList(['default', 'dev']));

        self::assertTrue($container->has(Fixtures::profileResolvedServices()->fooInterface()->getName()));
        self::assertTrue($container->has(Fixtures::profileResolvedServices()->devImplementation()->getName()));
        self::assertFalse($container->has(Fixtures::profileResolvedServices()->prodImplementation()->getName()));
        self::assertFalse($container->has(Fixtures::profileResolvedServices()->testImplementation()->getName()));
    }

    public function testNamedServiceProfileNotActiveNotShared() : void {
        $container = $this->getContainer(Fixtures::namedProfileResolvedServices()->getPath(), Profiles::fromList(['default', 'prod']));

        self::assertTrue($container->has(Fixtures::namedProfileResolvedServices()->fooInterface()->getName()));
        self::assertTrue($container->has('prod-foo'));
        self::assertFalse($container->has('dev-foo'));
        self::assertFalse($container->has('test-foo'));
    }

    public function deserializeContainerProvider() : array {
        return [
            [Fixtures::injectCustomStoreServices(), function(ContainerFactory $containerFactory, ContainerDefinition $deserialize) {
                $store = $this->getMockBuilder(ParameterStore::class)->getMock();
                $store->expects($this->once())
                    ->method('getName')
                    ->willReturn('test-store');

                $store->expects($this->once())
                    ->method('fetch')
                    ->with($this->isInstanceOf(NamedType::class), 'key')
                    ->willReturn('the store key value');
                $containerFactory->addParameterStore($store);

                $container = $containerFactory->createContainer($deserialize);
                $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());

                self::assertSame('the store key value', $service->key);
            }],
            [Fixtures::injectConstructorServices(), function(ContainerFactory $containerFactory, ContainerDefinition $deserialize) {
                $container = $containerFactory->createContainer($deserialize);

                $service = $container->get(Fixtures::injectConstructorServices()->injectTypeUnionService()->getName());

                self::assertSame(4.20, $service->value);
            }]
        ];
    }

    /**
     * @dataProvider deserializeContainerProvider
     */
    public function testDeserializingContainerWithInjectAllowsServiceCreation(Fixture $fixture, callable $assertions) {
        $serializer = new ContainerDefinitionSerializer();
        $containerDefinition = $this->getContainerDefinitionCompiler()->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($fixture->getPath())->build()
        );

        $serialized = $serializer->serialize($containerDefinition);
        $deserialize = $serializer->deserialize($serialized);

        $containerFactory = $this->getContainerFactory();

        $assertions($containerFactory, $deserialize);
    }

}
