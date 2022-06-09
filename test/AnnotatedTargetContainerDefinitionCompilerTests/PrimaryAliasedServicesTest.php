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

class PrimaryAliasedServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::primaryAliasedServices();
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::primaryAliasedServices()->fooInterface(), Fixtures::primaryAliasedServices()->fooImplementation())],
            [new ExpectedAliasDefinition(Fixtures::primaryAliasedServices()->fooInterface(), Fixtures::primaryAliasedServices()->barImplementation())],
            [new ExpectedAliasDefinition(Fixtures::primaryAliasedServices()->fooInterface(), Fixtures::primaryAliasedServices()->bazImplementation())]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::primaryAliasedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::primaryAliasedServices()->bazImplementation())],
            [new ExpectedServiceType(Fixtures::primaryAliasedServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::primaryAliasedServices()->fooImplementation())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::primaryAliasedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::primaryAliasedServices()->bazImplementation(), null)],
            [new ExpectedServiceName(Fixtures::primaryAliasedServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::primaryAliasedServices()->fooImplementation(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::primaryAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::primaryAliasedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsPrimary(Fixtures::primaryAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::primaryAliasedServices()->bazImplementation(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::primaryAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::primaryAliasedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::primaryAliasedServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::primaryAliasedServices()->bazImplementation(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::primaryAliasedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::primaryAliasedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::primaryAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::primaryAliasedServices()->bazImplementation(), false)]
        ];
    }

    protected function serviceIsSharedProvider() : array {
        return [
            [new ExpectedServiceIsShared(Fixtures::primaryAliasedServices()->fooInterface(), true)],
            [new ExpectedServiceIsShared(Fixtures::primaryAliasedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsShared(Fixtures::primaryAliasedServices()->barImplementation(), true)],
            [new ExpectedServiceIsShared(Fixtures::primaryAliasedServices()->bazImplementation(), true)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::primaryAliasedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::primaryAliasedServices()->fooImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::primaryAliasedServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::primaryAliasedServices()->bazImplementation(), ['default'])]
        ];
    }
}