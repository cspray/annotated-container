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

class AbstractClassAliasedServiceTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;


    protected function getFixtures() : array|Fixture {
        return Fixtures::abstractClassAliasedService();
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::abstractClassAliasedService()->fooAbstract(), Fixtures::abstractClassAliasedService()->fooImplementation())]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::abstractClassAliasedService()->fooAbstract())],
            [new ExpectedServiceType(Fixtures::abstractClassAliasedService()->fooImplementation())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::abstractClassAliasedService()->fooAbstract(), null)],
            [new ExpectedServiceName(Fixtures::abstractClassAliasedService()->fooImplementation(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::abstractClassAliasedService()->fooAbstract(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::abstractClassAliasedService()->fooImplementation(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::abstractClassAliasedService()->fooAbstract(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::abstractClassAliasedService()->fooImplementation(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::abstractClassAliasedService()->fooAbstract(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::abstractClassAliasedService()->fooImplementation(), false)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::abstractClassAliasedService()->fooAbstract(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::abstractClassAliasedService()->fooImplementation(), ['default'])]
        ];
    }

}