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

class MultipleAliasedServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::ambiguousAliasedServices();
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->barImplementation())],
            [new ExpectedAliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->bazImplementation())],
            [new ExpectedAliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->quxImplementation())]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::ambiguousAliasedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::ambiguousAliasedServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::ambiguousAliasedServices()->bazImplementation())],
            [new ExpectedServiceType(Fixtures::ambiguousAliasedServices()->quxImplementation())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::ambiguousAliasedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::ambiguousAliasedServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::ambiguousAliasedServices()->bazImplementation(), null)],
            [new ExpectedServiceName(Fixtures::ambiguousAliasedServices()->quxImplementation(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::ambiguousAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::ambiguousAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::ambiguousAliasedServices()->bazImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::ambiguousAliasedServices()->quxImplementation(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::ambiguousAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::ambiguousAliasedServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::ambiguousAliasedServices()->bazImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::ambiguousAliasedServices()->quxImplementation(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::ambiguousAliasedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::ambiguousAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::ambiguousAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::ambiguousAliasedServices()->quxImplementation(), false)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::ambiguousAliasedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::ambiguousAliasedServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::ambiguousAliasedServices()->bazImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::ambiguousAliasedServices()->quxImplementation(), ['default'])],
        ];
    }
}