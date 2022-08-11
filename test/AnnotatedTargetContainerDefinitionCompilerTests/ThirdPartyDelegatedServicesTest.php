<?php

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceDelegate;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait\HasServiceDelegateDefinitionTestsTrait;
use Cspray\AnnotatedContainer\CallableContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\ThirdPartyDelegatedServicesFixture;
use Psr\Log\LoggerInterface;
use function Cspray\AnnotatedContainer\service;
use function Cspray\Typiphy\objectType;

class ThirdPartyDelegatedServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasServiceDelegateDefinitionTestsTrait;

    use HasNoAliasDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : Fixture {
        return new ThirdPartyDelegatedServicesFixture();
    }

    protected function getContainerDefinitionBuilderContextConsumer() : ?ContainerDefinitionBuilderContextConsumer {
        return new CallableContainerDefinitionBuilderContextConsumer(function(ContainerDefinitionBuilderContext $context) {
            service($context, objectType(LoggerInterface::class));
        });
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(objectType(LoggerInterface::class))]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(objectType(LoggerInterface::class), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(objectType(LoggerInterface::class), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(objectType(LoggerInterface::class), false)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(objectType(LoggerInterface::class), true)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(objectType(LoggerInterface::class), ['default'])]
        ];
    }

    protected function serviceDelegateProvider() : array {
        return [
            [new ExpectedServiceDelegate(
                objectType(LoggerInterface::class),
                Fixtures::thirdPartyDelegatedServices()->loggerFactory(),
                'create'
            )]
        ];
    }
}