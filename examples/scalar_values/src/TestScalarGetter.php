<?php declare(strict_types=1);


namespace Acme\AnnotatedInjectorDemo;


use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

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