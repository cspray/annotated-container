<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints\ProtectedServiceDelegateMethod;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

final class ProtectedFooServiceFactory {

    #[ServiceDelegate]
    protected static function createFoo() : FooService {
        return new FooService();
    }

}