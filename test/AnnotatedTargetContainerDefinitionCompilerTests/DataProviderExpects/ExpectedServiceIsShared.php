<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects;

use Cspray\Typiphy\ObjectType;

final class ExpectedServiceIsShared {

    public function __construct(
        public readonly ObjectType $type,
        public readonly bool $isShared
    ) {}

}