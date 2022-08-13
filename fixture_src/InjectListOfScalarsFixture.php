<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectListOfScalarsFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectListOfScalars';
    }

}