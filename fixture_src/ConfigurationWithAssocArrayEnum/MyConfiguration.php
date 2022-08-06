<?php

namespace Cspray\AnnotatedContainerFixture\ConfigurationWithAssocArrayEnum;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
class MyConfiguration {

    #[Inject(['b' => MyEnum::B, 'c' => MyEnum::C])]
    public readonly array $letters;

}