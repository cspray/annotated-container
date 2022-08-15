<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Compile;

use Cspray\AnnotatedContainer\AnnotatedContainerEmitter;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\Internal\AfterCompileAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeCompileAnnotatedContainerEvent;

/**
 * @deprecated This class is designated to be removed in 2.0
 */
final class EventEmittingContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    public function __construct(
        private readonly ContainerDefinitionCompiler $compiler,
        private readonly AnnotatedContainerEmitter $emitter
    ) {}

    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition {
        $logger = $containerDefinitionCompileOptions->getLogger();
        if ($logger !== null) {
            $this->emitter->setLogger($logger);
        }
        $this->emitter->trigger(new BeforeCompileAnnotatedContainerEvent());
        $containerDefinition = $this->compiler->compile($containerDefinitionCompileOptions);
        $this->emitter->trigger(new AfterCompileAnnotatedContainerEvent($containerDefinition));
        return $containerDefinition;
    }
}