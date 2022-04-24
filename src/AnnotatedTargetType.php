<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

enum AnnotatedTargetType {
    case ClassTarget;
    case ClassConstTarget;
    case PropertyTarget;
    case MethodTarget;
    case ParameterTarget;
    case FunctionTarget;
}