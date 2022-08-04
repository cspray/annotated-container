<?php

namespace Cspray\AnnotatedContainer\LogicalErrorApps\ImplicitServiceDelegateScalarType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooFactory {

    #[ServiceDelegate]
    public static function create() : string {
        return '';
    }

}