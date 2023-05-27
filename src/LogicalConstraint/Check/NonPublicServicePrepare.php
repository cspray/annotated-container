<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;

final class NonPublicServicePrepare implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition, array $profiles) : LogicalConstraintViolationCollection {
        $violations =  new LogicalConstraintViolationCollection();

        foreach ($containerDefinition->getServicePrepareDefinitions() as $prepareDefinition) {
            $reflection = new \ReflectionMethod(sprintf('%s::%s', $prepareDefinition->getService()->getName(), $prepareDefinition->getMethod()));
            if ($reflection->isPrivate() || $reflection->isProtected()) {
                $protectedOrPrivate = $reflection->isProtected() ? 'protected' : 'private';
                $violations->add(
                    LogicalConstraintViolation::critical(
                        sprintf(
                            'A %s method, %s::%s, is marked as a service prepare. Service prepare methods MUST be marked public.',
                            $protectedOrPrivate,
                            $prepareDefinition->getService()->getName(),
                            $prepareDefinition->getMethod()
                        )
                    )
                );
            }
        }

        return $violations;
    }

}
