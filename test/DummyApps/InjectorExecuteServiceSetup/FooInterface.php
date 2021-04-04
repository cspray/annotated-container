<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\InjectorExecuteServiceSetup;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServiceSetup;

#[Service]
interface FooInterface {

    #[ServiceSetup]
    public function setBar(BarInterface $bar) : void;

}