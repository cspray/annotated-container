<?php

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedConfigurationName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedConfigurationType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasConfigurationDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServiceDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainerFixture\ConfigurationWithEnum\MyEnum;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\objectType;

class ConfigurationWithEnumTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasConfigurationDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait;

    use HasNoServiceDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoAliasDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::configurationWithEnum();
    }

    protected function configurationTypeProvider() : array {
        return [
            [new ExpectedConfigurationType(Fixtures::configurationWithEnum()->configuration())]
        ];
    }

    protected function configurationNameProvider() : array {
        return [
            [new ExpectedConfigurationName(Fixtures::configurationWithEnum()->configuration(), null)]
        ];
    }

    protected function injectProvider() : array {
        return [
            [ExpectedInject::forClassProperty(
                Fixtures::configurationWithEnum()->configuration(),
                'enum',
                objectType(MyEnum::class),
                Fixtures::configurationWithEnum()->fooEnum()
            )]
        ];
    }
}
