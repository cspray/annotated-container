<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Initializer;

abstract class ThirdPartyInitializer {

    final public function __construct() {
    }

    abstract public function packageName() : string;

    /**
     * @return list<non-empty-string>
     */
    abstract public function relativeScanDirectories() : array;

    abstract public function definitionProviderClass() : ?string;
}
