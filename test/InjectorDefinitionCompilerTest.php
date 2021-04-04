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
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedInjector\InjectorDefinitionCompiler
 * @covers \Cspray\AnnotatedInjector\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Visitor\ServicePrepareDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServicePrepareDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\ServiceDefinition
 * @covers \Cspray\AnnotatedInjector\AliasDefinition
 * @covers \Cspray\AnnotatedInjector\ServicePrepareDefinition
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
    }

    public function testSimpleServicesSomeNotAnnotated() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/SimpleServicesSomeNotAnnotated', 'test');

        $this->assertServiceDefinitionsHaveTypes([SimpleServicesSomeNotAnnotated\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            SimpleServicesSomeNotAnnotated\FooInterface::class => SimpleServicesSomeNotAnnotated\FooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
    }

    public function testEnvironmentResolvedServicesTest() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            EnvironmentResolvedServices\FooInterface::class => EnvironmentResolvedServices\TestFooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
    }

    public function testEnvironmentResolvedServicesDev() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'dev');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            EnvironmentResolvedServices\FooInterface::class => EnvironmentResolvedServices\DevFooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
    }

    public function testEnvironmentResolvedServicesProd() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'prod');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            EnvironmentResolvedServices\FooInterface::class => EnvironmentResolvedServices\ProdFooImplementation::class
        ], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
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
    }

    public function testAbstractSharedServices() {
        $injectorDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/AbstractSharedServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([AbstractSharedServices\AbstractFoo::class], $injectorDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([AbstractSharedServices\AbstractFoo::class => AbstractSharedServices\FooImplementation::class], $injectorDefinition->getAliasDefinitions());
        $this->assertEmpty($injectorDefinition->getServicePrepareDefinitions());
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

        $this->assertEqualsCanonicalizing($expectedAliasMap, $actualMap);
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

        $this->assertEqualsCanonicalizing($expectedServicePrepare, $actualMap);
    }
}