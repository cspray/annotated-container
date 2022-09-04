<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\Compile\AnnotatedTargetContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Compile\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

class MultipleAliasResolutionLogicalConstraintTest extends TestCase {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private MultipleAliasResolutionLogicalConstraint $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        $this->subject = new MultipleAliasResolutionLogicalConstraint();
    }

    public function testMultipleAliasResolvedHasWarning() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::ambiguousAliasedServices()->getPath())->build()
        );

        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame('Multiple aliases were found for ' . Fixtures::ambiguousAliasedServices()->fooInterface()->getName() . '. This may be a fatal error at runtime.', $violations->get(0)->getMessage());
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
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())->build()
        );

        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(0, $violations);
    }
}