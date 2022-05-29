<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedConfigurationName;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedConfigurationType;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasConfigurationDefinitionTestsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServiceDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\boolType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\stringType;

class ConfigurationServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasConfigurationDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait;

    use HasNoAliasDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::configurationServices();
    }

    protected function configurationTypeProvider() : array {
        return [
            [new ExpectedConfigurationType(Fixtures::configurationServices()->myConfig())],
            [new ExpectedConfigurationType(Fixtures::configurationServices()->multiPropConfig())]
        ];
    }

    protected function configurationNameProvider() : array {
        return [
            [new ExpectedConfigurationName(Fixtures::configurationServices()->myConfig(), null)],
            [new ExpectedConfigurationName(Fixtures::configurationServices()->multiPropConfig(), null)]
        ];
    }

    protected function injectProvider() : array {
        return [
            [ExpectedInject::forClassProperty(
                Fixtures::configurationServices()->myConfig(),
                'key',
                stringType(),
                'my-api-key'
            )],
            [ExpectedInject::forClassProperty(
                Fixtures::configurationServices()->myConfig(),
                'port',
                intType(),
                1234
            )],
            [ExpectedInject::forClassProperty(
                Fixtures::configurationServices()->myConfig(),
                'user',
                stringType(),
                'USER',
                store: 'env'
            )],
            [ExpectedInject::forClassProperty(
                Fixtures::configurationServices()->myConfig(),
                'testMode',
                boolType(),
                true,
                profiles: ['dev', 'test']
            )],
            [ExpectedInject::forClassProperty(
                Fixtures::configurationServices()->myConfig(),
                'testMode',
                boolType(),
                false,
                ['prod']
            )],
            [ExpectedInject::forClassProperty(
                Fixtures::configurationServices()->multiPropConfig(),
                'foo',
                stringType(),
                'baz'
            )],
            [ExpectedInject::forClassProperty(
                Fixtures::configurationServices()->multiPropConfig(),
                'bar',
                stringType(),
                'baz'
            )]
        ];
    }
}