<?php

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionProperty;
use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Reference;
use function array_map;
use function Cspray\Typiphy\arrayType;
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

    public function __construct(private readonly ContainerDefinition $containerDefinition)
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
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigException
     * @throws NotFoundExceptionInterface
     */
    public function createDefinitions(): array
    {
        $definitions = array_map(fn($concrete): string => $concrete, $this->aliases);

        foreach ($this->serviceDelegate as $service => [$delegate, $method]) {
            $definitions[$service] = static fn (AutowireableInvoker $invoker): mixed => $invoker->invoke($invoker->make($delegate)->$method(...));
        }

        foreach ($this->namedServices as $name => $service) {
            $definitions[$name] = $service;
        }

        foreach ($this->getMethodInject() as $class => $methods) {
            $definitions[$class] = $this->createMethodInjectConfig($class, $methods);
        }

        foreach ($this->getPropertyInject() as $class => $methods) {
            $definitions[$class] = $this->createPropertyInjectConfig($class, $methods);
        }

        foreach ($this->instances as $key => $value) {
            $definitions[$key] = $definitions[$key] ?? $value;
        }

        foreach ($this->concreteServices as $concrete) {
            $definitions[$concrete] = $definitions[$concrete] ?? $concrete;
        }

        foreach ($this->getServicePrepares() as $class => $methods) {
            if ($definitions[$class]) {
                $config = is_string($definitions[$class]) ? [ArrayDefinition::CLASS_NAME => $definitions[$class]] : $definitions[$class];
                foreach ($methods as $method) {
                    $params = array_map(fn($value) => $this->parameterValueOrReference($value, $class), $this->parametersForMethod($class, $method));
                    $config["$method()"] = $params;
                }
                $definitions[$class] = $config;
            }
        }

        return $definitions;
    }

    /**
     * @throws InvalidConfigException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function parameterValueOrReference(mixed $value, string $class, ?ContainerInterface $container = null): mixed
    {
        if ($value instanceof ContainerReference) {
            return Reference::to($value->name);
        }

        if ($value instanceof ServiceCollectorReference && ($value->collectionType === arrayType() || !is_null($container))) {
            if (is_null($container)) {
                return $value;
            }

            $values = [];

            foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
                $service = $serviceDefinition->getType()->getName();

                if ($serviceDefinition->isAbstract() || $service === $class) {
                    continue;
                }

                if (is_a($service, $value->valueType->getName(), true)) {
                    $values[] = $value->collectionType !== arrayType()
                        ? $container->get($service)
                        : Reference::to($service);
                }
            }

            return $value->listOf->toCollection($values);
        }

        return $value;
    }

    private function convertDefinitionConfigToClosure(array $config): \Closure
    {
        return function (ContainerInterface $container) use ($config) {
            $definition = ArrayDefinition::fromConfig($config);
            $class = $definition->getClass();

            $constructorArguments = array_map(
                fn($value) => $value instanceof ServiceCollectorReference
                    ? $this->parameterValueOrReference($value, $class, $container)
                    : $value
                , $definition->getConstructorArguments()
            );

            $definition = $definition->merge(
                ArrayDefinition::fromPreparedData(
                    $class,
                    $constructorArguments,
                    $definition->getMethodsAndProperties()
                ));

            return $definition->resolve($container);
        };
    }

    private function createMethodInjectConfig(string $class, array $methods): array | \Closure
    {
        $config = [ArrayDefinition::CLASS_NAME => $class];
        $convertDefinitionToClosure = false;
        foreach ($methods as $method => $val) {
            $constructor = "$method()";
            if ($constructor === ArrayDefinition::CONSTRUCTOR) {
                $config[$constructor] ??= [];
                foreach ($val as $param => $value) {
                    if ($value instanceof ServiceCollectorReference) {
                        $convertDefinitionToClosure = true;
                    }
                    $config[$constructor][$param] = $this->parameterValueOrReference($value, $class);
                }
            }
        }
        return $convertDefinitionToClosure ? $this->convertDefinitionConfigToClosure($config) : $config;
    }

    private function createPropertyInjectConfig(string $class, array $methods)
    {
        $config = [ArrayDefinition::CLASS_NAME => $class];

        foreach ($methods as $property => $value) {
            $reflectionProperty = new ReflectionProperty($class, $property);
            if ($reflectionProperty->isPublic() && !$reflectionProperty->isReadOnly()) {
                // Yii DI natively supports property injection only for public and writable properties
                $config["\$$property"] = $this->parameterValueOrReference($value, $class);
            } else {
                // add tag to service classes that have non-public or read-only properties
                // for injecting them manually after container creation
                $config['tags'] ??= [];
                $config['tags'][] = self::TAG_INJECT_READ_ONLY_PROPERTIES;
                $this->readOnlyPropertyInject[$class] ??= [];
                $this->readOnlyPropertyInject[$class][] = [$reflectionProperty, $value];
            }
        }
        return $config;
    }

}
