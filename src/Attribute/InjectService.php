<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

/**
 * Define a Service that should be used as a param to a Service constructor method or method annotated with
 * ServicePrepare.
 *
 * @package Cspray\AnnotatedContainer\Attribute
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectService {

    public function __construct(
        private string $name
    ) {}

}