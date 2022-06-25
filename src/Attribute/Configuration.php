<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

/**
 * An Attribute that defines a class as being a Configuration object.
 *
 * A Configuration object is expected to consist primarily of public readonly properties that have been annotated with
 * the Inject Attribute.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Configuration {

    /**
     * @param string|null $name An arbitrary name that can be used to retrieve this Configuration with Container::get
     *                          in addition to the FQCN
     */
    public function __construct(
        public readonly ?string $name = null
    ) {}

}