<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\AutowireableFactoryServices;

class FactoryCreatedService {

    public function __construct(
        public readonly FooInterface $foo,
        public readonly string $scalar
    ) {}

}