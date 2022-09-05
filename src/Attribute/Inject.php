<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;
use UnitEnum;

/**
 * Represents a value that should be injected into a method parameter or Configuration property.
 *
 * As of v0.6.0, this attribute SHOULD only be repeated if each use specifies a unique set of profiles. The behavior for
 * which Inject attribute would be used if you do not specify unique profiles is intentionally undefined. It is possible
 * that any one of the attributes could be used, so it is highly recommended if you repeat an Inject Attribute you also
 * give it a profile!
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final class Inject implements InjectAttribute {

    /**
     * Inject an explicit value into a constructor parameter, service prepare parameter, service delegate parameter, or
     * Configuration property.
     *
     * Whether a scalar value or a service from the container is injected is dependent upon the type of parameter or
     * property this Attribute is annotated against. For example, if you use #[Inject] on a method parameter that is an
     * int then whatever value is passed to $value will be used. If the method parameter is a Foo service then whatever
     * alias or concrete service the Container has defined will be injected.
     *
     * If the $from parameter is provided at the time of Container creation a ParameterStore with a name matching that
     * parameter will be fetched from the list of known ParameterStore implementations. The $value and type associated
     * with the parameter or property will be provided to the ParameterStore. In these cases what precisely is injected
     * will be dependent on the specific ParameterStore implementation.
     *
     * This Attribute is repeatable to allow defining different values based on profiles. Each use of the Inject
     * Attribute on a distinct parameter or property should include a unique set of profiles. Whichever Inject Attribute
     * that is included in the list of active profiles will be used.
     *
     * @param mixed $value The value that should be injected or provided to the
     *                     ParameterStore defined by $from if it is present
     * @param string|null $from The ParameterStore that should be used to fetch the value, with $value acting as the key
     *                          to fetch from the given store.
     * @param list<string> $profiles A list of active profiles that this Inject Attribute will applicable for.
     */
    public function __construct(
        public readonly mixed $value,
        public readonly ?string $from = null,
        public readonly array $profiles = []
    ) {}

    public function getValue() : mixed {
        return $this->value;
    }

    /**
     * @return list<string>
     */
    public function getProfiles() : array {
        return $this->profiles;
    }

    public function getFrom() : ?string {
        return $this->from;
    }
}