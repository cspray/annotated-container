<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\DummyApps\SimpleDefineService;

use Cspray\AnnotatedInjector\Attribute\DefineService;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class ConstructorInjection {

    public function __construct(
        #[DefineService(BarImplementation::class)]
        public FooInterface $bar,
        #[DefineService(BazImplementation::class)]
        public FooInterface $baz,
        #[DefineService(QuxImplementation::class)]
        public FooInterface $qux
    ) {}

}