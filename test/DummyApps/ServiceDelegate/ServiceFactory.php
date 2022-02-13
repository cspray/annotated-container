<?php

namespace Cspray\AnnotatedContainer\DummyApps\ServiceDelegate;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class ServiceFactory {

    private FooService $fooService;

    public function __construct(FooService $fooService) {
        $this->fooService = $fooService;
    }

    #[ServiceDelegate(ServiceInterface::class)]
    public function createService() : ServiceInterface {
        return new class($this->fooService) implements ServiceInterface {

            private FooService $fooService;

            public function __construct(FooService $fooService) {
                $this->fooService = $fooService;
            }

            public function getValue(): string {
                return 'From ServiceFactory ' . $this->fooService->getValue();
            }
        };
    }

}