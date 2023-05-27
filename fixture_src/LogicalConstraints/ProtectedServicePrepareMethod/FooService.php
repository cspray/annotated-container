<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints\ProtectedServicePrepareMethod;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class FooService {

    #[ServicePrepare]
    protected function postConstruct() {}

}