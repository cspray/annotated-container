<?php

namespace Cspray\AnnotatedContainer\LogicalErrorApps\ImplicitServiceDelegateNoType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooFactory {

    #[ServiceDelegate]
    public static function create() {}

}