<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * @covers \Cspray\AnnotatedContainer\AurynContainerFactory
 * @covers \Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompiler
 * @covers \Cspray\AnnotatedContainer\AliasDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\Attribute\Service
 * @covers \Cspray\AnnotatedContainer\ContainerDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder
 * @covers \Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter
 * @covers \Cspray\AnnotatedContainer\StaticAnalysisAnnotatedTargetParser
 * @covers \Cspray\AnnotatedContainer\ServiceDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\ContainerFactoryOptionsBuilder
 * @covers \Cspray\AnnotatedContainer\ServicePrepareDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\Attribute\ServiceDelegate
 * @covers \Cspray\AnnotatedContainer\ServiceDelegateDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\InjectDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\Internal\MethodParameterInjectTargetIdentifier
 * @covers \Cspray\AnnotatedContainer\Attribute\Inject
 * @covers \Cspray\AnnotatedContainer\EnvironmentParameterStore
 * @covers \Cspray\AnnotatedContainer\ConfigurationDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\Internal\PropertyInjectTargetIdentifier
 * @covers \Cspray\AnnotatedContainer\Attribute\Configuration
 */
class AurynContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory() : ContainerFactory {
        return new AurynContainerFactory();
    }
}