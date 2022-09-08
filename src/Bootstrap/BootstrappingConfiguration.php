<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionsProvider;
use Cspray\AnnotatedContainer\Compile\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Psr\Log\LoggerInterface;

interface BootstrappingConfiguration {

    /**
     * @return list<string>
     */
    public function getScanDirectories() : array;

    public function getCacheDirectory() : ?string;

    #[SingleEntrypointDefinitionsProvider]
    public function getContainerDefinitionConsumer() : ?DefinitionProvider;

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