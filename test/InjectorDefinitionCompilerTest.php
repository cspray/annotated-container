<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Cspray\AnnotatedInjector\DummyApps\ClassOnlyServices;
use Cspray\AnnotatedInjector\DummyApps\ClassOverridesInterfaceServiceSetup;
use Cspray\AnnotatedInjector\DummyApps\ClassServiceSetupWithoutInterfaceServiceSetup;
use Cspray\AnnotatedInjector\DummyApps\EnvironmentResolvedServices;
use Cspray\AnnotatedInjector\DummyApps\InterfaceServiceSetup;
use Cspray\AnnotatedInjector\DummyApps\SimpleServices;
use Cspray\AnnotatedInjector\DummyApps\MultipleSimpleServices;
use Cspray\AnnotatedInjector\DummyApps\SimpleServicesSomeNotAnnotated;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedInjector\InjectorDefinitionCompiler
 * @covers \Cspray\AnnotatedInjector\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Visitor\ServiceSetupDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServiceSetupDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\ServiceDefinition
 * @covers \Cspray\AnnotatedInjector\AliasDefinition
 * @covers \Cspray\AnnotatedInjector\ServiceSetupDefinition
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
        $this->assertEmpty($injectorDefinition->getServiceSetup());
    }

    public function testMultipleSimpleServices() {
        $serviceDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/MultipleSimpleServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            MultipleSimpleServices\BarInterface::class,
            MultipleSimpleServices\FooInterface::class
        ], $serviceDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            MultipleSimpleServices\FooInterface::class => MultipleSimpleServices\FooImplementation::class,
            MultipleSimpleServices\BarInterface::class => MultipleSimpleServices\BarImplementation::class
        ], $serviceDefinition->getAliasDefinitions());
        $this->assertEmpty($serviceDefinition->getServiceSetup());
    }

    public function testSimpleServicesSomeNotAnnotated() {
        $serviceDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/SimpleServicesSomeNotAnnotated', 'test');

        $this->assertServiceDefinitionsHaveTypes([SimpleServicesSomeNotAnnotated\FooInterface::class], $serviceDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            SimpleServicesSomeNotAnnotated\FooInterface::class => SimpleServicesSomeNotAnnotated\FooImplementation::class
        ], $serviceDefinition->getAliasDefinitions());
        $this->assertEmpty($serviceDefinition->getServiceSetup());
    }

    public function testEnvironmentResolvedServicesTest() {
        $serviceDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $serviceDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            EnvironmentResolvedServices\FooInterface::class => EnvironmentResolvedServices\TestFooImplementation::class
        ], $serviceDefinition->getAliasDefinitions());
        $this->assertEmpty($serviceDefinition->getServiceSetup());
    }

    public function testEnvironmentResolvedServicesDev() {
        $serviceDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'dev');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $serviceDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            EnvironmentResolvedServices\FooInterface::class => EnvironmentResolvedServices\DevFooImplementation::class
        ], $serviceDefinition->getAliasDefinitions());
        $this->assertEmpty($serviceDefinition->getServiceSetup());
    }

    public function testEnvironmentResolvedServicesProd() {
        $serviceDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/EnvironmentResolvedServices', 'prod');

        $this->assertServiceDefinitionsHaveTypes([EnvironmentResolvedServices\FooInterface::class], $serviceDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            EnvironmentResolvedServices\FooInterface::class => EnvironmentResolvedServices\ProdFooImplementation::class
        ], $serviceDefinition->getAliasDefinitions());
        $this->assertEmpty($serviceDefinition->getServiceSetup());
    }

    public function testClassOnlyServices() {
        $serviceDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/ClassOnlyServices', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            ClassOnlyServices\BarImplementation::class,
            ClassOnlyServices\BazImplementation::class,
            ClassOnlyServices\FooImplementation::class
        ], $serviceDefinition->getSharedServiceDefinitions());
        $this->assertEmpty($serviceDefinition->getAliasDefinitions());
        $this->assertEmpty($serviceDefinition->getServiceSetup());
    }

    public function testInterfaceServiceSetup() {
        $serviceDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/InterfaceServiceSetup', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            InterfaceServiceSetup\FooInterface::class
        ], $serviceDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            InterfaceServiceSetup\FooInterface::class => InterfaceServiceSetup\FooImplementation::class
        ], $serviceDefinition->getAliasDefinitions());
        $this->assertServiceSetupMethods([
            InterfaceServiceSetup\FooInterface::class => 'setBar'
        ], $serviceDefinition->getServiceSetup());
    }

    public function testClassOverridesInterfaceServiceSetup() {
        $serviceDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/ClassOverridesInterfaceServiceSetup', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            ClassOverridesInterfaceServiceSetup\FooInterface::class
        ], $serviceDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            ClassOverridesInterfaceServiceSetup\FooInterface::class => ClassOverridesInterfaceServiceSetup\FooImplementation::class
        ], $serviceDefinition->getAliasDefinitions());
        $this->assertServiceSetupMethods([
            ClassOverridesInterfaceServiceSetup\FooInterface::class => 'setBar'
        ], $serviceDefinition->getServiceSetup());
    }

    public function testClassServiceSetupWithoutInterfaceServiceSetup() {
        $serviceDefinition = $this->subject->compileDirectory(__DIR__ . '/DummyApps/ClassServiceSetupWithoutInterfaceServiceSetup', 'test');

        $this->assertServiceDefinitionsHaveTypes([
            ClassServiceSetupWithoutInterfaceServiceSetup\FooInterface::class
        ], $serviceDefinition->getSharedServiceDefinitions());
        $this->assertAliasDefinitionsMap([
            ClassServiceSetupWithoutInterfaceServiceSetup\FooInterface::class => ClassServiceSetupWithoutInterfaceServiceSetup\FooImplementation::class
        ], $serviceDefinition->getAliasDefinitions());
        $this->assertServiceSetupMethods([
           ClassServiceSetupWithoutInterfaceServiceSetup\FooImplementation::class => 'setBar'
        ], $serviceDefinition->getServiceSetup());
    }

    protected function assertServiceDefinitionsHaveTypes(array $expectedTypes, array $serviceDefinitions) : void {
        if ($countExpected = count($expectedTypes) !== $countActual = count($serviceDefinitions)) {
            $this->fail("Expected ${countExpected} ServiceDefinitions but received ${countActual}");
        }

        $actualTypes = [];
        foreach ($serviceDefinitions as $serviceDefinition) {
            $this->assertInstanceOf(ServiceDefinition::class, $serviceDefinition);
            $actualTypes[] = $serviceDefinition->getType();
        }

        $this->assertEquals($expectedTypes, $actualTypes);
    }

    protected function assertAliasDefinitionsMap(array $expectedAliasMap, array $aliasDefinitions) : void {
        if ($countExpected = count($expectedAliasMap) !== $countActual = count($aliasDefinitions)) {
            $this->fail("Expected ${countExpected} AliasDefinitions but received ${countActual}");
        }

        $actualMap = [];
        foreach ($aliasDefinitions as $aliasDefinition) {
            $this->assertInstanceOf(AliasDefinition::class, $aliasDefinition);
            $actualMap[$aliasDefinition->getOriginalServiceDefinition()->getType()] = $aliasDefinition->getAliasServiceDefinition()->getType();
        }

        $this->assertEquals($expectedAliasMap, $actualMap);
    }

    protected function assertServiceSetupMethods(array $expectedServiceSetup, array $serviceSetupDefinitions) : void {
        if ($countExpected = count($expectedServiceSetup) !== $countActual = count($serviceSetupDefinitions)) {
            $this->fail("Expected ${countExpected} ServiceSetupDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($serviceSetupDefinitions as $serviceSetupDefinition) {
            $actualMap[$serviceSetupDefinition->getType()] = $serviceSetupDefinition->getMethod();
        }

        $this->assertEquals($expectedServiceSetup, $actualMap);
    }
}