<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

/**
 * Defines a scalar value that should be used for a parameter to a Service constructor or method annotated
 * with ServicePrepare.
 *
 * It is possible to gather scalar values from the environment, {@see UseScalarFromEnv}. Please also be sure to
 * review the README's documentation on scalar constant resolution.
 *
 * @package Cspray\AnnotatedInjector\Attribute
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class UseScalar {

    public function __construct(
        private string|int|float|bool|array $value
    ) {}

}