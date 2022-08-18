<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

/**
 * Marks an interface or class that should be wired into the Injector as a shared object or alias.
 *
 * @package Cspray\AnnotatedContainer\Attribute
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Service implements ServiceAttribute {

    /**
     * @param list<string> $profiles A list of profiles that must be active for this service to be included in the Container
     * @param bool $primary Whether this service should be used as the concrete alias if multiple aliases are found
     * @param string|null $name An arbitrary string passed to ContainerInterface::get to retrieve this service.
     *                          Retrieval of a Service by its arbitrary name is in addition to retrieving it by the
     *                          FQCN of the service. If $name is null then the service will only be retrievable by the
     *                          FQCN.
     */
    public function __construct(
        public readonly array $profiles = [],
        public readonly bool $primary = false,
        public readonly ?string $name = null
    ) {}

    public function getProfiles() : array {
        return $this->profiles;
    }

    public function isPrimary() : bool {
        return $this->primary;
    }

    public function getName() : ?string {
        return $this->name;
    }
}