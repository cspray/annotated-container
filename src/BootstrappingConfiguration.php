<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointContainerDefinitionBuilderContextConsumer;

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
}