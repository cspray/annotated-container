<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

abstract class AnnotatedTargetContainerDefinitionCompilerTestCase extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    protected ContainerDefinition $subject;

    /**
     * @return Fixture[]|Fixture
     */
    abstract protected function getFixtures() : array|Fixture;

    protected function setUp() : void {
        $compiler = new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
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

        $builder = ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$dirs);
        $consumer = $this->getContainerDefinitionBuilderContextConsumer();
        if (!is_null($consumer)) {
            $builder = $builder->withContainerDefinitionBuilderContextConsumer($consumer);
        }

        $this->subject = $compiler->compile($builder->build());
    }

    protected function getContainerDefinitionBuilderContextConsumer() : ?ContainerDefinitionBuilderContextConsumer {
        return null;
    }

    final protected function getSubject() : ContainerDefinition {
        return $this->subject;
    }

}