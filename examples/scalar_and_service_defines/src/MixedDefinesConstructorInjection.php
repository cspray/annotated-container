<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\DefineService;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class MixedDefinesConstructorInjection {

    public function __construct(
        #[DefineService(FirstMixedDefinesImplementation::class)]
        public MixedDefinesInterface $first,
        #[DefineService(SecondMixedDefinesImplementation::class)]
        public MixedDefinesInterface $second,
        #[DefineScalar("mixed defines")]
        public string $stringParam
    ) {}

}