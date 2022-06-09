<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainerFixture;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Cspray\Typiphy\objectType;

abstract class ContainerFactoryTestCase extends TestCase {
    abstract protected function getContainerFactory() : ContainerFactory;

    abstract protected function getBackingContainerInstanceOf() : ObjectType;

    private function getContainerDefinitionCompiler() : ContainerDefinitionCompiler {
        return new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
    }

    private function getContainer(string $dir, array $profiles = [], ParameterStore $parameterStore = null) : ContainerInterface&AutowireableFactory {
        $compiler = $this->getContainerDefinitionCompiler();
        $optionsBuilder = ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir);
        $containerDefinition = $compiler->compile($optionsBuilder->build());
        $containerOptions = null;
        if (!empty($profiles)) {
            $containerOptions = ContainerFactoryOptionsBuilder::forActiveProfiles(...$profiles)->build();
        }
        $factory = $this->getContainerFactory();
        if (!is_null($parameterStore)) {
            $factory->addParameterStore($parameterStore);
        }
        return $factory->createContainer($containerDefinition, $containerOptions);
    }

    public function testCreateServiceNotHasThrowsException() {
        $container = $this->getContainer(Fixtures::nonAnnotatedServices()->getPath());

        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('The service "' . Fixtures::nonAnnotatedServices()->nonAnnotatedService()->getName() . '" could not be found in this container.');
        $container->get(Fixtures::nonAnnotatedServices()->nonAnnotatedService()->getName());
    }

    public function testGetSingleConcreteService() {
        $class = Fixtures::singleConcreteService()->fooImplementation()->getName();
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());
        $subject = $container->get($class);

        $this->assertInstanceOf($class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $container = $this->getContainer(Fixtures::interfacePrepareServices()->getPath());
        $subject = $container->get(Fixtures::interfacePrepareServices()->fooInterface()->getName());

        $this->assertInstanceOf(Fixtures::interfacePrepareServices()->fooImplementation()->getName(), $subject);
        $this->assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnContainer() {
        $container = $this->getContainer(Fixtures::injectPrepareServices()->getPath());
        $subject = $container->get(Fixtures::injectPrepareServices()->prepareInjector()->getName());

        $this->assertInstanceOf(Fixtures::injectPrepareServices()->prepareInjector()->getName(), $subject);
        $this->assertSame('foo', $subject->getVal());
        $this->assertInstanceOf(Fixtures::injectPrepareServices()->barImplementation()->getName(), $subject->getService());
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $container = $this->getContainer(Fixtures::ambiguousAliasedServices()->getPath());

        $this->expectException(ContainerExceptionInterface::class);
        $container->get(Fixtures::ambiguousAliasedServices()->fooInterface()->getName());
    }

    public function testServiceDelegate() {
        $container = $this->getContainer(Fixtures::delegatedService()->getPath());
        $service = $container->get(Fixtures::delegatedService()->serviceInterface()->getName());

        $this->assertSame('From ServiceFactory From FooService', $service->getValue());
    }

    public function testHasServiceIfCompiled() {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());

        $this->assertTrue($container->has(Fixtures::singleConcreteService()->fooImplementation()->getName()));
        $this->assertFalse($container->has(Fixtures::ambiguousAliasedServices()->fooInterface()->getName()));
    }

    public function testMultipleServicesWithPrimary() {
        $container = $this->getContainer(Fixtures::primaryAliasedServices()->getPath());

        $this->assertInstanceOf(Fixtures::primaryAliasedServices()->fooImplementation()->getName(), $container->get(Fixtures::primaryAliasedServices()->fooInterface()->getName()));
    }

    public function testProfileResolvedServices() {
        $container = $this->getContainer(Fixtures::profileResolvedServices()->getPath(), ['default', 'dev']);

        $instance = $container->get(Fixtures::profileResolvedServices()->fooInterface()->getName());

        $this->assertNotNull($instance);
        $this->assertInstanceOf(Fixtures::profileResolvedServices()->devImplementation()->getName(), $instance);
    }

    public function testCreateNamedService() {
        $container = $this->getContainer(Fixtures::namedServices()->getPath());

        $this->assertTrue($container->has('foo'));

        $instance = $container->get('foo');

        $this->assertNotNull($instance);
        $this->assertInstanceOf(Fixtures::namedServices()->fooImplementation()->getName(), $instance);
    }

    public function testCreateInjectStringService() {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath());

        $this->assertSame('foobar', $container->get(Fixtures::injectConstructorServices()->injectStringService()->getName())->val);
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

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('An AliasDefinition is defined with a concrete type ' . $concrete->getName() . ' that is not a registered #[Service].');
        $this->getContainerFactory()->createContainer($containerDefinition);
    }

    public function testMultipleServicePrepare() {
        $container = $this->getContainer(Fixtures::multiplePrepareServices()->getPath());

        $subject = $container->get(Fixtures::multiplePrepareServices()->fooImplementation()->getName());

        $this->assertSame('foobar', $subject->getProperty());
    }

    public function testInjectServiceObjectMethodParam() {
        $container = $this->getContainer(Fixtures::injectServiceConstructorServices()->getPath());

        $subject = $container->get(Fixtures::injectServiceConstructorServices()->serviceInjector()->getName());

        $this->assertInstanceOf(Fixtures::injectServiceConstructorServices()->fooImplementation()->getName(), $subject->foo);
    }

    public function testInjectEnvMethodParam() {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath());

        $subject = $container->get(Fixtures::injectConstructorServices()->injectEnvService()->getName());
        $this->assertSame(getenv('USER'), $subject->user);
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
        $this->assertSame('key_test_store', $subject->key);
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

        $this->assertInstanceOf(Fixtures::injectUnionCustomStoreServices()->fooImplementation()->getName(), $subject->fooOrBar);
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

        $this->assertInstanceOf(Fixtures::injectIntersectCustomStoreServices()->fooBarImplementation()->getName(), $subject->fooAndBar);
    }

    public function testCreateArbitraryStoreNotPresent() {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('The ParameterStore "test-store" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.');
        $this->getContainer(Fixtures::injectCustomStoreServices()->getPath());
    }

    public function profilesProvider() : array {
        return [
            ['from-prod', ['prod']],
            ['from-test', ['test']],
            ['from-dev', ['dev']]
        ];
    }

    /**
     * @dataProvider profilesProvider
     */
    public function testInjectProfilesMethodParam(string $expected, array $profiles)  {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath(), $profiles);
        $subject = $container->get(Fixtures::injectConstructorServices()->injectProfilesStringService()->getName());

        $this->assertSame($expected, $subject->val);
    }

    public function testConfigurationSharedInstance() {
        $container = $this->getContainer(Fixtures::configurationServices()->getPath(), ['default', 'dev']);

        $this->assertSame(
            $container->get(Fixtures::configurationServices()->myConfig()->getName()),
            $container->get(Fixtures::configurationServices()->myConfig()->getName())
        );
    }

    public function testConfigurationValues() {
        $container = $this->getContainer(Fixtures::configurationServices()->getPath(), ['default', 'dev']);
        /** @var AnnotatedContainerFixture\ConfigurationServices\MyConfig $subject */
        $subject = $container->get(Fixtures::configurationServices()->myConfig()->getName());

        $this->assertSame('my-api-key', $subject->key);
        $this->assertSame(1234, $subject->port);
        $this->assertSame(getenv('USER'), $subject->user);
        $this->assertTrue($subject->testMode);
    }

    public function testNamedConfigurationInstanceOf() {
        $container = $this->getContainer(Fixtures::namedConfigurationServices()->getPath());

        $this->assertInstanceOf(Fixtures::namedConfigurationServices()->myConfig()->getName(), $container->get('my-config'));
    }

    public function testMakeAutowiredObject() {
        $container = $this->getContainer(Fixtures::autowireableFactoryServices()->getPath());
        $subject = $container->make(Fixtures::autowireableFactoryServices()->factoryCreatedService()->getName(), autowiredParams(rawParam('scalar', '802')));

        $this->assertInstanceOf(Fixtures::autowireableFactoryServices()->fooImplementation()->getName(), $subject->foo);
        $this->assertSame('802', $subject->scalar);
    }

    public function testMakeAutowiredObjectReplaceServiceTarget() {
        $container = $this->getContainer(Fixtures::autowireableFactoryServices()->getPath());
        $subject = $container->make(Fixtures::autowireableFactoryServices()->factoryCreatedService()->getName(), autowiredParams(
            rawParam('scalar', 'quarters'),
            serviceParam('foo', Fixtures::autowireableFactoryServices()->barImplementation())
        ));

        $this->assertInstanceOf(Fixtures::autowireableFactoryServices()->barImplementation()->getName(), $subject->foo);
        $this->assertSame('quarters', $subject->scalar);
    }

    public function testBackingContainerInstanceOf() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $this->assertInstanceOf($this->getBackingContainerInstanceOf()->getName(), $this->getContainerFactory()->createContainer($containerDefinition)->getBackingContainer());
    }

    public function testGettingAutowireableFactory() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $container = $this->getContainerFactory()->createContainer($containerDefinition);

        $this->assertSame($container, $container->get(AutowireableFactory::class));
    }

    public function testNamedServicesShared() : void {
        $container = $this->getContainer(Fixtures::injectNamedServices()->getPath());

        $namedService = $container->get('bar');
        $typedService = $container->get(Fixtures::injectNamedServices()->barImplementation()->getName());

        $this->assertSame($namedService, $typedService);
    }

    public function testInjectingNamedServices() : void {
        $container = $this->getContainer(Fixtures::injectNamedServices()->getPath());

        /** @var AnnotatedContainerFixture\InjectNamedServices\ServiceConsumer $service */
        $service = $container->get(Fixtures::injectNamedServices()->serviceConsumer()->getName());

        $this->assertInstanceOf(Fixtures::injectNamedServices()->fooImplementation()->getName(), $service->foo);
        $this->assertInstanceOf(Fixtures::injectNamedServices()->barImplementation()->getName(), $service->bar);
    }

    public function testCreatingNonSharedServices() : void {
        $container = $this->getContainer(Fixtures::nonSharedServices()->getPath());

        $a = $container->get(Fixtures::nonSharedServices()->fooImplementation()->getName());
        $b = $container->get(Fixtures::nonSharedServices()->fooImplementation()->getName());

        $this->assertNotSame($a, $b);
    }

    public function testGettingActiveProfilesImplicitlyShared() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());

        $a = $container->get(ActiveProfiles::class);
        $b = $container->get(ActiveProfiles::class);

        $this->assertInstanceOf(ActiveProfiles::class, $a);
        $this->assertSame($a, $b);
    }

    public function testGettingActiveProfilesHasCorrectList() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath(), ['default', 'foo', 'bar']);

        /** @var ActiveProfiles $activeProfile */
        $activeProfile = $container->get(ActiveProfiles::class);

        $this->assertSame(['default', 'foo', 'bar'], $activeProfile->getProfiles());
    }

    public function testIsActiveProfileNotListed() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath(), ['default', 'foo', 'bar']);

        /** @var ActiveProfiles $activeProfile */
        $activeProfile = $container->get(ActiveProfiles::class);

        $this->assertFalse($activeProfile->isActive('baz'));
    }

    public function testIsActiveProfileListed() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath(), ['default', 'foo', 'bar']);

        /** @var ActiveProfiles $activeProfile */
        $activeProfile = $container->get(ActiveProfiles::class);

        $this->assertTrue($activeProfile->isActive('foo'));
    }

}