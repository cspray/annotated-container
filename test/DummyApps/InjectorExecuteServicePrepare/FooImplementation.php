<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\InjectorExecuteServicePrepare;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

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