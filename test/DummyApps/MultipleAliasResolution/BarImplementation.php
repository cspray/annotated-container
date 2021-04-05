<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\DummyApps\MultipleAliasResolution;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class BarImplementation implements FooInterface {

}