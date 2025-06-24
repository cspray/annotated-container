<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Internal\InjectDefinitionFromFunctionalApi;
use Cspray\AnnotatedContainer\Internal\ServiceDelegateFromFunctionalApi;
use Cspray\AnnotatedContainer\Internal\ServiceFromFunctionalApi;
use Cspray\AnnotatedContainer\Internal\ServicePrepareFromFunctionalApi;
use PHPUnit\Framework\TestCase;
use function Cspray\AnnotatedContainer\Definition\inject;
use function Cspray\AnnotatedContainer\Definition\serviceDelegate;
use function Cspray\AnnotatedContainer\Definition\servicePrepare;
use function Cspray\AnnotatedContainer\Definition\service;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ThirdPartyFunctionsTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    public function testHasServiceDefinitionForType() : void {
        $type = Fixtures::singleConcreteService()->fooImplementation();
        $serviceDefinition = service($type);

        self::assertSame(
            $serviceDefinition->type(),
            $type
        );
    }

    public function testServiceHasCorrectAttributeAssociatedWithIt() : void {
        $type = Fixtures::singleConcreteService()->fooImplementation();
        $serviceDefinition = service($type);

        self::assertInstanceOf(
            ServiceFromFunctionalApi::class,
            $serviceDefinition->attribute()
        );
        self::assertNull($serviceDefinition->attribute()->name());
        self::assertFalse($serviceDefinition->attribute()->isPrimary());
        self::assertSame([], $serviceDefinition->attribute()->profiles());
    }

    public function testAbstractDefinedServiceIsAbstract() {
        $serviceDefinition = service(Fixtures::implicitAliasedServices()->fooInterface());

        self::assertTrue($serviceDefinition->isAbstract());
    }

    public function testAbstractDefinedServiceGetName() {
        $serviceDefinition = service(Fixtures::implicitAliasedServices()->fooInterface(), 'fooService');

        self::assertSame('fooService', $serviceDefinition->name());
        self::assertSame('fooService', $serviceDefinition->attribute()->name());
    }

    public function testAbstractDefinedServiceGetProfiles() {
        $serviceDefinition = service(Fixtures::implicitAliasedServices()->fooInterface(), profiles: ['default', 'dev']);

        self::assertSame(['default', 'dev'], $serviceDefinition->profiles());
        self::assertSame(['default', 'dev'], $serviceDefinition->attribute()->profiles());
    }

    public function testSingleConcreteServiceIsConcrete() {
        $serviceDefinition = service(Fixtures::singleConcreteService()->fooImplementation());

        self::assertTrue($serviceDefinition->isConcrete());
    }

    public function testSingleConcreteServiceIsPrimary() {
        $serviceDefinition = service(Fixtures::singleConcreteService()->fooImplementation(), isPrimary: true);

        self::assertTrue($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->attribute()->isPrimary());
    }

    public function testServiceDelegateDefinition() {
        $serviceDelegateDefinition = serviceDelegate(Fixtures::delegatedService()->serviceFactory(), 'createService');

        self::assertSame(Fixtures::delegatedService()->serviceInterface()->name(), $serviceDelegateDefinition->service()->name());
        self::assertSame(Fixtures::delegatedService()->serviceFactory()->name(), $serviceDelegateDefinition->classMethod()->class()->name());
        self::assertSame('createService', $serviceDelegateDefinition->classMethod()->methodName());
        self::assertSame(['default'], $serviceDelegateDefinition->profiles());
        self::assertInstanceOf(ServiceDelegateFromFunctionalApi::class, $serviceDelegateDefinition->attribute());
        self::assertNull($serviceDelegateDefinition->attribute()->service());
    }

    public function testServiceDelegateDefinitionWithExplicitProfiles() : void {
        $serviceDelegateDefinition = serviceDelegate(Fixtures::delegatedService()->serviceFactory(), 'createService', ['the', 'love', 'plug']);

        self::assertSame(Fixtures::delegatedService()->serviceInterface()->name(), $serviceDelegateDefinition->service()->name());
        self::assertSame(Fixtures::delegatedService()->serviceFactory()->name(), $serviceDelegateDefinition->classMethod()->class()->name());
        self::assertSame('createService', $serviceDelegateDefinition->classMethod()->methodName());
        self::assertSame(['the', 'love', 'plug'], $serviceDelegateDefinition->profiles());
        self::assertSame(['the', 'love', 'plug'], $serviceDelegateDefinition->attribute()->profiles());
    }

    public function testServicePrepareDefinition() {
        $servicePrepareDefinition = servicePrepare(Fixtures::interfacePrepareServices()->fooInterface(), 'setBar');

        self::assertServicePrepareTypes([
            [Fixtures::interfacePrepareServices()->fooInterface()->name(), 'setBar']
        ], [$servicePrepareDefinition]);
        self::assertInstanceOf(ServicePrepareFromFunctionalApi::class, $servicePrepareDefinition->attribute());
    }

    public function testInjectMethodParam() {
        $inject = inject(
            Fixtures::injectConstructorServices()->injectFloatService(),
            '__construct',
            'dessert',
            types()->int(),
            42
        );

        self::assertSame(Fixtures::injectConstructorServices()->injectFloatService(), $inject->service());
        self::assertSame(Fixtures::injectConstructorServices()->injectFloatService(), $inject->classMethodParameter()->class());
        self::assertSame('__construct', $inject->classMethodParameter()->methodName());
        self::assertSame('dessert', $inject->classMethodParameter()->parameterName());
        self::assertSame(types()->int(), $inject->classMethodParameter()->type());
        self::assertFalse($inject->classMethodParameter()->isStatic());
        self::assertSame(42, $inject->value());
        self::assertSame(['default'], $inject->profiles());
        self::assertNull($inject->storeName());
    }

    public function testInjectHasCorrectAttribute() : void {
        $inject = inject(
            Fixtures::injectConstructorServices()->injectFloatService(),
            '__construct',
            'dessert',
            types()->int(),
            42
        );

        self::assertInstanceOf(
            InjectDefinitionFromFunctionalApi::class,
            $inject->attribute()
        );
        self::assertSame(42, $inject->attribute()->value());
        self::assertSame([], $inject->attribute()->profiles());
        self::assertNull($inject->attribute()->from());
    }

    public function testInjectMethodParamProfiles() {
        $inject = inject(
            Fixtures::injectConstructorServices()->injectFloatService(),
            '__construct',
            'dessert',
            types()->int(),
            42,
            ['foo', 'bar', 'baz']
        );

        self::assertSame(['foo', 'bar', 'baz'], $inject->profiles());
        self::assertSame(['foo', 'bar', 'baz'], $inject->attribute()->profiles());
    }

    public function testInjectMethodParamStoreName() {
        $inject = inject(
            Fixtures::injectConstructorServices()->injectFloatService(),
            '__construct',
            'dessert',
            types()->int(),
            42,
            from: 'store-name'
        );

        self::assertSame('store-name', $inject->storeName());
        self::assertSame('store-name', $inject->attribute()->from());
    }
}
