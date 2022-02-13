<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\AbstractSharedServices;
use Cspray\AnnotatedContainer\DummyApps\EnvironmentResolvedServices;
use Cspray\AnnotatedContainer\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseScalar;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseService;
use Cspray\AnnotatedContainer\DummyApps\MultipleSimpleServices;
use PHPUnit\Framework\TestCase;

class ContainerDefinitionSerializerTest extends TestCase {

    private ContainerDefinitionCompiler $injectorDefinitionCompiler;

    protected function setUp(): void {
        parent::setUp();
        $this->injectorDefinitionCompiler = new PhpParserContainerDefinitionCompiler();
    }

    public function testSerializeSimpleServices() {
        $injectorDefinition = $this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleServices');

        $expected = [
            'compiledServiceDefinitions' => [
                md5(SimpleServices\FooInterface::class) => [
                    'type' => SimpleServices\FooInterface::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => true,
                    'isClass' => false,
                    'isAbstract' => false
                ],
                md5(SimpleServices\FooImplementation::class) => [
                    'type' => SimpleServices\FooImplementation::class,
                    'implementedServices' => [md5(SimpleServices\FooInterface::class)],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ]
            ],
            'sharedServiceDefinitions' => [
                md5(SimpleServices\FooImplementation::class),
                md5(SimpleServices\FooInterface::class)
            ],
            'aliasDefinitions' => [
                [
                    'original' => md5(SimpleServices\FooInterface::class),
                    'alias' => md5(SimpleServices\FooImplementation::class)
                ]
            ],
            'servicePrepareDefinitions' => [],
            'useScalarDefinitions' => [],
            'useServiceDefinitions' => [],
            'serviceDelegateDefinitions' => []
        ];
        $this->assertEqualsCanonicalizing($expected, (new ContainerDefinitionSerializer())->serialize($injectorDefinition)->jsonSerialize());
    }

    public function testSerializeMultipleSimpleServices() {
        $injectorDefinition = $this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/MultipleSimpleServices');

        $expected = [
            'compiledServiceDefinitions' => [
                md5(MultipleSimpleServices\FooInterface::class) => [
                    'type' => MultipleSimpleServices\FooInterface::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => true,
                    'isClass' => false,
                    'isAbstract' => false
                ],
                md5(MultipleSimpleServices\FooImplementation::class) => [
                    'type' => MultipleSimpleServices\FooImplementation::class,
                    'implementedServices' => [md5(MultipleSimpleServices\FooInterface::class)],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ],
                md5(MultipleSimpleServices\BarInterface::class) => [
                    'type' => MultipleSimpleServices\BarInterface::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => true,
                    'isClass' => false,
                    'isAbstract' => false
                ],
                md5(MultipleSimpleServices\BarImplementation::class) => [
                    'type' => MultipleSimpleServices\BarImplementation::class,
                    'implementedServices' => [md5(MultipleSimpleServices\BarInterface::class)],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ]
            ],
            'sharedServiceDefinitions' => [
                md5(MultipleSimpleServices\BarImplementation::class),
                md5(MultipleSimpleServices\BarInterface::class),
                md5(MultipleSimpleServices\FooImplementation::class),
                md5(MultipleSimpleServices\FooInterface::class)
            ],
            'aliasDefinitions' => [
                [
                    'original' => md5(MultipleSimpleServices\BarInterface::class),
                    'alias' => md5(MultipleSimpleServices\BarImplementation::class)
                ],
                [
                    'original' => md5(MultipleSimpleServices\FooInterface::class),
                    'alias' => md5(MultipleSimpleServices\FooImplementation::class)
                ]
            ],
            'servicePrepareDefinitions' => [],
            'useScalarDefinitions' => [],
            'useServiceDefinitions' => [],
            'serviceDelegateDefinitions' => []
        ];
        $this->assertEqualsCanonicalizing($expected, (new ContainerDefinitionSerializer())->serialize($injectorDefinition)->jsonSerialize());
    }

