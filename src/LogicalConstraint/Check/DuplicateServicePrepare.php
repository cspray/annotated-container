<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;

final class DuplicateServicePrepare implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition, array $profiles) : LogicalConstraintViolationCollection {
        $violations = new LogicalConstraintViolationCollection();

        /** @var array<non-empty-string, list<ServicePrepareAttribute|null>> $servicePrepareMap */
        $servicePrepareMap = [];

        foreach ($containerDefinition->getServicePrepareDefinitions() as $prepareDefinition) {
            $classMethod = sprintf(
                '%s::%s',
                $prepareDefinition->getService()->getName(),
                $prepareDefinition->getMethod()
            );

            $servicePrepareMap[$classMethod] ??= [];
            $servicePrepareMap[$classMethod][] = $prepareDefinition->getAttribute();
        }

        foreach ($servicePrepareMap as $classMethod => $attributes) {
            if (count($attributes) > 1) {
                $attributeTypes = trim(implode('- ', array_map(
                    static fn(?ServicePrepareAttribute $attribute) => ($attribute === null ? 'Call to servicePrepare() in DefinitionProvider' : 'Attributed with ' . $attribute::class) . PHP_EOL,
                    $attributes
                )));
                $message = <<<TEXT
The method "$classMethod" has been defined to prepare multiple times!

- $attributeTypes

This will result in undefined behavior, determined by the backing container, and 
should be avoided.

TEXT;

                $violations->add(LogicalConstraintViolation::warning($message));
            }
        }

        return $violations;
    }

}
