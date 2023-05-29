<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\Typiphy\ObjectType;

/**
 * @internal
 */
final class ContainerReference {

    public function __construct(
        public readonly string $name,
        public readonly ObjectType $type
    ) {}

}