<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\DummyApps\SimpleUseService;

use Cspray\AnnotatedContainer\Attribute\UseService;
use Cspray\AnnotatedContainer\Attribute\Service;

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