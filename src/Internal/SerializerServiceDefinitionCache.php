<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\ServiceDefinition;

/**
 * @Internal
 */
final class SerializerServiceDefinitionCache {

    /**
     * @var array<string, ServiceDefinition>
     */
    private array $cache = [];

    public function add(string $key, ServiceDefinition $serviceDefinition) : void {
        $this->cache[$key] = $serviceDefinition;
    }

    public function get(string $key) : ?ServiceDefinition {
        return $this->cache[$key] ?? null;
    }

    public function has(string $key) : bool {
        return array_key_exists($key, $this->cache);
    }

}
