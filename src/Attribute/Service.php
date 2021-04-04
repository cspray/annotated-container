<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

/**
 * Marks an interface or class that should be wired into the Injector as a shared object or alias.
 *
 * Annotating an abstract type with this Attribute MUST result in an error being thrown.
 *
 * If the annotated type is an interface it SHALL be shared with the Injector. If only 1 concrete class, annotated as a
 * Service, implements the interface it SHALL be aliased so that any Injector::make with the interface will
 * return the marked Service. If multiple classes are found marked with Service that implement the same interface
 * additional steps will be required to properly resolve dependencies.
 *
 * Resolving Multiple Services
 * =====================================================================================================================
 * When multiple implementations of the same type of concrete Service exist there are 2 ways to configure which
 * implementation should be used. The first way is an environment-specific fix and works across the entire Injector.
 * This method is reasonably secure against possible runtime dependency resolution errors. The second way is
 * specifically defining for each use of the Service as a constructor or method parameter which concrete implementation
 * should be used. This way of specifying is more brittle, if you miss defining a parameter a runtime error will be
 * thrown when the dependency could not be resolved.
 *
 * Environment dependency resolution
 * ---------------------------------------------------------------------------------------------------------------------
 * This is the recommended approach if your concrete implementations differ based on which environment they're expected
 * to operate in. For example, you have a production Service that writes to a storage in the cloud, a development Service
 * that operates on the local filesystem, and a testing Service that operates on a virtual filesystem in memory. For
 * each of these scenarios we have just 1 Service that we'd like to have in the specific environment and the others
 * "don't matter". In this scenario you would simply define on the Service annotation which environments this concrete
 * implementation should be wired.
 *
 * {@see DefineService} dependency resolution
 * ---------------------------------------------------------------------------------------------------------------------
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