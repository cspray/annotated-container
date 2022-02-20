<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\DummyApps;
use Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler;
use PHPUnit\Framework\TestCase;

class MultipleAliasResolutionLogicalConstraintTest extends TestCase {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private MultipleAliasResolutionLogicalConstraint $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new PhpParserContainerDefinitionCompiler();
        $this->subject = new MultipleAliasResolutionLogicalConstraint();
    }

    public function testMultipleAliasResolvedHasWarning() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(dirname(__DIR__) . '/DummyApps/MultipleAliasResolution')->withProfiles('default')->build()
        );

        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame('Multiple aliases were found for ' . DummyApps\MultipleAliasResolution\FooInterface::class . '. This may be a fatal error at runtime.', $violations->get(0)->getMessage());
        $this->assertSame(LogicalConstraintViolationType::Notice, $violations->get(0)->getViolationType());
    }

    public function testNoAliasResolvedHasNoViolations() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias')->build()
        );

        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(0, $violations);
    }

    public function testSingleAliasResolvedHasNoViolations() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(dirname(__DIR__) . '/DummyApps/SimpleServices')->build()
        );

        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(0, $violations);
    }
}