<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

class AnnotatedTargetContainerDefinitionCompilerTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private AnnotatedTargetContainerDefinitionCompiler $subject;

    public function setUp() : void {
        $this->subject = new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
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
        self::expectException(InvalidCompileOptionsException::class);
        self::expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . AnnotatedTargetContainerDefinitionCompiler::class . ' must include at least 1 directory to scan, but none were provided.');
        $this->runCompileDirectory([]);
    }

    public function testServicePrepareNotOnServiceThrowsException() {
        self::expectException(InvalidAnnotationException::class);
        self::expectExceptionMessage('The #[ServicePrepare] Attribute on ' . LogicalErrorApps\ServicePrepareNotService\FooImplementation::class . '::postConstruct is not on a type marked as a #[Service].');;
        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
    }

    public function testDuplicateScanDirectoriesThrowsException() {
        self::expectException(InvalidCompileOptionsException::class);
        self::expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . AnnotatedTargetContainerDefinitionCompiler::class . ' includes duplicate directories. Please pass a distinct set of directories to scan.');
        $this->runCompileDirectory([
            Fixtures::singleConcreteService()->getPath(),
            Fixtures::ambiguousAliasedServices()->getPath(),
            Fixtures::singleConcreteService()->getPath()
        ]);
    }

}