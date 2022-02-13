<?php declare(strict_types=1);


namespace Acme\AnnotatedContainerDemo;


use Cspray\AnnotatedContainer\Attribute\UseScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(environments: ['test'])]
class TestScalarGetter extends AbstractScalarGetter implements ScalarGetter {

    public function __construct(
        #[UseScalar('test string')]
        string $stringParam,
        #[UseScalar(-1)]
        int $intParam,
        #[UseScalar(-3.14)]
        float $floatParam,
        #[UseScalar(false)]
        bool $boolParam
    ) {
        parent::__construct($stringParam, $intParam, $floatParam, $boolParam);
    }

}