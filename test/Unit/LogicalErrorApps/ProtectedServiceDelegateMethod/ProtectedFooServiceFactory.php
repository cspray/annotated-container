<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ProtectedServiceDelegateMethod;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

final class ProtectedFooServiceFactory {

    #[ServiceDelegate]
    protected static function createFoo() : FooService {
        return new FooService();
    }

}