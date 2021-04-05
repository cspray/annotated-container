<?php declare(strict_types=1);


namespace Acme\AnnotatedInjectorDemo;

abstract class AbstractScalarGetter implements ScalarGetter {

    protected function __construct(
        private string $stringParam,
        private int $intParam,
        private float $floatParam,
        private bool $boolParam
    ) {}

    public function getString() : string {
        return $this->stringParam;
    }

    public function getInt() : int {
        return $this->intParam;
    }

    public function getFloat() : float {
        return $this->floatParam;
    }

    public function getBool() : bool {
        return $this->boolParam;
    }

}