    public function testSerializeAbstractSharedServices() {
        $injectorDefinition = $this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/AbstractSharedServices');

        $expected = [
            'compiledServiceDefinitions' => [
                md5(AbstractSharedServices\AbstractFoo::class) => [
                    'type' => AbstractSharedServices\AbstractFoo::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => true
                ],
                md5(AbstractSharedServices\FooImplementation::class) => [
                    'type' => AbstractSharedServices\FooImplementation::class,
                    'implementedServices' => [],
                    'extendedServices' => [md5(AbstractSharedServices\AbstractFoo::class)],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ]
            ],
            'sharedServiceDefinitions' => [
                md5(AbstractSharedServices\AbstractFoo::class),
                md5(AbstractSharedServices\FooImplementation::class)
            ],
            'aliasDefinitions' => [
                [
                    'original' => md5(AbstractSharedServices\AbstractFoo::class),
                    'alias' => md5(AbstractSharedServices\FooImplementation::class)
                ]
            ],
            'servicePrepareDefinitions' => [],
            'useScalarDefinitions' => [],
            'useServiceDefinitions' => [],
            'serviceDelegateDefinitions' => []
        ];
        $this->assertEqualsCanonicalizing($expected, (new ContainerDefinitionSerializer())->serialize($injectorDefinition)->jsonSerialize());
    }

    public function testSerializeInterfaceServicePrepare() {
        $injectorDefinition = $this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/InterfaceServicePrepare');

        $expected = [
            'compiledServiceDefinitions' => [
                md5(InterfaceServicePrepare\FooInterface::class) => [
                    'type' => InterfaceServicePrepare\FooInterface::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => true,
                    'isClass' => false,
                    'isAbstract' => false
                ],
                md5(InterfaceServicePrepare\FooImplementation::class) => [
                    'type' => InterfaceServicePrepare\FooImplementation::class,
                    'implementedServices' => [md5(InterfaceServicePrepare\FooInterface::class)],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ]
            ],
            'sharedServiceDefinitions' => [
                md5(InterfaceServicePrepare\FooImplementation::class),
                md5(InterfaceServicePrepare\FooInterface::class)
            ],
            'aliasDefinitions' => [
                [
                    'original' => md5(InterfaceServicePrepare\FooInterface::class),
                    'alias' => md5(InterfaceServicePrepare\FooImplementation::class)
                ]
            ],
            'servicePrepareDefinitions' => [
                [
                    'type' => InterfaceServicePrepare\FooInterface::class,
                    'method' => 'setBar'
                ]
            ],
            'useScalarDefinitions' => [],
            'useServiceDefinitions' => [],
            'serviceDelegateDefinitions' => []
        ];
        $this->assertEqualsCanonicalizing($expected, (new ContainerDefinitionSerializer())->serialize($injectorDefinition)->jsonSerialize());
    }

    public function testSerializeSimpleUseScalar() {
        $injectorDefinition = $this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleUseScalar');

        $expected = [
            'compiledServiceDefinitions' => [
                md5(SimpleUseScalar\FooImplementation::class) => [
                    'type' => SimpleUseScalar\FooImplementation::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ]
            ],
            'sharedServiceDefinitions' => [md5(SimpleUseScalar\FooImplementation::class)],
            'aliasDefinitions' => [],
            'servicePrepareDefinitions' => [],
            'useScalarDefinitions' => [
                [
                    'type' => SimpleUseScalar\FooImplementation::class,
                    'method' => '__construct',
                    'paramName' => 'stringParam',
                    'paramType' => 'string',
                    'value' => 'string param test value'
                ],
                [
                    'type' => SimpleUseScalar\FooImplementation::class,
                    'method' => '__construct',
                    'paramName' => 'intParam',
                    'paramType' => 'int',
                    'value' => 42
                ],
                [
                    'type' => SimpleUseScalar\FooImplementation::class,
                    'method' => '__construct',
                    'paramName' => 'floatParam',
                    'paramType' => 'float',
                    'value' => 42.0
                ],
                [
                    'type' => SimpleUseScalar\FooImplementation::class,
                    'method' => '__construct',
                    'paramName' => 'boolParam',
                    'paramType' => 'bool',
                    'value' => true
                ],
                [
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
                ]
            ],
            'useServiceDefinitions' => [],
            'serviceDelegateDefinitions' => []
        ];
        $this->assertEqualsCanonicalizing($expected, (new ContainerDefinitionSerializer())->serialize($injectorDefinition)->jsonSerialize());
    }

