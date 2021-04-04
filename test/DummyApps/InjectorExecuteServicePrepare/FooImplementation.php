<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\InjectorExecuteServicePrepare;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
class FooImplementation implements FooInterface {

    private ?BarInterface $bar = null;

    #[ServicePrepare]
    public function setBar(BarInterface $bar) : void {
        $this->bar = $bar;
    }

    public function getBar() : ?BarInterface {
        return $this->bar;
    }
}