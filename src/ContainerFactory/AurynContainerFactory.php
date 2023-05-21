<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Auryn\InjectionException;
use Auryn\Injector;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameter;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\Typiphy\ObjectType;
use stdClass;
use UnitEnum;
use function Cspray\Typiphy\objectType;

// @codeCoverageIgnoreStart
if (!class_exists(Injector::class)) {
    throw new \RuntimeException("To enable the AurynContainerFactory please install rdlowrey/auryn!");
}
// @codeCoverageIgnoreEnd

/**
 * A ContainerFactory that utilizes the rdlowrey/auryn Container as its backing implementation.
 */
final class AurynContainerFactory extends AbstractContainerFactory implements ContainerFactory {

    protected function getBackingContainerType() : ObjectType {
        return objectType(Injector::class);
    }

    /**
     * @param stdClass $state
     * @return void
     */
    protected function startCreatingBackingContainer(stdClass $state) : void {
        $state->injector = new Injector();
        $state->nameTypeMap = [];
        $state->methodInject = [];
        $state->propertyInject = [];
        $state->servicePrepares = [];
    }

    /**
     * @param stdClass{injector: Injector, nameTypeMap: array} $state
     * @param ServiceDefinition $definition
     * @return void
     */
    protected function handleServiceDefinition(stdClass $state, ServiceDefinition $definition) : void {
        $state->injector->share($definition->getType()->getName());
        if ($definition->getName() !== null) {
            $state->nameTypeMap[$definition->getName()] = $definition->getType();
        }
    }

    protected function handleAliasDefinition(stdClass $state, AliasDefinitionResolution $resolution) : void {
        if ($resolution->getAliasDefinition() !== null) {
            $state->injector->alias(
                $resolution->getAliasDefinition()->getAbstractService()->getName(),
                $resolution->getAliasDefinition()->getConcreteService()->getName()
            );
        }
    }

    public function handleServiceDelegateDefinition(stdClass $state, ServiceDelegateDefinition $definition) : void {
        $delegateType = $definition->getDelegateType()->getName();
        $delegateMethod = $definition->getDelegateMethod();

        $parameters = $state->methodInject[$delegateType][$delegateMethod] ?? [];
        $state->injector->delegate(
            $definition->getServiceType()->getName(),
            static fn() => $state->injector->execute([$delegateType, $delegateMethod], $parameters)
        );
    }

    public function handleServicePrepareDefinition(stdClass $state, ServicePrepareDefinition $definition) : void {
        $serviceType = $definition->getService()->getName();

        $state->servicePrepares[$serviceType] ??= [];
        $state->servicePrepares[$serviceType][] = $definition->getMethod();
    }

    public function handleInjectDefinition(stdClass $state, InjectDefinition $definition) : void {
        $injectTargetType = $definition->getTargetIdentifier()->getClass()->getName();

        if ($definition->getTargetIdentifier()->isMethodParameter()) {
            $method = $definition->getTargetIdentifier()->getMethodName();
            $parameterName = $definition->getTargetIdentifier()->getName();

            $value = $definition->getValue();
            if ($definition->getType() instanceof ObjectType && !is_a($definition->getType()->getName(), UnitEnum::class, true)) {
                $key = $parameterName;
                if (isset($state->nameTypeMap[$value])) {
                    $value = $state->nameTypeMap[$value]->getName();
                }
            } else {
                $key = ':' . $parameterName;
            }

            $store = $definition->getStoreName();
            if ($store !== null) {
                $parameterStore = $this->getParameterStore($store);
                if ($parameterStore === null) {
                    throw ParameterStoreNotFound::fromParameterStoreNotAddedToContainerFactory($store);
                }
                $value = $parameterStore->fetch($definition->getType(), $value);
            }

            $state->methodInject[$injectTargetType] ??= [];
            $state->methodInject[$injectTargetType][$method] ??= [];
            $state->methodInject[$injectTargetType][$method][$key] = $value;
        } else {
            $property = $definition->getTargetIdentifier()->getName();
            $value = $definition->getValue();

            $store = $definition->getStoreName();
            if ($store !== null) {
                $parameterStore = $this->getParameterStore($store);
                if ($parameterStore === null) {
                    throw ParameterStoreNotFound::fromParameterStoreNotAddedToContainerFactory($store);
                }
                $value = $parameterStore->fetch($definition->getType(), $value);
            }

            $state->propertyInject[$injectTargetType] ??= [];
            $state->propertyInject[$injectTargetType][$property] = $value;
        }

    }

