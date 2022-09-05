<?php

namespace Cspray\AnnotatedContainerFixture\AliasedConfiguration;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface AppConfig {

    public function getAppName() : string;

}