<?php

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Reference;

final class YiiDiContainerFactoryState implements ContainerFactoryState
{
    use HasMethodInjectState;
    use HasPropertyInjectState;

    private array $abstractServices = [];
    private array $concreteServices = [];
    private array $namedServices = [];
    private array $aliases = [];

    public function __construct() {}

    public function addConcreteService(string $name): void
    {
        $this->concreteServices[$name] = $name;
    }

    public function addAbstractService(string $name): void
    {
        $this->abstractServices[$name] = $name;
    }

    public function addNamedService(string $service, string $name): void
    {
        $this->namedServices[$name] = $service;
    }

    public function addAlias(string $abstract, string $concrete): void
    {
        $this->aliases[$abstract] = $concrete;
    }

    /**
     * @throws InvalidConfigException
     */
    public function createDefinitions(): array
    {
        $definitions = \array_map(fn($concrete) => $concrete, $this->aliases);

        foreach ($this->namedServices as $name => $service) {
            if (\array_key_exists($name, $definitions) && $definitions[$name] !== $service) {
                // TODO: should this exception be removed?
                throw new \Exception("duplicate alias '{$name}' detected, please check");
            }
            $definitions[$name] = $service;
        }

        $methodInject = $this->getMethodInject();

        foreach ($methodInject as $class => $path) {
            $def = $definitions[$class] ?? ['class' => $class];

            foreach ($path as $method => $val) {
                $constructor = "$method()";
                if ($constructor === ArrayDefinition::CONSTRUCTOR) {
                    $def[$constructor] ??= [];
                    foreach ($val as $param => $value) {
                        $def[$constructor][$param] = Reference::to($value->name);
                    }
                }
            }
            $definitions[$class] = $def;
        }

        $propertiesInject = $this->getPropertyInject();

        foreach ($propertiesInject as $class => $path) {
            $def = $definitions[$class] ?? ['class' => $class];
            if (\is_string($def)) {
                $def = [
                    'class' => $class,
                ];
            }
            foreach ($path as $property => $value) {
                $def["\$$property"] = Reference::to($value->type->getName());
            }
            $definitions[$class] = $def;
        }

        return $definitions;
    }
}
