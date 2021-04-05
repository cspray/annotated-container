<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

final class DefineScalarDefinition {

    public function __construct(
        private string $type,
        private string $method,
        private string $param,
        private string $paramType,
        private string|int|float|bool|array $value,
        private bool $isPlainValue,
        private bool $isEnvironmentVar
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

    public function isPlainValue() : bool {
        return $this->isPlainValue;
    }

    public function isEnvironmentVar() : bool {
        return $this->isEnvironmentVar;
    }

}