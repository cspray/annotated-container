<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\SimpleDefineService;

use Cspray\AnnotatedInjector\Attribute\DefineService;
use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
class SetterInjection {

    public ?FooInterface $baz = null;
    public ?FooInterface $bar = null;
    public ?FooInterface $qux = null;

    #[ServicePrepare]
    public function setBaz(
        #[DefineService(BazImplementation::class)]
        FooInterface $foo
    ) {
        $this->baz = $foo;
    }

    #[ServicePrepare]
    public function setBar(
        #[DefineService(BarImplementation::class)]
        FooInterface $foo
    ) {
        $this->bar = $foo;
    }

    #[ServicePrepare]
    public function setQux(
        #[DefineService(QuxImplementation::class)]
        FooInterface $foo
    ) {
        $this->qux = $foo;
    }
}