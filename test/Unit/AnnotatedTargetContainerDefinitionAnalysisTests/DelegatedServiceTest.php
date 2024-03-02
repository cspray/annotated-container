<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceDelegate;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDelegateDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class DelegatedServiceTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasServiceDelegateDefinitionTestsTrait;

    use HasNoAliasDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::delegatedService();
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::delegatedService()->serviceInterface())],
            [new ExpectedServiceType(Fixtures::delegatedService()->fooService())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::delegatedService()->serviceInterface(), null)],
            [new ExpectedServiceName(Fixtures::delegatedService()->fooService(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::delegatedService()->serviceInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::delegatedService()->fooService(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::delegatedService()->serviceInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::delegatedService()->fooService(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::delegatedService()->serviceInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::delegatedService()->fooService(), false)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::delegatedService()->serviceInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::delegatedService()->fooService(), ['default'])]
        ];
    }

    protected function serviceDelegateProvider() : array {
        return [
            [new ExpectedServiceDelegate(Fixtures::delegatedService()->serviceInterface(), Fixtures::delegatedService()->serviceFactory(), 'createService')]
        ];
    }
}