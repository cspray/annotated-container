<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Cspray\AnnotatedInjector\DummyApps\AbstractSharedServices;
use Cspray\AnnotatedInjector\DummyApps\ClassOnlyServices;
use Cspray\AnnotatedInjector\DummyApps\ClassOverridesInterfaceServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\ClassServicePrepareWithoutInterfaceServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\EnvironmentResolvedServices;
use Cspray\AnnotatedInjector\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\SimpleServices;
use Cspray\AnnotatedInjector\DummyApps\MultipleSimpleServices;
use Cspray\AnnotatedInjector\DummyApps\SimpleServicesSomeNotAnnotated;
use Cspray\AnnotatedInjector\DummyApps\NestedServices;
use Cspray\AnnotatedInjector\DummyApps\SimpleDefineScalar;
use Cspray\AnnotatedInjector\DummyApps\NegativeNumberDefineScalar;
use Cspray\AnnotatedInjector\DummyApps\MultipleDefineScalars;
use Cspray\AnnotatedInjector\DummyApps\ClassConstantDefineScalar;
use Cspray\AnnotatedInjector\DummyApps\ConstantDefineScalar;
use Cspray\AnnotatedInjector\DummyApps\SimpleDefineScalarFromEnv;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedInjector\InjectorDefinitionCompiler
 * @covers \Cspray\AnnotatedInjector\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Visitor\ServicePrepareDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Visitor\DefineScalarDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServicePrepareDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Interrogator\DefineScalarDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\ServiceDefinition
 * @covers \Cspray\AnnotatedInjector\AliasDefinition
 * @covers \Cspray\AnnotatedInjector\ServicePrepareDefinition
 * @covers \Cspray\AnnotatedInjector\DefineScalarDefinition
 * @covers \Cspray\AnnotatedInjector\Visitor\AbstractNodeVisitor
 */
class InjectorDefinitionCompilerTest extends TestCase {

    private InjectorDefinitionCompiler $subject;

    public function setUp() : void {
        $this->subject = new InjectorDefinitionCompiler();
    }

