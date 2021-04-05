<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

/**
 * Marks an interface or class that should be wired into the Injector as a shared object or alias.
 *
 * @package Cspray\AnnotatedInjector\Attribute
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Service {

    public function __construct(
        /**
         * The environment in which this service is used; if no environments are provided the service is used for all
         * environments.
         *
         * Environments are a good way to handle when there are differences in testing, development, and/or production
         * implementations and 1 interface has multiple implementations and an appropriate one must be determined to
         * resolve the conflict.
         */
        private array $environments = []
    ) {}

}