<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\ClassOnlyPrepareServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface FooInterface {

    public function setBar();

}