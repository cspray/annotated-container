<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Initializer;

use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;

abstract class ThirdPartyInitializer {

    final public function __construct() {
    }

    abstract public function packageName() : string;

    /**
     * @return list<non-empty-string>
     */
    abstract public function relativeScanDirectories() : array;

    /**
     * @return list<class-string<Listener>>
     */
    abstract public function listeners() : array;

    /**
     * @return ?class-string<DefinitionProvider>
     */
    abstract public function definitionProviderClass() : ?string;
}
