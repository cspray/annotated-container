<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\ClassOverridesInterfaceServicePrepare;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
class FooImplementation implements FooInterface {

    private int $barCounter = 0;

    #[ServicePrepare]
    public function setBar() {
        $this->barCounter++;
    }

    public function getBarCounter() : int {
        return $this->barCounter;
    }

}