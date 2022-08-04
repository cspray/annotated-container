<?php

namespace Cspray\AnnotatedContainerFixture\ImplicitServiceDelegateUnionType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class ServiceFactory {

    #[ServiceDelegate]
    public static function create() : BarService|FooService {
        return new FooService();
    }

}