<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\InjectService;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainer\Internal\AnnotationDetailsList;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\Internal\AnnotationVisitor;
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
    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions): ContainerDefinition {
        if (empty($containerDefinitionCompileOptions->getScanDirectories())) {
            throw new InvalidCompileOptionsException(sprintf(
                'The ContainerDefinitionCompileOptions passed to %s must include at least 1 directory to scan, but none were provided.',
                self::class
            ));
        }

        $containerDefinitionBuilder = ContainerDefinitionBuilder::newDefinition();

        $annotationDetailsList = $this->parseDirectories($containerDefinitionCompileOptions->getScanDirectories());

        $containerDefinitionBuilder = $this->addAllServiceDefinitions($containerDefinitionBuilder, $annotationDetailsList);
        $containerDefinitionBuilder = $this->addAllAliasDefinitions($containerDefinitionBuilder, $annotationDetailsList);
        $containerDefinitionBuilder = $this->addAllServicePrepareDefinitions($containerDefinitionBuilder, $annotationDetailsList);
        $containerDefinitionBuilder = $this->addAllServiceDelegateDefinitions($containerDefinitionBuilder, $annotationDetailsList);
        $containerDefinitionBuilder = $this->addAllInjectScalarDefinitions($containerDefinitionBuilder, $annotationDetailsList);
        $containerDefinitionBuilder = $this->addAllInjectServiceDefinitions($containerDefinitionBuilder, $annotationDetailsList);

        $containerDefinitionBuilder = $this->addAllThirdPartyServices($containerDefinitionBuilder, $annotationDetailsList);

        return $containerDefinitionBuilder->build();
    }

    private function parseDirectories(array $dirs): AnnotationDetailsList {
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

    private function addAllServiceDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList): ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::Service) as $serviceAnnotationDetails) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($this->getServiceDefinition($annotationDetailsList, $serviceAnnotationDetails->getReflection()->getName()));
        }
        return $containerDefinitionBuilder;
    }

    private function getServiceDefinition(AnnotationDetailsList $annotationDetailsList, string $serviceType): ?ServiceDefinition {
        static $serviceDefinitions = [];
        if (!isset($serviceDefinitions[$serviceType])) {
            foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::Service) as $annotationDetails) {
                if ($annotationDetails->getReflection()->getName() === $serviceType) {
                    if ($annotationDetails->getReflection()->isAbstract() || $annotationDetails->getReflection()->isInterface()) {
                        $serviceDefinitionBuilder = ServiceDefinitionBuilder::forAbstract($annotationDetails->getReflection()->getName());
                    } else {
                        $isPrimary = $annotationDetails->getAnnotationArguments()->get('primary', false)->getCompileValue();
                        $serviceDefinitionBuilder = ServiceDefinitionBuilder::forConcrete($annotationDetails->getReflection()->getName(), $isPrimary);
                    }

                    $serviceDefinitionBuilder = $serviceDefinitionBuilder->withProfiles($annotationDetails->getAnnotationArguments()->get('profiles', []));

                    if ($annotationDetails->getAnnotationArguments()->has('name')) {
                        $serviceDefinitionBuilder = $serviceDefinitionBuilder->withName($annotationDetails->getAnnotationArguments()->get('name'));
                    }

                    $serviceDefinitions[$serviceType] = $serviceDefinitionBuilder->build();
                    break;
                }
            }
        }

        return $serviceDefinitions[$serviceType] ?? null;
    }

    private function addAllAliasDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList) : ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::Service) as $serviceAnnotationDetails) {
            if (!$serviceAnnotationDetails->getReflection()->isInterface() && !$serviceAnnotationDetails->getReflection()->isAbstract()) {
                continue;
            }

            foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::Service) as $sd) {
                if ($sd->getReflection()->isSubclassOf($serviceAnnotationDetails->getReflection())) {
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

            if ($serviceDefinition->isConcrete() && $this->doesServiceDefinitionHaveAbstractPrepare($annotationDetailsList, $serviceDefinition, $reflection->getName())) {
                continue;
            }

            $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition(
                ServicePrepareDefinitionBuilder::forMethod($serviceDefinition, $reflection->getName())->build()
            );
        }

        return $containerDefinitionBuilder;
    }

    private function doesServiceDefinitionHaveAbstractPrepare(AnnotationDetailsList $annotationDetailsList, ServiceDefinition $serviceDefinition, string $method) : bool {
        $concreteType = $serviceDefinition->getType();
        $classInterfaces = class_implements($concreteType);
        if (empty($classInterfaces)) {
            return false;
        }

        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::ServicePrepare) as $annotationDetails) {
            if (!$annotationDetails->getReflection()->getDeclaringClass()->isAbstract() && !$annotationDetails->getReflection()->getDeclaringClass()->isInterface()) {
                continue;
            }
            $abstractType = $annotationDetails->getReflection()->getDeclaringClass()->getName();
            if (in_array($abstractType, $classInterfaces)) {
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
                    ->withProfiles($annotationDetails->getAnnotationArguments()->get('profiles', []))
                    ->build()
            );
        }

        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::InjectEnv) as $annotationDetails) {
            /** @var ReflectionParameter $reflection */
            $reflection = $annotationDetails->getReflection();
            $reflectionClass = $reflection->getDeclaringClass();
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectScalarDefinition(
                InjectScalarDefinitionBuilder::forMethod(
                    $this->getServiceDefinition($annotationDetailsList, $reflectionClass->getName()), $reflection->getDeclaringFunction()->getName()
                )->withParam(ScalarType::fromName($reflection->getType()->getName()), $reflection->getName())
                    ->withValue($annotationDetails->getAnnotationArguments()->get('value'))
                    ->withProfiles($annotationDetails->getAnnotationArguments()->get('profiles', []))
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
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectServiceDefinition(
                InjectServiceDefinitionBuilder::forMethod($this->getServiceDefinition($annotationDetailsList, $reflectionClass->getName()), $reflection->getDeclaringFunction()->getName())
                    ->withParam($reflection->getType()->getName(), $reflection->getName())
                    // Normally we wouldn't use getRuntimeValue here, but we're in a unique situation with the InjectService
                    // where we currently need to have a valid ServiceDefinition.It might be useful to refactor the InjectServiceDefinition
                    // to return an AnnotationValue for the service type, as it is more semantic and wouldn't require having the
                    // runtime value for an annotation argument be required
                    ->withInjectedService($annotationDetails->getAnnotationArguments()->get('name'))
                    ->build()
            );
        }

        return $containerDefinitionBuilder;
    }

    private function addAllServiceDelegateDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList) : ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::ServiceDelegate) as $annotationDetails) {
            $reflection = $annotationDetails->getReflection();
            $serviceDefinition = $this->getServiceDefinition($annotationDetailsList, $annotationDetails->getAnnotationArguments()->get('service')->getCompileValue());
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition(
                ServiceDelegateDefinitionBuilder::forService($serviceDefinition)
                    ->withDelegateMethod($reflection->getDeclaringClass()->getName(), $reflection->getName())
                    ->build()
            );
        }
        return $containerDefinitionBuilder;
    }

    private function addAllThirdPartyServices(ContainerDefinitionBuilder $containerDefinitionBuilder, AnnotationDetailsList $annotationDetailsList) : ContainerDefinitionBuilder {
        foreach ($annotationDetailsList->getSubsetForAttributeType(AttributeType::ThirdPartyService) as $annotationDetails) {
            $reflection = $annotationDetails->getReflection();
            $method = ($reflection->isInterface() || $reflection->isAbstract()) ? 'forAbstract' : 'forConcrete';
            $serviceDefinition = ServiceDefinitionBuilder::$method($annotationDetails->getAnnotationArguments()->get('type')->getCompileValue())->build();
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($serviceDefinition);
        }
        return $containerDefinitionBuilder;
    }

    private function traverseCode(string $fileContents, AnnotationVisitor $annotationVisitor): void {
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