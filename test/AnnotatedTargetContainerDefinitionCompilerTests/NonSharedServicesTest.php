<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsShared;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class NonSharedServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait;

    use HasNoAliasDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::nonSharedServices();
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::nonSharedServices()->fooImplementation())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::nonSharedServices()->fooImplementation(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::nonSharedServices()->fooImplementation(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::nonSharedServices()->fooImplementation(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::nonSharedServices()->fooImplementation(), false)]
        ];
    }

    protected function serviceIsSharedProvider() : array {
        return [
            [new ExpectedServiceIsShared(Fixtures::nonSharedServices()->fooImplementation(), false)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::nonSharedServices()->fooImplementation(), [])]
        ];
    }
}