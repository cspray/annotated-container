<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\InterfaceServiceSetup;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServiceSetup;

#[Service]
interface FooInterface {

    #[ServiceSetup]
    public function setBar();

}