<?php

namespace Cspray\AnnotatedContainer;

use Psr\Container\ContainerInterface;

interface AnnotatedContainer extends ContainerInterface, AutowireableFactory, AutowireableInvoker {

    public function getBackingContainer() : object;

}