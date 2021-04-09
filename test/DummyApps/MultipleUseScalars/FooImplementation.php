<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\DummyApps\MultipleUseScalars;

use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

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