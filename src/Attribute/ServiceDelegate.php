<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

/**
 * Defines a class method that should be used to instantiate a service instead of having the Container
 * instantiate-and-autowire implicitly.
 *
 * If your factory depends on other services provided by the Container you can depend on them either in the __construct
 * method OR the method that you annotated with #[ServiceDelegate]. Instead of the Container implicitly
 * instantiate-and-autowire your Service it will do so with the defined factory. The functionality provided by the
 * AutowireableInvoker interface will then be utilized to actually invoke the method that creates your service.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class ServiceDelegate implements ServiceDelegateAttribute {

    /**
     * @param ?string $service The FQCN of the service that should be created, or null if return type of the attributed
     *                         method should be used instead.
     */
    public function __construct(public readonly ?string $service = null) {}

    public function getService() : ?string {
        return $this->service;
    }
}