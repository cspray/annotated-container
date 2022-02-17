<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\DummyApps\MultipleUseScalars;

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class FooImplementation {

    public string $prepareParam = '';

    public function __construct(
        #[InjectScalar("constructor param")]
        public string $stringParam
    ) {}

    #[ServicePrepare]
    public function setUp(
        #[InjectScalar("prepare param")]
        string $stringParam
    ) {
        $this->prepareParam = $stringParam;
    }

}