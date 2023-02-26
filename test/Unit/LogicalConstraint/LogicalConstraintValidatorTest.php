<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint;

use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintValidator;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

class LogicalConstraintValidatorTest extends TestCase {

    private ContainerDefinitionAnalyzer $containerDefinitionCompiler;
    private LogicalConstraintValidator $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
        );
        $this->subject = new LogicalConstraintValidator();
    }

    public function testValidatorRunsAllConstraints() {
        $containerDefinition = $this->containerDefinitionCompiler->analyze(ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            Fixtures::ambiguousAliasedServices()->getPath(),
            dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias'
        )->build());
        $violations = $this->subject->validate($containerDefinition);

        $this->assertCount(2, $violations);
    }

    public function testValidatorHasCorrectViolationMessages() {
        $containerDefinition = $this->containerDefinitionCompiler->analyze(ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias'
        )->build());
        $violations = $this->subject->validate($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame('The abstract, ' . \Cspray\AnnotatedContainer\Unit\LogicalErrorApps\NoInterfaceServiceAlias\FooInterface::class . ', does not have an alias. Create a concrete class that implements this type and annotate it with a #[Service] Attribute.', $violations->get(0)->getMessage());
        $this->assertSame(LogicalConstraintViolationType::Warning, $violations->get(0)->getViolationType());
    }


}