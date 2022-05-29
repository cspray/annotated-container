<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsShared;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServicePrepare;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServicePrepareDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class ClassOverridesPrepareServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait,
        HasServicePrepareDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::classOverridesPrepareServices();
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::classOverridesPrepareServices()->fooInterface(), Fixtures::classOverridesPrepareServices()->fooImplementation())]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::classOverridesPrepareServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::classOverridesPrepareServices()->fooImplementation())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::classOverridesPrepareServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::classOverridesPrepareServices()->fooImplementation(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::classOverridesPrepareServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::classOverridesPrepareServices()->fooImplementation(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::classOverridesPrepareServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::classOverridesPrepareServices()->fooImplementation(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::classOverridesPrepareServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::classOverridesPrepareServices()->fooImplementation(), false)]
        ];
    }

    protected function serviceIsSharedProvider() : array {
        return [
            [new ExpectedServiceIsShared(Fixtures::classOverridesPrepareServices()->fooInterface(), true)],
            [new ExpectedServiceIsShared(Fixtures::classOverridesPrepareServices()->fooImplementation(), true)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::classOverridesPrepareServices()->fooInterface(), [])],
            [new ExpectedServiceProfiles(Fixtures::classOverridesPrepareServices()->fooImplementation(), [])]
        ];
    }

    protected function servicePrepareProvider() : array {
        return [
            [new ExpectedServicePrepare(Fixtures::classOverridesPrepareServices()->fooInterface(), 'setBar')]
        ];
    }

}