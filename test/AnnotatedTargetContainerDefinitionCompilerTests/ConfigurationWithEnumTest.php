<?php

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
use Cspray\AnnotatedContainerFixture\ConfigurationWithEnum\MyEnum;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
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
