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
use function Cspray\Typiphy\stringType;

class NamedConfigurationServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasConfigurationDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait;

    use HasNoAliasDefinitionsTrait,
        HasNoServiceDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::namedConfigurationServices();
    }

    protected function configurationTypeProvider() : array {
        return [
            [new ExpectedConfigurationType(Fixtures::namedConfigurationServices()->myConfig())]
        ];
    }

    protected function configurationNameProvider() : array {
        return [
            [new ExpectedConfigurationName(Fixtures::namedConfigurationServices()->myConfig(), 'my-config')]
        ];
    }

    protected function injectProvider() : array {
        return [
            [ExpectedInject::forClassProperty(
                Fixtures::namedConfigurationServices()->myConfig(),
                'key',
                stringType(),
                'my-api-key'
            )],
            [ExpectedInject::forClassProperty(
                Fixtures::namedConfigurationServices()->myConfig(),
                'secret',
                stringType(),
                'my-api-secret'
            )]
        ];
    }
}