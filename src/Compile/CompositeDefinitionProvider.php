<?php

namespace Cspray\AnnotatedContainer\Compile;

final class CompositeDefinitionProvider implements DefinitionProvider {

    /**
     * @var list<DefinitionProvider>
     */
    private readonly array $providers;

    public function __construct(
        DefinitionProvider $provider,
        DefinitionProvider... $providers
    ) {
        $this->providers = [
            $provider,
            ...$providers
        ];
    }

    public function consume(DefinitionProviderContext $context) : void {
        foreach ($this->providers as $provider) {
            $provider->consume($context);
        }
    }
}