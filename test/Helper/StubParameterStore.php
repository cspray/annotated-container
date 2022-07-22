<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\ParameterStore;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;

final class StubParameterStore implements ParameterStore {

    public function getName() : string {
        return 'test-store';
    }

    public function fetch(TypeUnion|Type|TypeIntersect $type, string $key) : string {
        return 'from test-store ' . $key;
    }
}