<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\SimpleUseService;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class QuxImplementation implements FooInterface {

}