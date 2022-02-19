<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\LogicalErrorApps;
use Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler;
use PHPUnit\Framework\TestCase;

class ServicePrepareRequiresServiceLogicalConstraintTest extends TestCase {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private ServicePrepareRequiresServiceLogicalConstraint $subject;

    protected function setUp(): void {
        $this->containerDefinitionCompiler = new PhpParserContainerDefinitionCompiler();
        $this->subject = new ServicePrepareRequiresServiceLogicalConstraint();
    }

    public function testServicePrepareNotOnServiceViolation() {
        $containerDefinition = $this->containerDefinitionCompiler->compileDirectory('test', dirname(__DIR__) . '/LogicalErrorApps/ServicePrepareNotService');
        $violations = $this->subject->getConstraintViolations($containerDefinition);

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The method ' . LogicalErrorApps\ServicePrepareNotService\FooImplementation::class . '::postConstruct() is marked as a #[ServicePrepare] but the type is not a #[Service].',
            $violations->get(0)->getMessage()
        );
        $this->assertSame(LogicalConstraintViolationType::Warning, $violations->get(0)->getViolationType());
    }

}