<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

final class AnnotatedContainerVersion {

    private static ?string $version = null;

    private function __construct() {}

    public static function getVersion() : string {
        if (self::$version === null) {
            self::$version = trim(file_get_contents(dirname(__DIR__) . '/VERSION'));
        }
        return self::$version;
    }

}