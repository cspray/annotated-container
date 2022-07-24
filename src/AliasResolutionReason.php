<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

enum AliasResolutionReason {
    case NoConcreteService;
    case SingleConcreteService;
    case MultipleConcreteService;
    case ConcreteServiceIsPrimary;
}