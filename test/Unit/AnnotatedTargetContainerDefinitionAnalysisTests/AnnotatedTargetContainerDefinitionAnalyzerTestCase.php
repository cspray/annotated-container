<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Unit\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvents;
use Cspray\AnnotatedContainer\Unit\Helper\StubAnalysisListener;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

abstract class AnnotatedTargetContainerDefinitionAnalyzerTestCase extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    protected ContainerDefinition $subject;

    private StubAnalysisListener $stubAnalysisListener;

    /**
     * @return Fixture[]|Fixture
     */
    abstract protected function getFixtures() : array|Fixture;

    /**
     * @return list<AnalysisEvents>
     */
    abstract protected function getExpectedEvents() : array;

    protected function setUp() : void {
        $this->stubAnalysisListener = new StubAnalysisListener();

        $emitter = new Emitter();

        $emitter->addBeforeContainerAnalysisListener($this->stubAnalysisListener);
        $emitter->addAnalyzedContainerDefinitionFromCacheListener($this->stubAnalysisListener);
        $emitter->addAnalyzedInjectDefinitionFromAttributeListener($this->stubAnalysisListener);
        $emitter->addAnalyzedServiceDefinitionFromAttributeListener($this->stubAnalysisListener);
        $emitter->addAnalyzedServiceDelegateDefinitionFromAttributeListener($this->stubAnalysisListener);
        $emitter->addAnalyzedServicePrepareDefinitionFromAttributeListener($this->stubAnalysisListener);
        $emitter->addAfterContainerAnalysisListener($this->stubAnalysisListener);

        $analyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter(),
            $emitter,
        );

        $fixtures = $this->getFixtures();
        if (!is_array($fixtures)) {
            $fixtures = [$fixtures];
        }
        $dirs = [];
        foreach ($fixtures as $fixture) {
            $dirs[] = $fixture->getPath();
        }

        $builder = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(...$dirs);
        $consumer = $this->getDefinitionProvider();
        if (!is_null($consumer)) {
            $builder = $builder->withDefinitionProvider($consumer);
        }

        $this->subject = $analyzer->analyze($builder->build());
    }

    public function testEventsEmittedInCorrectOrder() : void {
        self::assertSame(
            $this->getExpectedEvents(),
            $this->stubAnalysisListener->getTriggeredEvents()
        );
    }

    protected function getDefinitionProvider() : ?DefinitionProvider {
        return null;
    }

    final protected function getSubject() : ContainerDefinition {
        return $this->subject;
    }

}