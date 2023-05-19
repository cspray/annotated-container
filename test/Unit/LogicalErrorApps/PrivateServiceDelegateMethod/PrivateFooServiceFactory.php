<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\PrivateServiceDelegateMethod;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

final class PrivateFooServiceFactory {

    #[ServiceDelegate]
    private static function createFoo() : FooService {
        return new FooService();
    }

}