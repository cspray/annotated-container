<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

enum SupportedContainers {

    case Default;

    case Auryn;
    case PhpDi;
}