<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects;

use Cspray\Typiphy\ObjectType;

final class ExpectedServiceIsPrimary {

    public function __construct(public readonly ObjectType $type, public readonly bool $isPrimary) {}

}