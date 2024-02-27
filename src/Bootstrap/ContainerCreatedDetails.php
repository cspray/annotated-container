<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\AbstractEvent;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

final class ContainerCreatedDetails {

    public function __construct(
        public readonly ActiveProfiles $profiles,
        public readonly ContainerDefinition $containerDefinition,
        public readonly AnnotatedContainer $container,
        public readonly ContainerAnalytics $containerAnalytics,
    ) {}


}