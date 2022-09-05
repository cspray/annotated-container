<?php

namespace Cspray\AnnotatedContainerFixture\AliasedConfiguration;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
final class MyAppConfig implements AppConfig {

    #[Inject('my-app-name')]
    private readonly string $name;

    public function getAppName() : string {
        return $this->name;
    }
}