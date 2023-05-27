<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints;

use Cspray\AnnotatedContainerFixture\Fixture;

final class ProtectedServicePrepareFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ProtectedServicePrepareMethod';
    }

}
