<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\DummyApps\MultipleDefineScalars;

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
class FooImplementation {

    public string $prepareParam = '';

    public function __construct(
        #[DefineScalar("constructor param")]
        public string $stringParam
    ) {}

    #[ServicePrepare]
    public function setUp(
        #[DefineScalar("prepare param")]
        string $stringParam
    ) {
        $this->prepareParam = $stringParam;
    }

}