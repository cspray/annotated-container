<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\InjectEnv;
use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\InjectService;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainer\Internal\AnnotationDetailsList;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\Internal\AnnotationVisitor;
use Cspray\AnnotatedContainer\Internal\Visitor\AbstractNodeVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionParameter;
use SplFileInfo;
use function PHPUnit\Framework\once;

/**
 * A ContainerDefinitionCompiler that uses PhpParser to statically analyze source code for Attributes defined by
 * AnnotatedContainer.
 */
final class PhpParserContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    private Parser $parser;
    private NodeTraverserInterface $nodeTraverser;

    public function __construct() {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
    }

    /**
     * Will parse source code, according to the passed $containerDefinitionCompileOptions, and construct a ContainerDefinition
     * instance based off of the resultant parsing.
     *
     * @param ContainerDefinitionCompileOptions $containerDefinitionCompileOptions
     * @return ContainerDefinition
     * @throws InvalidAnnotationException
     * @throws InvalidCompileOptionsException
     */
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

        $containerDefinitionBuilder = ContainerDefinitionBuilder::newDefinition();

        $aggregatedMap = $this->parseDirectories($containerDefinitionCompileOptions->getScanDirectories());

        $containerDefinitionBuilder = $this->addAllServiceDefinitions($containerDefinitionBuilder, $aggregatedMap, $containerDefinitionCompileOptions->getProfiles());
        $containerDefinitionBuilder = $this->addAllAliasDefinitions($containerDefinitionBuilder, $aggregatedMap, $containerDefinitionCompileOptions->getProfiles());
        $containerDefinitionBuilder = $this->addAllServicePrepareDefinitions($containerDefinitionBuilder, $aggregatedMap);
        $containerDefinitionBuilder = $this->addAllServiceDelegateDefinitions($containerDefinitionBuilder, $aggregatedMap);
        $containerDefinitionBuilder = $this->addAllInjectScalarDefinitions($containerDefinitionBuilder, $aggregatedMap);
        $containerDefinitionBuilder = $this->addAllInjectServiceDefinitions($containerDefinitionBuilder, $aggregatedMap);

        return $containerDefinitionBuilder->build();
    }

    private function parseDirectories(array $dirs) : AnnotationDetailsList {
        $list = new AnnotationDetailsList();
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

                $visitor = new AnnotationVisitor($file);
                $this->traverseCode(file_get_contents($file->getRealPath()), $visitor);
                $list->merge($visitor->getAnnotationDetailsList());
            }
        }

        return $list;
    }

    private function addAllServiceDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList, array $activeProfiles) : ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::Service) as $serviceAnnotationDetails) {
            $serviceProfiles = $serviceAnnotationDetails->getAnnotationArguments()->get('profiles', ['default']);
            foreach ($serviceProfiles as $serviceProfile) {
                if (in_array($serviceProfile, $activeProfiles)) {
                    $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($this->getServiceDefinition($annotationDetailsList, $serviceAnnotationDetails->getReflection()->getName()));
                    break;
                }
            }
        }
        return $containerDefinitionBuilder;
    }

    private function getServiceDefinition(AnnotationDetailsList $annotationDetailsList, string $serviceType) : ?ServiceDefinition {
        static $serviceDefinitions = [];
        if (!isset($serviceDefinitions[$serviceType])) {
            foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::Service) as $annotationDetails) {
                if ($annotationDetails->getReflection()->getName() === $serviceType) {
                    $method = $annotationDetails->getReflection()->isAbstract() || $annotationDetails->getReflection()->isInterface() ? 'forAbstract' : 'forConcrete';
                    /** @var ServiceDefinitionBuilder $serviceDefinitionBuilder */
                    $serviceDefinitionBuilder = ServiceDefinitionBuilder::$method($annotationDetails->getReflection()->getName());
                    if ($method === 'forConcrete') {
                        $parent = $annotationDetails->getReflection()->getParentClass();
                        if ($parent) {
                            $extendedServiceDefinition = $this->getServiceDefinition($annotationDetailsList, $parent->getName());
                            if (!is_null($extendedServiceDefinition)) {
                                $serviceDefinitionBuilder = $serviceDefinitionBuilder->withImplementedService($extendedServiceDefinition);
                            }
                        }
                        foreach ($annotationDetails->getReflection()->getInterfaceNames() as $interfaceName) {
                            $implementServiceDefinition = $this->getServiceDefinition($annotationDetailsList, $interfaceName);
                            $serviceDefinitionBuilder = $serviceDefinitionBuilder->withImplementedService($implementServiceDefinition);
                        }
                    }
                    $serviceDefinitions[$serviceType] = $serviceDefinitionBuilder->build();
                    break;
                }
            }
        }

        return $serviceDefinitions[$serviceType] ?? null;
    }

    private function addAllAliasDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList, array $activeProfiles) : ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::Service) as $serviceAnnotationDetails) {
            if (!$serviceAnnotationDetails->getReflection()->isInterface() && !$serviceAnnotationDetails->getReflection()->isAbstract()) {
                continue;
            }

            foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::Service) as $sd) {
                $serviceProfiles = $sd->getAnnotationArguments()->get('profiles', ['wtf']);
                foreach ($serviceProfiles as $serviceProfile) {
                    if (in_array($serviceProfile, $activeProfiles) && $sd->getReflection()->isSubclassOf($serviceAnnotationDetails->getReflection())) {
                        $containerDefinitionBuilder = $containerDefinitionBuilder->withAliasDefinition(
                            AliasDefinitionBuilder::forAbstract(
                                $this->getServiceDefinition($annotationDetailsList, $serviceAnnotationDetails->getReflection()->getName())
                            )->withConcrete(
                                $this->getServiceDefinition($annotationDetailsList, $sd->getReflection()->getName())
                            )->build()
                        );
                    }
                }
            }

        }

        return $containerDefinitionBuilder;
    }

    private function addAllServicePrepareDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList) : ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::ServicePrepare) as $annotationDetails) {
            $reflection = $annotationDetails->getReflection();
            $serviceDefinition = $this->getServiceDefinition($annotationDetailsList, $reflection->getDeclaringClass()->getName());
            if (is_null($serviceDefinition)) {
                throw new InvalidAnnotationException(sprintf(
                    'The #[ServicePrepare] Attribute on %s::%s is not on a type marked as a #[Service].',
                    $reflection->getDeclaringClass()->getName(),
                    $reflection->getName()
                ));
            }
            foreach ($serviceDefinition->getImplementedServices() as $implementedService) {
                if ($this->doesServiceDefinitionHavePrepare($annotationDetailsList, $implementedService, $reflection->getName())) {
                    continue 2;
                }
            }

            $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition(
                ServicePrepareDefinitionBuilder::forMethod($serviceDefinition, $reflection->getName())->build()
            );
        }

        return $containerDefinitionBuilder;
    }

    private function doesServiceDefinitionHavePrepare(AnnotationDetailsList $annotationDetailsList, ServiceDefinition $serviceDefinition, string $method) : bool {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::ServicePrepare) as $annotationDetails) {
            if ($serviceDefinition->getType() === $annotationDetails->getReflection()->getDeclaringClass()->getName() && $annotationDetails->getReflection()->getName() === $method) {
                return true;
            }
        }
        return false;
    }

    private function addAllInjectScalarDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList) : ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::InjectScalar) as $annotationDetails) {
            /** @var ReflectionParameter $reflection */
            $reflection = $annotationDetails->getReflection();
            $reflectionClass = $reflection->getDeclaringClass();
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectScalarDefinition(
                InjectScalarDefinitionBuilder::forMethod($this->getServiceDefinition($annotationDetailsList, $reflectionClass->getName()), $reflection->getDeclaringFunction()->getName())
                    ->withParam(ScalarType::fromName($reflection->getType()->getName()), $reflection->getName())
                    ->withValue($annotationDetails->getAnnotationArguments()->get('value'))
                    ->build()
            );
        }

        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::InjectEnv) as $annotationDetails) {
            /** @var ReflectionParameter $reflection */
            $reflection = $annotationDetails->getReflection();
            $reflectionClass = $reflection->getDeclaringClass();
            /** @var InjectEnv $injectEnv */
            $injectEnv = $reflection->getAttributes(InjectEnv::class)[0]->newInstance();
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectScalarDefinition(
                InjectScalarDefinitionBuilder::forMethod(
                    $this->getServiceDefinition($annotationDetailsList, $reflectionClass->getName()), $reflection->getDeclaringFunction()->getName()
                )->withParam(ScalarType::fromName($reflection->getType()->getName()), $reflection->getName())
                ->withValue("!env(" . $injectEnv->getVariableName() . ")")
                ->build()
            );
        }

        return $containerDefinitionBuilder;
    }

    private function addAllInjectServiceDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList) : ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::InjectService) as $annotationDetails) {
            /** @var ReflectionParameter $reflection */
            $reflection = $annotationDetails->getReflection();
            $reflectionClass = $reflection->getDeclaringClass();
            /** @var InjectService $injectService */
            $injectService = $reflection->getAttributes(InjectService::class)[0]->newInstance();
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectServiceDefinition(
                InjectServiceDefinitionBuilder::forMethod($this->getServiceDefinition($annotationDetailsList, $reflectionClass->getName()), $reflection->getDeclaringFunction()->getName())
                    ->withParam($reflection->getType()->getName(), $reflection->getName())
                    ->withInjectedService($this->getServiceDefinition($annotationDetailsList, $injectService->getName()))
                    ->build()
            );
        }

        return $containerDefinitionBuilder;
    }

    private function addAllServiceDelegateDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList) : ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::ServiceDelegate) as $annotationDetails) {
            $reflection = $annotationDetails->getReflection();
            $serviceDefinition = $this->getServiceDefinition($annotationDetailsList, $annotationDetails->getAnnotationArguments()->get('service'));
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition(
                ServiceDelegateDefinitionBuilder::forService($serviceDefinition)
                    ->withDelegateMethod($reflection->getDeclaringClass()->getName(), $reflection->getName())
                    ->build()
            );
        }
        return $containerDefinitionBuilder;
    }

    private function traverseCode(string $fileContents, AnnotationVisitor $annotationVisitor) : void {
        $statements = $this->parser->parse($fileContents);

        $nameResolver = new NameResolver();
        $nodeConnectingVisitor = new NodeConnectingVisitor();
        $this->nodeTraverser->addVisitor($nameResolver);
        $this->nodeTraverser->addVisitor($nodeConnectingVisitor);
        $this->nodeTraverser->traverse($statements);

        $this->nodeTraverser->removeVisitor($nameResolver);
        $this->nodeTraverser->removeVisitor($nodeConnectingVisitor);

        $this->nodeTraverser->addVisitor($annotationVisitor);
        $this->nodeTraverser->traverse($statements);

        $this->nodeTraverser->removeVisitor($annotationVisitor);

        unset($statements);
    }


}