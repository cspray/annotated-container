<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

/**
 * Defines a scalar value, that's gathered from an environment variable, that should be used for a param to a Service
 * constructor or method annotated with ServicePrepare.
 *
 * It is possible to pass a scalar's plain value, {@see UseScalar}. Please also be sure to review the README's
 * documentation for environment variable resolution.
 *
 * @package Cspray\AnnotatedContainer\Attribute
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class UseScalarFromEnv {

    public function __construct(private string $envVar) {}

}