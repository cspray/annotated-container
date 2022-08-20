<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Compile;

use Cspray\AnnotatedContainer\AliasDefinition;
use Cspray\AnnotatedContainer\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\Exception\InvalidScanDirectories;
use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegate;
use Cspray\AnnotatedContainer\Exception\InvalidServicePrepare;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\ConfigurationDefinition;
use Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptions;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\AnnotatedTarget\AnnotatedTargetParser;
use Cspray\AnnotatedTarget\AnnotatedTargetParserOptionsBuilder;
use Cspray\AnnotatedTarget\Exception\InvalidArgumentException;
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
 *
 * @psalm-type DefinitionsCollection = array{
 *     serviceDefinitions: list<ServiceDefinition>,
 *     servicePrepareDefinitions: list<ServicePrepareDefinition>,
 *     serviceDelegateDefinitions: list<ServiceDelegateDefinition>,
 *     injectDefinitions: list<InjectDefinition>,
 *     configurationDefinitions: list<ConfigurationDefinition>
 * }
 */
final class AnnotatedTargetContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    public function __construct(
        private readonly AnnotatedTargetParser $annotatedTargetCompiler,
        private readonly AnnotatedTargetDefinitionConverter $definitionConverter,
    ) {
    }

    /**
     * Will parse source code, according to the passed $containerDefinitionCompileOptions, and construct a ContainerDefinition
     * instance based off of the resultant parsing.
     *
     * @param ContainerDefinitionCompileOptions $containerDefinitionCompileOptions
     * @return ContainerDefinition
     * @throws InvalidArgumentException
     * @throws InvalidScanDirectories
     * @throws InvalidServiceDelegate
     * @throws InvalidServicePrepare
     */
    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition {
        $logger = $containerDefinitionCompileOptions->getLogger() ?? new NullLogger();

        $scanDirs = $containerDefinitionCompileOptions->getScanDirectories();
        if (empty($scanDirs)) {
            $exception = InvalidScanDirectories::fromEmptyList();
            $logger->error($exception->getMessage());
            throw $exception;
        }

        if (count(array_unique($scanDirs)) !== count($scanDirs)) {
            $exception = InvalidScanDirectories::fromDuplicatedDirectories();
            $logger->error($exception->getMessage(), ['sourcePaths' => $scanDirs]);
            throw $exception;
        }

        $containerDefinitionBuilder = ContainerDefinitionBuilder::newDefinition();
        $consumer = $this->parse($containerDefinitionCompileOptions, $logger);
        $containerDefinitionBuilder = $this->addThirdPartyServices(
            $containerDefinitionCompileOptions,
            $containerDefinitionBuilder,
            $logger
        );
        $containerDefinitionBuilder = $this->addAnnotatedDefinitions($containerDefinitionBuilder, $consumer, $logger);
        $containerDefinitionBuilder = $this->addAliasDefinitions($containerDefinitionBuilder, $logger);

        $logger->info('Annotated Container compiling finished.');

        return $containerDefinitionBuilder->build();
    }

    /**
     * @param ContainerDefinitionCompileOptions $containerDefinitionCompileOptions
     * @param LoggerInterface $logger
     * @return DefinitionsCollection
     * @throws InvalidArgumentException
     */
    private function parse(
        ContainerDefinitionCompileOptions $containerDefinitionCompileOptions,
        LoggerInterface $logger
    ) : array {
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

        $logger->info('Annotated Container compiling started.');
        $logger->info(
            sprintf('Scanning directories for Attributes: %s.', implode(' ', $dirs)),
            ['sourcePaths' => $dirs]
        );

        /** @var AnnotatedTarget $target */
        foreach ($this->annotatedTargetCompiler->parse($options) as $target) {
            $definition = $this->definitionConverter->convert($target);

            if ($definition instanceof ServiceDefinition) {
                $consumer->serviceDefinitions[] = $definition;
                $this->logServiceDefinition($target, $definition, $logger);
            } else if ($definition instanceof ServicePrepareDefinition) {
                $consumer->servicePrepareDefinitions[] = $definition;
                $this->logServicePrepareDefinition($target, $definition, $logger);
            } else if ($definition instanceof ServiceDelegateDefinition) {
                $consumer->serviceDelegateDefinitions[] = $definition;
                $this->logServiceDelegateDefinition($target, $definition, $logger);
            } else if ($definition instanceof InjectDefinition) {
                $consumer->injectDefinitions[] = $definition;
                if ($definition->getTargetIdentifier()->isMethodParameter()) {
                    $this->logParameterInjectDefinition($target, $definition, $logger);
                } else {
                    $this->logPropertyInjectDefinition($target, $definition, $logger);
                }
            } else if ($definition instanceof ConfigurationDefinition) {
                $consumer->configurationDefinitions[] = $definition;
                $this->logConfigurationDefinition($target, $definition, $logger);
            }
        }
        /**
         * @var DefinitionsCollection $consumer
         */
        $consumer = (array) $consumer;
        return $consumer;
    }

    private function logServiceDefinition(
        AnnotatedTarget $target,
        ServiceDefinition $definition,
        LoggerInterface $logger
    ) : void {
        $logger->info(
            sprintf(
                'Parsed ServiceDefinition from #[%s] Attribute on %s.',
                $target->getAttributeReflection()->getName(),
                $definition->getType()->getName()
            ),
            [
                'attribute' => $target->getAttributeReflection()->getName(),
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

    private function logServicePrepareDefinition(
        AnnotatedTarget $target,
        ServicePrepareDefinition $definition,
        LoggerInterface $logger
    ) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionMethod);
        $logger->info(
            sprintf(
                'Parsed ServicePrepareDefinition from #[%s] Attribute on %s::%s.',
                $target->getAttributeReflection()->getName(),
                $definition->getService()->getName(), $definition->getMethod()
            ),
            [
                'attribute' => $target->getAttributeReflection()->getName(),
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

    private function logServiceDelegateDefinition(
        AnnotatedTarget $target,
        ServiceDelegateDefinition $definition,
        LoggerInterface $logger
    ) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionMethod);
        $logger->info(
            sprintf(
                'Parsed ServiceDelegateDefinition from #[%s] Attribute on %s::%s.',
                $target->getAttributeReflection()->getName(),
                $targetReflection->getDeclaringClass()->getName(),
                $target->getTargetReflection()->getName()
            ),
            [
                'attribute' => $target->getAttributeReflection()->getName(),
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

    private function logParameterInjectDefinition(
        AnnotatedTarget $target,
        InjectDefinition $definition,
        LoggerInterface $logger
    ) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionParameter);
        $declaringClass = $targetReflection->getDeclaringClass();
        assert($declaringClass instanceof ReflectionClass);
        $logger->info(
            sprintf(
                'Parsed InjectDefinition from #[%s] Attribute on %s::%s(%s).',
                $target->getAttributeReflection()->getName(),
                $declaringClass->getName(),
                $targetReflection->getDeclaringFunction()->getName(),
                $targetReflection->getName()
            ),
            [
                'attribute' => $target->getAttributeReflection()->getName(),
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
                    'value' => $this->convertValueToJsonifiable($definition->getValue()),
                    'store' => $definition->getStoreName(),
                    'profiles' => $definition->getProfiles()
                ]
            ]
        );
    }

    private function logPropertyInjectDefinition(
        AnnotatedTarget $target,
        InjectDefinition $definition,
        LoggerInterface $logger
    ) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionProperty);


        $logger->info(
            sprintf(
                'Parsed InjectDefinition from #[%s] Attribute on %s::%s.',
                $target->getAttributeReflection()->getName(),
                $targetReflection->getDeclaringClass()->getName(),
                $targetReflection->getName()
            ),
            [
                'attribute' => $target->getAttributeReflection()->getName(),
                'target' => [
                    'class' => $targetReflection->getDeclaringClass()->getName(),
                    'property' => $targetReflection->getName()
                ],
                'definition' => [
                    'type' => InjectDefinition::class,
                    'serviceType' => $definition->getTargetIdentifier()->getClass()->getName(),
                    'property' => $definition->getTargetIdentifier()->getName(),
                    'propertyType' => $definition->getType()->getName(),
                    'value' => $this->convertValueToJsonifiable($definition->getValue()),
                    'store' => $definition->getStoreName(),
                    'profiles' => $definition->getProfiles()
                ]
            ]
        );
    }

    private function convertValueToJsonifiable(mixed $value) : mixed {
        $convertedValue = $value;
        if ($value instanceof \UnitEnum) {
            $convertedValue = sprintf('%s::%s', $value::class, $value->name);
        } else if (is_array($value)) {
            $convertedValue = [];
            foreach ($value as $k => $v) {
               $convertedValue[$k] = $this->convertValueToJsonifiable($v);
            }
        }

        return $convertedValue;
    }

    private function logConfigurationDefinition(
        AnnotatedTarget $target,
        ConfigurationDefinition $definition,
        LoggerInterface $logger
    ) : void {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof ReflectionClass);
        $logger->info(
            sprintf(
                'Parsed ConfigurationDefinition from #[%s] Attribute on %s.',
                $target->getAttributeReflection()->getName(),
                $targetReflection->getName()
            ),
            [
                'attribute' => $target->getAttributeReflection()->getName(),
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

    private function logAliasDefinition(AliasDefinition $aliasDefinition, LoggerInterface $logger) : void {
        $logger->info(
            sprintf(
                'Added alias for abstract service %s to concrete service %s.',
                $aliasDefinition->getAbstractService()->getName(),
                $aliasDefinition->getConcreteService()->getName()
            ),
            [
                'abstractService' => $aliasDefinition->getAbstractService()->getName(),
                'concreteService' => $aliasDefinition->getConcreteService()->getName()
            ]
        );
    }

    /**
     * @param ContainerDefinitionBuilder $containerDefinitionBuilder
     * @param DefinitionsCollection $consumer
     * @param LoggerInterface $logger
     * @return ContainerDefinitionBuilder
     * @throws InvalidServiceDelegate
     * @throws InvalidServicePrepare
     */
    private function addAnnotatedDefinitions(
        ContainerDefinitionBuilder $containerDefinitionBuilder,
        array $consumer,
        LoggerInterface $logger
    ) : ContainerDefinitionBuilder {
        foreach ($consumer['serviceDefinitions'] as $serviceDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($serviceDefinition);
        }

        foreach ($consumer['serviceDelegateDefinitions'] as $serviceDelegateDefinition) {
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $serviceDelegateDefinition->getServiceType());
            if ($serviceDef === null) {
                throw InvalidServiceDelegate::factoryMethodDoesNotCreateService(
                    $serviceDelegateDefinition->getServiceType()->getName(),
                    $serviceDelegateDefinition->getDelegateType()->getName(),
                    $serviceDelegateDefinition->getDelegateMethod()
                );
            }
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition($serviceDelegateDefinition);
        }

        $concretePrepareDefinitions = array_filter($consumer['servicePrepareDefinitions'], function (ServicePrepareDefinition $prepareDef) use ($containerDefinitionBuilder, $logger) {
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $prepareDef->getService());
            if (is_null($serviceDef)) {
                $exception = InvalidServicePrepare::fromClassNotService($prepareDef->getService()->getName(), $prepareDef->getMethod());
                $logger->error($exception->getMessage());
                throw $exception;
            }
            return $serviceDef->isConcrete();
        });
        $abstractPrepareDefinitions = array_filter($consumer['servicePrepareDefinitions'], function (ServicePrepareDefinition $prepareDef) use ($containerDefinitionBuilder) {
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $prepareDef->getService());
            return $serviceDef?->isAbstract() ?? false;
        });

        foreach ($abstractPrepareDefinitions as $abstractPrepareDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition($abstractPrepareDefinition);
        }

        foreach ($concretePrepareDefinitions as $concretePrepareDefinition) {
            $hasAbstractPrepare = false;
            foreach ($abstractPrepareDefinitions as $abstractPrepareDefinition) {
                /** @var class-string $concreteServiceName */
                $concreteServiceName = $concretePrepareDefinition->getService()->getName();
                /** @var class-string $abstractServiceName */
                $abstractServiceName = $abstractPrepareDefinition->getService()->getName();
                if (is_subclass_of($concreteServiceName, $abstractServiceName)) {
                    $hasAbstractPrepare = true;
                    break;
                }
            }
            if (!$hasAbstractPrepare) {
                $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition($concretePrepareDefinition);
            }
        }

        foreach ($consumer['injectDefinitions'] as $injectDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectDefinition($injectDefinition);
        }

        foreach ($consumer['configurationDefinitions'] as $configurationDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withConfigurationDefinition($configurationDefinition);
        }

        return $containerDefinitionBuilder;
    }

    private function getServiceDefinition(ContainerDefinitionBuilder $containerDefinitionBuilder, ObjectType $objectType) : ?ServiceDefinition {
        $return = null;
        foreach ($containerDefinitionBuilder->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType() === $objectType) {
                $return = $serviceDefinition;
                break;
            }
        }
        return $return;
    }

    private function addThirdPartyServices(
        ContainerDefinitionCompileOptions $compileOptions,
        ContainerDefinitionBuilder $builder,
        LoggerInterface $logger
    ) : ContainerDefinitionBuilder {
        $contextConsumer = $compileOptions->getContainerDefinitionBuilderContextConsumer();
        if ($contextConsumer !== null) {
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
            $contextConsumer->consume($context);
            $logger->info(
                sprintf('Added services from %s to ContainerDefinition.', $contextConsumer::class),
                [
                    'containerDefinitionBuilderConsumer' => $contextConsumer::class
                ]
            );
            return $context->getBuilder();
        } else {
            $logger->info(
                sprintf('No %s was provided.', ContainerDefinitionBuilderContextConsumer::class)
            );
            return $builder;
        }
    }

    private function addAliasDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, LoggerInterface $logger) : ContainerDefinitionBuilder {
        /** @var list<ServiceDefinition> $abstractDefinitions */
        $abstractDefinitions = array_filter($containerDefinitionBuilder->getServiceDefinitions(), static fn($def): bool => $def->isAbstract());
        /** @var list<ServiceDefinition> $concreteDefinitions */
        $concreteDefinitions = array_filter($containerDefinitionBuilder->getServiceDefinitions(), static fn($def): bool => $def->isConcrete());

        foreach ($abstractDefinitions as $abstractDefinition) {
            foreach ($concreteDefinitions as $concreteDefinition) {
                if (is_subclass_of($concreteDefinition->getType()->getName(), $abstractDefinition->getType()->getName())) {
                    $aliasDefinition = AliasDefinitionBuilder::forAbstract($abstractDefinition->getType())
                        ->withConcrete($concreteDefinition->getType())
                        ->build();
                    $containerDefinitionBuilder = $containerDefinitionBuilder->withAliasDefinition($aliasDefinition);
                    $this->logAliasDefinition($aliasDefinition, $logger);
                }
            }
        }

        return $containerDefinitionBuilder;
    }

}