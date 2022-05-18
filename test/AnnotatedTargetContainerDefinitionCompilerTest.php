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
use Cspray\AnnotatedContainer\DummyApps\MultipleSimpleServices;
use Cspray\AnnotatedContainer\DummyApps\SimpleServicesSomeNotAnnotated;
use Cspray\AnnotatedContainer\DummyApps\NestedServices;
use Cspray\AnnotatedContainer\DummyApps\MultipleAliasResolution;
use Cspray\AnnotatedContainer\DummyApps\NonPhpFiles;
use Cspray\AnnotatedContainer\DummyApps\ImplementsServiceExtendsSameService;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\objectType;

class AnnotatedTargetContainerDefinitionCompilerTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private AnnotatedTargetContainerDefinitionCompiler $subject;

    public function setUp() : void {
        $this->subject = new AnnotatedTargetContainerDefinitionCompiler(
            new StaticAnalysisAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
    }

    private function runCompileDirectory(array|string $dir) : ContainerDefinition {
        if (is_string($dir)) {
            $dir = [$dir];
        }
        return $this->subject->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$dir)->build());
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
        $this->assertSame(['dev'], $serviceDefinition->getProfiles());

        $this->assertAliasDefinitionsMap([
            [ProfileResolvedServices\FooInterface::class, ProfileResolvedServices\DevFooImplementation::class],
            [ProfileResolvedServices\FooInterface::class, ProfileResolvedServices\ProdFooImplementation::class],
            [ProfileResolvedServices\FooInterface::class, ProfileResolvedServices\TestFooImplementation::class]
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
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
    }

    public function testNonPhpFiles() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/NonPhpFiles');

        $this->assertServiceDefinitionsHaveTypes([NonPhpFiles\FooInterface::class], $injectorDefinition->getServiceDefinitions());
        $this->assertEmpty($injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
    }

    public function testMultipleDirs() {
        $this->markTestSkipped('This requires multiple Fixtures to be available and only one is at the moment.');
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
    }

    public function testServiceDelegate() {
        $injectorDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/ServiceDelegate');
        $serviceDelegateDefinitions = $injectorDefinition->getServiceDelegateDefinitions();

        $this->assertCount(1, $serviceDelegateDefinitions);
        $serviceDelegateDefinition = $serviceDelegateDefinitions[0];

        $this->assertSame(ServiceFactory::class, $serviceDelegateDefinition->getDelegateType()->getName());
        $this->assertSame('createService', $serviceDelegateDefinition->getDelegateMethod());
        $this->assertSame(ServiceInterface::class, $serviceDelegateDefinition->getServiceType()->getName());
    }

    public function testEmptyScanDirectoriesThrowsException() {
        $this->expectException(InvalidCompileOptionsException::class);
        $this->expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . AnnotatedTargetContainerDefinitionCompiler::class . ' must include at least 1 directory to scan, but none were provided.');
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

    public function testNamedServiceHasCorrectName() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/NamedService');

        $fooInterfaceService = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\NamedService\FooInterface::class);
        $fooImplementationService = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\NamedService\FooImplementation::class);

        $this->assertSame('foo', $fooInterfaceService->getName());
        $this->assertNull($fooImplementationService->getName());
    }

    public function testServicesAddedFromAnnotationAndContextConsumerHaveServiceDefinitions() {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ThirdPartyServices')
            ->withContainerDefinitionBuilderContextConsumer(new CallableContainerDefinitionBuilderContextConsumer(function($context) {
                service($context, objectType(DummyApps\ThirdPartyServices\FooImplementation::class));
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
                service($context, objectType(DummyApps\ThirdPartyServices\FooImplementation::class));
            }))
            ->build();
        $containerDefinition = $this->subject->compile($compilerOptions);

        $this->assertAliasDefinitionsMap([
            [DummyApps\ThirdPartyServices\FooInterface::class, DummyApps\ThirdPartyServices\FooImplementation::class]
        ], $containerDefinition->getAliasDefinitions());
    }

    public function testNonSharedServiceDefinitionNotShared() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/NonSharedService') ;

        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\NonSharedService\FooImplementation::class);
        $this->assertFalse($serviceDefinition?->isShared());
    }

    public function testInjectIntMethodParamHasInjectDefinition() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/InjectIntMethodParam');

        $this->assertCount(1, $containerDefinition->getInjectDefinitions());
        $injectDefinition = $containerDefinition->getInjectDefinitions()[0];

        $this->assertSame(DummyApps\InjectIntMethodParam\FooImplementation::class, $injectDefinition->getTargetIdentifier()->getClass()->getName());
        $this->assertSame('setSomething', $injectDefinition->getTargetIdentifier()->getMethodName());
        $this->assertSame('value', $injectDefinition->getTargetIdentifier()->getName());
        $this->assertSame(intType(), $injectDefinition->getType());
        $this->assertSame(42, $injectDefinition->getValue());
        $this->assertNull($injectDefinition->getStoreName());
    }

    public function testServicePrepareNotOnServiceThrowsException() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage('The #[ServicePrepare] Attribute on ' . LogicalErrorApps\ServicePrepareNotService\FooImplementation::class . '::postConstruct is not on a type marked as a #[Service].');;
        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
    }

    public function testSimpleConfigurationGetConfigurationDefinitions() {
        $containerDefinition = $this->runCompileDirectory(DummyAppUtils::getRootDir() . '/SimpleConfiguration');

        $this->assertCount(1, $containerDefinition->getConfigurationDefinitions());
        $configDefinition = $containerDefinition->getConfigurationDefinitions()[0];

        $this->assertSame(DummyApps\SimpleConfiguration\MyConfig::class, $configDefinition->getClass()->getName());
    }

}