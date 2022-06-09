<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsShared;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class SubNamespacedServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::subNamespacedServices();
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::subNamespacedServices()->barInterface(), Fixtures::subNamespacedServices()->barImplementation())],
            [new ExpectedAliasDefinition(Fixtures::subNamespacedServices()->bazInterface(), Fixtures::subNamespacedServices()->bazImplementation())],
            [new ExpectedAliasDefinition(Fixtures::subNamespacedServices()->fooInterface(), Fixtures::subNamespacedServices()->fooImplementation())]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->barInterface())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->bazInterface())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->bazImplementation())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->fooImplementation())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->barInterface(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->bazInterface(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->fooImplementation(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->bazImplementation(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->barInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->bazInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->bazImplementation(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->barInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->bazInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->bazImplementation(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->barInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->bazInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->bazImplementation(), false)],
        ];
    }

    protected function serviceIsSharedProvider() : array {
        return [
            [new ExpectedServiceIsShared(Fixtures::subNamespacedServices()->barInterface(), true)],
            [new ExpectedServiceIsShared(Fixtures::subNamespacedServices()->fooInterface(), true)],
            [new ExpectedServiceIsShared(Fixtures::subNamespacedServices()->bazInterface(), true)],
            [new ExpectedServiceIsShared(Fixtures::subNamespacedServices()->barImplementation(), true)],
            [new ExpectedServiceIsShared(Fixtures::subNamespacedServices()->bazImplementation(), true)],
            [new ExpectedServiceIsShared(Fixtures::subNamespacedServices()->fooImplementation(), true)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->barInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->bazInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->bazImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->fooImplementation(), ['default'])],
        ];
    }
}