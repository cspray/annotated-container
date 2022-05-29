<?php

namespace Cspray\AnnotatedContainerFixture\DelegatedService;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooService {

    public function getValue() : string {
        return 'From FooService';
    }

}