<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\DummyApps\SimpleUseService;

use Cspray\AnnotatedInjector\Attribute\UseService;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class ConstructorInjection {

    public function __construct(
        #[UseService(BarImplementation::class)]
        public FooInterface $bar,
        #[UseService(BazImplementation::class)]
        public FooInterface $baz,
        #[UseService(QuxImplementation::class)]
        public FooInterface $qux
    ) {}

}