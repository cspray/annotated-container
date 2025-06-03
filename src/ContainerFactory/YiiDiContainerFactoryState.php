<?php

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Exception;
use Psr\Container\ContainerInterface;
use ReflectionProperty;
use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Reference;
use function array_map;
use function is_string;

final class YiiDiContainerFactoryState implements ContainerFactoryState
{
    const TAG_INJECT_READ_ONLY_PROPERTIES = '__inject_read_only_properties';

    use HasMethodInjectState;
    use HasPropertyInjectState;
    use HasServicePrepareState;

    private array $abstractServices = [];
    private array $concreteServices = [];

    /** @var array<string> */
    private array $namedServices = [];

    /** @var array<class-string, array<string, string>> */
    private array $serviceDelegate = [];

    private array $readOnlyPropertyInject = [];

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

    public function addServiceDelegate(string $service, string $delegate, string $delegateMethod): void
    {
        $this->serviceDelegate[$service] = [$delegate, $delegateMethod];
    }

    public function getReadOnlyPropertyInjectsForService(string $service): array
    {
        return $this->readOnlyPropertyInject[$service] ?? [];
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function createDefinitions(): array
    {
        $definitions = array_map(fn($concrete): string => $concrete, $this->aliases);

        foreach ($this->serviceDelegate as $service => [$delegate, $method]) {
            $definitions[$service] = static function (AutowireableInvoker $invoker, ContainerInterface $container) use ($delegate, $method){
                $factory = $container->has($delegate) ? $container->get($delegate) : $invoker->make($delegate);
                return $invoker->invoke($factory->$method(...));
            };
        }

        foreach ($this->namedServices as $name => $service) {
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
                        $def[$constructor][$param] = $this->parameterValueOrReference($value);
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
                $reflectionProperty = new ReflectionProperty($class, $property);
                if ($reflectionProperty->isPublic() && !$reflectionProperty->isReadOnly()) {
                    // Yii DI natively supports property injection only for public and writable properties
                    $def["\$$property"] = $this->parameterValueOrReference($value);
                } else {
                    // add tag to service classes that have non-public or read-only properties
                    // for injecting them manually after container creation
                    $def['tags'] ??= [];
                    $def['tags'][] = self::TAG_INJECT_READ_ONLY_PROPERTIES;
                    $this->readOnlyPropertyInject[$class] ??= [];
                    $this->readOnlyPropertyInject[$class][] = [$reflectionProperty, $value];
                }
            }
            $definitions[$class] = $def;
        }

        foreach ($this->instances as $key => $value) {
            $definitions[$key] = $definitions[$key] ?? $value;
        }

        foreach ($this->concreteServices as $concrete) {
            $definitions[$concrete] = $definitions[$concrete] ?? $concrete;
        }

        foreach ($this->getServicePrepares() as $service => $methods) {
            if ($definitions[$service]) {
                $def = is_string($definitions[$service]) ? [ArrayDefinition::CLASS_NAME => $definitions[$service]] : $definitions[$service];
                foreach ($methods as $method) {
                    $params = array_map(fn ($value) => $this->parameterValueOrReference($value), $this->parametersForMethod($service, $method));
                    $def["$method()"] = $params;
                }
                $definitions[$service] = $def;
            }
        }

        return $definitions;
    }

    /**
     * @throws InvalidConfigException
     */
    private function parameterValueOrReference(mixed $value): mixed
    {
        return $value instanceof ContainerReference ? Reference::to($value->name) : $value;
    }

}
