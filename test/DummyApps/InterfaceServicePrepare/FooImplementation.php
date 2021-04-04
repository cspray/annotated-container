<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\InterfaceServicePrepare;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
class FooImplementation implements FooInterface {

    private int $barCounter = 0;

    public function setBar() {
        $this->barCounter++;
    }

    public function getBarCounter() : int {
        return $this->barCounter;
    }

}