<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use function Cspray\Typiphy\objectType;

final class DefaultAnnotatedTargetDefinitionConverter implements AnnotatedTargetDefinitionConverter {

    public function convert(AnnotatedTarget $target) : ServiceDefinition|ServicePrepareDefinition|ServiceDelegateDefinition {
        return match ($target->getAttributeReflection()->getName()) {
            AttributeType::Service->value => $this->buildServiceDefinition($target),
            AttributeType::ServiceDelegate->value => $this->buildServiceDelegateDefinition($target),
            AttributeType::ServicePrepare->value => $this->buildServicePrepareDefinition($target)
        };
    }

    private function buildServiceDefinition(AnnotatedTarget $target) : ServiceDefinition {
        $serviceType = objectType($target->getTargetReflection()->getName());
        /** @var Service $attribute */
        $attribute = $target->getAttributeInstance();
        if ($target->getTargetReflection()->isInterface() || $target->getTargetReflection()->isAbstract()) {
            $builder = ServiceDefinitionBuilder::forAbstract($serviceType);
        } else {
            $builder = ServiceDefinitionBuilder::forConcrete($serviceType, $attribute->primary);
        }

        $builder = $attribute->shared ? $builder->withShared() : $builder->withNotShared();
        $builder = $builder->withProfiles($attribute->profiles);
        if ($attribute->name !== null) {
            $builder = $builder->withName($attribute->name);
        }

        return $builder->build();
    }

    private function buildServiceDelegateDefinition(AnnotatedTarget $target) : ServiceDelegateDefinition {
        $delegateType = $target->getTargetReflection()->getDeclaringClass()->getName();
        $delegateMethod = $target->getTargetReflection()->getName();
        return ServiceDelegateDefinitionBuilder::forService(objectType($target->getAttributeInstance()->service))->withDelegateMethod(objectType($delegateType), $delegateMethod)->build();
    }

    private function buildServicePrepareDefinition(AnnotatedTarget $target) : ServicePrepareDefinition {
        $prepareType = $target->getTargetReflection()->getDeclaringClass()->getName();
        $method = $target->getTargetReflection()->getName();
        return ServicePrepareDefinitionBuilder::forMethod(objectType($prepareType), $method)->build();
    }

}