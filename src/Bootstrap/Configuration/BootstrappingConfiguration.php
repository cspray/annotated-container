<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Configuration;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;

interface BootstrappingConfiguration {

    /**
     * @return list<string>
     */
    public function scanDirectories() : array;

    public function cache() : ?ContainerDefinitionCache;

    #[SingleEntrypointDefinitionProvider]
    public function containerDefinitionProvider() : ?DefinitionProvider;

    /**
     * @return list<ParameterStore>
     */
    public function parameterStores() : array;

    /**
     * @return list<Listener>
     */
    public function listeners() : array;
}
