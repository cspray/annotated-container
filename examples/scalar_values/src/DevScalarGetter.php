<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(environments: ['dev'])]
class DevScalarGetter extends AbstractScalarGetter implements ScalarGetter {

    public function __construct(
        #[InjectScalar(FOO_BAR)]
        string $stringParam,
        #[InjectScalar(1)]
        int $intParam,
        #[InjectScalar(3.14)]
        float $floatParam,
        #[InjectScalar(true)]
        bool $boolParam
    ) {
        parent::__construct($stringParam, $intParam, $floatParam, $boolParam);
    }

}