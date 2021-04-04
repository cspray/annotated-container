<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\ClassServicePrepareWithoutInterfaceServicePrepare;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
interface FooInterface {

    public function setBar();

}