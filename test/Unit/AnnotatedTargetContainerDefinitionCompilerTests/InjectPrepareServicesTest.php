<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServicePrepare;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServicePrepareDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\stringType;
use function Cspray\Typiphy\typeUnion;

class InjectPrepareServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasServicePrepareDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::injectPrepareServices();
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::injectPrepareServices()->fooInterface(), Fixtures::injectPrepareServices()->fooImplementation())],
            [new ExpectedAliasDefinition(Fixtures::injectPrepareServices()->fooInterface(), Fixtures::injectPrepareServices()->barImplementation())]
        ];
    }

    protected function injectProvider() : array {
        return [
            [ExpectedInject::forMethodParam(
                Fixtures::injectPrepareServices()->prepareInjector(),
                'setVals',
                'val',
                stringType(),
                'foo'
            )],
            [ExpectedInject::forMethodParam(
                Fixtures::injectPrepareServices()->prepareInjector(),
                'setVals',
                'service',
                Fixtures::injectPrepareServices()->fooInterface(),
                Fixtures::injectPrepareServices()->barImplementation()->getName()
            )],
            [ExpectedInject::forMethodParam(
                Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(),
                'setValue',
                'val',
                typeUnion(floatType(), Fixtures::injectPrepareServices()->fooInterface()),
                3.14
            )]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->fooImplementation())],
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->prepareInjector())],
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->fooImplementation(), null)],
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->prepareInjector(), null)],
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->fooImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->prepareInjector(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->prepareInjector(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->prepareInjector(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), false)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->fooImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->prepareInjector(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), ['default'])]
        ];
    }

    protected function servicePrepareProvider() : array {
        return [
            [new ExpectedServicePrepare(Fixtures::injectPrepareServices()->prepareInjector(), 'setVals')],
            [new ExpectedServicePrepare(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), 'setValue')]
        ];
    }
}