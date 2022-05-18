<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysisAnnotatedTargetParser;
use Cspray\AnnotatedContainerFixture\Fixture;
use PHPUnit\Framework\TestCase;

abstract class AnnotatedTargetContainerDefinitionCompilerTestCase extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    /**
     * @return Fixture[]|Fixture
     */
    abstract protected function getFixtures() : array|Fixture;

    abstract protected function getExpectedServiceDefinitionCount() : int;

    abstract protected function getExpectedAliasDefinitionCount() : int;

    abstract protected function getExpectedServiceDelegateDefinitionCount() : int;

    abstract protected function getExpectedServicePrepareDefinitionCount() : int;

    abstract protected function getExpectedInjectDefinitionCount() : int;

    abstract protected function getExpectedConfigurationDefinitionCount() : int;

    protected ContainerDefinition $subject;

    protected function setUp() : void {
        $compiler = new AnnotatedTargetContainerDefinitionCompiler(
            new StaticAnalysisAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );

        $fixtures = $this->getFixtures();
        if (!is_array($fixtures)) {
            $fixtures = [$fixtures];
        }
        $dirs = [];
        foreach ($fixtures as $fixture) {
            $dirs[] = $fixture->getPath();
        }

        $this->subject = $compiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$dirs)->build());
    }

    final public function testServiceDefinitionsCount() : void {
        $this->assertCount($this->getExpectedServiceDefinitionCount(), $this->subject->getServiceDefinitions());
    }

    final public function testAliasDefinitionsCount() : void {
        $this->assertCount($this->getExpectedAliasDefinitionCount(), $this->subject->getAliasDefinitions());
    }

    final public function testServiceDelegateDefinitionsCount() : void {
        $this->assertCount($this->getExpectedServiceDelegateDefinitionCount(), $this->subject->getServiceDelegateDefinitions());
    }

    final public function testServicePrepareDefinitionsCount() : void {
        $this->assertCount($this->getExpectedServicePrepareDefinitionCount(), $this->subject->getServicePrepareDefinitions());
    }

    final public function testInjectDefinitionsCount() : void {
        $this->assertCount($this->getExpectedInjectDefinitionCount(), $this->subject->getInjectDefinitions());
    }

    final public function testConfigurationDefinitionsCount() : void {
        $this->assertCount($this->getExpectedConfigurationDefinitionCount(), $this->subject->getConfigurationDefinitions());
    }


}