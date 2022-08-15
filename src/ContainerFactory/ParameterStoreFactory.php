<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

interface ParameterStoreFactory {

    /**
     * @param string|class-string<ParameterStore> $identifier
     * @return ParameterStore
     */
    public function createParameterStore(string $identifier) : ParameterStore;

}