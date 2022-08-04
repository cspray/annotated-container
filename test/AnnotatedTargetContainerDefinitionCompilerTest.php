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
        $this->expectException(InvalidCompileOptionsException::class);
        $this->expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . AnnotatedTargetContainerDefinitionCompiler::class . ' must include at least 1 directory to scan, but none were provided.');
        $this->runCompileDirectory([]);
    }

    public function testServicePrepareNotOnServiceThrowsException() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage('The #[ServicePrepare] Attribute on ' . LogicalErrorApps\ServicePrepareNotService\FooImplementation::class . '::postConstruct is not on a type marked as a #[Service].');;
        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
    }

    public function testDuplicateScanDirectoriesThrowsException() {
        $this->expectException(InvalidCompileOptionsException::class);
        $this->expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . AnnotatedTargetContainerDefinitionCompiler::class . ' includes duplicate directories. Please pass a distinct set of directories to scan.');
        $this->runCompileDirectory([
            Fixtures::singleConcreteService()->getPath(),
            Fixtures::ambiguousAliasedServices()->getPath(),
            Fixtures::singleConcreteService()->getPath()
        ]);
    }

    public function testImplicitServiceDelegateHasNoReturnType() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateNoType\FooFactory::class . '::create does not declare a service in the Attribute or as a return type of the method.'
        );

        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateNoType');
    }

    public function testImplicitServiceDelegateHasScalarReturnType() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateScalarType\FooFactory::class . '::create declares a scalar value as a service type.'
        );

        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateScalarType');
    }

    public function testImplicitServiceDelegateHasIntersectionReturnType() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateIntersectionType\FooFactory::class . '::create declares an unsupported intersection as a service type.'
        );

        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateIntersectionType');
    }

    public function testImplicitServiceDelegateHasUnionReturnType() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateUnionType\FooFactory::class . '::create declares an unsupported union as a service type.'
        );

        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateUnionType');
    }
}