<?php declare(strict_types=1);


namespace Acme\AnnotatedInjectorDemo;


use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service(environments: ['prod'])]
class ProdScalarGetter extends AbstractScalarGetter implements ScalarGetter {

    public function __construct(
        #[DefineScalar('prod string')]
        string $stringParam,
        #[DefineScalar(42)]
        int $intParam,
        #[DefineScalar(6.28)]
        float $floatParam,
        #[DefineScalar(false)]
        bool $boolParam
    ) {
        parent::__construct($stringParam, $intParam, $floatParam, $boolParam);
    }

}