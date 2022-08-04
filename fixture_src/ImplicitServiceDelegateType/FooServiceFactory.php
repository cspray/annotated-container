<?php

namespace Cspray\AnnotatedContainerFixture\ImplicitServiceDelegateType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooServiceFactory {

    #[ServiceDelegate]
    public static function create() : FooService {
        return new FooService();
    }

}