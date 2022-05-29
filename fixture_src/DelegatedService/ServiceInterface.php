<?php

namespace Cspray\AnnotatedContainerFixture\DelegatedService;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface ServiceInterface {

    public function getValue() : string;

}