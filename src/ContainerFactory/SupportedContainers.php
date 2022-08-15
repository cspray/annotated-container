<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

/**
 * @deprecated This class is designated to be removed in 2.0
 */
enum SupportedContainers {
    case Default;
    case Auryn;
    case PhpDi;
}