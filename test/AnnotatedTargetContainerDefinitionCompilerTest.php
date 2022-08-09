<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainer\Helper\StubContextConsumer;
use Cspray\AnnotatedContainer\Helper\TestLogger;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class AnnotatedTargetContainerDefinitionCompilerTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private AnnotatedTargetContainerDefinitionCompiler $subject;
    private TestLogger $logger;

    public function setUp() : void {
        $this->logger = new TestLogger();
        $this->subject = new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter($this->logger)
        );
    }

    private function runCompileDirectory(
        array|string $dir,
        ContainerDefinitionBuilderContextConsumer $consumer = null
    ) : ContainerDefinition {
        if (is_string($dir)) {
            $dir = [$dir];
        }
        $options = ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$dir)
            ->withLogger($this->logger);

        if ($consumer !== null) {
            $options = $options->withContainerDefinitionBuilderContextConsumer($consumer);
        }

        return $this->subject->compile($options->build());
    }

    public function testEmptyScanDirectoriesThrowsException() : void {
        $this->expectException(InvalidCompileOptionsException::class);
        $this->expectExceptionMessage('The ContainerDefinitionCompileOptions passed to ' . AnnotatedTargetContainerDefinitionCompiler::class . ' must include at least 1 directory to scan, but none were provided.');
        $this->runCompileDirectory([]);
    }

    public function testLogEmptyScanDirectories() : void {
        try {
            $this->runCompileDirectory([]);
        } catch (InvalidCompileOptionsException $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'The ContainerDefinitionCompileOptions passed to ' . AnnotatedTargetContainerDefinitionCompiler::class . ' must include at least 1 directory to scan, but none were provided.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testServicePrepareNotOnServiceThrowsException() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage(sprintf(
            'The class %s is not marked as a #[Service] but has a #[ServicePrepare] Attribute on the method "postConstruct".',
            LogicalErrorApps\ServicePrepareNotService\FooImplementation::class
        ));
        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
    }

    public function testLogServicePrepareNotOnService() : void {
        try {
            $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
        }  catch (InvalidAnnotationException $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => sprintf(
                    'The class %s is not marked as a #[Service] but has a #[ServicePrepare] Attribute on the method "postConstruct".',
                    LogicalErrorApps\ServicePrepareNotService\FooImplementation::class
                ),
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
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

    public function testLogScanDuplicateDirectories() : void {
        try {
            $this->runCompileDirectory([
                Fixtures::singleConcreteService()->getPath(),
                Fixtures::singleConcreteService()->getPath(),
                Fixtures::configurationServices()->getPath()
            ]);
        } catch (InvalidCompileOptionsException $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'The ContainerDefinitionCompileOptions passed to ' . AnnotatedTargetContainerDefinitionCompiler::class . ' includes duplicate directories. Please pass a distinct set of directories to scan.',
                'context' => [
                    'sourcePaths' => [
                        Fixtures::singleConcreteService()->getPath(),
                        Fixtures::singleConcreteService()->getPath(),
                        Fixtures::configurationServices()->getPath()
                    ]
                ]
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testImplicitServiceDelegateHasNoReturnType() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateNoType\FooFactory::class . '::create does not declare a service in the Attribute or as a return type of the method.'
        );

        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateNoType');
    }

    public function testLogImplicitServiceDelegateHasNoReturnType() : void {
        try {
            $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateNoType');
        }  catch (InvalidAnnotationException $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateNoType\FooFactory::class . '::create does not declare a service in the Attribute or as a return type of the method.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testImplicitServiceDelegateHasScalarReturnType() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateScalarType\FooFactory::class . '::create declares a scalar value as a service type.'
        );

        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateScalarType');
    }

    public function testLogImplicitServiceDelegateHasScalarReturnType() : void {
        try {
            $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateScalarType');
        } catch (InvalidAnnotationException $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateScalarType\FooFactory::class . '::create declares a scalar value as a service type.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testImplicitServiceDelegateHasIntersectionReturnType() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateIntersectionType\FooFactory::class . '::create declares an unsupported intersection as a service type.'
        );

        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateIntersectionType');
    }

    public function testLogImplicitServiceDelegateHasIntersectionReturnType() : void {
        try {
            $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateIntersectionType');
        } catch (InvalidAnnotationException $exception) {
            // noop we expect this
        } finally {
            $expected = [
                'message' => 'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateIntersectionType\FooFactory::class . '::create declares an unsupported intersection as a service type.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testImplicitServiceDelegateHasUnionReturnType() {
        $this->expectException(InvalidAnnotationException::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateUnionType\FooFactory::class . '::create declares an unsupported union as a service type.'
        );

        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateUnionType');
    }

    public function testLogImplicitServiceDelegateHasUnionReturnType() {
        try {
            $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateUnionType');
        } catch (InvalidAnnotationException $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateUnionType\FooFactory::class . '::create declares an unsupported union as a service type.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testLoggingScannedDirs() : void {
        $this->runCompileDirectory([
            $path1 = Fixtures::singleConcreteService()->getPath(),
            $path2 = Fixtures::ambiguousAliasedServices()->getPath()
        ]);

        $expected = [
            'message' => sprintf('Annotated Container compiling started. Scanning directories: %s %s', $path1, $path2),
            'context' => [
                'sourcePaths' => [$path1, $path2]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingServiceDefinition() : void {
        $this->runCompileDirectory(Fixtures::singleConcreteService()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed ServiceDefinition from #[Service] Attribute on %s.',
                Fixtures::singleConcreteService()->fooImplementation()->getName()
            ),
            'context' => [
                'attribute' => Service::class,
                'target' => [
                    'class' => Fixtures::singleConcreteService()->fooImplementation()->getName(),
                ],
                'definition' => [
                    'type' => ServiceDefinition::class,
                    'serviceType' => Fixtures::singleConcreteService()->fooImplementation()->getName(),
                    'name' => null,
                    'profiles' => ['default'],
                    'isPrimary' => false,
                    'isConcrete' => true,
                    'isAbstract' => false
                ]
            ]
        ];
        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingServiceDelegateTarget() : void {
        $this->runCompileDirectory(Fixtures::delegatedService()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed ServiceDelegateDefinition from #[ServiceDelegate] Attribute on %s::%s.',
                Fixtures::delegatedService()->serviceFactory()->getName(),
                'createService'
            ),
            'context' => [
                'attribute' => ServiceDelegate::class,
                'target' => [
                    'class' => Fixtures::delegatedService()->serviceFactory()->getName(),
                    'method' => 'createService',
                ],
                'definition' => [
                    'type' => ServiceDelegateDefinition::class,
                    'serviceType' => Fixtures::delegatedService()->serviceInterface()->getName(),
                    'delegateType' => Fixtures::delegatedService()->serviceFactory()->getName(),
                    'delegateMethod' => 'createService'
                ]
            ]
        ];
        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingServicePrepareTarget() : void {
        $this->runCompileDirectory(Fixtures::classOnlyPrepareServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed ServicePrepareDefinition from #[ServicePrepare] Attribute on %s::%s.',
                Fixtures::classOnlyPrepareServices()->fooImplementation()->getName(),
                'setBar'
            ),
            'context' => [
                'attribute' => ServicePrepare::class,
                'target' => [
                    'class' => Fixtures::classOnlyPrepareServices()->fooImplementation()->getName(),
                    'method' => 'setBar'
                ],
                'definition' => [
                    'type' => ServicePrepareDefinition::class,
                    'serviceType' => Fixtures::classOnlyPrepareServices()->fooImplementation()->getName(),
                    'prepareMethod' => 'setBar'
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingConfigurationTarget() : void {
        $this->runCompileDirectory(Fixtures::configurationServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed ConfigurationDefinition from #[Configuration] Attribute on %s.',
                Fixtures::configurationServices()->myConfig()->getName()
            ),
            'context' => [
                'attribute' => Configuration::class,
                'target' => [
                    'class' => Fixtures::configurationServices()->myConfig()->getName(),
                ],
                'definition' => [
                    'type' => ConfigurationDefinition::class,
                    'configurationType' => Fixtures::configurationServices()->myConfig()->getName(),
                    'name' => null
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectMethodParameter() : void {
        $this->runCompileDirectory(Fixtures::injectConstructorServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed InjectDefinition from #[Inject] Attribute on %s::%s(%s).',
                Fixtures::injectConstructorServices()->injectStringService()->getName(),
                '__construct',
                'val'
            ),
            'context' => [
                'attribute' => Inject::class,
                'target' => [
                    'class' => Fixtures::injectConstructorServices()->injectStringService()->getName(),
                    'method' => '__construct',
                    'parameter' => 'val'
                ],
                'definition' => [
                    'type' => InjectDefinition::class,
                    'serviceType' => Fixtures::injectConstructorServices()->injectStringService()->getName(),
                    'method' => '__construct',
                    'parameterType' => 'string',
                    'parameter' => 'val',
                    'value' => 'foobar',
                    'store' => null,
                    'profiles' => ['default']
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectConfigurationProperty() : void {
        $this->runCompileDirectory(Fixtures::configurationServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed InjectDefinition from #[Inject] Attribute on %s::%s.',
                Fixtures::configurationServices()->myConfig()->getName(),
                'user'
            ),
            'context' => [
                'attribute' => Inject::class,
                'target' => [
                    'class' => Fixtures::configurationServices()->myConfig()->getName(),
                    'property' => 'user'
                ],
                'definition' => [
                    'type' => InjectDefinition::class,
                    'serviceType' => Fixtures::configurationServices()->myConfig()->getName(),
                    'property' => 'user',
                    'propertyType' => 'string',
                    'value' => 'USER',
                    'store' => 'env',
                    'profiles' => ['default']
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingAddAliasDefinitions() : void {
        $this->runCompileDirectory(Fixtures::implicitAliasedServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Added alias for abstract service %s to concrete service %s.',
                Fixtures::implicitAliasedServices()->fooInterface()->getName(),
                Fixtures::implicitAliasedServices()->fooImplementation()->getName()
            ),
            'context' => [
                'abstractService' => Fixtures::implicitAliasedServices()->fooInterface()->getName(),
                'concreteService' => Fixtures::implicitAliasedServices()->fooImplementation()->getName()
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingNoThirdPartyServices() : void {
        $this->runCompileDirectory(Fixtures::singleConcreteService()->getPath());

        $expected = [
            'message' => sprintf(
                'No %s was provided.',
                ContainerDefinitionBuilderContextConsumer::class
            ),
            'context' => []
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingThirdPartyServices() : void {
        $this->runCompileDirectory(
            Fixtures::singleConcreteService()->getPath(),
            new StubContextConsumer()
        );

        $expected = [
            'message' => sprintf(
                'Added services from %s to ContainerDefinition.',
                StubContextConsumer::class
            ),
            'context' => [
                'containerDefinitionBuilderConsumer' => StubContextConsumer::class
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLastLoggingMessageIsCompilingFinished() : void {
        $this->runCompileDirectory(
            Fixtures::singleConcreteService()->getPath(),
        );

        $expected = [
            'message' => 'Annotated Container compiling finished.',
            'context' => []
        ];

        $logs = $this->logger->getLogsForLevel(LogLevel::INFO);

        self::assertEquals(
            $expected,
            $logs[count($logs) - 1]
        );
    }

    public function testImplementServiceDelegateNotServiceThrowsException() : void {
        $message = sprintf(
            'The #[ServiceDelegate] Attribute on %s::create declares a type, %s, that is not a service.',
            LogicalErrorApps\ServiceDelegateNotService\ServiceFactory::class,
            LogicalErrorApps\ServiceDelegateNotService\FooService::class
        );
        self::expectException(InvalidAnnotationException::class);
        self::expectExceptionMessage($message);

        $this->runCompileDirectory(__DIR__ . '/LogicalErrorApps/ServiceDelegateNotService');
    }
}