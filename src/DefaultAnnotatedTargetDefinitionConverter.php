<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
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

/**
 *
 */
final class DefaultAnnotatedTargetDefinitionConverter implements AnnotatedTargetDefinitionConverter {

    public function convert(AnnotatedTarget $target) : ServiceDefinition|ServicePrepareDefinition|ServiceDelegateDefinition|InjectDefinition|ConfigurationDefinition {
        return match ($target->getAttributeReflection()->getName()) {
            AttributeType::Service->value => $this->buildServiceDefinition($target),
            AttributeType::ServiceDelegate->value => $this->buildServiceDelegateDefinition($target),
            AttributeType::ServicePrepare->value => $this->buildServicePrepareDefinition($target),
            AttributeType::Inject->value => $this->buildInjectDefinition($target),
            AttributeType::Configuration->value => $this->buildConfigurationDefinition($target)
        };
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
            $builder = ServiceDefinitionBuilder::forConcrete($serviceType, $attribute->primary);
        }

        $profiles = empty($attribute->profiles) ? ['default'] : $attribute->profiles;
        $builder = $builder->withProfiles($profiles);
        if ($attribute->name !== null) {
            $builder = $builder->withName($attribute->name);
        }

        return $builder->build();
    }

    private function buildServiceDelegateDefinition(AnnotatedTarget $target) : ServiceDelegateDefinition {
        $reflection = $target->getTargetReflection();
        assert($reflection instanceof ReflectionMethod);
        $delegateType = $reflection->getDeclaringClass()->getName();
        $delegateMethod = $reflection->getName();
        return ServiceDelegateDefinitionBuilder::forService(objectType($target->getAttributeInstance()->service))->withDelegateMethod(objectType($delegateType), $delegateMethod)->build();
    }

    private function buildServicePrepareDefinition(AnnotatedTarget $target) : ServicePrepareDefinition {
        $reflection = $target->getTargetReflection();
        assert($reflection instanceof ReflectionMethod);
        $prepareType = $reflection->getDeclaringClass()->getName();
        $method = $reflection->getName();
        return ServicePrepareDefinitionBuilder::forMethod(objectType($prepareType), $method)->build();
    }

    private function buildConfigurationDefinition(AnnotatedTarget $target) : ConfigurationDefinition {
        $builder = ConfigurationDefinitionBuilder::forClass(objectType($target->getTargetReflection()->getName()));
        if (!is_null($target->getAttributeInstance()->name)) {
            $builder = $builder->withName($target->getAttributeInstance()->name);
        }
        return $builder->build();
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
        if (is_null($targetReflection->getType())) {
            $paramType = mixedType();
        } else if ($targetReflection->getType() instanceof ReflectionNamedType) {
            $paramType = $this->convertReflectionNamedType($targetReflection->getType());
            // The ?type syntax is not recognized as a TypeUnion but we normalize it to use with our type system
            if ($paramType !== mixedType() && $targetReflection->getType()->allowsNull()) {
                $paramType = typeUnion($paramType, nullType());
            }
        } else if ($targetReflection->getType() instanceof ReflectionUnionType || $targetReflection->getType() instanceof ReflectionIntersectionType) {
            $types = [];
            foreach ($targetReflection->getType()->getTypes() as $type) {
                assert($type instanceof ReflectionNamedType);
                $types[] = $this->convertReflectionNamedType($type);
            }
            if ($targetReflection->getType() instanceof ReflectionUnionType) {
                $paramType = typeUnion(...$types);
            } else {
                /** @psalm-var list<ObjectType> $types */
                $paramType = typeIntersect(...$types);
            }
        } else {
            throw new \Exception();
        }

        $builder = InjectDefinitionBuilder::forService($serviceType)
            ->withMethod($method, $paramType, $param)
            ->withValue($target->getAttributeInstance()->value);

        if (isset($target->getAttributeInstance()->from)) {
            $builder = $builder->withStore($target->getAttributeInstance()->from);
        }

        $profiles = $target->getAttributeInstance()->profiles;
        if (empty($profiles)) {
            $profiles[] = 'default';
        }

        $builder = $builder->withProfiles(...$profiles);
        return $builder->build();
    }

    private function buildPropertyInjectDefinition(AnnotatedTarget $target) : InjectDefinition {
        $targetReflection = $target->getTargetReflection();
        assert($targetReflection instanceof \ReflectionProperty);
        $builder = InjectDefinitionBuilder::forService(objectType($targetReflection->getDeclaringClass()->getName()));
        if ($targetReflection->getType() instanceof ReflectionNamedType) {
            $propType = $this->convertReflectionNamedType($targetReflection->getType());
        } else if ($targetReflection->getType() instanceof ReflectionIntersectionType || $targetReflection->getType() instanceof ReflectionUnionType) {
            $types = [];
            foreach ($targetReflection->getType()->getTypes() as $reflectionType) {
                assert($reflectionType instanceof ReflectionNamedType);
                $types[] = $this->convertReflectionNamedType($reflectionType);
            }
            if ($targetReflection->getType() instanceof ReflectionIntersectionType) {
                /** @psalm-var list<ObjectType> $types */
                $propType = typeIntersect(...$types);
            } else {
                $propType = typeUnion(...$types);
            }
        } else {
            throw new \Exception();
        }
        $builder = $builder->withProperty(
            $propType,
            $target->getTargetReflection()->getName()
        );
        $builder = $builder->withValue($target->getAttributeInstance()->value);
        if (isset($target->getAttributeInstance()->from)) {
            $builder = $builder->withStore($target->getAttributeInstance()->from);
        }

        $profiles = $target->getAttributeInstance()->profiles;
        if (empty($profiles)) {
            $profiles[] = 'default';
        }

        $builder = $builder->withProfiles(...$profiles);
        return $builder->build();
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