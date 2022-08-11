<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointContainerDefinitionBuilderContextConsumer;
use Psr\Log\LoggerInterface;

interface BootstrappingConfiguration {

    /**
     * @return list<string>
     */
    public function getScanDirectories() : array;

    public function getCacheDirectory() : ?string;

    #[SingleEntrypointContainerDefinitionBuilderContextConsumer]
    public function getContainerDefinitionConsumer() : ?ContainerDefinitionBuilderContextConsumer;

    /**
     * @return list<ParameterStore>
     */
    public function getParameterStores() : array;

    public function getLogger() : ?LoggerInterface;

    public function getLoggingExcludedProfiles() : array;
}