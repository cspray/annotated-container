<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\UseService;
use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
class MixedDefinesSetterInjection {

    public MixedDefinesInterface $first;

    public MixedDefinesInterface $second;

    public string $stringParam;

    #[ServicePrepare]
    public function setFirst(
        #[UseService(FirstMixedDefinesImplementation::class)]
        MixedDefinesInterface $first
    ) {
        $this->first = $first;
    }

    #[ServicePrepare]
    public function setSecond(
        #[UseService(SecondMixedDefinesImplementation::class)]
        MixedDefinesInterface $second
    ) {
        $this->second = $second;
    }

    #[ServicePrepare]
    public function setString(
        #[UseScalar("this is the value")]
        string $string
    ) {
        $this->stringParam = $string;
    }
}