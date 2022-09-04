<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
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

class ProfileResolvedServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::profileResolvedServices();
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::profileResolvedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::profileResolvedServices()->devImplementation())],
            [new ExpectedServiceType(Fixtures::profileResolvedServices()->testImplementation())],
            [new ExpectedServiceType(Fixtures::profileResolvedServices()->prodImplementation())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::profileResolvedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::profileResolvedServices()->devImplementation(), null)],
            [new ExpectedServiceName(Fixtures::profileResolvedServices()->testImplementation(), null)],
            [new ExpectedServiceName(Fixtures::profileResolvedServices()->prodImplementation(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::profileResolvedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::profileResolvedServices()->devImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::profileResolvedServices()->testImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::profileResolvedServices()->prodImplementation(), false)],
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::profileResolvedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::profileResolvedServices()->devImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::profileResolvedServices()->testImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::profileResolvedServices()->prodImplementation(), true)],
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::profileResolvedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::profileResolvedServices()->devImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::profileResolvedServices()->testImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::profileResolvedServices()->prodImplementation(), false)],
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::profileResolvedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::profileResolvedServices()->devImplementation(), ['dev'])],
            [new ExpectedServiceProfiles(Fixtures::profileResolvedServices()->testImplementation(), ['test'])],
            [new ExpectedServiceProfiles(Fixtures::profileResolvedServices()->prodImplementation(), ['prod'])],
        ];
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::profileResolvedServices()->fooInterface(), Fixtures::profileResolvedServices()->devImplementation())],
            [new ExpectedAliasDefinition(Fixtures::profileResolvedServices()->fooInterface(), Fixtures::profileResolvedServices()->testImplementation())],
            [new ExpectedAliasDefinition(Fixtures::profileResolvedServices()->fooInterface(), Fixtures::profileResolvedServices()->prodImplementation())]
        ];
    }
}