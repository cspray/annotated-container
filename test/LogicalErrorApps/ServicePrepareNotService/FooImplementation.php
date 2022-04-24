<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalErrorApps\ServicePrepareNotService;

use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

class FooImplementation {

    #[ServicePrepare]
    public function postConstruct() {}

}