<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class ScanningMultipleDirectoriesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return [
            Fixtures::implicitAliasedServices(),
            Fixtures::singleConcreteService()
        ];
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::implicitAliasedServices()->fooInterface(), Fixtures::implicitAliasedServices()->fooImplementation())]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::implicitAliasedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::implicitAliasedServices()->fooImplementation())],
            [new ExpectedServiceType(Fixtures::singleConcreteService()->fooImplementation())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::implicitAliasedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::implicitAliasedServices()->fooImplementation(), null)],
            [new ExpectedServiceName(Fixtures::singleConcreteService()->fooImplementation(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::implicitAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::implicitAliasedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::singleConcreteService()->fooImplementation(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::implicitAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::implicitAliasedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::singleConcreteService()->fooImplementation(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::implicitAliasedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::implicitAliasedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::singleConcreteService()->fooImplementation(), false)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::implicitAliasedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::implicitAliasedServices()->fooImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::singleConcreteService()->fooImplementation(), ['default'])]
        ];
    }
}