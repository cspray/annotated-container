<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\SimpleUseService;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class BarImplementation implements FooInterface {

}