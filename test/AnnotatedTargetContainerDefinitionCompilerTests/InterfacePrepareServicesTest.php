<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
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

class InterfacePrepareServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait,
        HasServicePrepareDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::interfacePrepareServices();
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::interfacePrepareServices()->fooInterface(), Fixtures::interfacePrepareServices()->fooImplementation())]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::interfacePrepareServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::interfacePrepareServices()->fooImplementation())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::interfacePrepareServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::interfacePrepareServices()->fooImplementation(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::interfacePrepareServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::interfacePrepareServices()->fooImplementation(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::interfacePrepareServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::interfacePrepareServices()->fooImplementation(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::interfacePrepareServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::interfacePrepareServices()->fooImplementation(), false)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::interfacePrepareServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::interfacePrepareServices()->fooImplementation(), ['default'])]
        ];
    }

    protected function servicePrepareProvider() : array {
        return [
            [new ExpectedServicePrepare(Fixtures::interfacePrepareServices()->fooInterface(), 'setBar')]
        ];
    }
}