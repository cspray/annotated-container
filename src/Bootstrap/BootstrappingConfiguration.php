<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\ParameterStore;
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

    /**
     * @return list<Observer>
     */
    public function getObservers() : array;

    public function getLogger() : ?LoggerInterface;

    public function getLoggingExcludedProfiles() : array;
}