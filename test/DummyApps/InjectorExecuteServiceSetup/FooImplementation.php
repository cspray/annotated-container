<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\InjectorExecuteServiceSetup;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServiceSetup;

#[Service]
class FooImplementation implements FooInterface {

    private ?BarInterface $bar = null;

    #[ServiceSetup]
    public function setBar(BarInterface $bar) : void {
        $this->bar = $bar;
    }

    public function getBar() : ?BarInterface {
        return $this->bar;
    }
}