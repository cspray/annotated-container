<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServicePrepare;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;

final class DuplicateServicePrepareTest extends LogicalConstraintTestCase {

    private ContainerDefinitionAnalyzer $analyzer;
    private DuplicateServicePrepare $subject;

    protected function setUp() : void {
        $this->analyzer = $this->getAnalyzer();
        $this->subject = new DuplicateServicePrepare();
    }

    public function testNoDuplicatePreparesHasZeroViolations() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::multiplePrepareServices()->getPath()
            )->build()
        );

        $results = $this->subject->getConstraintViolations($definition, ['default']);

        self::assertCount(0, $results);
    }

    public function testDuplicatePreparesHasViolation() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::multiplePrepareServices()->getPath()
            )->build()
        );

        $results = $this->subject->getConstraintViolations($definition, ['default']);


    }

}