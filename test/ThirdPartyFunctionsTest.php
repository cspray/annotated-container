<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Internal\ArrayAnnotationValue;
use Cspray\AnnotatedContainer\Internal\SingleAnnotationValue;
use PHPUnit\Framework\TestCase;

const ANNOTATED_CONTAINER_THIRD_PARTY_TEST = 'foobar';

class ThirdPartyFunctionsTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    public function scalarValueProvider() : array {
        return [
            ['foo'],
            [42],
            [3.14],
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider scalarValueProvider
     * @return void
     */
    public function testScalarValueHasCorrectCompileTimeValue(string|int|float|bool $value) : void {
        $annotationValue = scalarValue($value);

        $this->assertSame($value, $annotationValue->getCompileValue());
    }

    /**
     * @dataProvider scalarValueProvider
     * @return void
     */
    public function testScalarValueHasCorrectRuntimeValue(string|int|float|bool $value) : void {
        $annotationValue = scalarValue($value);

        $this->assertSame($value, $annotationValue->getRuntimeValue());
    }

    /**
     * @dataProvider scalarValueProvider
     * @param string|int|float|bool $value
     * @return void
     */
    public function testScalarValueSerializeAndUnserialize(string|int|float|bool $value) : void {
        $annotationValue = scalarValue($value);
        $serializedValue = serialize($annotationValue);
        $unserializedValue = unserialize($serializedValue);

        $this->assertSame($value, $unserializedValue->getCompileValue());
    }

    public function constantValueProvider() : array {
        return [
            [DummyApps\ClassConstantUseScalar\FooImplementation::class. '::VALUE'],
            ['\\Cspray\\AnnotatedContainer\\ANNOTATED_CONTAINER_THIRD_PARTY_TEST']
        ];
    }

    /**
     * @dataProvider constantValueProvider
     * @return void
     */
    public function testConstantValueCompileValue(string $constName) : void {
        $annotationValue = constantValue($constName);

        $this->assertSame($constName, $annotationValue->getCompileValue());
    }

    public function testClassConstantValueRunTimeValue() {
        $annotationValue = constantValue(DummyApps\ClassConstantUseScalar\FooImplementation::class . '::VALUE');

        $this->assertSame(DummyApps\ClassConstantUseScalar\FooImplementation::VALUE, $annotationValue->getRuntimeValue());
    }

    public function testNotClassConstantValueRunTimeValue() {
        $annotationValue = constantValue('\\Cspray\\AnnotatedContainer\\ANNOTATED_CONTAINER_THIRD_PARTY_TEST');

        $this->assertSame('foobar', $annotationValue->getRuntimeValue());
    }

    /**
     * @dataProvider constantValueProvider
     * @param string $constName
     * @return void
     */
    public function testConstantValueSerializeAndUnserialize(string $constName) {
        $annotationValue = constantValue($constName);

        $serialized = serialize($annotationValue);
        $unserialize = unserialize($serialized);

        $this->assertSame($constName, $unserialize->getCompileValue());
    }

    public function testEnvValueCompileValue() {
        $annotationValue = envValue('USER');

        $this->assertSame('USER', $annotationValue->getCompileValue());
    }

    public function testEnvValueRuntimeValue() {
        $annotationValue = envValue('USER');

        $this->assertSame(get_current_user(), $annotationValue->getRuntimeValue());
    }

    public function testEnvValueSerializeAndUnserialize() {
        $annotationValue = envValue('USER');

        $serialized = serialize($annotationValue);
        $unserialize = unserialize($serialized);

        $this->assertSame('USER', $unserialize->getCompileValue());
    }

    public function testScalarCollectionConvertedToAnnotationValues() {
        $annotationValue = arrayValue(['a', 'b', 'c']);
        $items = [];
        foreach ($annotationValue as $item) {
            $items[] = $item::class;
        }
        $this->assertEquals([
            SingleAnnotationValue::class,
            SingleAnnotationValue::class,
            SingleAnnotationValue::class
        ], $items);
    }

    public function testScalarConvertedToAnnotationValuesHasCompileValue() {
        $annotationValue = arrayValue(['a', 'b', 'c']);

        $this->assertSame(['a', 'b', 'c'], $annotationValue->getCompileValue());
    }

    public function testEnsureArrayKeysPreservedForArrayAnnotationValue() {
        $annotationValue = arrayValue(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $annotationValue->getCompileValue());
    }

    public function testRecursivelyConvertsArraysToAnnotationValues() {
        $annotationValue = arrayValue([
            'foo' => [
                'bar' => [
                    'string' => 'foobar',
                    'float' => 3.14,
                    'int' => 42,
                    'bool' => true
                ]
            ]
        ]);

        $values = iterator_to_array($annotationValue);
        $this->assertInstanceOf(ArrayAnnotationValue::class, $values['foo']);

        $fooValues = iterator_to_array($values['foo']);
        $this->assertInstanceOf(ArrayAnnotationValue::class, $fooValues['bar']);
    }

    public function testRespectsValuesAlreadyAnnotationValues() {
        $fooValue = constantValue(DummyApps\ClassConstantUseScalar\FooImplementation::class . '::VALUE');
        $barValue = envValue('USER');
        $bazValue = scalarValue('foobar');
        $annotationValue = arrayValue([
            'foo' => $fooValue,
            'bar' => $barValue,
            'baz' => $bazValue
        ]);

        $values = iterator_to_array($annotationValue);

        $this->assertSame($fooValue, $values['foo']);
        $this->assertSame($barValue, $values['bar']);
        $this->assertSame($bazValue, $values['baz']);
    }

    public function testRecursiveCompileValues() {
        $annotationValue = arrayValue($expected = [
            'foo' => [
                'bar' => [
                    'string' => 'foobar',
                    'float' => 3.14,
                    'int' => 42,
                    'bool' => true
                ]
            ]
        ]);

        $this->assertSame($expected, $annotationValue->getCompileValue());
    }

    public function testArrayRuntimeValues() {
        $fooValue = constantValue(DummyApps\ClassConstantUseScalar\FooImplementation::class . '::VALUE');
        $barValue = envValue('USER');
        $bazValue = scalarValue('foobar');
        $annotationValue = arrayValue([
            'foo' => $fooValue,
            'bar' => $barValue,
            'baz' => $bazValue
        ]);

        $expected = [
            'foo' => DummyApps\ClassConstantUseScalar\FooImplementation::VALUE,
            'bar' => get_current_user(),
            'baz' => 'foobar'
        ];
        $this->assertSame($expected, $annotationValue->getRuntimeValue());
    }

    public function testSerializeDeserializeArrayValues() {
        $annotationValue = arrayValue([
            'foo' => [
                'bar' => [
                    'string' => 'foobar',
                    'float' => 3.14,
                    'int' => 42,
                    'bool' => true
                ]
            ]
        ]);

        $serialized = serialize($annotationValue);
        $unserialize = unserialize($serialized);

        $this->assertSame($annotationValue->getCompileValue(), $unserialize->getCompileValue());
    }

    private function getContext() : ContainerDefinitionBuilderContext {
        $builder = ContainerDefinitionBuilder::newDefinition();
        return new class($builder) implements ContainerDefinitionBuilderContext {

            public function __construct(private ContainerDefinitionBuilder $builder) {}

            public function getBuilder(): ContainerDefinitionBuilder {
                return $this->builder;
            }

            public function setBuilder(ContainerDefinitionBuilder $containerDefinitionBuilder) {
                $this->builder = $containerDefinitionBuilder;
            }
        };
    }

    public function testHasServiceDefinitionForType() : void {
        $context = $this->getContext();
        service($context, DummyApps\SimpleServices\FooInterface::class);

        $containerDefinition = $context->getBuilder()->build();

        $this->assertServiceDefinitionsHaveTypes([
            DummyApps\SimpleServices\FooInterface::class
        ], $containerDefinition->getServiceDefinitions());
    }

    public function testServiceDefinitionReturnsIsInContainerDefinition() {
        $context = $this->getContext();
        $def = service($context, DummyApps\SimpleServices\FooInterface::class);

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooInterface::class);

        $this->assertSame($serviceDefinition, $def);
    }

