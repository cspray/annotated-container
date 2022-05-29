<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsShared;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\nullType;
use function Cspray\Typiphy\typeUnion;

class InjectServiceConstructorServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::injectServiceConstructorServices();
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::injectServiceConstructorServices()->fooInterface(), Fixtures::injectServiceConstructorServices()->fooImplementation())],
            [new ExpectedAliasDefinition(Fixtures::injectServiceConstructorServices()->fooInterface(), Fixtures::injectServiceConstructorServices()->barImplementation())]
        ];
    }

    protected function injectProvider() : array {
        return [
            [ExpectedInject::forConstructParam(
                Fixtures::injectServiceConstructorServices()->serviceInjector(),
                'foo',
                Fixtures::injectServiceConstructorServices()->fooInterface(),
                Fixtures::injectServiceConstructorServices()->fooImplementation()->getName()
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectServiceConstructorServices()->nullableServiceInjector(),
                'maybeFoo',
                typeUnion(nullType(), Fixtures::injectServiceConstructorServices()->fooInterface()),
                null
            )]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->fooImplementation())],
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->serviceInjector())],
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->nullableServiceInjector())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->fooImplementation(), null)],
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->serviceInjector(), null)],
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->fooImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->serviceInjector(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->serviceInjector(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->serviceInjector(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), false)]
        ];
    }

    protected function serviceIsSharedProvider() : array {
        return [
            [new ExpectedServiceIsShared(Fixtures::injectServiceConstructorServices()->fooInterface(), true)],
            [new ExpectedServiceIsShared(Fixtures::injectServiceConstructorServices()->fooImplementation(), true)],
            [new ExpectedServiceIsShared(Fixtures::injectServiceConstructorServices()->barImplementation(), true)],
            [new ExpectedServiceIsShared(Fixtures::injectServiceConstructorServices()->serviceInjector(), true)],
            [new ExpectedServiceIsShared(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), true)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->fooInterface(), [])],
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->fooImplementation(), [])],
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->barImplementation(), [])],
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->serviceInjector(), [])],
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), [])]
        ];
    }
}