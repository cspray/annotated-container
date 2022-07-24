<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AliasDefinitionResolver;
use Cspray\AnnotatedContainer\ConfigurationDefinition;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerFactory;
use Cspray\AnnotatedContainer\EnvironmentParameterStore;
use Cspray\AnnotatedContainer\ParameterStore;
use Cspray\AnnotatedContainer\StandardAliasDefinitionResolver;

abstract class AbstractContainerFactory implements ContainerFactory {

    protected readonly AliasDefinitionResolver $aliasDefinitionResolver;

    /**
     * @var ParameterStore[]
     */
    private array $parameterStores = [];

    public function __construct(
        AliasDefinitionResolver $aliasDefinitionResolver = null
    ) {
        // Injecting environment variables is something we have supported since early versions.
        // We don't require adding this parameter store explicitly to continue providing this functionality
        // without the end-user having to change how they construct their ContainerFactory.
        $this->addParameterStore(new EnvironmentParameterStore());
        $this->aliasDefinitionResolver = $aliasDefinitionResolver ?? new StandardAliasDefinitionResolver();
    }

    /**
     * Add a custom ParameterStore, allowing you to Inject arbitrary values into your Services.
     *
     * @param ParameterStore $parameterStore
     * @return void
     * @see Inject
     */
    final public function addParameterStore(ParameterStore $parameterStore): void {
        $this->parameterStores[$parameterStore->getName()] = $parameterStore;
    }

    final protected function getParameterStore(string $storeName) : ?ParameterStore {
        return $this->parameterStores[$storeName] ?? null;
    }

}