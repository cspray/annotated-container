<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Compile;

use Cspray\AnnotatedContainer\Attribute\ConfigurationAttribute;
use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegate;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\boolType;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\mixedType;
use function Cspray\Typiphy\nullType;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;
use function Cspray\Typiphy\typeIntersect;
use function Cspray\Typiphy\typeUnion;

final class DefaultAnnotatedTargetDefinitionConverter implements AnnotatedTargetDefinitionConverter {

    private readonly LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function convert(AnnotatedTarget $target) : ServiceDefinition|ServicePrepareDefinition|ServiceDelegateDefinition|InjectDefinition|ConfigurationDefinition {
        $attrInstance = $target->getAttributeInstance();
        if ($attrInstance instanceof ServiceAttribute) {
            return $this->buildServiceDefinition($target);
        } else if ($attrInstance instanceof ConfigurationAttribute) {
            return $this->buildConfigurationDefinition($target);
        } else if ($attrInstance instanceof InjectAttribute) {
            return $this->buildInjectDefinition($target);
        } else if ($attrInstance instanceof ServiceDelegateAttribute) {
            return $this->buildServiceDelegateDefinition($target);
        } else if ($attrInstance instanceof ServicePrepareAttribute) {
            return $this->buildServicePrepareDefinition($target);
        } else {
            throw new \RuntimeException();
        }
    }

    private function buildServiceDefinition(AnnotatedTarget $target) : ServiceDefinition {
        $serviceType = objectType($target->getTargetReflection()->getName());
        /** @var Service $attribute */
        $attribute = $target->getAttributeInstance();
        $reflection = $target->getTargetReflection();
        assert($reflection instanceof ReflectionClass);
        if ($reflection->isInterface() || $reflection->isAbstract()) {
            $builder = ServiceDefinitionBuilder::forAbstract($serviceType);
        } else {
            $builder = ServiceDefinitionBuilder::forConcrete($serviceType, $attribute->isPrimary());
        }

        $builder = $builder->withAttribute($attribute);

        $profiles = empty($attribute->getProfiles()) ? ['default'] : $attribute->getProfiles();
        $builder = $builder->withProfiles($profiles);
        if ($attribute->getName() !== null) {
            $builder = $builder->withName($attribute->getName());
        }

        return $builder->build();
    }

    private function buildServiceDelegateDefinition(AnnotatedTarget $target) : ServiceDelegateDefinition {
        $reflection = $target->getTargetReflection();
        assert($reflection instanceof ReflectionMethod);
        $delegateType = $reflection->getDeclaringClass()->getName();
        $delegateMethod = $reflection->getName();
        $attribute = $target->getAttributeInstance();
        assert($attribute instanceof ServiceDelegateAttribute);

        $service = $attribute->getService();
        if ($service !== null) {
            return ServiceDelegateDefinitionBuilder::forService(objectType($service))
                ->withDelegateMethod(objectType($delegateType), $delegateMethod)
                ->withAttribute($attribute)
                ->build();
        } else {
            $returnType = $reflection->getReturnType();
            if ($returnType instanceof ReflectionIntersectionType) {
                $exception = InvalidServiceDelegate::factoryMethodReturnsIntersectionType($delegateType, $delegateMethod);
                $this->logger->error($exception->getMessage());
                throw $exception;
            } else if ($returnType instanceof ReflectionUnionType) {
                $exception = InvalidServiceDelegate::factoryMethodReturnsUnionType($delegateType, $delegateMethod);
                $this->logger->error($exception->getMessage());
                throw $exception;
            }

            if ($returnType instanceof ReflectionNamedType) {
                if (!class_exists($returnType->getName()) && !interface_exists($returnType->getName())) {
                    $exception = InvalidServiceDelegate::factoryMethodReturnsScalarType($delegateType, $delegateMethod);
                    $this->logger->error($exception->getMessage());
                    throw $exception;
                }
                return ServiceDelegateDefinitionBuilder::forService(objectType($returnType->getName()))
                    ->withDelegateMethod(objectType($delegateType), $delegateMethod)
                    ->withAttribute($attribute)
                    ->build();
            } else {
                $exception = InvalidServiceDelegate::factoryMethodDoesNotDeclareService($delegateType, $delegateMethod);
                $this->logger->error($exception->getMessage());
                throw $exception;
            }
        }
    }

