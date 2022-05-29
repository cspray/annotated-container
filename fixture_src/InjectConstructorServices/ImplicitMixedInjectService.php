<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class ImplicitMixedInjectService {

    public function __construct(
        #[Inject('something')] public $val
    ) {}

}