<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\SimpleUseService;

use Cspray\AnnotatedContainer\Attribute\UseService;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class SetterInjection {

    public ?FooInterface $baz = null;
    public ?FooInterface $bar = null;
    public ?FooInterface $qux = null;

    #[ServicePrepare]
    public function setBaz(
        #[UseService(BazImplementation::class)]
        FooInterface $foo
    ) {
        $this->baz = $foo;
    }

    #[ServicePrepare]
    public function setBar(
        #[UseService(BarImplementation::class)]
        FooInterface $foo
    ) {
        $this->bar = $foo;
    }

    #[ServicePrepare]
    public function setQux(
        #[UseService(QuxImplementation::class)]
        FooInterface $foo
    ) {
        $this->qux = $foo;
    }
}