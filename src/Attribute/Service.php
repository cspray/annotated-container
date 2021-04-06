<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

/**
 * Marks an interface or class that should be wired into the Injector as a shared object or alias.
 *
 * Please be sure to review the README's overview of the Service Attribute.
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
         * Please be sure to review the README's details on multiple alias resolution with the environment.
         */
        private array $environments = []
    ) {}

}