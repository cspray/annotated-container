<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\InjectService;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class MixedDefinesSetterInjection {

    public MixedDefinesInterface $first;

    public MixedDefinesInterface $second;

    public string $stringParam;

    #[ServicePrepare]
    public function setFirst(
        #[InjectService(FirstMixedDefinesImplementation::class)]
        MixedDefinesInterface $first
    ) {
        $this->first = $first;
    }

    #[ServicePrepare]
    public function setSecond(
        #[InjectService(SecondMixedDefinesImplementation::class)]
        MixedDefinesInterface $second
    ) {
        $this->second = $second;
    }

    #[ServicePrepare]
    public function setString(
        #[InjectScalar("this is the value")]
        string $string
    ) {
        $this->stringParam = $string;
    }
}