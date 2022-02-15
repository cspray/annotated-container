<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\InjectService;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class MixedDefinesConstructorInjection {

    public function __construct(
        #[InjectService(FirstMixedDefinesImplementation::class)]
        public MixedDefinesInterface $first,
        #[InjectService(SecondMixedDefinesImplementation::class)]
        public MixedDefinesInterface $second,
        #[InjectScalar("mixed defines")]
        public string $stringParam
    ) {}

}