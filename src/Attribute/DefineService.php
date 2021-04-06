<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

/**
 * Define a Service that should be used as a param to a Service constructor method or method annotated with
 * ServicePrepare.
 *
 * @package Cspray\AnnotatedInjector\Attribute
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class DefineService {

    public function __construct(
        private string $name
    ) {}

}