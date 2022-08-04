<?php

namespace Cspray\AnnotatedContainer\LogicalErrorApps\ImplicitServiceDelegateIntersectionType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooFactory {

    #[ServiceDelegate]
    public static function create() : FooService&BarService {

    }

}