<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
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

class NonAnnotatedServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait;

    use HasNoAliasDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::nonAnnotatedServices();
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::nonAnnotatedServices()->annotatedService())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::nonAnnotatedServices()->annotatedService(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::nonAnnotatedServices()->annotatedService(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::nonAnnotatedServices()->annotatedService(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::nonAnnotatedServices()->annotatedService(), false)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::nonAnnotatedServices()->annotatedService(), ['default'])]
        ];
    }

}