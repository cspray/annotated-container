<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Listener;

interface AfterBootstrap {

    public function handleAfterBootstrap(
        BootstrappingConfiguration $bootstrappingConfiguration,
        ContainerDefinition $containerDefinition,
        AnnotatedContainer $container,
        ContainerAnalytics $containerAnalytics,
    ) : void;

}
