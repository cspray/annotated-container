<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\InvalidScanDirectories;
use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegate;
use Cspray\AnnotatedContainer\Exception\InvalidServicePrepare;
use Cspray\AnnotatedContainer\Unit\Helper\AnotherDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\TestLogger;
use Cspray\AnnotatedContainerFixture\ConfigurationWithArrayEnum\FooEnum;
use Cspray\AnnotatedContainerFixture\ConfigurationWithEnum\MyEnum;
use Cspray\AnnotatedContainerFixture\CustomServiceAttribute\Repository;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class AnnotatedTargetContainerDefinitionAnalyzerTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private AnnotatedTargetContainerDefinitionAnalyzer $subject;

    public function setUp() : void {
        $this->subject = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
        );
    }

    private function runAnalysisDirectory(
        array|string $dir,
        DefinitionProvider $consumer = null
    ) : ContainerDefinition {
        if (is_string($dir)) {
            $dir = [$dir];
        }
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(...$dir);

        if ($consumer !== null) {
            $options = $options->withDefinitionProvider($consumer);
        }

        return $this->subject->analyze($options->build());
    }

    public function testEmptyScanDirectoriesThrowsException() : void {
        $this->expectException(InvalidScanDirectories::class);
        $this->expectExceptionMessage('ContainerDefinitionAnalysisOptions must include at least 1 directory to scan, but none were provided.');
        $this->runAnalysisDirectory([]);
    }

    public function testServicePrepareNotOnServiceThrowsException() {
        $this->expectException(InvalidServicePrepare::class);
        $this->expectExceptionMessage(sprintf(
            'Service preparation defined on %s::postConstruct, but that class is not a service.',
            LogicalErrorApps\ServicePrepareNotService\FooImplementation::class
        ));
        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
    }

    public function testDuplicateScanDirectoriesThrowsException() {
        $this->expectException(InvalidScanDirectories::class);
        $this->expectExceptionMessage('ContainerDefinitionAnalysisOptions includes duplicate scan directories. Please pass a distinct set of directories to scan.');
        $this->runAnalysisDirectory([
            Fixtures::singleConcreteService()->getPath(),
            Fixtures::ambiguousAliasedServices()->getPath(),
            Fixtures::singleConcreteService()->getPath()
        ]);
    }

    public function testImplicitServiceDelegateHasNoReturnType() {
        $this->expectException(InvalidServiceDelegate::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateNoType\FooFactory::class . '::create does not declare a service in the Attribute or as a return type of the method.'
        );

        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateNoType');
    }

    public function testImplicitServiceDelegateHasScalarReturnType() {
        $this->expectException(InvalidServiceDelegate::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateScalarType\FooFactory::class . '::create declares a scalar value as a service type.'
        );

        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateScalarType');
    }

    public function testImplicitServiceDelegateHasIntersectionReturnType() {
        $this->expectException(InvalidServiceDelegate::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateIntersectionType\FooFactory::class . '::create declares an unsupported intersection as a service type.'
        );

        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateIntersectionType');
    }

    public function testImplicitServiceDelegateHasUnionReturnType() {
        $this->expectException(InvalidServiceDelegate::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateUnionType\FooFactory::class . '::create declares an unsupported union as a service type.'
        );

        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateUnionType');
    }

    public function testServiceDelegateNotServiceThrowsException() : void {
        $message = sprintf(
            'Service delegation defined on %s::create declares a type, %s, that is not a service.',
            LogicalErrorApps\ServiceDelegateNotService\ServiceFactory::class,
            LogicalErrorApps\ServiceDelegateNotService\FooService::class
        );
        self::expectException(InvalidServiceDelegate::class);
        self::expectExceptionMessage($message);

        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ServiceDelegateNotService');
    }

}
