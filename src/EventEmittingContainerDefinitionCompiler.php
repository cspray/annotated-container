<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Internal\AfterCompileAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeCompileAnnotatedContainerEvent;

final class EventEmittingContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    public function __construct(
        private readonly ContainerDefinitionCompiler $compiler,
        private readonly AnnotatedContainerEmitter $emitter
    ) {}

    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition {
        $this->emitter->trigger(new BeforeCompileAnnotatedContainerEvent());
        $containerDefinition = $this->compiler->compile($containerDefinitionCompileOptions);
        $this->emitter->trigger(new AfterCompileAnnotatedContainerEvent($containerDefinition));
        return $containerDefinition;
    }
}