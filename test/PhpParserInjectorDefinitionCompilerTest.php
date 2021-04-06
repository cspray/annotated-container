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
use Cspray\AnnotatedInjector\DummyApps\SimpleDefineService;
use Cspray\AnnotatedInjector\DummyApps\MultipleAliasResolution;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedInjector\PhpParserInjectorDefinitionCompiler
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
class PhpParserInjectorDefinitionCompilerTest extends TestCase {

    private PhpParserInjectorDefinitionCompiler $subject;

    public function setUp() : void {
        $this->subject = new PhpParserInjectorDefinitionCompiler();
    }

    private function runCompileDirectory(string $dir, string $environment = 'test') : InjectorDefinition {
        return $this->subject->compileDirectory($environment, $dir);
    }

    public function testSimpleServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleServices');

        $this->assertServiceDefinitionsHaveTypes([SimpleServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [SimpleServices\FooInterface::class, SimpleServices\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testMultipleSimpleServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/MultipleSimpleServices');

        $this->assertServiceDefinitionsHaveTypes([
            MultipleSimpleServices\BarInterface::class,
            MultipleSimpleServices\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [MultipleSimpleServices\FooInterface::class, MultipleSimpleServices\FooImplementation::class],
            [MultipleSimpleServices\BarInterface::class, MultipleSimpleServices\BarImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testSimpleServicesSomeNotAnnotated() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleServicesSomeNotAnnotated');

        $this->assertServiceDefinitionsHaveTypes([SimpleServicesSomeNotAnnotated\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [SimpleServicesSomeNotAnnotated\FooInterface::class, SimpleServicesSomeNotAnnotated\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testEnvironmentResolvedServicesTest() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [EnvironmentResolvedServices\FooInterface::class, EnvironmentResolvedServices\TestFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testEnvironmentResolvedServicesDev() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'dev');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [EnvironmentResolvedServices\FooInterface::class, EnvironmentResolvedServices\DevFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testEnvironmentResolvedServicesProd() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'prod');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [EnvironmentResolvedServices\FooInterface::class, EnvironmentResolvedServices\ProdFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testClassOnlyServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ClassOnlyServices');

        $this->assertServiceDefinitionsHaveTypes([
            ClassOnlyServices\BarImplementation::class,
            ClassOnlyServices\BazImplementation::class,
            ClassOnlyServices\FooImplementation::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testInterfaceServicePrepare() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/InterfaceServicePrepare');

        $this->assertServiceDefinitionsHaveTypes([
            InterfaceServicePrepare\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [InterfaceServicePrepare\FooInterface::class, InterfaceServicePrepare\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [InterfaceServicePrepare\FooInterface::class, 'setBar']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testClassOverridesInterfaceServicePrepare() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ClassOverridesInterfaceServicePrepare');

        $this->assertServiceDefinitionsHaveTypes([
            ClassOverridesInterfaceServicePrepare\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [ClassOverridesInterfaceServicePrepare\FooInterface::class, ClassOverridesInterfaceServicePrepare\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [ClassOverridesInterfaceServicePrepare\FooInterface::class, 'setBar']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testClassServicePrepareWithoutInterfaceServicePrepare() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ClassServicePrepareWithoutInterfaceServicePrepare');

        $this->assertServiceDefinitionsHaveTypes([
            ClassServicePrepareWithoutInterfaceServicePrepare\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [ClassServicePrepareWithoutInterfaceServicePrepare\FooInterface::class, ClassServicePrepareWithoutInterfaceServicePrepare\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [ClassServicePrepareWithoutInterfaceServicePrepare\FooImplementation::class, 'setBar']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testNestedServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/NestedServices');

        $this->assertServiceDefinitionsHaveTypes([
            NestedServices\BazInterface::class,
            NestedServices\BarInterface::class,
            NestedServices\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [NestedServices\FooInterface::class, NestedServices\Foo\FooImplementation::class],
            [NestedServices\BarInterface::class, NestedServices\Foo\Bar\BarImplementation::class],
            [NestedServices\BazInterface::class, NestedServices\Foo\Bar\Baz\BazImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testAbstractSharedServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/AbstractSharedServices');

        $this->assertServiceDefinitionsHaveTypes([AbstractSharedServices\AbstractFoo::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [AbstractSharedServices\AbstractFoo::class, AbstractSharedServices\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testSimpleDefineScalar() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleDefineScalar');

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
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testNegativeNumberDefineScalar() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/NegativeNumberDefineScalar');

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
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testMultipleDefineScalars() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/MultipleDefineScalars');

        $this->assertServiceDefinitionsHaveTypes([MultipleDefineScalars\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [MultipleDefineScalars\FooImplementation::class, 'setUp']
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
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testClassConstantDefineScalar() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ClassConstantDefineScalar');

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
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testConstantDefineScalar() {
        require_once __DIR__ . '/DummyApps/ConstantDefineScalar/FooImplementation.php';
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ConstantDefineScalar');

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
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testDefineScalarFromEnv() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleDefineScalarFromEnv');

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
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
    }

    public function testSimpleDefineService() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleDefineService');

        $this->assertServiceDefinitionsHaveTypes([
            SimpleDefineService\FooInterface::class,
            SimpleDefineService\SetterInjection::class,
            SimpleDefineService\ConstructorInjection::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [SimpleDefineService\FooInterface::class, SimpleDefineService\BarImplementation::class],
            [SimpleDefineService\FooInterface::class, SimpleDefineService\BazImplementation::class],
            [SimpleDefineService\FooInterface::class, SimpleDefineService\QuxImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [SimpleDefineService\SetterInjection::class, 'setBar'],
            [SimpleDefineService\SetterInjection::class, 'setBaz'],
            [SimpleDefineService\SetterInjection::class, 'setQux']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertDefineServiceParamValues([
            SimpleDefineService\SetterInjection::class . '::setBar(foo)' => SimpleDefineService\BarImplementation::class,
            SimpleDefineService\SetterInjection::class . '::setBaz(foo)' => SimpleDefineService\BazImplementation::class,
            SimpleDefineService\SetterInjection::class . '::setQux(foo)' => SimpleDefineService\QuxImplementation::class,
            SimpleDefineService\ConstructorInjection::class . '::__construct(bar)' => SimpleDefineService\BarImplementation::class,
            SimpleDefineService\ConstructorInjection::class . '::__construct(baz)' => SimpleDefineService\BazImplementation::class,
            SimpleDefineService\ConstructorInjection::class . '::__construct(qux)' => SimpleDefineService\QuxImplementation::class
        ], $injectorDefinition->getDefineServiceDefinitions());
    }

    public function testMultipleAliasResolution() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/MultipleAliasResolution');

        $this->assertServiceDefinitionsHaveTypes([
            MultipleAliasResolution\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [MultipleAliasResolution\FooInterface::class, MultipleAliasResolution\BarImplementation::class],
            [MultipleAliasResolution\FooInterface::class, MultipleAliasResolution\BazImplementation::class],
            [MultipleAliasResolution\FooInterface::class, MultipleAliasResolution\QuxImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getDefineServiceDefinitions());
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
            $actualMap[] = [
                $aliasDefinition->getOriginalServiceDefinition()->getType(),
                $aliasDefinition->getAliasServiceDefinition()->getType()
            ];
        }

        array_multisort($actualMap);
        array_multisort($expectedAliasMap);
        $this->assertEquals($expectedAliasMap, $actualMap);
    }

    protected function assertServicePrepareTypes(array $expectedServicePrepare, array $servicePrepareDefinitions) : void {
        if (($countExpected = count($expectedServicePrepare)) !== ($countActual = count($servicePrepareDefinitions))) {
            $this->fail("Expected ${countExpected} ServicePrepareDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            $this->assertInstanceOf(ServicePrepareDefinition::class, $servicePrepareDefinition);
            $key = $servicePrepareDefinition->getType();
            $actualMap[] = [$key, $servicePrepareDefinition->getMethod()];
        }

        array_multisort($actualMap);
        array_multisort($expectedServicePrepare);
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

    protected function assertDefineServiceParamValues(array $expectedValueMap, array $defineServiceDefinitions) : void {
        if (($countExpected = count($expectedValueMap)) !== ($countActual = count($defineServiceDefinitions))) {
            $this->fail("Expected ${countExpected} DefineScalarDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($defineServiceDefinitions as $defineServiceDefinition) {
            $this->assertInstanceOf(DefineServiceDefinition::class, $defineServiceDefinition);
            $key = sprintf(
                "%s::%s(%s)",
                $defineServiceDefinition->getType(),
                $defineServiceDefinition->getMethod(),
                $defineServiceDefinition->getParamName()
            );
            $actualMap[$key] = $defineServiceDefinition->getValue();
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