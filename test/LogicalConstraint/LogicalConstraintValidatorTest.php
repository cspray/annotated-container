<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\LogicalErrorApps\NoInterfaceServiceAlias;
use Cspray\AnnotatedContainer\StaticAnalysisAnnotatedTargetParser;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;

class LogicalConstraintValidatorTest extends TestCase {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private LogicalConstraintValidator $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new AnnotatedTargetContainerDefinitionCompiler(
            new StaticAnalysisAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        $this->subject = new LogicalConstraintValidator();
    }

    public function testValidatorRunsAllConstraints() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(
            Fixtures::ambiguousAliasedServices()->getPath(),
            dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias'
        )->build());
        $violations = $this->subject->validate($containerDefinition);

        $this->assertCount(2, $violations);
    }

    public function testValidatorHasCorrectViolationMessages() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(
            dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias'
        )->build());
        $violations = $this->subject->validate($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame('The abstract, ' . NoInterfaceServiceAlias\FooInterface::class . ', does not have an alias. Create a concrete class that implements this type and annotate it with a #[Service] Attribute.', $violations->get(0)->getMessage());
        $this->assertSame(LogicalConstraintViolationType::Warning, $violations->get(0)->getViolationType());
    }


}