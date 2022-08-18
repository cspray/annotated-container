<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * @deprecated This class is designated to be removed in 2.0
 */
enum AnnotatedContainerLifecycle {
    case BeforeCompile;
    case AfterCompile;
    case BeforeContainerCreation;
    case AfterContainerCreation;
}