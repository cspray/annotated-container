<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
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
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(dirname(__DIR__) . '/LogicalErrorApps/NoInterfaceServiceAlias')->withProfiles('default')->build()
        );
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The abstract, Cspray\\AnnotatedContainer\\LogicalErrorApps\\NoInterfaceServiceAlias\\FooInterface, does not have an alias. Create a concrete class that implements this type and annotate it with a #[Service] Attribute.',
            $violations->get(0)->getMessage()
        );
        $this->assertSame(
            LogicalConstraintViolationType::Warning,
            $violations->get(0)->getViolationType()
        );
    }

    public function testViolationsForNoAbstractServiceAlias() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(dirname(__DIR__) . '/LogicalErrorApps/NoAbstractServiceAlias')->withProfiles('default')->build()
        );
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The abstract, Cspray\\AnnotatedContainer\\LogicalErrorApps\\NoAbstractServiceAlias\\AbstractFoo, does not have an alias. Create a concrete class that implements this type and annotate it with a #[Service] Attribute.',
            $violations->get(0)->getMessage()
        );
        $this->assertSame(
            LogicalConstraintViolationType::Warning,
            $violations->get(0)->getViolationType()
        );
    }

    public function testNoViolationsForInterfaceWithServiceAlias() {
        $containerDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')->withProfiles('default')->build()
        );
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(0, $violations);
    }

}