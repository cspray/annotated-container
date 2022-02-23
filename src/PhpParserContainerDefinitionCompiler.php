<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainer\Internal\Interrogator\ServiceDelegateDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Interrogator\InjectScalarDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Interrogator\InjectServiceDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Interrogator\ServiceDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Interrogator\ServicePrepareDefinitionInterrogator;
use Cspray\AnnotatedContainer\Internal\Visitor\ServiceDelegateVisitor;
use Cspray\AnnotatedContainer\Internal\Visitor\InjectScalarDefinitionVisitor;
use Cspray\AnnotatedContainer\Internal\Visitor\InjectServiceDefinitionVisitor;
use Cspray\AnnotatedContainer\Internal\Visitor\ServiceDefinitionVisitor;
use Cspray\AnnotatedContainer\Internal\Visitor\ServicePrepareDefinitionVisitor;
use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * @package Cspray\AnnotatedContainer
 */
final class PhpParserContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    private Parser $parser;
    private NodeTraverserInterface $nodeTraverser;

    public function __construct() {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
    }

    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition {
        if (empty($containerDefinitionCompileOptions->getScanDirectories())) {
            throw new InvalidCompileOptionsException(sprintf(
                'The ContainerDefinitionCompileOptions passed to %s must include at least 1 directory to scan, but none were provided.',
                self::class
            ));
        } else if (empty($containerDefinitionCompileOptions->getProfiles())) {
            throw new InvalidCompileOptionsException(sprintf(
                'The ContainerDefinitionCompileOptions passed to %s must include at least 1 active profile, but none were provided.',
                self::class
            ));
        }

        $rawServiceDefinitions = [];
        $rawServicePrepareDefinitions = [];
        $rawUseScalarDefinitions = [];
        $rawUseServiceDefinitions = [];
        $rawServiceDelegateDefinitions = [];
        /** @var Node $node */
        foreach ($this->gatherDefinitions($containerDefinitionCompileOptions->getScanDirectories()) as $rawDefinition) {
            if ($rawDefinition['definitionType'] === ServiceDefinition::class) {
                $rawServiceDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === ServicePrepareDefinition::class) {
                $rawServicePrepareDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === InjectScalarDefinition::class) {
                $rawUseScalarDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === InjectServiceDefinition::class) {
                $rawUseServiceDefinitions[] = $rawDefinition;
            } else if ($rawDefinition['definitionType'] === ServiceDelegateDefinition::class) {
                $rawServiceDelegateDefinitions[] = $rawDefinition;
            }
        }
        $serviceDefinitions = $this->marshalRawServiceDefinitions($rawServiceDefinitions);
        $serviceDefinitionInterrogator = new ServiceDefinitionInterrogator(
            $containerDefinitionCompileOptions->getProfiles(),
            ...$serviceDefinitions
        );
        $servicePrepareInterrogator = new ServicePrepareDefinitionInterrogator(
            ...$this->marshalRawServicePrepareDefinitions($serviceDefinitions, $rawServicePrepareDefinitions)
        );
        $useScalarInterrogator = new InjectScalarDefinitionInterrogator(
            ...$this->marshalRawUseScalarDefinitions($serviceDefinitions, $rawUseScalarDefinitions)
        );
        $useServiceInterrogator = new InjectServiceDefinitionInterrogator(
            ...$this->marshalRawUseServiceDefinitions($serviceDefinitions, $rawUseServiceDefinitions)
        );
        $serviceDelegateInterrogator = new ServiceDelegateDefinitionInterrogator(
            ...$this->marshalRawServiceDelegateDefinitions($serviceDefinitions, $rawServiceDelegateDefinitions)
        );

        return $this->interrogateDefinitions(
            $serviceDefinitionInterrogator,
            $servicePrepareInterrogator,
            $useScalarInterrogator,
            $useServiceInterrogator,
            $serviceDelegateInterrogator
        );
    }

    private function marshalRawServiceDefinitions(array $rawServiceDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawServiceDefinitions as $rawServiceDefinition) {
            $implementServiceDefinitions = $this->marshalCollectionServiceDefinitionFromTypes(
                $rawServiceDefinitions,
                $rawServiceDefinition['implements']
            );

            $extendedServiceDefinitions = $this->marshalCollectionServiceDefinitionFromTypes(
                $rawServiceDefinitions,
                $rawServiceDefinition['extends']
            );

            if ($rawServiceDefinition['isAbstract'] || $rawServiceDefinition['isInterface']) {
                $factoryMethod = 'forAbstract';
            } else {
                $factoryMethod = 'forConcrete';
            }

            $serviceDefinitionBuilder = ServiceDefinitionBuilder::$factoryMethod($rawServiceDefinition['type'])
                ->withProfiles(...$rawServiceDefinition['profiles']);

            foreach (array_merge($implementServiceDefinitions, $extendedServiceDefinitions) as $serviceDefinition) {
                $serviceDefinitionBuilder = $serviceDefinitionBuilder->withImplementedService($serviceDefinition);
            }

            $marshaledDefinitions[] = $serviceDefinitionBuilder->build();
        }

        return $marshaledDefinitions;
    }

    private function marshalCollectionServiceDefinitionFromTypes(array $rawServiceDefinitions, array $targetTypes) : array {
        $collection = [];
        foreach ($targetTypes as $targetType) {
            $collection[] = $this->marshalServiceDefinitionFromType($rawServiceDefinitions, $targetType);
        }
        return $collection;
    }

    private function marshalServiceDefinitionFromType(array $rawServiceDefinitions, string $targetType) {
        $serviceDefinition = null;
        foreach ($rawServiceDefinitions as $rawServiceDefinition) {
            if ($targetType === $rawServiceDefinition['type']) {
                if ($rawServiceDefinition['isAbstract'] || $rawServiceDefinition['isInterface']) {
                    $factoryMethod = 'forAbstract';
                } else {
                    $factoryMethod = 'forConcrete';
                }
                $serviceDefinitionBuilder = ServiceDefinitionBuilder::$factoryMethod($rawServiceDefinition['type'])
                    ->withProfiles(...$rawServiceDefinition['profiles']);
                $implements = $this->marshalCollectionServiceDefinitionFromTypes($rawServiceDefinitions, $rawServiceDefinition['implements']);
                $extends = $this->marshalCollectionServiceDefinitionFromTypes($rawServiceDefinitions, $rawServiceDefinition['extends']);

                foreach (array_merge($implements, $extends) as $implementedService) {
                    $serviceDefinitionBuilder->withImplementedService($implementedService);
                }

                $serviceDefinition = $serviceDefinitionBuilder->build();
            }
        }
        return $serviceDefinition;
    }

    private function marshalRawServicePrepareDefinitions(array $serviceDefinitions, array $rawServicePrepareDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawServicePrepareDefinitions as $rawServicePrepareDefinition) {
            $service = null;
            foreach ($serviceDefinitions as $serviceDefinition) {
                if ($serviceDefinition->getType() === $rawServicePrepareDefinition['type']) {
                    $service = $serviceDefinition;
                    break;
                }
            }
            if (is_null($service)) {
                throw new InvalidAnnotationException(sprintf(
                    'The #[ServicePrepare] Attribute on %s::%s is not on a type marked as a #[Service].',
                    $rawServicePrepareDefinition['type'],
                    $rawServicePrepareDefinition['method']
                ));
            }
            $marshaledDefinitions[] = ServicePrepareDefinitionBuilder::forMethod($service, $rawServicePrepareDefinition['method'])->build();
        }
        return $marshaledDefinitions;
    }

    private function marshalRawUseScalarDefinitions(array $serviceDefinitions, array $rawUseScalarDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawUseScalarDefinitions as $rawUseScalarDefinition) {
            $injectScalarDefinition = null;
            foreach ($serviceDefinitions as $serviceDefinition) {
                if ($rawUseScalarDefinition['type'] === $serviceDefinition->getType()) {
                    $injectScalarDefinition = $serviceDefinition;
                    break;
                }
            }
            $marshaledDefinitions[] = InjectScalarDefinitionBuilder::forMethod($injectScalarDefinition, $rawUseScalarDefinition['method'])
                ->withParam(ScalarType::fromName($rawUseScalarDefinition['paramType']), $rawUseScalarDefinition['param'])
                ->withValue($rawUseScalarDefinition['value'])
                ->build();
        }
        return $marshaledDefinitions;
    }

    private function marshalRawUseServiceDefinitions(array $serviceDefinitions, array $rawUseServiceDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawUseServiceDefinitions as $rawUseServiceDefinition) {
            $targetDefinition = null;
            $injectDefinition = null;
            foreach ($serviceDefinitions as $serviceDefinition) {
                if ($rawUseServiceDefinition['type'] === $serviceDefinition->getType()) {
                    $targetDefinition = $serviceDefinition;
                } else if ($rawUseServiceDefinition['value'] === $serviceDefinition->getType()) {
                    $injectDefinition = $serviceDefinition;
                }
            }
            $marshaledDefinitions[] = InjectServiceDefinitionBuilder::forMethod($targetDefinition, $rawUseServiceDefinition['method'])
                ->withParam($rawUseServiceDefinition['paramType'], $rawUseServiceDefinition['param'])
                ->withInjectedService($injectDefinition)
                ->build();
        }
        return $marshaledDefinitions;
    }

    private function marshalRawServiceDelegateDefinitions(array $serviceDefinitions, array $rawServiceDelegateDefinitions) : array {
        $marshaledDefinitions = [];
        foreach ($rawServiceDelegateDefinitions as $rawServiceDelegateDefinition) {
            $service = null;
            foreach ($serviceDefinitions as $serviceDefinition) {
                if ($serviceDefinition->getType() === $rawServiceDelegateDefinition['serviceType']) {
                    $service = $serviceDefinition;
                    break;
                }
            }
            $marshaledDefinitions[] = ServiceDelegateDefinitionBuilder::forService($service)
                ->withDelegateMethod($rawServiceDelegateDefinition['delegateType'], $rawServiceDelegateDefinition['delegateMethod'])
                ->build();
        }
        return $marshaledDefinitions;
    }

    private function gatherDefinitions(array $dirs) : Generator {
        foreach ($dirs as $dir) {
            $dirIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::KEY_AS_PATHNAME |
                    FilesystemIterator::CURRENT_AS_FILEINFO |
                    FilesystemIterator::SKIP_DOTS
                )
            );

            /** @var SplFileInfo $file */
            foreach ($dirIterator as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $statements = $this->parser->parse(file_get_contents($file->getRealPath()));

                $nameResolver = new NameResolver();
                $nodeConnectingVisitor = new NodeConnectingVisitor();
                $this->nodeTraverser->addVisitor($nameResolver);
                $this->nodeTraverser->addVisitor($nodeConnectingVisitor);
                $this->nodeTraverser->traverse($statements);

                $this->nodeTraverser->removeVisitor($nameResolver);
                $this->nodeTraverser->removeVisitor($nodeConnectingVisitor);

                $serviceDefinitionVisitor = new ServiceDefinitionVisitor();
                $servicePrepareDefinitionVisitor = new ServicePrepareDefinitionVisitor();
                $useScalarDefinitionVisitor = new InjectScalarDefinitionVisitor();
                $useServiceDefinitionVisitor = new InjectServiceDefinitionVisitor();
                $serviceDelegateDefinitionVisitor = new ServiceDelegateVisitor();

                $this->nodeTraverser->addVisitor($serviceDefinitionVisitor);
                $this->nodeTraverser->addVisitor($servicePrepareDefinitionVisitor);
                $this->nodeTraverser->addVisitor($useScalarDefinitionVisitor);
                $this->nodeTraverser->addVisitor($useServiceDefinitionVisitor);
                $this->nodeTraverser->addVisitor($serviceDelegateDefinitionVisitor);
                $this->nodeTraverser->traverse($statements);

                $this->nodeTraverser->removeVisitor($serviceDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($servicePrepareDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($useScalarDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($useServiceDefinitionVisitor);
                $this->nodeTraverser->removeVisitor($serviceDelegateDefinitionVisitor);

                yield from $serviceDefinitionVisitor->getServiceDefinitions();
                yield from $servicePrepareDefinitionVisitor->getServicePrepareDefinitions();
                yield from $useScalarDefinitionVisitor->getUseScalarDefinitions();
                yield from $useServiceDefinitionVisitor->getUseServiceDefinitions();
                yield from $serviceDelegateDefinitionVisitor->getServiceDelegateDefinitions();
            }
        }
    }

    private function interrogateDefinitions(
        ServiceDefinitionInterrogator         $serviceDefinitionInterrogator,
        ServicePrepareDefinitionInterrogator  $servicePrepareDefinitionInterrogator,
        InjectScalarDefinitionInterrogator    $useScalarDefinitionInterrogator,
        InjectServiceDefinitionInterrogator   $useServiceDefinitionInterrogator,
        ServiceDelegateDefinitionInterrogator $serviceDelegateDefinitionInterrogator
    ) : ContainerDefinition {
        $builder = ContainerDefinitionBuilder::newDefinition();

        foreach ($serviceDefinitionInterrogator->gatherSharedServices() as $serviceDefinition) {
            $builder = $builder->withServiceDefinition($serviceDefinition);
        }

        foreach ($serviceDefinitionInterrogator->gatherAliases() as $aliasDefinition) {
            $builder = $builder->withAliasDefinition($aliasDefinition);
        }

        foreach ($servicePrepareDefinitionInterrogator->gatherServicePrepare() as $servicePrepareDefinition) {
            $builder = $builder->withServicePrepareDefinition($servicePrepareDefinition);
        }

        foreach ($useScalarDefinitionInterrogator->gatherUseScalarDefinitions() as $injectScalarDefinition) {
            $builder = $builder->withInjectScalarDefinition($injectScalarDefinition);
        }

        foreach ($useServiceDefinitionInterrogator->gatherUseServiceDefinitions() as $injectServiceDefinition) {
            $builder = $builder->withInjectServiceDefinition($injectServiceDefinition);
        }

        foreach ($serviceDelegateDefinitionInterrogator->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            $builder = $builder->withServiceDelegateDefinition($serviceDelegateDefinition);
        }

        return $builder->build();
    }

}