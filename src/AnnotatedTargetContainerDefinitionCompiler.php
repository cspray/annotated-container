<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\AnnotatedTarget\AnnotatedTargetParser;
use Cspray\AnnotatedTarget\AnnotatedTargetParserOptionsBuilder;
use Cspray\Typiphy\ObjectType;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use stdClass;
use function Cspray\Typiphy\objectType;

/**
 * A ContainerDefinitionCompiler that utilizes the AnnotatedTarget concept by parsing given source code directories and
 * converting any found targets into the appropriate definition object.
 */
final class AnnotatedTargetContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly AnnotatedTargetParser $annotatedTargetCompiler,
        private readonly AnnotatedTargetDefinitionConverter $definitionConverter,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Will parse source code, according to the passed $containerDefinitionCompileOptions, and construct a ContainerDefinition
     * instance based off of the resultant parsing.
     *
     * @param ContainerDefinitionCompileOptions $containerDefinitionCompileOptions
     * @return ContainerDefinition
     * @throws InvalidCompileOptionsException|InvalidAnnotationException
     */
    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition {
        $scanDirs = $containerDefinitionCompileOptions->getScanDirectories();
        if (empty($scanDirs)) {
            throw new InvalidCompileOptionsException(sprintf(
                'The ContainerDefinitionCompileOptions passed to %s must include at least 1 directory to scan, but none were provided.',
                self::class
            ));
        }

        if (count(array_unique($scanDirs)) !== count($scanDirs)) {
            throw new InvalidCompileOptionsException(sprintf(
                'The ContainerDefinitionCompileOptions passed to %s includes duplicate directories. Please pass a distinct set of directories to scan.',
                self::class
            ));
        }

        $containerDefinitionBuilder = ContainerDefinitionBuilder::newDefinition();
        $consumer = $this->parse($containerDefinitionCompileOptions);
        $containerDefinitionBuilder = $this->addAnnotatedDefinitions($containerDefinitionBuilder, $consumer);
        $containerDefinitionBuilder = $this->addThirdPartyServices($containerDefinitionCompileOptions, $containerDefinitionBuilder);
        $containerDefinitionBuilder = $this->addAliasDefinitions($containerDefinitionBuilder);

        return $containerDefinitionBuilder->build();
    }

    private function parse(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : stdClass {
        $consumer = new stdClass();
        $consumer->serviceDefinitions = [];
        $consumer->servicePrepareDefinitions = [];
        $consumer->serviceDelegateDefinitions = [];
        $consumer->injectDefinitions = [];
        $consumer->configurationDefinitions = [];
        $attributeTypes = array_map(fn(AttributeType $attributeType) => objectType($attributeType->value), AttributeType::cases());
        $dirs = $containerDefinitionCompileOptions->getScanDirectories();
        $options = AnnotatedTargetParserOptionsBuilder::scanDirectories(...$dirs)
            ->filterAttributes(...$attributeTypes)
            ->build();

        $this->logger->info(
            sprintf('Scanning directories: %s', implode(' ', $dirs)),
            ['sourcePaths' => $dirs]
        );

        /** @var AnnotatedTarget $target */
        foreach ($this->annotatedTargetCompiler->parse($options) as $target) {
            $definition = $this->definitionConverter->convert($target);

            if ($definition instanceof ServiceDefinition) {
                $consumer->serviceDefinitions[] = $definition;
                $this->logServiceDefinition($target, $definition);
            } else if ($definition instanceof ServicePrepareDefinition) {
                $consumer->servicePrepareDefinitions[] = $definition;
                $this->logServicePrepareDefinition($target, $definition);
            } else if ($definition instanceof ServiceDelegateDefinition) {
                $consumer->serviceDelegateDefinitions[] = $definition;
                $this->logServiceDelegateDefinition($target, $definition);
            } else if ($definition instanceof InjectDefinition) {
                $consumer->injectDefinitions[] = $definition;
                if ($definition->getTargetIdentifier()->isMethodParameter()) {
                    $this->logParameterInjectDefinition($target, $definition);
                } else {
                    $this->logPropertyInjectDefinition($target, $definition);
                }
            } else if ($definition instanceof ConfigurationDefinition) {
                $consumer->configurationDefinitions[] = $definition;
                $this->logConfigurationDefinition($target, $definition);
            }
        }
        return $consumer;
    }

    private function logServiceDefinition(AnnotatedTarget $target, ServiceDefinition $definition) : void {
        $this->logger->info(
            sprintf('Parsed ServiceDefinition from #[Service] Attribute on %s.', $definition->getType()->getName()),
            [
                'attribute' => AttributeType::Service->value,
                'target' => [
                    'class' => $target->getTargetReflection()->getName()
                ],
                'definition' => [
                    'type' => ServiceDefinition::class,
                    'serviceType' => $definition->getType()->getName(),
                    'name' => $definition->getName(),
                    'profiles' => $definition->getProfiles(),
                    'isPrimary' => $definition->isPrimary(),
                    'isConcrete' => $definition->isConcrete(),
                    'isAbstract' => $definition->isAbstract()
                ]
            ]
        );
    }

    private function logServicePrepareDefinition(AnnotatedTarget $target, ServicePrepareDefinition $definition) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionMethod);
        $this->logger->info(
            sprintf('Parsed ServicePrepareDefinition from #[ServicePrepare] Attribute on %s::%s.', $definition->getService()->getName(), $definition->getMethod()),
            [
                'attribute' => AttributeType::ServicePrepare->value,
                'target' => [
                    'class' => $targetReflection->getDeclaringClass()->getName(),
                    'method' => $targetReflection->getName()
                ],
                'definition' => [
                    'type' => ServicePrepareDefinition::class,
                    'serviceType' => $definition->getService()->getName(),
                    'prepareMethod' => $definition->getMethod()
                ]
            ]
        );
    }

    private function logServiceDelegateDefinition(AnnotatedTarget $target, ServiceDelegateDefinition $definition) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionMethod);
        $this->logger->info(
            sprintf(
                'Parsed ServiceDelegateDefinition from #[ServiceDelegate] Attribute on %s::%s.',
                $targetReflection->getDeclaringClass()->getName(),
                $target->getTargetReflection()->getName()
            ),
            [
                'attribute' => AttributeType::ServiceDelegate->value,
                'target' => [
                    'class' => $targetReflection->getDeclaringClass()->getName(),
                    'method' => $target->getTargetReflection()->getName()
                ],
                'definition' => [
                    'type' => ServiceDelegateDefinition::class,
                    'serviceType' => $definition->getServiceType()->getName(),
                    'delegateType' => $definition->getDelegateType()->getName(),
                    'delegateMethod' => $definition->getDelegateMethod()
                ]
            ]
        );
    }

    private function logParameterInjectDefinition(AnnotatedTarget $target, InjectDefinition $definition) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionParameter);
        $declaringClass = $targetReflection->getDeclaringClass();
        assert($declaringClass instanceof ReflectionClass);
        $this->logger->info(
            sprintf(
                'Parsed InjectDefinition from #[Inject] Attribute on %s::%s(%s).',
                $declaringClass->getName(),
                $targetReflection->getDeclaringFunction()->getName(),
                $targetReflection->getName()
            ),
            [
                'attribute' => Inject::class,
                'target' => [
                    'class' => $declaringClass->getName(),
                    'method' => $targetReflection->getDeclaringFunction()->getName(),
                    'parameter' => $targetReflection->getName()
                ],
                'definition' => [
                    'type' => InjectDefinition::class,
                    'serviceType' => $definition->getTargetIdentifier()->getClass()->getName(),
                    'method' => $definition->getTargetIdentifier()->getMethodName(),
                    'parameterType' => $definition->getType()->getName(),
                    'parameter' => $definition->getTargetIdentifier()->getName(),
                    'value' => $definition->getValue(),
                    'store' => $definition->getStoreName(),
                    'profiles' => $definition->getProfiles()
                ]
            ]
        );
    }

    private function logPropertyInjectDefinition(AnnotatedTarget $target, InjectDefinition $definition) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionProperty);
        $this->logger->info(
            sprintf(
                'Parsed InjectDefinition from #[Inject] Attribute on %s::%s.',
                $targetReflection->getDeclaringClass()->getName(),
                $targetReflection->getName()
            ),
            [
                'attribute' => Inject::class,
                'target' => [
                    'class' => $targetReflection->getDeclaringClass()->getName(),
                    'property' => $targetReflection->getName()
                ],
                'definition' => [
                    'type' => InjectDefinition::class,
                    'serviceType' => $definition->getTargetIdentifier()->getClass()->getName(),
                    'property' => $definition->getTargetIdentifier()->getName(),
                    'propertyType' => $definition->getType()->getName(),
                    'value' => $definition->getValue(),
                    'store' => $definition->getStoreName(),
                    'profiles' => $definition->getProfiles()
                ]
            ]
        );
    }

    private function logConfigurationDefinition(AnnotatedTarget $target, ConfigurationDefinition $definition) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionClass);
        $this->logger->info(
            sprintf(
                'Parsed ConfigurationDefinition from #[Configuration] Attribute on %s.',
                Fixtures::configurationServices()->myConfig()->getName(),
            ),
            [
                'attribute' => AttributeType::Configuration->value,
                'target' => [
                    'class' => $targetReflection->getName()
                ],
                'definition' => [
                    'type' => ConfigurationDefinition::class,
                    'configurationType' => $definition->getClass()->getName(),
                    'name' => $definition->getName()
                ]
            ]
        );
    }

    /**
     * @param ContainerDefinitionBuilder $containerDefinitionBuilder
     * @param object $consumer
     * @return ContainerDefinitionBuilder
     * @throws InvalidAnnotationException
     */
    private function addAnnotatedDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, object $consumer) : ContainerDefinitionBuilder {
        foreach ($consumer->serviceDefinitions as $serviceDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($serviceDefinition);
        }

        foreach ($consumer->serviceDelegateDefinitions as $serviceDelegateDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition($serviceDelegateDefinition);
        }

        $concretePrepareDefinitions = array_filter($consumer->servicePrepareDefinitions, function (ServicePrepareDefinition $prepareDef) use ($containerDefinitionBuilder) {
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $prepareDef->getService());
            if (is_null($serviceDef)) {
                throw new InvalidAnnotationException(sprintf(
                    'The #[ServicePrepare] Attribute on %s::%s is not on a type marked as a #[Service].',
                    $prepareDef->getService()->getName(),
                    $prepareDef->getMethod()
                ));
            }
            return $serviceDef->isConcrete();
        });
        $abstractPrepareDefinitions = array_filter($consumer->servicePrepareDefinitions, function (ServicePrepareDefinition $prepareDef) use ($containerDefinitionBuilder) {
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $prepareDef->getService());
            return $serviceDef?->isAbstract() ?? false;
        });

        foreach ($abstractPrepareDefinitions as $abstractPrepareDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition($abstractPrepareDefinition);
        }
        /** @var ServicePrepareDefinition $concretePrepareDefinition */
        foreach ($concretePrepareDefinitions as $concretePrepareDefinition) {
            $hasAbstractPrepare = false;
            foreach ($abstractPrepareDefinitions as $abstractPrepareDefinition) {
                if (is_subclass_of($concretePrepareDefinition->getService()->getName(), $abstractPrepareDefinition->getService()->getName())) {
                    $hasAbstractPrepare = true;
                    break;
                }
            }
            if (!$hasAbstractPrepare) {
                $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition($concretePrepareDefinition);
            }
        }

        foreach ($consumer->injectDefinitions as $injectDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectDefinition($injectDefinition);
        }

        foreach ($consumer->configurationDefinitions as $configurationDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withConfigurationDefinition($configurationDefinition);
        }

        return $containerDefinitionBuilder;
    }

    private function getServiceDefinition(ContainerDefinitionBuilder $containerDefinitionBuilder, ObjectType $objectType) : ?ServiceDefinition {
        foreach ($containerDefinitionBuilder->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType() === $objectType) {
                return $serviceDefinition;
            }
        }
        return null;
    }

    private function addThirdPartyServices(ContainerDefinitionCompileOptions $compileOptions, ContainerDefinitionBuilder $builder) : ContainerDefinitionBuilder {
        $context = new class($builder) implements ContainerDefinitionBuilderContext {
            public function __construct(private ContainerDefinitionBuilder $builder) {
            }

            public function getBuilder() : ContainerDefinitionBuilder {
                return $this->builder;
            }

            public function setBuilder(ContainerDefinitionBuilder $containerDefinitionBuilder) : void {
                $this->builder = $containerDefinitionBuilder;
            }
        };

        $contextConsumer = $compileOptions->getContainerDefinitionBuilderContextConsumer();
        if (!is_null($contextConsumer)) {
            $contextConsumer->consume($context);
        }

        return $context->getBuilder();
    }

    private function addAliasDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder) : ContainerDefinitionBuilder {
        $abstractDefinitions = array_filter($containerDefinitionBuilder->getServiceDefinitions(), static fn($def): bool => $def->isAbstract());
        $concreteDefinitions = array_filter($containerDefinitionBuilder->getServiceDefinitions(), static fn($def): bool => $def->isConcrete());

        foreach ($abstractDefinitions as $abstractDefinition) {
            foreach ($concreteDefinitions as $concreteDefinition) {
                if (is_subclass_of($concreteDefinition->getType()->getName(), $abstractDefinition->getType()->getName())) {
                    $containerDefinitionBuilder = $containerDefinitionBuilder->withAliasDefinition(
                        AliasDefinitionBuilder::forAbstract($abstractDefinition->getType())->withConcrete($concreteDefinition->getType())->build()
                    );
                }
            }
        }

        return $containerDefinitionBuilder;
    }

}