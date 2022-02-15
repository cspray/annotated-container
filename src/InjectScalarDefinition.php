<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

final class InjectScalarDefinition {

    public function __construct(
        private string $type,
        private string $method,
        private string $param,
        private string $paramType,
        private string|int|float|bool|array $value
    ) {}

    public function getType() : string {
        return $this->type;
    }

    public function getMethod() : string {
        return $this->method;
    }

    public function getParamName() : string {
        return $this->param;
    }

    public function getParamType() : string {
        return $this->paramType;
    }

    public function getValue() : string|int|float|bool|array {
        return $this->value;
    }

}