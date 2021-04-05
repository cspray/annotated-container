<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\SimpleDefineService;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class BazImplementation implements FooInterface {

}