    private function buildServicePrepareDefinition(AnnotatedTarget $target) : ServicePrepareDefinition {
        $reflection = $target->getTargetReflection();
        assert($reflection instanceof ReflectionMethod);
        $prepareType = $reflection->getDeclaringClass()->getName();
        $method = $reflection->getName();
        $attribute = $target->getAttributeInstance();
        assert($attribute instanceof ServicePrepare);

        return ServicePrepareDefinitionBuilder::forMethod(objectType($prepareType), $method)
            ->withAttribute($attribute)
            ->build();
    }

    private function buildConfigurationDefinition(AnnotatedTarget $target) : ConfigurationDefinition {
        $builder = ConfigurationDefinitionBuilder::forClass(objectType($target->getTargetReflection()->getName()));
        $attributeInstance = $target->getAttributeInstance();
        assert($attributeInstance instanceof ConfigurationAttribute);
        $name = $attributeInstance->getName();
        if ($name !== null) {
            $builder = $builder->withName($name);
        }
        return $builder->withAttribute($attributeInstance)->build();
    }

    private function buildInjectDefinition(AnnotatedTarget $target) : InjectDefinition {
        if ($target->getTargetReflection() instanceof \ReflectionProperty) {
            return $this->buildPropertyInjectDefinition($target);
        } else {
            return $this->buildMethodInjectDefinition($target);
        }
    }

    private function buildMethodInjectDefinition(AnnotatedTarget $target) : InjectDefinition {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof \ReflectionParameter);
        $declaringClass = $targetReflection->getDeclaringClass();
        assert(!is_null($declaringClass));

        $serviceType = objectType($declaringClass->getName());
        $method = $targetReflection->getDeclaringFunction()->getName();
        $param = $targetReflection->getName();
        $paramType = $this->convertReflectionType($targetReflection->getType());
        $attributeInstance = $target->getAttributeInstance();
        assert($attributeInstance instanceof InjectAttribute);

        $builder = InjectDefinitionBuilder::forService($serviceType)->withMethod($method, $paramType, $param);

        return $this->buildInjectFromAttributeData($builder, $attributeInstance);
    }

    private function buildPropertyInjectDefinition(AnnotatedTarget $target) : InjectDefinition {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof \ReflectionProperty);

        $propType = $this->convertReflectionType($targetReflection->getType());
        $attributeInstance = $target->getAttributeInstance();
        assert($attributeInstance instanceof InjectAttribute);

        $builder = InjectDefinitionBuilder::forService(objectType($targetReflection->getDeclaringClass()->getName()))
            ->withProperty(
                $propType,
                $target->getTargetReflection()->getName()
            );

        return $this->buildInjectFromAttributeData($builder, $attributeInstance);
    }

    private function buildInjectFromAttributeData(InjectDefinitionBuilder $builder, InjectAttribute $inject) : InjectDefinition {
        $builder = $builder->withAttribute($inject)->withValue($inject->getValue());
        $from = $inject->getFrom();
        if ($from !== null) {
            $builder = $builder->withStore($from);
        }

        $profiles = $inject->getProfiles();
        if (count($profiles) === 0) {
            $profiles[] = 'default';
        }

        $builder = $builder->withProfiles(...$profiles);
        return $builder->build();
    }

    private function convertReflectionType(?ReflectionType $reflectionType) : Type|TypeUnion|TypeIntersect {
        if ($reflectionType instanceof ReflectionNamedType) {
            $paramType = $this->convertReflectionNamedType($reflectionType);
            // The ?type syntax is not recognized as a TypeUnion but we normalize it to use with our type system
            if ($paramType !== mixedType() && $reflectionType->allowsNull()) {
                $paramType = typeUnion($paramType, nullType());
            }
        } else if ($reflectionType instanceof ReflectionUnionType || $reflectionType instanceof ReflectionIntersectionType) {
            $types = [];
            foreach ($reflectionType->getTypes() as $type) {
                assert($type instanceof ReflectionNamedType);
                $types[] = $this->convertReflectionNamedType($type);
            }
            if ($reflectionType instanceof ReflectionUnionType) {
                $paramType = typeUnion(...$types);
            } else {
                /** @psalm-var list<ObjectType> $types */
                $paramType = typeIntersect(...$types);
            }
        } else {
            $paramType = mixedType();
        }

        return $paramType;
    }

    private function convertReflectionNamedType(ReflectionNamedType $reflectionNamedType) : Type {
        return  match ($type = $reflectionNamedType->getName()) {
            'int' => intType(),
            'string' => stringType(),
            'bool' => boolType(),
            'array' => arrayType(),
            'float' => floatType(),
            'mixed' => mixedType(),
            default => objectType($type)
        };
    }


}