    public function handleConfigurationDefinition(stdClass $state, ConfigurationDefinition $definition) : void {
        $state->injector->share($definition->getClass()->getName());
        if ($definition->getName() !== null) {
            $state->nameTypeMap[$definition->getName()] = $definition->getClass();
        }

        if (!method_exists($definition->getClass()->getName(), '__construct')) {
            $state->injector->delegate($definition->getClass()->getName(), static function() use($definition, $state) {
                $configReflection = (new \ReflectionClass($definition->getClass()->getName()));
                $configInstance = $configReflection->newInstanceWithoutConstructor();
                $properties = $state->propertyInject[$definition->getClass()->getName()] ?? [];
                foreach ($properties as $prop => $value) {
                    $reflectionProperty = $configReflection->getProperty($prop);
                    $reflectionProperty->setValue($configInstance, $value);
                }
                return $configInstance;
            });
        }
    }

    protected function createAnnotatedContainer(stdClass $state, ActiveProfiles $activeProfiles) : AnnotatedContainer {
        $injector = $state->injector;
        assert($injector instanceof Injector);

        foreach ($state->methodInject as $service => $methods) {
            if (array_key_exists('__construct', $methods)) {
                $injector->define($service, $methods['__construct']);
            }
        }

        /**
         * @var class-string $serviceType
         * @var list<string> $methods
         */
        foreach ($state->servicePrepares as $serviceType => $methods) {
            $injector->prepare(
                $serviceType,
                static function(object $object) use($injector, $state, $methods) : void {
                    foreach ($methods as $method) {
                        $params = $state->methodInject[$object::class][$method] ?? [];
                        $injector->execute([$object, $method], $params);
                    }
                }
            );
        }

        return new class($state->injector, $state->nameTypeMap, $activeProfiles) implements AnnotatedContainer {

            public function __construct(
                private readonly Injector $injector,
                private readonly array $nameTypeMap,
                ActiveProfiles $activeProfiles
            ) {
                $this->injector->delegate(AutowireableFactory::class, fn() => $this);
                $this->injector->delegate(AutowireableInvoker::class, fn() => $this);
                $this->injector->delegate(ActiveProfiles::class, fn() => $activeProfiles);
            }

            public function get(string $id) {
                try {
                    if (!$this->has($id)) {
                        throw ServiceNotFound::fromServiceNotInContainer($id);
                    }

                    if (isset($this->nameTypeMap[$id])) {
                        $id = $this->nameTypeMap[$id]->getName();
                    }
                    return $this->injector->make($id);
                } catch (InjectionException $injectionException) {
                    throw ContainerException::fromCaughtThrowable($injectionException);
                }
            }

            public function has(string $id): bool {
                if (isset($this->nameTypeMap[$id])) {
                    return true;
                }

                $anyDefined = 0;
                foreach ($this->injector->inspect($id) as $definitions) {
                    $anyDefined += count($definitions);
                }
                return $anyDefined > 0;
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                return $this->injector->make(
                    $classType,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            public function getBackingContainer() : Injector {
                return $this->injector;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->injector->execute(
                    $callable,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            private function convertAutowireableParameterSet(AutowireableParameterSet $parameters = null) : array {
                $params = [];
                if (!is_null($parameters)) {
                    /** @var AutowireableParameter $parameter */
                    foreach ($parameters as $parameter) {
                        $name = $parameter->isServiceIdentifier() ? $parameter->getName() : ':' . $parameter->getName();
                        $params[$name] = $parameter->isServiceIdentifier() ? $parameter->getValue()->getName() : $parameter->getValue();
                    }
                }
                return $params;
            }
        };
    }
}