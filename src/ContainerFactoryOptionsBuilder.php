<?php

namespace Cspray\AnnotatedContainer;

final class ContainerFactoryOptionsBuilder {

    private array $activeProfiles;

    private function __construct() {}

    public static function forActiveProfiles(string $profile, string... $additionalProfiles) : self {
        $instance = new self;
        $instance->activeProfiles = [$profile, ...$additionalProfiles];
        return $instance;
    }

    public function build() : ContainerFactoryOptions {
        return new class($this->activeProfiles) implements ContainerFactoryOptions {
            public function __construct(private array $activeProfiles) {}

            public function getActiveProfiles(): array {
                return $this->activeProfiles;
            }
        };
    }

}