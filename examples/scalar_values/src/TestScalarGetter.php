<?php declare(strict_types=1);


namespace Acme\AnnotatedInjectorDemo;


use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service(environments: ['test'])]
class TestScalarGetter extends AbstractScalarGetter implements ScalarGetter {

    public function __construct(
        #[DefineScalar('test string')]
        string $stringParam,
        #[DefineScalar(-1)]
        int $intParam,
        #[DefineScalar(-3.14)]
        float $floatParam,
        #[DefineScalar(false)]
        bool $boolParam
    ) {
        parent::__construct($stringParam, $intParam, $floatParam, $boolParam);
    }

}