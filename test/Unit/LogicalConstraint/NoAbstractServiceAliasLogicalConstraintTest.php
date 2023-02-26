<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint;

use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\LogicalConstraint\NoAbstractServiceAliasLogicalConstraint;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

class NoAbstractServiceAliasLogicalConstraintTest extends TestCase {

    private ContainerDefinitionAnalyzer $containerDefinitionCompiler;
    private NoAbstractServiceAliasLogicalConstraint $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        $this->subject = new NoAbstractServiceAliasLogicalConstraint();
    }

    public function testViolationsForNoInterfaceServiceAlias() {
        $containerDefinition = $this->containerDefinitionCompiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias')->build()
        );
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The abstract, Cspray\\AnnotatedContainer\\Unit\\LogicalErrorApps\\NoInterfaceServiceAlias\\FooInterface, does not have an alias. Create a concrete class that implements this type and annotate it with a #[Service] Attribute.',
            $violations->get(0)->getMessage()
        );
        $this->assertSame(
            LogicalConstraintViolationType::Warning,
            $violations->get(0)->getViolationType()
        );
    }

    public function testViolationsForNoAbstractServiceAlias() {
        $containerDefinition = $this->containerDefinitionCompiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(dirname(__DIR__) . '/LogicalErrorApps/NoAbstractServiceAlias')->build()
        );
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The abstract, Cspray\\AnnotatedContainer\\Unit\\LogicalErrorApps\\NoAbstractServiceAlias\\AbstractFoo, does not have an alias. Create a concrete class that implements this type and annotate it with a #[Service] Attribute.',
            $violations->get(0)->getMessage()
        );
        $this->assertSame(
            LogicalConstraintViolationType::Warning,
            $violations->get(0)->getViolationType()
        );
    }

    public function testNoViolationsForInterfaceWithServiceAlias() {
        $containerDefinition = $this->containerDefinitionCompiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())->build()
        );
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(0, $violations);
    }

}