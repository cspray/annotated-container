<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\State;

use Cspray\AnnotatedContainer\Definition\InjectDefinition;

/**
 * @internal
 */
interface InjectParameterValueProvider {

    public function resolveInjectParameterValue(
        object $container,
        ContainerFactoryState $state,
        InjectDefinition $injectDefinition
    ) : InjectParameterValue;
}
