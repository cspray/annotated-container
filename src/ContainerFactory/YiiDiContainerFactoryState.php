<?php

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Exception;
use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Reference;
use function array_key_exists;
use function array_map;
use function is_string;

final class YiiDiContainerFactoryState implements ContainerFactoryState
{
    use HasMethodInjectState;
    use HasPropertyInjectState;
    use HasServicePrepareState;

    private array $abstractServices = [];
    private array $concreteServices = [];
    /** @var array<string> */
    private array $namedServices = [];
    /** @var array<string> */
    private array $aliases = [];
    private array $instances = [];

    public function __construct()
    {
    }

    public function addConcreteService(string $name): void
    {
        $this->concreteServices[$name] = $name;
    }

    public function addAbstractService(string $name): void
    {
        $this->abstractServices[$name] = $name;
    }

    public function addNamedService(string $name, string $service): void
    {
        $this->namedServices[$name] = $service;
    }

    public function addAlias(string $abstract, string $concrete): void
    {
        $this->aliases[$abstract] = $concrete;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function addInstance(string $name, object $instance): void
    {
        $this->instances[$name] = $instance;
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function createDefinitions(): array
    {
        $definitions = array_map(fn($concrete): string => $concrete, $this->aliases);

        foreach ($this->namedServices as $name => $service) {
            if (array_key_exists($name, $definitions) && $definitions[$name] !== $service) {
                // TODO: should this exception be removed?
                throw new Exception("duplicate alias '$name' while trying to define named service");
            }
            $definitions[$name] = $service;
        }

        $methodInject = $this->getMethodInject();

        foreach ($methodInject as $class => $path) {
            $def = $definitions[$class] ?? [ArrayDefinition::CLASS_NAME => $class];

            foreach ($path as $method => $val) {
                $constructor = "$method()";
                if ($constructor === ArrayDefinition::CONSTRUCTOR) {
                    $def[$constructor] ??= [];
                    foreach ($val as $param => $value) {
                        $def[$constructor][$param] = $value instanceof ContainerReference ? Reference::to($value->name) : $value;
                    }
                }
            }
            $definitions[$class] = $def;
        }

        $propertiesInject = $this->getPropertyInject();

        foreach ($propertiesInject as $class => $path) {
            $def = $definitions[$class] ?? [ArrayDefinition::CLASS_NAME => $class];
            if (is_string($def)) {
                $def = [
                    ArrayDefinition::CLASS_NAME => $class,
                ];
            }
            foreach ($path as $property => $value) {
                // TODO: check case with container reference one more carefully ($value->name or $value->type->getName())
                $def["\$$property"] = $value instanceof ContainerReference ? Reference::to($value->name) : $value;
            }
            $definitions[$class] = $def;
        }

        foreach ($this->instances as $key => $value) {
            if (array_key_exists($key, $definitions) && $definitions[$key] !== $value) {
                // TODO: remove me
                throw new Exception("Duplicate alias '$key' while trying to define value");
            }
            $definitions[$key] = $value;

//            $definitions[$key] = $definitions[$key] ?? $value;
        }

        foreach ($this->concreteServices as $concrete) {
            $definitions[$concrete] = $definitions[$concrete] ?? $concrete;
        }

        foreach ($this->getServicePrepares() as $service => $methods) {
            if ($definitions[$service]) {
                $def = is_string($definitions[$service]) ? [ArrayDefinition::CLASS_NAME => $definitions[$service]] : $definitions[$service];
                foreach ($methods as $method) {
                    $def["$method()"] = [];
                }
                $definitions[$service] = $def;
            }
        }

        return $definitions;
    }
}
