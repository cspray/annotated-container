<?php declare(strict_types=1);


namespace Acme\AnnotatedContainerDemo;


use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(environments: ['test'])]
class TestScalarGetter extends AbstractScalarGetter implements ScalarGetter {

    public function __construct(
        #[InjectScalar('test string')]
        string $stringParam,
        #[InjectScalar(-1)]
        int $intParam,
        #[InjectScalar(-3.14)]
        float $floatParam,
        #[InjectScalar(false)]
        bool $boolParam
    ) {
        parent::__construct($stringParam, $intParam, $floatParam, $boolParam);
    }

}