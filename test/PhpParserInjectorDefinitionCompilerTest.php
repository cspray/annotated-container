<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Cspray\AnnotatedInjector\DummyApps\AbstractSharedServices;
use Cspray\AnnotatedInjector\DummyApps\ClassOnlyServices;
use Cspray\AnnotatedInjector\DummyApps\ClassOverridesInterfaceServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\ClassServicePrepareWithoutInterfaceServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\EnvironmentResolvedServices;
use Cspray\AnnotatedInjector\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\ServiceDelegate\ServiceFactory;
use Cspray\AnnotatedInjector\DummyApps\ServiceDelegate\ServiceInterface;
use Cspray\AnnotatedInjector\DummyApps\SimpleServices;
use Cspray\AnnotatedInjector\DummyApps\MultipleSimpleServices;
use Cspray\AnnotatedInjector\DummyApps\SimpleServicesSomeNotAnnotated;
use Cspray\AnnotatedInjector\DummyApps\NestedServices;
use Cspray\AnnotatedInjector\DummyApps\SimpleUseScalar;
use Cspray\AnnotatedInjector\DummyApps\NegativeNumberUseScalar;
use Cspray\AnnotatedInjector\DummyApps\MultipleUseScalars;
use Cspray\AnnotatedInjector\DummyApps\ClassConstantUseScalar;
use Cspray\AnnotatedInjector\DummyApps\ConstantUseScalar;
use Cspray\AnnotatedInjector\DummyApps\SimpleUseScalarFromEnv;
use Cspray\AnnotatedInjector\DummyApps\SimpleUseService;
use Cspray\AnnotatedInjector\DummyApps\MultipleAliasResolution;
use Cspray\AnnotatedInjector\DummyApps\NonPhpFiles;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedInjector\PhpParserInjectorDefinitionCompiler
 * @covers \Cspray\AnnotatedInjector\Internal\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Internal\Visitor\ServicePrepareDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Internal\Visitor\UseScalarDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Internal\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Internal\Interrogator\ServicePrepareDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Internal\Interrogator\UseScalarDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\ServiceDefinition
 * @covers \Cspray\AnnotatedInjector\AliasDefinition
 * @covers \Cspray\AnnotatedInjector\ServicePrepareDefinition
 * @covers \Cspray\AnnotatedInjector\UseScalarDefinition
 * @covers \Cspray\AnnotatedInjector\Internal\Visitor\AbstractNodeVisitor
 */
class PhpParserInjectorDefinitionCompilerTest extends TestCase {

    private PhpParserInjectorDefinitionCompiler $subject;

    public function setUp() : void {
        $this->subject = new PhpParserInjectorDefinitionCompiler();
    }

    private function runCompileDirectory(string|array $dir, string $environment = 'test') : InjectorDefinition {
        return $this->subject->compileDirectory($environment, $dir);
    }

    public function testSimpleServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleServices');

