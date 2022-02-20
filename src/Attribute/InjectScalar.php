<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

/**
 * Defines a scalar value that should be used for a parameter to a Service constructor or method annotated
 * with ServicePrepare.
 *
 * It is possible to gather scalar values from the environment, {@see InjectEnv}. Please also be sure to
 * review the README's documentation on scalar constant resolution.
 *
 * @package Cspray\AnnotatedContainer\Attribute
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class InjectScalar {

    public function __construct(
        private string|int|float|bool|array $value
    ) {}

}