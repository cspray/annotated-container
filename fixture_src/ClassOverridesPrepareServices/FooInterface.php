<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\ClassOverridesPrepareServices;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
interface FooInterface {

    #[ServicePrepare]
    public function setBar();

}