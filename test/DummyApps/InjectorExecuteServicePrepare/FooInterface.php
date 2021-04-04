<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\InjectorExecuteServicePrepare;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
interface FooInterface {

    #[ServicePrepare]
    public function setBar(BarInterface $bar) : void;

}