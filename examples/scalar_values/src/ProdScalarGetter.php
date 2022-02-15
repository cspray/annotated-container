<?php declare(strict_types=1);


namespace Acme\AnnotatedContainerDemo;


use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(environments: ['prod'])]
class ProdScalarGetter extends AbstractScalarGetter implements ScalarGetter {

    public function __construct(
        #[InjectScalar('prod string')]
        string $stringParam,
        #[InjectScalar(42)]
        int $intParam,
        #[InjectScalar(6.28)]
        float $floatParam,
        #[InjectScalar(false)]
        bool $boolParam
    ) {
        parent::__construct($stringParam, $intParam, $floatParam, $boolParam);
    }

}