    public function testSimpleServices() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/SimpleServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([SimpleServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([SimpleServices\FooInterface::class => SimpleServices\FooImplementation::class], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testMultipleSimpleServices() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/MultipleSimpleServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            MultipleSimpleServices\BarInterface::class,
            MultipleSimpleServices\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            MultipleSimpleServices\FooInterface::class => MultipleSimpleServices\FooImplementation::class,
            MultipleSimpleServices\BarInterface::class => MultipleSimpleServices\BarImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testSimpleServicesSomeNotAnnotated() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/SimpleServicesSomeNotAnnotated', 'test');

        $this->assertServiceDefinitionsHaveTypes([SimpleServicesSomeNotAnnotated\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            SimpleServicesSomeNotAnnotated\FooInterface::class => SimpleServicesSomeNotAnnotated\FooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testEnvironmentResolvedServicesTest() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            EnvironmentResolvedServices\FooInterface::class => EnvironmentResolvedServices\TestFooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testEnvironmentResolvedServicesDev() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'dev');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            EnvironmentResolvedServices\FooInterface::class => EnvironmentResolvedServices\DevFooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testEnvironmentResolvedServicesProd() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'prod');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            EnvironmentResolvedServices\FooInterface::class => EnvironmentResolvedServices\ProdFooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testClassOnlyServices() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/ClassOnlyServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            ClassOnlyServices\BarImplementation::class,
            ClassOnlyServices\BazImplementation::class,
            ClassOnlyServices\FooImplementation::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testInterfaceServicePrepare() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/InterfaceServicePrepare', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            InterfaceServicePrepare\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            InterfaceServicePrepare\FooInterface::class => InterfaceServicePrepare\FooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            InterfaceServicePrepare\FooInterface::class => 'setBar'
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testClassOverridesInterfaceServicePrepare() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/ClassOverridesInterfaceServicePrepare', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            ClassOverridesInterfaceServicePrepare\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            ClassOverridesInterfaceServicePrepare\FooInterface::class => ClassOverridesInterfaceServicePrepare\FooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            ClassOverridesInterfaceServicePrepare\FooInterface::class => 'setBar'
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testClassServicePrepareWithoutInterfaceServicePrepare() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/ClassServicePrepareWithoutInterfaceServicePrepare', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            ClassServicePrepareWithoutInterfaceServicePrepare\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            ClassServicePrepareWithoutInterfaceServicePrepare\FooInterface::class => ClassServicePrepareWithoutInterfaceServicePrepare\FooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
           ClassServicePrepareWithoutInterfaceServicePrepare\FooImplementation::class => 'setBar'
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testNestedServices() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/NestedServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            NestedServices\BazInterface::class,
            NestedServices\BarInterface::class,
            NestedServices\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            NestedServices\FooInterface::class => NestedServices\Foo\FooImplementation::class,
            NestedServices\BarInterface::class => NestedServices\Foo\Bar\BarImplementation::class,
            NestedServices\BazInterface::class => NestedServices\Foo\Bar\Baz\BazImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testAbstractSharedServices() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/AbstractSharedServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([AbstractSharedServices\AbstractFoo::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([AbstractSharedServices\AbstractFoo::class => AbstractSharedServices\FooImplementation::class], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testSimpleDefineScalar() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/SimpleDefineScalar', 'test');

        $this->assertServiceDefinitionsHaveTypes([SimpleDefineScalar\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertDefineScalarParamValues([
            SimpleDefineScalar\FooImplementation::class . '::__construct(stringParam)' => 'string param test value',
            SimpleDefineScalar\FooImplementation::class . '::__construct(intParam)' => 42,
            SimpleDefineScalar\FooImplementation::class . '::__construct(floatParam)' => 42.0,
            SimpleDefineScalar\FooImplementation::class . '::__construct(boolParam)' => true,
            SimpleDefineScalar\FooImplementation::class . '::__construct(arrayParam)' => [
                ['a', 'b', 'c'],
                [1, 2, 3],
                [1.0, 2.0, 3.0],
                [true, false, true],
                [['a', 'b', 'c'], [1, 2, 3], [1.0, 2.0, 3.0], [true, false, true]]
            ]
        ], $injectorDefinition->getDefineScalarDefinitions());

        $this->assertDefineScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 5 times
            SimpleDefineScalar\FooImplementation::class . '::__construct',
            SimpleDefineScalar\FooImplementation::class . '::__construct',
            SimpleDefineScalar\FooImplementation::class . '::__construct',
            SimpleDefineScalar\FooImplementation::class . '::__construct',
            SimpleDefineScalar\FooImplementation::class . '::__construct'
        ], $injectorDefinition->getDefineScalarDefinitions());

        $this->assertDefineScalarAllPlainValue($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testNegativeNumberDefineScalar() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/NegativeNumberDefineScalar', 'test');

        $this->assertServiceDefinitionsHaveTypes([NegativeNumberDefineScalar\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertDefineScalarParamValues([
            NegativeNumberDefineScalar\FooImplementation::class . '::__construct(intParam)' => -1,
            NegativeNumberDefineScalar\FooImplementation::class . '::__construct(floatParam)' => -42.0,
        ], $injectorDefinition->getDefineScalarDefinitions());

        $this->assertDefineScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            NegativeNumberDefineScalar\FooImplementation::class . '::__construct',
            NegativeNumberDefineScalar\FooImplementation::class . '::__construct'
        ], $injectorDefinition->getDefineScalarDefinitions());
        $this->assertDefineScalarAllPlainValue($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testMultipleDefineScalars() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/MultipleDefineScalars', 'test');

        $this->assertServiceDefinitionsHaveTypes([MultipleDefineScalars\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            MultipleDefineScalars\FooImplementation::class => 'setUp'
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertDefineScalarParamValues([
            MultipleDefineScalars\FooImplementation::class . '::__construct(stringParam)' => 'constructor param',
            MultipleDefineScalars\FooImplementation::class . '::setUp(stringParam)' => 'prepare param',
        ], $injectorDefinition->getDefineScalarDefinitions());

        $this->assertDefineScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            MultipleDefineScalars\FooImplementation::class . '::__construct',
            MultipleDefineScalars\FooImplementation::class . '::setUp'
        ], $injectorDefinition->getDefineScalarDefinitions());
        $this->assertDefineScalarAllPlainValue($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testClassConstantDefineScalar() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/ClassConstantDefineScalar', 'test');

        $this->assertServiceDefinitionsHaveTypes([ClassConstantDefineScalar\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertDefineScalarParamValues([
            ClassConstantDefineScalar\FooImplementation::class . '::__construct(val)' => '!const(Cspray\AnnotatedInjector\DummyApps\ClassConstantDefineScalar\FooImplementation::VALUE)',
        ], $injectorDefinition->getDefineScalarDefinitions());

        $this->assertDefineScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            ClassConstantDefineScalar\FooImplementation::class . '::__construct',
        ], $injectorDefinition->getDefineScalarDefinitions());
        $this->assertDefineScalarAllPlainValue($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testConstantDefineScalar() {
        require_once __DIR__ . '/DummyApps/ConstantDefineScalar/FooImplementation.php';
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/ConstantDefineScalar', 'test');

        $this->assertServiceDefinitionsHaveTypes([ConstantDefineScalar\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertDefineScalarParamValues([
            ConstantDefineScalar\FooImplementation::class . '::__construct(val)' => '!const(Cspray\AnnotatedInjector\DummyApps\ConstantDefineScalar\FOO_BAR)',
        ], $injectorDefinition->getDefineScalarDefinitions());

        $this->assertDefineScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            ConstantDefineScalar\FooImplementation::class . '::__construct',
        ], $injectorDefinition->getDefineScalarDefinitions());
        $this->assertDefineScalarAllPlainValue($injectorDefinition->getDefineScalarDefinitions());
    }

    public function testDefineScalarFromEnv() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/SimpleDefineScalarFromEnv', 'test');

        $this->assertServiceDefinitionsHaveTypes([SimpleDefineScalarFromEnv\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertDefineScalarParamValues([
            SimpleDefineScalarFromEnv\FooImplementation::class . '::__construct(user)' => '!env(USER)',
        ], $injectorDefinition->getDefineScalarDefinitions());

        $this->assertDefineScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            SimpleDefineScalarFromEnv\FooImplementation::class . '::__construct',
        ], $injectorDefinition->getDefineScalarDefinitions());
        $this->assertDefineScalarAllEnvironment($injectorDefinition->getDefineScalarDefinitions());
    }

    protected function assertServiceDefinitionsHaveTypes(array $expectedTypes, array $serviceDefinitions) : void {
        if (($countExpected = count($expectedTypes)) !== ($countActual = count($serviceDefinitions))) {
            $this->fail("Expected ${countExpected} ServiceDefinitions but received ${countActual}");
        }

        $actualTypes = [];
        foreach ($serviceDefinitions as $serviceDefinition) {
            $this->assertInstanceOf(ServiceDefinition::class, $serviceDefinition);
            $actualTypes[] = $serviceDefinition->getType();
        }

        $this->assertEqualsCanonicalizing($expectedTypes, $actualTypes);
    }

    protected function assertAliasDefinitionsMap(array $expectedAliasMap, array $aliasDefinitions) : void {
        if (($countExpected = count($expectedAliasMap)) !== ($countActual = count($aliasDefinitions))) {
            $this->fail("Expected ${countExpected} AliasDefinitions but received ${countActual}");
        }

        $actualMap = [];
        foreach ($aliasDefinitions as $aliasDefinition) {
            $this->assertInstanceOf(AliasDefinition::class, $aliasDefinition);
            $actualMap[$aliasDefinition->getOriginalServiceDefinition()->getType()] = $aliasDefinition->getAliasServiceDefinition()->getType();
        }

        ksort($actualMap);
        ksort($expectedAliasMap);
        $this->assertEquals($expectedAliasMap, $actualMap);
    }

    protected function assertServicePrepareTypes(array $expectedServicePrepare, array $servicePrepareDefinitions) : void {
        if (($countExpected = count($expectedServicePrepare)) !== ($countActual = count($servicePrepareDefinitions))) {
            $this->fail("Expected ${countExpected} ServicePrepareDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            $this->assertInstanceOf(ServicePrepareDefinition::class, $servicePrepareDefinition);
            $actualMap[$servicePrepareDefinition->getType()] = $servicePrepareDefinition->getMethod();
        }

        ksort($actualMap);
        ksort($expectedServicePrepare);
        $this->assertEquals($expectedServicePrepare, $actualMap);
    }

    protected function assertDefineScalarParamValues(array $expectedValueMap, array $defineScalarDefinitions) : void {
        if (($countExpected = count($expectedValueMap)) !== ($countActual = count($defineScalarDefinitions))) {
            $this->fail("Expected ${countExpected} DefineScalarDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($defineScalarDefinitions as $defineScalarDefinition) {
            $this->assertInstanceOf(DefineScalarDefinition::class, $defineScalarDefinition);
            $key = sprintf(
                "%s::%s(%s)",
                $defineScalarDefinition->getType(),
                $defineScalarDefinition->getMethod(),
                $defineScalarDefinition->getParamName()
            );
            $actualMap[$key] = $defineScalarDefinition->getValue();
        }

        ksort($actualMap);
        ksort($expectedValueMap);
        $this->assertEquals($expectedValueMap, $actualMap);
    }

    protected function assertDefineScalarMethod(array $expectedMethods, array $defineScalarDefinitions) : void {
        if (($countExpected = count($expectedMethods)) !== ($countActual = count($defineScalarDefinitions))) {
            $this->fail("Expected ${countExpected} DefineScalarDefinition but received ${countActual}");
        }

        $actualMethods = [];
        foreach ($defineScalarDefinitions as $defineScalarDefinition) {
            $actualMethods[] = $defineScalarDefinition->getType() . "::" . $defineScalarDefinition->getMethod();
        }

        $this->assertEqualsCanonicalizing($expectedMethods, $actualMethods);
    }

    protected function assertDefineScalarAllPlainValue(array $defineScalarDefinitions) : void {
        $hasNotPlainValue = false;
        foreach ($defineScalarDefinitions as $defineScalarDefinition) {
            if (!$defineScalarDefinition->isPlainValue()) {
                $hasNotPlainValue = true;
                break;
            }
        }

        $this->assertFalse($hasNotPlainValue, 'Expected all DefineScalarDefinitions to be plain values');
    }

    protected function assertDefineScalarAllEnvironment(array $defineScalarDefinitions) : void {
        $hasNotEnv = false;
        foreach ($defineScalarDefinitions as $defineScalarDefinition) {
            if (!$defineScalarDefinition->isEnvironmentVar()) {
                $hasNotEnv = true;
                break;
            }
        }

        $this->assertFalse($hasNotEnv, 'Expected all DefineScalarDefinitions to be environment variables');
    }
}