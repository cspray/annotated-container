<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\DummyApps\SimpleUseService;

use Cspray\AnnotatedContainer\Attribute\InjectService;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class ConstructorInjection {

    public function __construct(
        #[InjectService(BarImplementation::class)]
        public FooInterface $bar,
        #[InjectService(BazImplementation::class)]
        public FooInterface $baz,
        #[InjectService(QuxImplementation::class)]
        public FooInterface $qux
    ) {}

}