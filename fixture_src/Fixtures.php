<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

final class Fixtures {

    private function __construct() {}

    public static function singleConcreteService() : SingleConcreteServiceFixture {
        return new SingleConcreteServiceFixture();
    }

    public static function implicitAliasedServices() : ImplicitAliasedServicesFixture {
        return new ImplicitAliasedServicesFixture();
    }

    public static function nonAnnotatedServices() : NonAnnotatedServicesFixture {
        return new NonAnnotatedServicesFixture();
    }

    public static function profileResolvedServices() : ProfileResolvedServicesFixture {
        return new ProfileResolvedServicesFixture();
    }

    public static function interfacePrepareServices() : InterfacePrepareServicesFixture {
        return new InterfacePrepareServicesFixture();
    }

    public static function classOverridesPrepareServices() : ClassOverridesPrepareServicesFixture {
        return new ClassOverridesPrepareServicesFixture();
    }

    public static function classOnlyPrepareServices() : ClassOnlyPrepareServicesFixture {
        return new ClassOnlyPrepareServicesFixture();
    }

    public static function subNamespacedServices() : SubNamespacedServicesFixture {
        return new SubNamespacedServicesFixture();
    }

    public static function abstractClassAliasedService() : AbstractClassAliasedServiceFixture {
        return new AbstractClassAliasedServiceFixture();
    }

    public static function ambiguousAliasedServices() : AmbiguousAliasedServicesFixture {
        return new AmbiguousAliasedServicesFixture();
    }

    public static function delegatedService() : DelegatedServiceFixture {
        return new DelegatedServiceFixture();
    }

    public static function primaryAliasedServices() : PrimaryAliasedServicesFixture {
        return new PrimaryAliasedServicesFixture();
    }

    public static function namedServices() : NamedServicesFixture {
        return new NamedServicesFixture();
    }

    public static function implicitAliasThroughAbstractServices() : ImplicitAliasThroughAbstractClassServicesFixture {
        return new ImplicitAliasThroughAbstractClassServicesFixture();
    }

    public static function thirdPartyServices() : ThirdPartyServicesFixture {
        return new ThirdPartyServicesFixture();
    }

    public static function nonSharedServices() : NonSharedServicesFixture {
        return new NonSharedServicesFixture();
    }

    public static function injectConstructorServices() : InjectConstructorServicesFixture {
        return new InjectConstructorServicesFixture();
    }

    public static function injectServiceConstructorServices() : InjectServiceConstructorServicesFixture {
        return new InjectServiceConstructorServicesFixture();
    }

    public static function injectPrepareServices() : InjectPrepareServicesFixture {
        return new InjectPrepareServicesFixture();
    }

    public static function injectCustomStoreServices() : InjectCustomStoreServicesFixture {
        return new InjectCustomStoreServicesFixture();
    }

    public static function multiplePrepareServices() : MultiplePrepareServicesFixture {
        return new MultiplePrepareServicesFixture();
    }

    public static function configurationServices() : ConfigurationServicesFixture {
        return new ConfigurationServicesFixture();
    }

    public static function namedConfigurationServices() : NamedConfigurationServicesFixture {
        return new NamedConfigurationServicesFixture();
    }

    public static function autowireableFactoryServices() : AutowireableFactoryServicesFixture {
        return new AutowireableFactoryServicesFixture();
    }

}