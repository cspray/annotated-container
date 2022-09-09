<?php

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ServiceDelegateNotService;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class ServiceFactory {

    #[ServiceDelegate]
    public function create() : FooService {
        return new FooService();
    }

}