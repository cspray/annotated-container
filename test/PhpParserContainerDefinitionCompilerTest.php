<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\AbstractSharedServices;
use Cspray\AnnotatedContainer\DummyApps\ClassOnlyServices;
use Cspray\AnnotatedContainer\DummyApps\ClassOverridesInterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\ClassServicePrepareWithoutInterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\ProfileResolvedServices;
use Cspray\AnnotatedContainer\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate\ServiceFactory;
use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate\ServiceInterface;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\MultipleSimpleServices;
use Cspray\AnnotatedContainer\DummyApps\SimpleServicesSomeNotAnnotated;
use Cspray\AnnotatedContainer\DummyApps\NestedServices;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseScalar;
use Cspray\AnnotatedContainer\DummyApps\NegativeNumberUseScalar;
use Cspray\AnnotatedContainer\DummyApps\MultipleUseScalars;
use Cspray\AnnotatedContainer\DummyApps\ClassConstantUseScalar;
use Cspray\AnnotatedContainer\DummyApps\ConstantUseScalar;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseScalarFromEnv;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseService;
use Cspray\AnnotatedContainer\DummyApps\MultipleAliasResolution;
use Cspray\AnnotatedContainer\DummyApps\NonPhpFiles;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler
 * @covers \Cspray\AnnotatedContainer\Internal\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedContainer\Internal\Visitor\ServicePrepareDefinitionVisitor
 * @covers \Cspray\AnnotatedContainer\Internal\Visitor\InjectScalarDefinitionVisitor
 * @covers \Cspray\AnnotatedContainer\Internal\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedContainer\Internal\Interrogator\ServicePrepareDefinitionInterrogator
 * @covers \Cspray\AnnotatedContainer\Internal\Interrogator\InjectScalarDefinitionInterrogator
 * @covers \Cspray\AnnotatedContainer\ServiceDefinition
 * @covers \Cspray\AnnotatedContainer\AliasDefinition
 * @covers \Cspray\AnnotatedContainer\ServicePrepareDefinition
 * @covers \Cspray\AnnotatedContainer\InjectScalarDefinition
 * @covers \Cspray\AnnotatedContainer\Internal\Visitor\AbstractNodeVisitor
 */
class PhpParserContainerDefinitionCompilerTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private PhpParserContainerDefinitionCompiler $subject;

    public function setUp() : void {
        $this->subject = new PhpParserContainerDefinitionCompiler();
    }

    private function runCompileDirectory(array|string $dir, array $profiles = ['default']) : ContainerDefinition {
        if (is_string($dir)) {
            $dir = [$dir];
        }
        return $this->subject->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$dir)->withProfiles(...$profiles)->build());
    }

    public function testSimpleServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleServices');

        $this->assertServiceDefinitionsHaveTypes([
            SimpleServices\FooInterface::class,
            SimpleServices\FooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [SimpleServices\FooInterface::class, SimpleServices\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testSimpleServicesHasDefaultProfile() {
        $containerDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleServices');

        $this->assertCount(2, $containerDefinition->getServiceDefinitions());
        $this->assertEquals(['default'], $containerDefinition->getServiceDefinitions()[0]->getProfiles());
        $this->assertEquals(['default'], $containerDefinition->getServiceDefinitions()[1]->getProfiles());
    }

    public function testMultipleSimpleServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/MultipleSimpleServices');

        $this->assertServiceDefinitionsHaveTypes([
            MultipleSimpleServices\BarImplementation::class,
            MultipleSimpleServices\BarInterface::class,
            MultipleSimpleServices\FooImplementation::class,
            MultipleSimpleServices\FooInterface::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [MultipleSimpleServices\FooInterface::class, MultipleSimpleServices\FooImplementation::class],
            [MultipleSimpleServices\BarInterface::class, MultipleSimpleServices\BarImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testSimpleServicesSomeNotAnnotated() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleServicesSomeNotAnnotated');

        $this->assertServiceDefinitionsHaveTypes([
            SimpleServicesSomeNotAnnotated\FooInterface::class,
            SimpleServicesSomeNotAnnotated\FooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [SimpleServicesSomeNotAnnotated\FooInterface::class, SimpleServicesSomeNotAnnotated\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testEnvironmentResolvedServicesTest() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ProfileResolvedServices', ['default', 'test']);

        $this->assertServiceDefinitionsHaveTypes([
            ProfileResolvedServices\FooInterface::class,
            ProfileResolvedServices\TestFooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [ProfileResolvedServices\FooInterface::class, ProfileResolvedServices\TestFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testEnvironmentResolvedServicesDev() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ProfileResolvedServices', ['default', 'dev']);

        $this->assertServiceDefinitionsHaveTypes([
            ProfileResolvedServices\FooInterface::class,
            ProfileResolvedServices\DevFooImplementation::class,
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [ProfileResolvedServices\FooInterface::class, ProfileResolvedServices\DevFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testEnvironmentResolvedServicesProd() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ProfileResolvedServices', ['default', 'prod']);

        $this->assertServiceDefinitionsHaveTypes([
            ProfileResolvedServices\FooInterface::class,
            ProfileResolvedServices\ProdFooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [ProfileResolvedServices\FooInterface::class, ProfileResolvedServices\ProdFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testClassOnlyServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ClassOnlyServices');

        $this->assertServiceDefinitionsHaveTypes([
            ClassOnlyServices\BarImplementation::class,
            ClassOnlyServices\BazImplementation::class,
            ClassOnlyServices\FooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testInterfaceServicePrepare() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/InterfaceServicePrepare');

        $this->assertServiceDefinitionsHaveTypes([
            InterfaceServicePrepare\FooInterface::class,
            InterfaceServicePrepare\FooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [InterfaceServicePrepare\FooInterface::class, InterfaceServicePrepare\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [InterfaceServicePrepare\FooInterface::class, 'setBar']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testClassOverridesInterfaceServicePrepare() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ClassOverridesInterfaceServicePrepare');

        $this->assertServiceDefinitionsHaveTypes([
            ClassOverridesInterfaceServicePrepare\FooInterface::class,
            ClassOverridesInterfaceServicePrepare\FooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [ClassOverridesInterfaceServicePrepare\FooInterface::class, ClassOverridesInterfaceServicePrepare\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [ClassOverridesInterfaceServicePrepare\FooInterface::class, 'setBar']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testClassServicePrepareWithoutInterfaceServicePrepare() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ClassServicePrepareWithoutInterfaceServicePrepare');

        $this->assertServiceDefinitionsHaveTypes([
            ClassServicePrepareWithoutInterfaceServicePrepare\FooInterface::class,
            ClassServicePrepareWithoutInterfaceServicePrepare\FooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [ClassServicePrepareWithoutInterfaceServicePrepare\FooInterface::class, ClassServicePrepareWithoutInterfaceServicePrepare\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [ClassServicePrepareWithoutInterfaceServicePrepare\FooImplementation::class, 'setBar']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testNestedServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/NestedServices');

        $this->assertServiceDefinitionsHaveTypes([
            NestedServices\BazInterface::class,
            NestedServices\BarInterface::class,
            NestedServices\FooInterface::class,
            NestedServices\Foo\Bar\BarImplementation::class,
            NestedServices\Foo\Bar\Baz\BazImplementation::class,
            NestedServices\Foo\FooImplementation::class,
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [NestedServices\FooInterface::class, NestedServices\Foo\FooImplementation::class],
            [NestedServices\BarInterface::class, NestedServices\Foo\Bar\BarImplementation::class],
            [NestedServices\BazInterface::class, NestedServices\Foo\Bar\Baz\BazImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testAbstractSharedServices() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/AbstractSharedServices');

        $this->assertServiceDefinitionsHaveTypes([
            AbstractSharedServices\AbstractFoo::class,
            AbstractSharedServices\FooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [AbstractSharedServices\AbstractFoo::class, AbstractSharedServices\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testSimpleUseScalar() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleUseScalar');

        $this->assertServiceDefinitionsHaveTypes([SimpleUseScalar\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
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
                [1.1, 2.1, 3.1],
                [true, false, true],
                [['a', 'b', 'c'], [1, 2, 3], [1.1, 2.1, 3.1], [true, false, true]]
            ]
        ], $injectorDefinition->getInjectScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 5 times
            SimpleUseScalar\FooImplementation::class . '::__construct',
            SimpleUseScalar\FooImplementation::class . '::__construct',
            SimpleUseScalar\FooImplementation::class . '::__construct',
            SimpleUseScalar\FooImplementation::class . '::__construct',
            SimpleUseScalar\FooImplementation::class . '::__construct'
        ], $injectorDefinition->getInjectScalarDefinitions());

        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testNegativeNumberUseScalar() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/NegativeNumberUseScalar');

        $this->assertServiceDefinitionsHaveTypes([NegativeNumberUseScalar\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            NegativeNumberUseScalar\FooImplementation::class . '::__construct(intParam)' => -1,
            NegativeNumberUseScalar\FooImplementation::class . '::__construct(floatParam)' => -42.0,
        ], $injectorDefinition->getInjectScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            NegativeNumberUseScalar\FooImplementation::class . '::__construct',
            NegativeNumberUseScalar\FooImplementation::class . '::__construct'
        ], $injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testMultipleUseScalars() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/MultipleUseScalars');

        $this->assertServiceDefinitionsHaveTypes([MultipleUseScalars\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [MultipleUseScalars\FooImplementation::class, 'setUp']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            MultipleUseScalars\FooImplementation::class . '::__construct(stringParam)' => 'constructor param',
            MultipleUseScalars\FooImplementation::class . '::setUp(stringParam)' => 'prepare param',
        ], $injectorDefinition->getInjectScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            MultipleUseScalars\FooImplementation::class . '::__construct',
            MultipleUseScalars\FooImplementation::class . '::setUp'
        ], $injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testClassConstantUseScalar() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ClassConstantUseScalar');

        $this->assertServiceDefinitionsHaveTypes([ClassConstantUseScalar\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            ClassConstantUseScalar\FooImplementation::class . '::__construct(val)' => '!const(Cspray\AnnotatedContainer\DummyApps\ClassConstantUseScalar\FooImplementation::VALUE)',
        ], $injectorDefinition->getInjectScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            ClassConstantUseScalar\FooImplementation::class . '::__construct',
        ], $injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testConstantUseScalar() {
        require_once __DIR__ . '/DummyApps/ConstantUseScalar/FooImplementation.php';
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ConstantUseScalar');

        $this->assertServiceDefinitionsHaveTypes([ConstantUseScalar\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            ConstantUseScalar\FooImplementation::class . '::__construct(val)' => '!const(Cspray\AnnotatedContainer\DummyApps\ConstantUseScalar\FOO_BAR)',
        ], $injectorDefinition->getInjectScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            ConstantUseScalar\FooImplementation::class . '::__construct',
        ], $injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testUseScalarFromEnv() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleUseScalarFromEnv');

        $this->assertServiceDefinitionsHaveTypes([SimpleUseScalarFromEnv\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertUseScalarParamValues([
            SimpleUseScalarFromEnv\FooImplementation::class . '::__construct(user)' => '!env(USER)',
        ], $injectorDefinition->getInjectScalarDefinitions());

        $this->assertUseScalarMethod([
            // all of our parameters are in the same method so we'd expect to see this 2 times
            SimpleUseScalarFromEnv\FooImplementation::class . '::__construct',
        ], $injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testSimpleUseService() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/SimpleUseService');

        $this->assertServiceDefinitionsHaveTypes([
            SimpleUseService\BarImplementation::class,
            SimpleUseService\BazImplementation::class,
            SimpleUseService\QuxImplementation::class,
            SimpleUseService\FooInterface::class,
            SimpleUseService\SetterInjection::class,
            SimpleUseService\ConstructorInjection::class,
        ], $injectorDefinition->getServiceDefinitions());
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
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertUseServiceParamValues([
            SimpleUseService\SetterInjection::class . '::setBar(foo)' => SimpleUseService\BarImplementation::class,
            SimpleUseService\SetterInjection::class . '::setBaz(foo)' => SimpleUseService\BazImplementation::class,
            SimpleUseService\SetterInjection::class . '::setQux(foo)' => SimpleUseService\QuxImplementation::class,
            SimpleUseService\ConstructorInjection::class . '::__construct(bar)' => SimpleUseService\BarImplementation::class,
            SimpleUseService\ConstructorInjection::class . '::__construct(baz)' => SimpleUseService\BazImplementation::class,
            SimpleUseService\ConstructorInjection::class . '::__construct(qux)' => SimpleUseService\QuxImplementation::class
        ], $injectorDefinition->getInjectServiceDefinitions());
    }

    public function testMultipleAliasResolution() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/MultipleAliasResolution');

        $this->assertServiceDefinitionsHaveTypes([
            MultipleAliasResolution\FooInterface::class,
            MultipleAliasResolution\BazImplementation::class,
            MultipleAliasResolution\BarImplementation::class,
            MultipleAliasResolution\QuxImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [MultipleAliasResolution\FooInterface::class, MultipleAliasResolution\BarImplementation::class],
            [MultipleAliasResolution\FooInterface::class, MultipleAliasResolution\BazImplementation::class],
            [MultipleAliasResolution\FooInterface::class, MultipleAliasResolution\QuxImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testNonPhpFiles() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/NonPhpFiles');

        $this->assertServiceDefinitionsHaveTypes([NonPhpFiles\FooInterface::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testMultipleDirs() {
        $injectorDefinition = $this->runCompileDirectory([
            __DIR__ . '/DummyApps/MultipleSimpleServices',
            __DIR__ . '/DummyApps/SimpleServices'
        ]);

        $this->assertServiceDefinitionsHaveTypes([
            MultipleSimpleServices\BarInterface::class,
            MultipleSimpleServices\FooInterface::class,
            SimpleServices\FooInterface::class,
            MultipleSimpleServices\BarImplementation::class,
            MultipleSimpleServices\FooImplementation::class,
            SimpleServices\FooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            [MultipleSimpleServices\FooInterface::class, MultipleSimpleServices\FooImplementation::class],
            [MultipleSimpleServices\BarInterface::class, MultipleSimpleServices\BarImplementation::class],
            [SimpleServices\FooInterface::class, SimpleServices\FooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testServiceDelegate() {
        $injectorDefinition = $this->runCompileDirectory(__DIR__ . '/DummyApps/ServiceDelegate');
        $serviceDelegateDefinitions = $injectorDefinition->getServiceDelegateDefinitions();

        $this->assertCount(1, $serviceDelegateDefinitions);
        $serviceDelegateDefinition = $serviceDelegateDefinitions[0];

        $this->assertSame(ServiceFactory::class, $serviceDelegateDefinition->getDelegateType());
        $this->assertSame('createService', $serviceDelegateDefinition->getDelegateMethod());
        $this->assertSame(ServiceInterface::class, $serviceDelegateDefinition->getServiceType()->getType());
    }

    public function testServicePrepareNotOnServiceThrowsException() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The #[ServicePrepare] Attribute on ' . LogicalErrorApps\ServicePrepareNotService\FooImplementation::class . '::postConstruct is not on a type marked as a #[Service].');;
        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
    }

    public function testEmptyScanDirectoriesThrowsException() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . PhpParserContainerDefinitionCompiler::class . ' must include at least 1 directory to scan, but none were provided.');
        $this->runCompileDirectory([]);
    }

    public function testEmptyProfilesThrowsException() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . PhpParserContainerDefinitionCompiler::class . ' must include at least 1 active profile, but none were provided.');
        $this->runCompileDirectory([__DIR__ . '/DummyApps/SimpleServices'], []);
    }

    protected function assertUseScalarMethod(array $expectedMethods, array $UseScalarDefinitions) : void {
        if (($countExpected = count($expectedMethods)) !== ($countActual = count($UseScalarDefinitions))) {
            $this->fail("Expected ${countExpected} InjectScalarDefinition but received ${countActual}");
        }

        $actualMethods = [];
        foreach ($UseScalarDefinitions as $UseScalarDefinition) {
            $actualMethods[] = $UseScalarDefinition->getService()->getType() . "::" . $UseScalarDefinition->getMethod();
        }

        $this->assertEqualsCanonicalizing($expectedMethods, $actualMethods);
    }
}