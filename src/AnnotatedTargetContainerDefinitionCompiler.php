<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedTarget\AnnotatedTargetParser;
use Cspray\AnnotatedTarget\AnnotatedTargetParserOptionsBuilder;
use Cspray\Typiphy\ObjectType;
use stdClass;
use function Cspray\Typiphy\objectType;

/**
 * A ContainerDefinitionCompiler that utilizes the AnnotatedTarget concept by parsing given source code directories and
 * converting any found targets into the appropriate definition object.
 */
final class AnnotatedTargetContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    public function __construct(
        private readonly AnnotatedTargetParser $annotatedTargetCompiler,
        private readonly AnnotatedTargetDefinitionConverter $definitionConverter
    ) {}

    /**
     * Will parse source code, according to the passed $containerDefinitionCompileOptions, and construct a ContainerDefinition
     * instance based off of the resultant parsing.
     *
     * @param ContainerDefinitionCompileOptions $containerDefinitionCompileOptions
     * @return ContainerDefinition
     * @throws InvalidCompileOptionsException|InvalidAnnotationException
     */
    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition {
        if (empty($containerDefinitionCompileOptions->getScanDirectories())) {
            throw new InvalidCompileOptionsException(sprintf(
                'The ContainerDefinitionCompileOptions passed to %s must include at least 1 directory to scan, but none were provided.',
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
        $options = AnnotatedTargetParserOptionsBuilder::scanDirectories(...$containerDefinitionCompileOptions->getScanDirectories())
            ->filterAttributes(...$attributeTypes)
            ->build();
        foreach ($this->annotatedTargetCompiler->parse($options) as $target) {
            $definition = $this->definitionConverter->convert($target);
            match (true) {
                $definition instanceof ServiceDefinition => $consumer->serviceDefinitions[] = $definition,
                $definition instanceof ServicePrepareDefinition => $consumer->servicePrepareDefinitions[] = $definition,
                $definition instanceof ServiceDelegateDefinition => $consumer->serviceDelegateDefinitions[] = $definition,
                $definition instanceof InjectDefinition => $consumer->injectDefinitions[] = $definition,
                $definition instanceof ConfigurationDefinition => $consumer->configurationDefinitions[] = $definition
            };
        }
        return $consumer;
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
        $abstractDefinitions = array_filter($containerDefinitionBuilder->getServiceDefinitions(), fn($def) => $def->isAbstract());
        $concreteDefinitions = array_filter($containerDefinitionBuilder->getServiceDefinitions(), fn($def) => $def->isConcrete());

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