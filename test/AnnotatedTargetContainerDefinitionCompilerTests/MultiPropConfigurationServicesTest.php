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

class MultiPropConfigurationServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasConfigurationDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoAliasDefinitionsTrait,
        HasNoServiceDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::multiPropConfigurationServices();
    }

    protected function configurationTypeProvider() : array {
        return [
            [new ExpectedConfigurationType(Fixtures::multiPropConfigurationServices()->myConfig())]
        ];
    }

    protected function configurationNameProvider() : array {
        return [
            [new ExpectedConfigurationName(Fixtures::multiPropConfigurationServices()->myConfig(), null)]
        ];
    }

    protected function injectProvider() : array {
        return [
            [ExpectedInject::forClassProperty(
                Fixtures::multiPropConfigurationServices()->myConfig(),
                'foo',
                stringType(),
                'baz'
            )],
            [ExpectedInject::forClassProperty(
                Fixtures::multiPropConfigurationServices()->myConfig(),
                'bar',
                stringType(),
                'baz'
            )]
        ];
    }
}