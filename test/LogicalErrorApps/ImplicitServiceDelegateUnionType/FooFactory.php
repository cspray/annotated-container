<?php

namespace Cspray\AnnotatedContainer\LogicalErrorApps\ImplicitServiceDelegateUnionType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooFactory {

    #[ServiceDelegate]
    public static function create() : FooService|BarService {

    }

}