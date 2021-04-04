<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\ClassOverridesInterfaceServiceSetup;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServiceSetup;

#[Service]
class FooImplementation implements FooInterface {

    private int $barCounter = 0;

    #[ServiceSetup]
    public function setBar() {
        $this->barCounter++;
    }

    public function getBarCounter() : int {
        return $this->barCounter;
    }

}