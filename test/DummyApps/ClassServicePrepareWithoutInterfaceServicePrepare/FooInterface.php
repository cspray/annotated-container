<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\ClassServicePrepareWithoutInterfaceServicePrepare;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface FooInterface {

    public function setBar();

}