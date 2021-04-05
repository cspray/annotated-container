<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service(environments: ['dev'])]
class DevScalarGetter extends AbstractScalarGetter implements ScalarGetter {

    public function __construct(
        #[DefineScalar(FOO_BAR)]
        string $stringParam,
        #[DefineScalar(1)]
        int $intParam,
        #[DefineScalar(3.14)]
        float $floatParam,
        #[DefineScalar(true)]
        bool $boolParam
    ) {
        parent::__construct($stringParam, $intParam, $floatParam, $boolParam);
    }

}