    public function testSerializeSimpleUserScalarFromEnv() {
        $injectorDefinition = $this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/EnvironmentResolvedServices');

        $expected = [
            'compiledServiceDefinitions' => [
                md5(EnvironmentResolvedServices\FooInterface::class) => [
                    'type' => EnvironmentResolvedServices\FooInterface::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => true,
                    'isClass' => false,
                    'isAbstract' => false
                ],
                md5(EnvironmentResolvedServices\TestFooImplementation::class) => [
                    'type' => EnvironmentResolvedServices\TestFooImplementation::class,
                    'implementedServices' => [md5(EnvironmentResolvedServices\FooInterface::class)],
                    'extendedServices' => [],
                    'environments' => ['test'],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ]
            ],
            'sharedServiceDefinitions' => [
                md5(EnvironmentResolvedServices\FooInterface::class),
                md5(EnvironmentResolvedServices\TestFooImplementation::class)
            ],
            'aliasDefinitions' => [
                [
                    'original' => md5(EnvironmentResolvedServices\FooInterface::class),
                    'alias' => md5(EnvironmentResolvedServices\TestFooImplementation::class)
                ]
            ],
            'servicePrepareDefinitions' => [],
            'useScalarDefinitions' => [],
            'useServiceDefinitions' => [],
            'serviceDelegateDefinitions' => []
        ];
        $this->assertEqualsCanonicalizing($expected, (new ContainerDefinitionSerializer())->serialize($injectorDefinition)->jsonSerialize());
    }

