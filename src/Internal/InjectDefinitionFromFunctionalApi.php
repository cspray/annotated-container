<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;


use Cspray\AnnotatedContainer\Attribute\InjectAttribute;

final readonly class InjectDefinitionFromFunctionalApi implements InjectAttribute {

    /**
     * @param mixed $value
     * @param list<non-empty-string> $profiles
     * @param non-empty-string|null $from
     */
    public function __construct(
        private mixed $value,
        private array $profiles,
        private ?string $from
    ) {}

    public function value() : mixed {
        return $this->value;
    }

    /**
     * @return list<non-empty-string>
     */
    public function profiles() : array {
        return $this->profiles;
    }

    /**
     * @return non-empty-string|null
     */
    public function from() : ?string {
        return $this->from;
    }
}