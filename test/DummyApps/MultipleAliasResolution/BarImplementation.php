<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\DummyApps\MultipleAliasResolution;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class BarImplementation implements FooInterface {

}