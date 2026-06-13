<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolver;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\StandardAliasDefinitionResolver;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerFactoryState;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Event\ContainerFactoryEmitter;
use Cspray\AnnotatedContainer\Profiles;

/**
 * @template ContainerBuilder of object
 * @template IntermediaryContainer of object
 */
abstract class AbstractContainerFactory implements ContainerFactory {

    /**
     * @var array<non-empty-string, ParameterStore>
     */
    private array $parameterStores = [];

    public function __construct(
        private readonly ContainerFactoryEmitter $emitter,
        private readonly AliasDefinitionResolver $aliasDefinitionResolver = new StandardAliasDefinitionResolver(),
    ) {
        // Injecting environment variables is something we have supported since early versions.
        // We don't require adding this parameter store explicitly to continue providing this functionality
        // without the end-user having to change how they construct their ContainerFactory.
        $this->addParameterStore(new EnvironmentParameterStore());
    }

    final public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        $activeProfiles = $containerFactoryOptions?->profiles() ?? Profiles::defaultOnly();

        $this->emitter->emitBeforeContainerCreation($activeProfiles, $containerDefinition);

        $state = new ContainerFactoryState(
            new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles),
            $activeProfiles,
            $this->aliasDefinitionResolver,
            $this->parameterStores,
        );
        $container = $this->createAnnotatedContainer($state);

        $this->emitter->emitAfterContainerCreation($activeProfiles, $containerDefinition, $container);

        return $container;
    }



    /**
     * Add a custom ParameterStore, allowing you to Inject arbitrary values into your Services.
     *
     * @param ParameterStore $parameterStore
     * @return void
     * @see Inject
     */
    final public function addParameterStore(ParameterStore $parameterStore): void {
        $this->parameterStores[$parameterStore->name()] = $parameterStore;
    }

    abstract protected function createAnnotatedContainer(ContainerFactoryState $state) : AnnotatedContainer;
}
