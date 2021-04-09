<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\UseService;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class MixedDefinesConstructorInjection {

    public function __construct(
        #[UseService(FirstMixedDefinesImplementation::class)]
        public MixedDefinesInterface $first,
        #[UseService(SecondMixedDefinesImplementation::class)]
        public MixedDefinesInterface $second,
        #[UseScalar("mixed defines")]
        public string $stringParam
    ) {}

}