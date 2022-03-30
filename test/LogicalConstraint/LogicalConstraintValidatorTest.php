<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
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
            DummyAppUtils::getRootDir() . '/MultipleAliasResolution',
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