    public function testSerializeSimpleUseService() {
        $injectorDefinition = $this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleUseService');

        $expected = [
            'compiledServiceDefinitions' => [
                md5(SimpleUseService\BarImplementation::class) => [
                    'type' => SimpleUseService\BarImplementation::class,
                    'implementedServices' => [md5(SimpleUseService\FooInterface::class)],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ],
                md5(SimpleUseService\BazImplementation::class) => [
                    'type' => SimpleUseService\BazImplementation::class,
                    'implementedServices' => [md5(SimpleUseService\FooInterface::class)],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ],
                md5(SimpleUseService\ConstructorInjection::class) => [
                    'type' => SimpleUseService\ConstructorInjection::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ],
                md5(SimpleUseService\FooInterface::class) => [
                    'type' => SimpleUseService\FooInterface::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => true,
                    'isClass' => false,
                    'isAbstract' => false
                ],
                md5(SimpleUseService\SetterInjection::class) => [
                    'type' => SimpleUseService\SetterInjection::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ],
                md5(SimpleUseService\QuxImplementation::class) => [
                    'type' => SimpleUseService\QuxImplementation::class,
                    'implementedServices' => [md5(SimpleUseService\FooInterface::class)],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ],
            ],
            'sharedServiceDefinitions' => [
                md5(SimpleUseService\BarImplementation::class),
                md5(SimpleUseService\BazImplementation::class),
                md5(SimpleUseService\ConstructorInjection::class),
                md5(SimpleUseService\FooInterface::class),
                md5(SimpleUseService\QuxImplementation::class),
                md5(SimpleUseService\SetterInjection::class)
            ],
            'aliasDefinitions' => [
                [
                    'original' => md5(SimpleUseService\FooInterface::class),
                    'alias' => md5(SimpleUseService\BarImplementation::class)
                ],
                [
                    'original' => md5(SimpleUseService\FooInterface::class),
                    'alias' => md5(SimpleUseService\BazImplementation::class)
                ],
                [
                    'original' => md5(SimpleUseService\FooInterface::class),
                    'alias' => md5(SimpleUseService\QuxImplementation::class)
                ]
            ],
            'servicePrepareDefinitions' => [
                [
                    'type' => SimpleUseService\SetterInjection::class,
                    'method' => 'setBaz'
                ],
                [
                    'type' => SimpleUseService\SetterInjection::class,
                    'method' => 'setBar'
                ],
                [
                    'type' => SimpleUseService\SetterInjection::class,
                    'method' => 'setQux'
                ]
            ],
            'useScalarDefinitions' => [],
            'useServiceDefinitions' => [
                [
                    'type' => SimpleUseService\ConstructorInjection::class,
                    'method' => '__construct',
                    'paramName' => 'bar',
                    'paramType' => SimpleUseService\FooInterface::class,
                    'value' => SimpleUseService\BarImplementation::class
                ],
                [
                    'type' => SimpleUseService\ConstructorInjection::class,
                    'method' => '__construct',
                    'paramName' => 'baz',
                    'paramType' => SimpleUseService\FooInterface::class,
                    'value' => SimpleUseService\BazImplementation::class
                ],
                [
                    'type' => SimpleUseService\ConstructorInjection::class,
                    'method' => '__construct',
                    'paramName' => 'qux',
                    'paramType' => SimpleUseService\FooInterface::class,
                    'value' => SimpleUseService\QuxImplementation::class
                ],
                [
                    'type' => DummyApps\SimpleUseService\SetterInjection::class,
                    'method' => 'setBaz',
                    'paramName' => 'foo',
                    'paramType' => SimpleUseService\FooInterface::class,
                    'value' => SimpleUseService\BazImplementation::class
                ],
                [
                    'type' => DummyApps\SimpleUseService\SetterInjection::class,
                    'method' => 'setBar',
                    'paramName' => 'foo',
                    'paramType' => SimpleUseService\FooInterface::class,
                    'value' => SimpleUseService\BarImplementation::class
                ],
                [
                    'type' => DummyApps\SimpleUseService\SetterInjection::class,
                    'method' => 'setQux',
                    'paramName' => 'foo',
                    'paramType' => SimpleUseService\FooInterface::class,
                    'value' => SimpleUseService\QuxImplementation::class
                ]
            ],
            'serviceDelegateDefinitions' => []
        ];
        $actual = (new ContainerDefinitionSerializer())->serialize($injectorDefinition)->jsonSerialize();
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testSerializeServiceDelegate() {
        $injectorDefinition = $this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/ServiceDelegate');

        $expected = [
            'compiledServiceDefinitions' => [
                md5(ServiceDelegate\ServiceInterface::class) => [
                    'type' => ServiceDelegate\ServiceInterface::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => true,
                    'isClass' => false,
                    'isAbstract' => false
                ],
                md5(ServiceDelegate\FooService::class) => [
                    'type' => ServiceDelegate\FooService::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ]
            ],
            'sharedServiceDefinitions' => [
                md5(ServiceDelegate\FooService::class),
                md5(ServiceDelegate\ServiceInterface::class)
            ],
            'aliasDefinitions' => [],
            'servicePrepareDefinitions' => [],
            'useScalarDefinitions' => [],
            'useServiceDefinitions' => [],
            'serviceDelegateDefinitions' => [
                [
                    'delegateType' => ServiceDelegate\ServiceFactory::class,
                    'delegateMethod' => 'createService',
                    'serviceType' => ServiceDelegate\ServiceInterface::class
                ]
            ]
        ];
        $this->assertEqualsCanonicalizing($expected, (new ContainerDefinitionSerializer())->serialize($injectorDefinition)->jsonSerialize());
    }

    public function testDeserializeSimpleServices() {
        $serializer = new ContainerDefinitionSerializer();
        $json = json_encode($serializer->serialize($this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleServices')));
        $injectorDefinition = $serializer->deserialize($json);

        $injector = (new AurynInjectorFactory())->createInjector($injectorDefinition);

        $foo1 = $injector->make(SimpleServices\FooInterface::class);

        $this->assertInstanceOf(SimpleServices\FooImplementation::class, $foo1);

        $foo2 = $injector->make(SimpleServices\FooInterface::class);

        $this->assertSame($foo1, $foo2);
    }

    public function testDeserializeInterfaceServicePrepare() {
        $serializer = new ContainerDefinitionSerializer();
        $json = json_encode($serializer->serialize($this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/InterfaceServicePrepare')));
        $injectorDefinition = $serializer->deserialize($json);

        $injector = (new AurynInjectorFactory())->createInjector($injectorDefinition);

        $foo = $injector->make(InterfaceServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InterfaceServicePrepare\FooImplementation::class, $foo);

        $this->assertSame(1, $foo->getBarCounter());
    }

    public function testDeserializeSimpleUseScalar() {
        $serializer = new ContainerDefinitionSerializer();
        $json = json_encode($serializer->serialize($this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleUseScalar')));
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
        $serializer = new ContainerDefinitionSerializer();
        $json = json_encode($serializer->serialize($this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleUseService')));
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
        $serializer = new ContainerDefinitionSerializer();
        $json = json_encode($serializer->serialize($this->injectorDefinitionCompiler->compileDirectory('test', __DIR__ . '/DummyApps/ServiceDelegate')));
        $injectorDefinition = $serializer->deserialize($json);

        $injector = (new AurynInjectorFactory())->createInjector($injectorDefinition);

        $service = $injector->make(ServiceDelegate\ServiceInterface::class);

        $this->assertSame('From ServiceFactory From FooService', $service->getValue());
    }

}