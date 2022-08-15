<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

enum SupportedContainers {
    case Default;
    case Auryn;
    case PhpDi;
}