    public function testAbstractDefinedServiceIsAbstract() {
        $context = $this->getContext();
        service($context, DummyApps\SimpleServices\FooInterface::class);

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooInterface::class);

        $this->assertTrue($serviceDefinition?->isAbstract());
    }

    public function testAbstractDefinedServiceGetName() {
        $context = $this->getContext();
        service($context, DummyApps\SimpleServices\FooInterface::class, scalarValue('fooService'));

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooInterface::class);

        $this->assertSame('fooService', $serviceDefinition?->getName()?->getCompileValue());
    }

    public function testAbstractDefinedServiceGetProfiles() {
        $context = $this->getContext();
        service($context, DummyApps\SimpleServices\FooImplementation::class, profiles: arrayValue(['default', 'dev']));

        $containerDefinition = $context->getBuilder();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooImplementation::class);

        $this->assertSame(['default', 'dev'], $serviceDefinition->getProfiles()->getCompileValue());
    }

    public function testConcreteServiceIsNotDefined() {
        $context = $this->getContext();
        service($context, DummyApps\SimpleServices\FooImplementation::class);

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooImplementation::class);

        $this->assertTrue($serviceDefinition?->isConcrete());
    }

    public function testServiceIsPrimary() {
        $context = $this->getContext();
        service($context, DummyApps\SimpleServices\FooImplementation::class, isPrimary: true);

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooImplementation::class);

        $this->assertTrue($serviceDefinition->isPrimary());
    }

    public function testAddAliasDefinition() {
        $context = $this->getContext();
        $abstract = service($context, DummyApps\SimpleServices\FooInterface::class);
        $concrete = service($context, DummyApps\SimpleServices\FooImplementation::class);
        alias($context, $abstract, $concrete);

        $containerDefinition = $context->getBuilder()->build();
        $this->assertAliasDefinitionsMap([
            [DummyApps\SimpleServices\FooInterface::class, DummyApps\SimpleServices\FooImplementation::class]
        ], $containerDefinition->getAliasDefinitions());
    }

    public function testServiceDelegateDefinition() {
        $context = $this->getContext();
        $service = service($context, DummyApps\ServiceDelegate\ServiceInterface::class);
        serviceDelegate($context, $service, DummyApps\ServiceDelegate\ServiceFactory::class, 'createService');

        $containerDefinition = $context->getBuilder()->build();

        $this->assertCount(1, $containerDefinition->getServiceDelegateDefinitions());
        $this->assertSame(DummyApps\ServiceDelegate\ServiceInterface::class, $containerDefinition->getServiceDelegateDefinitions()[0]->getServiceType()->getType());
        $this->assertSame(DummyApps\ServiceDelegate\ServiceFactory::class, $containerDefinition->getServiceDelegateDefinitions()[0]->getDelegateType());
        $this->assertSame('createService', $containerDefinition->getServiceDelegateDefinitions()[0]->getDelegateMethod());
    }

    public function testServicePrepareDefinition() {
        $context = $this->getContext();
        $service = service($context, DummyApps\InterfaceServicePrepare\FooInterface::class);
        servicePrepare($context, $service, 'setBar');

        $containerDefinition = $context->getBuilder()->build();

        $this->assertServicePrepareTypes([
            [DummyApps\InterfaceServicePrepare\FooInterface::class, 'setBar']
        ], $containerDefinition->getServicePrepareDefinitions());
    }

    public function testInjectScalarDefinition() {
        $context = $this->getContext();
        $service = service($context, DummyApps\SimpleUseScalar\FooImplementation::class);
        injectScalar($context, $service, '__construct', 'stringParam', ScalarType::String, scalarValue('foobar'), arrayValue(['default', 'dev']));

        $containerDefinition = $context->getBuilder()->build();

        $this->assertInjectScalarParamValues([
            DummyApps\SimpleUseScalar\FooImplementation::class . '::__construct(stringParam)|default,dev' => 'foobar'
        ], $containerDefinition->getInjectScalarDefinitions());
    }

    public function testInjectEnvHasInjectScalarDefinition() {
        $context = $this->getContext();
        $service = service($context, DummyApps\SimpleUseScalarFromEnv\FooImplementation::class);
        injectEnv($context, $service, '__construct', 'user', ScalarType::String, 'USER', arrayValue(['default']));

        $containerDefinition = $context->getBuilder()->build();

        $this->assertInjectScalarParamValues([
            DummyApps\SimpleUseScalarFromEnv\FooImplementation::class . '::__construct(user)|default' => 'USER'
        ], $containerDefinition->getInjectScalarDefinitions());
    }

    public function testInjectService() {
        $context = $this->getContext();
        $injectInto = service($context, DummyApps\SimpleUseService\ConstructorInjection::class);
        injectService($context, $injectInto, '__construct', 'bar', DummyApps\SimpleUseService\FooInterface::class, scalarValue(DummyApps\SimpleUseService\BarImplementation::class));

        $containerDefinition = $context->getBuilder()->build();

        $this->assertCount(1, $containerDefinition->getInjectServiceDefinitions());
        $this->assertSame(DummyApps\SimpleUseService\ConstructorInjection::class, $containerDefinition->getInjectServiceDefinitions()[0]->getService()->getType());
        $this->assertSame('__construct', $containerDefinition->getInjectServiceDefinitions()[0]->getMethod());
        $this->assertSame('bar', $containerDefinition->getInjectServiceDefinitions()[0]->getParamName());
        $this->assertSame(DummyApps\SimpleUseService\FooInterface::class, $containerDefinition->getInjectServiceDefinitions()[0]->getParamType());
        $this->assertSame(DummyApps\SimpleUseService\BarImplementation::class, $containerDefinition->getInjectServiceDefinitions()[0]->getInjectedService()->getCompileValue());
    }

}