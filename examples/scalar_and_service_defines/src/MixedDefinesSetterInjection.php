<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\DefineService;
use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
class MixedDefinesSetterInjection {

    public MixedDefinesInterface $first;

    public MixedDefinesInterface $second;

    public string $stringParam;

    #[ServicePrepare]
    public function setFirst(
        #[DefineService(FirstMixedDefinesImplementation::class)]
        MixedDefinesInterface $first
    ) {
        $this->first = $first;
    }

    #[ServicePrepare]
    public function setSecond(
        #[DefineService(SecondMixedDefinesImplementation::class)]
        MixedDefinesInterface $second
    ) {
        $this->second = $second;
    }

    #[ServicePrepare]
    public function setString(
        #[DefineScalar("this is the value")]
        string $string
    ) {
        $this->stringParam = $string;
    }
}