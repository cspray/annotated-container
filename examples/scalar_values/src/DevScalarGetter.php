<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service(environments: ['dev'])]
class DevScalarGetter extends AbstractScalarGetter implements ScalarGetter {

    public function __construct(
        #[UseScalar(FOO_BAR)]
        string $stringParam,
        #[UseScalar(1)]
        int $intParam,
        #[UseScalar(3.14)]
        float $floatParam,
        #[UseScalar(true)]
        bool $boolParam
    ) {
        parent::__construct($stringParam, $intParam, $floatParam, $boolParam);
    }

}