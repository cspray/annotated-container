<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\LogicalErrorApps\NoInterfaceServiceAlias;
use PHPUnit\Framework\TestCase;

class LogicalConstraintValidatorTest extends TestCase {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private LogicalConstraintValidator $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new PhpParserContainerDefinitionCompiler();
        $this->subject = new LogicalConstraintValidator();
    }

    public function testValidatorRunsAllConstraints() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(
            dirname(__DIR__) . '/DummyApps/MultipleAliasResolution',
            dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias',
            dirname(__DIR__) . '/LogicalErrorApps/ServicePrepareNotService'
        )->withProfiles('default')->build());
        $violations = $this->subject->validate($containerDefinition);

        $this->assertCount(3, $violations);
    }

    public function testValidatorHasCorrectViolationMessages() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(
            dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias'
        )->withProfiles('default')->build());
        $violations = $this->subject->validate($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame('The interface, ' . NoInterfaceServiceAlias\FooInterface::class . ', does not have an alias. Create a concrete class that implements this interface and annotate it with a #[Service] Attribute.', $violations->get(0)->getMessage());
        $this->assertSame(LogicalConstraintViolationType::Warning, $violations->get(0)->getViolationType());
    }


}