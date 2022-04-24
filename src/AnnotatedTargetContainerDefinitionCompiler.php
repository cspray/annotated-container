<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\Typiphy\ObjectType;

/**
 * A ContainerDefinitionCompiler that uses PhpParser to statically analyze source code for Attributes defined by
 * AnnotatedContainer.
 */
final class AnnotatedTargetContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    public function __construct(
        private readonly AnnotatedTargetCompiler $annotatedTargetCompiler,
        private readonly AnnotatedTargetDefinitionConverter $definitionConverter
    ) {}

    /**
     * Will parse source code, according to the passed $containerDefinitionCompileOptions, and construct a ContainerDefinition
     * instance based off of the resultant parsing.
     *
     * @param ContainerDefinitionCompileOptions $containerDefinitionCompileOptions
     * @return ContainerDefinition
     * @throws InvalidCompileOptionsException
     */
    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions): ContainerDefinition {
        if (empty($containerDefinitionCompileOptions->getScanDirectories())) {
            throw new InvalidCompileOptionsException(sprintf(
                'The ContainerDefinitionCompileOptions passed to %s must include at least 1 directory to scan, but none were provided.',
                self::class
            ));
        }

        $consumer = $this->getTargetConsumer();
        $this->annotatedTargetCompiler->compile($containerDefinitionCompileOptions->getScanDirectories(), $consumer);

        $containerDefinitionBuilder = ContainerDefinitionBuilder::newDefinition();
        $containerDefinitionBuilder = $this->addAnnotatedDefinitions($containerDefinitionBuilder, $consumer);
        $containerDefinitionBuilder = $this->addThirdPartyServices($containerDefinitionCompileOptions, $containerDefinitionBuilder);
        $containerDefinitionBuilder = $this->addAliasDefinitions($containerDefinitionBuilder);

        return $containerDefinitionBuilder->build();
    }

    private function getTargetConsumer() : AnnotatedTargetConsumer {
        return new class($this->definitionConverter) implements AnnotatedTargetConsumer {
            public array $serviceDefinitions = [];
            public array $servicePrepareDefinitions = [];
            public array $serviceDelegateDefinitions = [];

            public function __construct(
                private readonly AnnotatedTargetDefinitionConverter $converter
            ) {}

            public function consume(AnnotatedTarget $annotatedTarget): void {
                $definition = $this->converter->convert($annotatedTarget);
                match (true) {
                    $definition instanceof ServiceDefinition => $this->serviceDefinitions[] = $definition,
                    $definition instanceof ServicePrepareDefinition => $this->servicePrepareDefinitions[] = $definition,
                    $definition instanceof ServiceDelegateDefinition => $this->serviceDelegateDefinitions[] = $definition
                };
            }
        };
    }

    private function addAnnotatedDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, object $consumer) : ContainerDefinitionBuilder {
        foreach ($consumer->serviceDefinitions as $serviceDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($serviceDefinition);
        }

        foreach ($consumer->serviceDelegateDefinitions as $serviceDelegateDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition($serviceDelegateDefinition);
        }

        $concretePrepareDefinitions = array_filter($consumer->servicePrepareDefinitions, function(ServicePrepareDefinition $prepareDef) use($containerDefinitionBuilder) {
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $prepareDef->getService());
            return $serviceDef->isConcrete();
        });
        $abstractPrepareDefinitions = array_filter($consumer->servicePrepareDefinitions, function(ServicePrepareDefinition $prepareDef) use($containerDefinitionBuilder) {
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $prepareDef->getService());
            return $serviceDef->isAbstract();
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
            public function __construct(private ContainerDefinitionBuilder $builder) {}

            public function getBuilder(): ContainerDefinitionBuilder {
                return $this->builder;
            }

            public function setBuilder(ContainerDefinitionBuilder $containerDefinitionBuilder) {
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