<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\ContainerFactory;

use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface ServiceDelegated {

    public function handleServiceDelegated(Profiles $profiles, ServiceDelegateDefinition $definition) : void;

}
