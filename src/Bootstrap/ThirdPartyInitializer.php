<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

abstract class ThirdPartyInitializer {

    final public function __construct() {}

    abstract public function getPackageName() : string;

    abstract public function getRelativeScanDirectories() : array;

    abstract public function getObserverClasses() : array;

    abstract public function getDefinitionProviderClass() : ?string;

}