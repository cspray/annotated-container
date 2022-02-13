<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Psr\Container\ContainerInterface;

interface ContainerFactory {

    public function createContainer(ContainerDefinition $containerDefinition) : ContainerInterface;

}