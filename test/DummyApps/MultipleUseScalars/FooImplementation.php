<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\DummyApps\MultipleUseScalars;

use Cspray\AnnotatedContainer\Attribute\UseScalar;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class FooImplementation {

    public string $prepareParam = '';

    public function __construct(
        #[UseScalar("constructor param")]
        public string $stringParam
    ) {}

    #[ServicePrepare]
    public function setUp(
        #[UseScalar("prepare param")]
        string $stringParam
    ) {
        $this->prepareParam = $stringParam;
    }

}