<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

final class Fixtures {

    private function __construct() {}

    public static function singleConcreteService() : SingleConcreteServiceFixture {
        return new SingleConcreteServiceFixture();
    }

    public static function singleAliasedService() : SingleAliasedServiceFixture {
        return new SingleAliasedServiceFixture();
    }

}