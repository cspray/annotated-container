<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\AbstractSharedServices;
use Cspray\AnnotatedContainer\DummyApps\ClassOnlyServices;
use Cspray\AnnotatedContainer\DummyApps\ClassOverridesInterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\ClassServicePrepareWithoutInterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
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
use Cspray\AnnotatedContainer\DummyApps\ImplementsServiceExtendsSameService;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use PHPUnit\Framework\TestCase;

class PhpParserContainerDefinitionCompilerTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private PhpParserContainerDefinitionCompiler $subject;

    public function setUp() : void {
        $this->subject = new PhpParserContainerDefinitionCompiler();
    }

    private function runCompileDirectory(array|string $dir) : ContainerDefinition {
        if (is_string($dir)) {
            $dir = [$dir];
        }
        return $this->subject->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$dir)->build());
    }

    public function testSimpleServices() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/SimpleServices');

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

    public function testSimpleServicesDefaultsPrimaryToFalse() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/SimpleServices');

        $this->assertServiceDefinitionIsNotPrimary($containerDefinition->getServiceDefinitions(), SimpleServices\FooInterface::class);
        $this->assertServiceDefinitionIsNotPrimary($containerDefinition->getServiceDefinitions(), SimpleServices\FooImplementation::class);
    }

    public function testSimpleServicesHasNoProfile() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/SimpleServices');

        $this->assertCount(2, $containerDefinition->getServiceDefinitions());
        $this->assertEmpty($containerDefinition->getServiceDefinitions()[0]->getProfiles()->getCompileValue());
        $this->assertEmpty($containerDefinition->getServiceDefinitions()[1]->getProfiles()->getCompileValue());
    }

    public function testMultipleSimpleServices() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/MultipleSimpleServices');

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
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/SimpleServicesSomeNotAnnotated');

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

    public function testEnvironmentResolvedServices() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/ProfileResolvedServices');

        $this->assertServiceDefinitionsHaveTypes([
            ProfileResolvedServices\FooInterface::class,
            ProfileResolvedServices\DevFooImplementation::class,
            ProfileResolvedServices\TestFooImplementation::class,
            ProfileResolvedServices\ProdFooImplementation::class
        ], $injectorDefinition->getServiceDefinitions());
        $serviceDefinition = $this->getServiceDefinition($injectorDefinition->getServiceDefinitions(), DummyApps\ProfileResolvedServices\DevFooImplementation::class);
        $this->assertSame(['dev'], $serviceDefinition->getProfiles()->getRuntimeValue());

        $this->assertAliasDefinitionsMap([
            [ProfileResolvedServices\FooInterface::class, ProfileResolvedServices\DevFooImplementation::class],
            [ProfileResolvedServices\FooInterface::class, ProfileResolvedServices\ProdFooImplementation::class],
            [ProfileResolvedServices\FooInterface::class, ProfileResolvedServices\TestFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testClassOnlyServices() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/ClassOnlyServices');

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
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/InterfaceServicePrepare');

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
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/ClassOverridesInterfaceServicePrepare');

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
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/ClassServicePrepareWithoutInterfaceServicePrepare');

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
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/NestedServices');

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
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/AbstractSharedServices');

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

    public function testSimpleInjectScalar() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/SimpleUseScalar');

        $this->assertServiceDefinitionsHaveTypes([SimpleUseScalar\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertInjectScalarParamValues([
            SimpleUseScalar\FooImplementation::class . '::__construct(stringParam)|' => 'string param test value',
            SimpleUseScalar\FooImplementation::class . '::__construct(intParam)|' => 42,
            SimpleUseScalar\FooImplementation::class . '::__construct(floatParam)|' => 42.0,
            SimpleUseScalar\FooImplementation::class . '::__construct(boolParam)|' => true,
            SimpleUseScalar\FooImplementation::class . '::__construct(arrayParam)|' => [
                ['a', 'b', 'c'],
                [1, 2, 3],
                [1.1, 2.1, 3.1],
                [true, false, true],
                [['a', 'b', 'c'], [1, 2, 3], [1.1, 2.1, 3.1], [true, false, true]]
            ]
        ], $injectorDefinition->getInjectScalarDefinitions());

        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testNegativeNumberUseScalar() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/NegativeNumberUseScalar');

        $this->assertServiceDefinitionsHaveTypes([NegativeNumberUseScalar\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertInjectScalarParamValues([
            NegativeNumberUseScalar\FooImplementation::class . '::__construct(intParam)|' => -1,
            NegativeNumberUseScalar\FooImplementation::class . '::__construct(floatParam)|' => -42.0,
        ], $injectorDefinition->getInjectScalarDefinitions());

        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testMultipleUseScalars() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/MultipleUseScalars');

        $this->assertServiceDefinitionsHaveTypes([MultipleUseScalars\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertServicePrepareTypes([
            [MultipleUseScalars\FooImplementation::class, 'setUp']
        ], $injectorDefinition->getServicePrepareDefinitions());
        $this->assertInjectScalarParamValues([
            MultipleUseScalars\FooImplementation::class . '::__construct(stringParam)|' => 'constructor param',
            MultipleUseScalars\FooImplementation::class . '::setUp(stringParam)|' => 'prepare param',
        ], $injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testClassConstantUseScalar() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/ClassConstantUseScalar');

        $this->assertServiceDefinitionsHaveTypes([ClassConstantUseScalar\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertInjectScalarParamValues([
            ClassConstantUseScalar\FooImplementation::class . '::__construct(val)|' => 'Cspray\AnnotatedContainer\DummyApps\ClassConstantUseScalar\FooImplementation::VALUE',
        ], $injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testConstantInjectScalar() {
        require_once DummyAppUtils::getRootDir() . '/ConstantUseScalar/FooImplementation.php';
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/ConstantUseScalar');

        $this->assertServiceDefinitionsHaveTypes([ConstantUseScalar\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertInjectScalarParamValues([
            ConstantUseScalar\FooImplementation::class . '::__construct(val)|' => 'Cspray\AnnotatedContainer\DummyApps\ConstantUseScalar\FOO_BAR',
        ], $injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testInjectScalarFromEnv() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/SimpleUseScalarFromEnv');

        $this->assertServiceDefinitionsHaveTypes([SimpleUseScalarFromEnv\FooImplementation::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertInjectScalarParamValues([
            SimpleUseScalarFromEnv\FooImplementation::class . '::__construct(user)|' => 'USER',
        ], $injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testSimpleInjectService() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/SimpleUseService');

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
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/MultipleAliasResolution');

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
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/NonPhpFiles');

        $this->assertServiceDefinitionsHaveTypes([NonPhpFiles\FooInterface::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectScalarDefinitions());
        $this->assertEmpty($injectorDefinition->getInjectServiceDefinitions());
    }

    public function testMultipleDirs() {
        $injectorDefinition = $this->runCompileDirectory([
            DummyAppUtils::getRootDir() . '/MultipleSimpleServices',
            DummyAppUtils::getRootDir() . '/SimpleServices'
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
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/ServiceDelegate');
        $serviceDelegateDefinitions = $injectorDefinition->getServiceDelegateDefinitions();

        $this->assertCount(1, $serviceDelegateDefinitions);
        $serviceDelegateDefinition = $serviceDelegateDefinitions[0];

        $this->assertSame(ServiceFactory::class, $serviceDelegateDefinition->getDelegateType());
        $this->assertSame('createService', $serviceDelegateDefinition->getDelegateMethod());
        $this->assertSame(ServiceInterface::class, $serviceDelegateDefinition->getServiceType()->getType());
    }

    public function testServicePrepareNotOnServiceThrowsException() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage('The #[ServicePrepare] Attribute on ' . LogicalErrorApps\ServicePrepareNotService\FooImplementation::class . '::postConstruct is not on a type marked as a #[Service].');;
        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
    }

    public function testEmptyScanDirectoriesThrowsException() {
        $this->expectException(InvalidCompileOptionsException::class);
        $this->expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . PhpParserContainerDefinitionCompiler::class . ' must include at least 1 directory to scan, but none were provided.');
        $this->runCompileDirectory([]);
    }

    public function testImplementsServiceExtendsSameService() {
        $containerDefinition = $this->runCompileDirectory([DummyAppUtils::getRootDir() . '/ImplementsServiceExtendsSameService']);

        $this->assertServiceDefinitionsHaveTypes([
            ImplementsServiceExtendsSameService\FooInterface::class,
            ImplementsServiceExtendsSameService\FooImplementation::class
        ], $containerDefinition->getServiceDefinitions());
    }

    public function testMultipleServicesWithPrimaryMarkedAppropriately() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/MultipleServicesWithPrimary');

        $this->assertServiceDefinitionIsNotPrimary($containerDefinition->getServiceDefinitions(), DummyApps\MultipleServicesWithPrimary\FooInterface::class);
        $this->assertServiceDefinitionIsPrimary($containerDefinition->getServiceDefinitions(), DummyApps\MultipleServicesWithPrimary\FooImplementation::class);
        $this->assertServiceDefinitionIsNotPrimary($containerDefinition->getServiceDefinitions(), DummyApps\MultipleServicesWithPrimary\BarImplementation::class);
        $this->assertServiceDefinitionIsNotPrimary($containerDefinition->getServiceDefinitions(), DummyApps\MultipleServicesWithPrimary\BazImplementation::class);
    }

    public function testInjectScalarProfilesHasAllInjectScalarDefinitions() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/InjectScalarProfiles');

        $this->assertInjectScalarParamValues([
            DummyApps\InjectScalarProfiles\FooImplementation::class . '::__construct(value)|dev' => 'foo',
            DummyApps\InjectScalarProfiles\FooImplementation::class . '::__construct(value)|prod' => 'bar',
            DummyApps\InjectScalarProfiles\FooImplementation::class . '::__construct(value)|Cspray\AnnotatedContainer\DummyApps\InjectScalarProfiles\Constants::INJECT_SCALAR_BAZ_TEST_PROFILE' => 'baz'
        ], $containerDefinition->getInjectScalarDefinitions());
    }

    public function testInjectEnvProfilesHasAllInjectScalarDefinitions() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/InjectEnvProfiles');

        $this->assertInjectScalarParamValues([
            DummyApps\InjectEnvProfiles\FooImplementation::class . '::setValue(value)|dev' => 'foo',
            DummyApps\InjectEnvProfiles\FooImplementation::class . '::setValue(value)|prod' => 'USER',
            DummyApps\InjectEnvProfiles\FooImplementation::class . '::__construct(testUser)|test' => 'USER'
        ], $containerDefinition->getInjectScalarDefinitions());
    }

    public function testNamedServiceHasCorrectName() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/NamedService');

        $fooInterfaceService = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\NamedService\FooInterface::class);
        $fooImplementationService = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\NamedService\FooImplementation::class);

        $this->assertSame('foo', $fooInterfaceService->getName()->getCompileValue());
        $this->assertNull($fooImplementationService->getName());
    }

    public function testServicesAddedFromAnnotationAndContextConsumerHaveServiceDefinitions() {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ThirdPartyServices')
            ->withContainerDefinitionBuilderContextConsumer(new CallableContainerDefinitionBuilderContextConsumer(function($context) {
                service($context, DummyApps\ThirdPartyServices\FooImplementation::class);
            }))
            ->build();
        $containerDefinition = $this->subject->compile($compilerOptions);

        $this->assertServiceDefinitionsHaveTypes([
            DummyApps\ThirdPartyServices\FooInterface::class,
            DummyApps\ThirdPartyServices\FooImplementation::class
        ], $containerDefinition->getServiceDefinitions());
    }

    public function testServicesAddedFromAnnotationAndContextConsumerHaveAliasDefinitions() {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ThirdPartyServices')
            ->withContainerDefinitionBuilderContextConsumer(new CallableContainerDefinitionBuilderContextConsumer(function($context) {
                service($context, DummyApps\ThirdPartyServices\FooImplementation::class);
            }))
            ->build();
        $containerDefinition = $this->subject->compile($compilerOptions);

        $this->assertAliasDefinitionsMap([
            [DummyApps\ThirdPartyServices\FooInterface::class, DummyApps\ThirdPartyServices\FooImplementation::class]
        ], $containerDefinition->getAliasDefinitions());
    }
}