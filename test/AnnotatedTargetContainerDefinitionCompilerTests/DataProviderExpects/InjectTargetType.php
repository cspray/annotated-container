<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects;

use Cspray\AnnotatedContainer\Definition\InjectTargetIdentifier;

enum InjectTargetType {
    case MethodParameter;
    case ClassProperty;

    public function isValidTargetIdentifier(InjectTargetIdentifier $injectTargetIdentifier) : bool {
        if ($this === self::MethodParameter) {
            return $injectTargetIdentifier->isMethodParameter();
        } else if ($this === self::ClassProperty) {
            return $injectTargetIdentifier->isClassProperty();
        }

        return false;
    }
}