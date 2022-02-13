<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\InjectorExecuteServicePrepare;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
interface FooInterface {

    #[ServicePrepare]
    public function setBar(BarInterface $bar) : void;

}