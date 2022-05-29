<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use PHPUnit\Framework\TestCase;

class AnnotatedTargetContainerDefinitionCompilerTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private AnnotatedTargetContainerDefinitionCompiler $subject;

    public function setUp() : void {
        $this->subject = new AnnotatedTargetContainerDefinitionCompiler(
            new StaticAnalysisAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
    }

    private function runCompileDirectory(array|string $dir) : ContainerDefinition {
        if (is_string($dir)) {
            $dir = [$dir];
        }
        return $this->subject->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$dir)->build());
    }

    public function testEmptyScanDirectoriesThrowsException() {
        $this->expectException(InvalidCompileOptionsException::class);
        $this->expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . AnnotatedTargetContainerDefinitionCompiler::class . ' must include at least 1 directory to scan, but none were provided.');
        $this->runCompileDirectory([]);
    }

    public function testServicePrepareNotOnServiceThrowsException() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage('The #[ServicePrepare] Attribute on ' . LogicalErrorApps\ServicePrepareNotService\FooImplementation::class . '::postConstruct is not on a type marked as a #[Service].');;
        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
    }

}