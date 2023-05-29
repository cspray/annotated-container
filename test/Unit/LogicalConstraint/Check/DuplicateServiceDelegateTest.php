<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\LogicalConstraintFixtures;

final class DuplicateServiceDelegateTest extends LogicalConstraintTestCase {

    private ContainerDefinitionAnalyzer $analyzer;

    private DuplicateServiceDelegate $subject;

    protected function setUp() : void {
        $this->analyzer = $this->getAnalyzer();
        $this->subject = new DuplicateServiceDelegate();
    }

    public function testNoDuplicateDelegateHasNoViolations() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::implicitServiceDelegateType()->getPath()
            )->build()
        );

        $violations = $this->subject->getConstraintViolations($definition, ['default']);

        self::assertCount(0, $violations);
    }

    public function testDuplicateDelegateAttributeForSameServiceHasCorrectViolation() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                LogicalConstraintFixtures::duplicateServiceDelegate()->getPath()
            )->build()
        );

        $violations = $this->subject->getConstraintViolations($definition, ['default']);

        self::assertCount(1, $violations);

        $violation = $violations->get(0);
        $fooService = LogicalConstraintFixtures::duplicateServiceDelegate()->fooService()->getName();
        $factory = LogicalConstraintFixtures::duplicateServiceDelegate()->factory()->getName();
        $serviceDelegate = ServiceDelegate::class;

        $expected = <<<TEXT
There are multiple delegates for the service "$fooService"!

- $factory::createFoo attributed with $serviceDelegate
- $factory::createFooAgain attributed with $serviceDelegate

This will result in undefined behavior, determined by the backing container, and 
should be avoided.
TEXT;

        self::assertSame(LogicalConstraintViolationType::Warning, $violation->violationType);
        self::assertSame($expected, $violation->message);
    }

    public function testDuplicateDelegateAddedWithFunctionalApi() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::implicitServiceDelegateType()->getPath()
            )->withDefinitionProvider(
                new class implements DefinitionProvider {

                    public function consume(DefinitionProviderContext $context) : void {
                        \Cspray\AnnotatedContainer\serviceDelegate(
                            $context,
                            Fixtures::implicitServiceDelegateType()->fooService(),
                            Fixtures::implicitServiceDelegateType()->fooServiceFactory(),
                            'create'
                        );
                    }
                }
            )->build()
        );

        $violations = $this->subject->getConstraintViolations($definition, ['default']);

        self::assertCount(1, $violations);

        $violation = $violations->get(0);

        $fooService = Fixtures::implicitServiceDelegateType()->fooService()->getName();
        $factory = Fixtures::implicitServiceDelegateType()->fooServiceFactory()->getName();
        $serviceDelegate = ServiceDelegate::class;

        $expected = <<<TEXT
There are multiple delegates for the service "$fooService"!

- $factory::create added with serviceDelegate()
- $factory::create attributed with $serviceDelegate

This will result in undefined behavior, determined by the backing container, and 
should be avoided.
TEXT;

        self::assertSame(LogicalConstraintViolationType::Warning, $violation->violationType);
        self::assertSame($expected, $violation->message);
    }

}