        $this->assertServiceDefinitionsHaveTypes([SimpleServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [SimpleServices\FooInterface::class, SimpleServices\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
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
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testSimpleServicesSomeNotAnnotated() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleServicesSomeNotAnnotated');

        $this->assertServiceDefinitionsHaveTypes([SimpleServicesSomeNotAnnotated\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [SimpleServicesSomeNotAnnotated\FooInterface::class, SimpleServicesSomeNotAnnotated\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testEnvironmentResolvedServicesTest() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [EnvironmentResolvedServices\FooInterface::class, EnvironmentResolvedServices\TestFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testEnvironmentResolvedServicesDev() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'dev');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [EnvironmentResolvedServices\FooInterface::class, EnvironmentResolvedServices\DevFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testEnvironmentResolvedServicesProd() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'prod');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [EnvironmentResolvedServices\FooInterface::class, EnvironmentResolvedServices\ProdFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
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
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
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
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
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
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
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
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
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
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testAbstractSharedServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/AbstractSharedServices');

        $this->assertServiceDefinitionsHaveTypes([AbstractSharedServices\AbstractFoo::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [AbstractSharedServices\AbstractFoo::class, AbstractSharedServices\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testSimpleUseScalar() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleUseScalar');

        $this->assertServiceDefinitionsHaveTypes([SimpleUseScalar\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            SimpleUseScalar\FooImplementation::class . '::__construct(stringParam)' => 'string param test value',
            SimpleUseScalar\FooImplementation::class . '::__construct(intParam)' => 42,
            SimpleUseScalar\FooImplementation::class . '::__construct(floatParam)' => 42.0,
            SimpleUseScalar\FooImplementation::class . '::__construct(boolParam)' => true,
            SimpleUseScalar\FooImplementation::class . '::__construct(arrayParam)' => [
                ['a', 'b', 'c'],
                [1, 2, 3],
                [1.0, 2.0, 3.0],
                [true, false, true],
                [['a', 'b', 'c'], [1, 2, 3], [1.0, 2.0, 3.0], [true, false, true]]
            ]
        ], $injectorDefinition->getUseScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 5 times
            SimpleUseScalar\FooImplementation::class . '::__construct',
            SimpleUseScalar\FooImplementation::class . '::__construct',
            SimpleUseScalar\FooImplementation::class . '::__construct',
            SimpleUseScalar\FooImplementation::class . '::__construct',
            SimpleUseScalar\FooImplementation::class . '::__construct'
        ], $injectorDefinition->getUseScalarDefinitions());

        $this->assertUseScalarAllPlainValue($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testNegativeNumberUseScalar() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/NegativeNumberUseScalar');

        $this->assertServiceDefinitionsHaveTypes([NegativeNumberUseScalar\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            NegativeNumberUseScalar\FooImplementation::class . '::__construct(intParam)' => -1,
            NegativeNumberUseScalar\FooImplementation::class . '::__construct(floatParam)' => -42.0,
        ], $injectorDefinition->getUseScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            NegativeNumberUseScalar\FooImplementation::class . '::__construct',
            NegativeNumberUseScalar\FooImplementation::class . '::__construct'
        ], $injectorDefinition->getUseScalarDefinitions());
        $this->assertUseScalarAllPlainValue($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testMultipleUseScalars() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/MultipleUseScalars');

        $this->assertServiceDefinitionsHaveTypes([MultipleUseScalars\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [MultipleUseScalars\FooImplementation::class, 'setUp']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            MultipleUseScalars\FooImplementation::class . '::__construct(stringParam)' => 'constructor param',
            MultipleUseScalars\FooImplementation::class . '::setUp(stringParam)' => 'prepare param',
        ], $injectorDefinition->getUseScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            MultipleUseScalars\FooImplementation::class . '::__construct',
            MultipleUseScalars\FooImplementation::class . '::setUp'
        ], $injectorDefinition->getUseScalarDefinitions());
        $this->assertUseScalarAllPlainValue($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testClassConstantUseScalar() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ClassConstantUseScalar');

        $this->assertServiceDefinitionsHaveTypes([ClassConstantUseScalar\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            ClassConstantUseScalar\FooImplementation::class . '::__construct(val)' => '!const(Cspray\AnnotatedInjector\DummyApps\ClassConstantUseScalar\FooImplementation::VALUE)',
        ], $injectorDefinition->getUseScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            ClassConstantUseScalar\FooImplementation::class . '::__construct',
        ], $injectorDefinition->getUseScalarDefinitions());
        $this->assertUseScalarAllPlainValue($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testConstantUseScalar() {
        require_once __DIR__ . '/DummyApps/ConstantUseScalar/FooImplementation.php';
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ConstantUseScalar');

        $this->assertServiceDefinitionsHaveTypes([ConstantUseScalar\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            ConstantUseScalar\FooImplementation::class . '::__construct(val)' => '!const(Cspray\AnnotatedInjector\DummyApps\ConstantUseScalar\FOO_BAR)',
        ], $injectorDefinition->getUseScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            ConstantUseScalar\FooImplementation::class . '::__construct',
        ], $injectorDefinition->getUseScalarDefinitions());
        $this->assertUseScalarAllPlainValue($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testUseScalarFromEnv() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleUseScalarFromEnv');

        $this->assertServiceDefinitionsHaveTypes([SimpleUseScalarFromEnv\FooImplementation::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            SimpleUseScalarFromEnv\FooImplementation::class . '::__construct(user)' => '!env(USER)',
        ], $injectorDefinition->getUseScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            SimpleUseScalarFromEnv\FooImplementation::class . '::__construct',
        ], $injectorDefinition->getUseScalarDefinitions());
        $this->assertUseScalarAllEnvironment($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testSimpleUseService() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleUseService');

        $this->assertServiceDefinitionsHaveTypes([
            SimpleUseService\FooInterface::class,
            SimpleUseService\SetterInjection::class,
            SimpleUseService\ConstructorInjection::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [SimpleUseService\FooInterface::class, SimpleUseService\BarImplementation::class],
            [SimpleUseService\FooInterface::class, SimpleUseService\BazImplementation::class],
            [SimpleUseService\FooInterface::class, SimpleUseService\QuxImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [SimpleUseService\SetterInjection::class, 'setBar'],
            [SimpleUseService\SetterInjection::class, 'setBaz'],
            [SimpleUseService\SetterInjection::class, 'setQux']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertUseServiceParamValues([
            SimpleUseService\SetterInjection::class . '::setBar(foo)' => SimpleUseService\BarImplementation::class,
            SimpleUseService\SetterInjection::class . '::setBaz(foo)' => SimpleUseService\BazImplementation::class,
            SimpleUseService\SetterInjection::class . '::setQux(foo)' => SimpleUseService\QuxImplementation::class,
            SimpleUseService\ConstructorInjection::class . '::__construct(bar)' => SimpleUseService\BarImplementation::class,
            SimpleUseService\ConstructorInjection::class . '::__construct(baz)' => SimpleUseService\BazImplementation::class,
            SimpleUseService\ConstructorInjection::class . '::__construct(qux)' => SimpleUseService\QuxImplementation::class
        ], $injectorDefinition->getUseServiceDefinitions());
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
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testNonPhpFiles() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/NonPhpFiles');

        $this->assertServiceDefinitionsHaveTypes([NonPhpFiles\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testMultipleDirs() {
        $injectorDefinition = $this->runCompileDirectory([
            __DIR__ . '/DummyApps/MultipleSimpleServices',
            __DIR__ . '/DummyApps/SimpleServices'
        ]);

        $this->assertServiceDefinitionsHaveTypes([
            MultipleSimpleServices\BarInterface::class,
            MultipleSimpleServices\FooInterface::class,
            SimpleServices\FooInterface::class
        ], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [MultipleSimpleServices\FooInterface::class, MultipleSimpleServices\FooImplementation::class],
            [MultipleSimpleServices\BarInterface::class, MultipleSimpleServices\BarImplementation::class],
            [SimpleServices\FooInterface::class, SimpleServices\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getUseScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getUseServiceDefinitions());
    }

    public function testServiceDelegate() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ServiceDelegate');
        $serviceDelegateDefinitions = $injectorDefinition->getServiceDelegateDefinitions();

        $this->assertCount(1, $serviceDelegateDefinitions);
        $serviceDelegateDefinition = $serviceDelegateDefinitions[0];

        $this->assertSame(ServiceFactory::class, $serviceDelegateDefinition->getDelegateType());
        $this->assertSame('createService', $serviceDelegateDefinition->getDelegateMethod());
        $this->assertSame(ServiceInterface::class, $serviceDelegateDefinition->getServiceType());
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

    protected function assertUseScalarParamValues(array $expectedValueMap, array $UseScalarDefinitions) : void {
        if (($countExpected = count($expectedValueMap)) !== ($countActual = count($UseScalarDefinitions))) {
            $this->fail("Expected ${countExpected} UseScalarDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($UseScalarDefinitions as $UseScalarDefinition) {
            $this->assertInstanceOf(UseScalarDefinition::class, $UseScalarDefinition);
            $key = sprintf(
                "%s::%s(%s)",
                $UseScalarDefinition->getType(),
                $UseScalarDefinition->getMethod(),
                $UseScalarDefinition->getParamName()
            );
            $actualMap[$key] = $UseScalarDefinition->getValue();
        }

        ksort($actualMap);
        ksort($expectedValueMap);
        $this->assertEquals($expectedValueMap, $actualMap);
    }

    protected function assertUseServiceParamValues(array $expectedValueMap, array $UseServiceDefinitions) : void {
        if (($countExpected = count($expectedValueMap)) !== ($countActual = count($UseServiceDefinitions))) {
            $this->fail("Expected ${countExpected} UseScalarDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($UseServiceDefinitions as $UseServiceDefinition) {
            $this->assertInstanceOf(UseServiceDefinition::class, $UseServiceDefinition);
            $key = sprintf(
                "%s::%s(%s)",
                $UseServiceDefinition->getType(),
                $UseServiceDefinition->getMethod(),
                $UseServiceDefinition->getParamName()
            );
            $actualMap[$key] = $UseServiceDefinition->getValue();
        }

        ksort($actualMap);
        ksort($expectedValueMap);
        $this->assertEquals($expectedValueMap, $actualMap);
    }

    protected function assertUseScalarMethod(array $expectedMethods, array $UseScalarDefinitions) : void {
        if (($countExpected = count($expectedMethods)) !== ($countActual = count($UseScalarDefinitions))) {
            $this->fail("Expected ${countExpected} UseScalarDefinition but received ${countActual}");
        }

        $actualMethods = [];
        foreach ($UseScalarDefinitions as $UseScalarDefinition) {
            $actualMethods[] = $UseScalarDefinition->getType() . "::" . $UseScalarDefinition->getMethod();
        }

        $this->assertEqualsCanonicalizing($expectedMethods, $actualMethods);
    }

    protected function assertUseScalarAllPlainValue(array $UseScalarDefinitions) : void {
        $hasNotPlainValue = false;
        foreach ($UseScalarDefinitions as $UseScalarDefinition) {
            if (!$UseScalarDefinition->isPlainValue()) {
                $hasNotPlainValue = true;
                break;
            }
        }

        $this->assertFalse($hasNotPlainValue, 'Expected all UseScalarDefinitions to be plain values');
    }

    protected function assertUseScalarAllEnvironment(array $UseScalarDefinitions) : void {
        $hasNotEnv = false;
        foreach ($UseScalarDefinitions as $UseScalarDefinition) {
            if (!$UseScalarDefinition->isEnvironmentVar()) {
                $hasNotEnv = true;
                break;
            }
        }

        $this->assertFalse($hasNotEnv, 'Expected all UseScalarDefinitions to be environment variables');
    }
}