<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler;
use PHPUnit\Framework\TestCase;

class NoAbstractServiceAliasLogicalConstraintTest extends TestCase {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private NoAbstractServiceAliasLogicalConstraint $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new PhpParserContainerDefinitionCompiler();
        $this->subject = new NoAbstractServiceAliasLogicalConstraint();
    }

    public function testViolationsForNoInterfaceServiceAlias() {
        $containerDefinition = $this->containerDefinitionCompiler->compileDirectory('test', dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias');
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The interface, Cspray\\AnnotatedContainer\\LogicalErrorApps\\NoInterfaceServiceAlias\\FooInterface, does not have an alias. Create a concrete class that implements this interface and annotate it with a #[Service] Attribute.',
            $violations->get(0)->getMessage()
        );
        $this->assertSame(
            LogicalConstraintViolationType::Warning,
            $violations->get(0)->getViolationType()
        );
    }

    public function testViolationsForNoAbstractServiceAlias() {
        $containerDefinition = $this->containerDefinitionCompiler->compileDirectory('test', dirname(__DIR__) . '/LogicalErrorApps/NoAbstractServiceAlias');
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The abstract class, Cspray\\AnnotatedContainer\\LogicalErrorApps\\NoAbstractServiceAlias\\AbstractFoo, does not have an alias. Create a concrete class that extends this abstract class and annotate it with a #[Service] Attribute.',
            $violations->get(0)->getMessage()
        );
        $this->assertSame(
            LogicalConstraintViolationType::Warning,
            $violations->get(0)->getViolationType()
        );
    }

    public function testNoViolationsForInterfaceWithServiceAlias() {
        $containerDefinition = $this->containerDefinitionCompiler->compileDirectory('test', dirname(__DIR__) . '/DummyApps/SimpleServices');
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(0, $violations);
    }

}