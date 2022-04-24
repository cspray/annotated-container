<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

/**
 * Marks an interface or class that should be wired into the Injector as a shared object or alias.
 *
 * Please be sure to review the README's overview of the Service Attribute.
 *
 * @package Cspray\AnnotatedContainer\Attribute
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Service {

    public function __construct(
        public array $profiles = [],
        public bool $primary = false,
        public bool $shared = true,
        public ?string $name = null
    ) {}

}