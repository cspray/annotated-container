<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\UseScalar;
use Cspray\AnnotatedContainer\Attribute\UseService;
use Cspray\AnnotatedContainer\Attribute\Service;

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