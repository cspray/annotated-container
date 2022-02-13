<?php declare(strict_types=1);


namespace Acme\AnnotatedContainerDemo;


use Cspray\AnnotatedContainer\Attribute\UseScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(environments: ['prod'])]
class ProdScalarGetter extends AbstractScalarGetter implements ScalarGetter {

    public function __construct(
        #[UseScalar('prod string')]
        string $stringParam,
        #[UseScalar(42)]
        int $intParam,
        #[UseScalar(6.28)]
        float $floatParam,
        #[UseScalar(false)]
        bool $boolParam
    ) {
        parent::__construct($stringParam, $intParam, $floatParam, $boolParam);
    }

}