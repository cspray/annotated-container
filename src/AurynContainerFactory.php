<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Auryn\InjectionException;
use Auryn\Injector;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Psr\Container\ContainerInterface;

/**
 * Creates a PSR Container from a ContainerDefinition backed by an Auryn\Injector.
 */
final class AurynContainerFactory implements ContainerFactory {

    /**
     * Returns a PSR ContainerInterface that uses an Auryn\Injector to create services.
     *
     * Because Auryn does not provide a PSR compatible Container we wrap the injector in an anonymous class that
     * implements the PSR ContainerInterface. Auryn has the capacity to recursively autowire Services at time of
     * construction and does not necessarily need to have the Service defined ahead of time if the constructor
     * dependencies can be reliably determined. This fact makes the has() method for this particular Container a little
     * tricky in that a service could be successfully constructed but if we don't have something specifically defined
     * stating how to construct some aspect of it we can't reliably determine whether or not the Container "has" the
     * Service.
     *
     * This limitation should be short-lived as the Auryn Injector is being migrated to a new organization and codebase.
     * Once that migration has been completed a new ContainerFactory using that implementation will be used and this
     * implementation will be deprecated.
     *
     * @param ContainerDefinition $containerDefinition
     * @return ContainerInterface
     */
    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : ContainerInterface {
        $activeProfiles = is_null($containerFactoryOptions) ? ['default'] : $containerFactoryOptions->getActiveProfiles();
        return new class($this->createInjector($containerDefinition, $activeProfiles)) implements ContainerInterface {

            private Injector $injector;

            public function __construct(Injector $injector) {
                $this->injector = $injector;
            }

            public function get(string $id) {
                try {
                    return $this->injector->make($id);
                } catch (InjectionException $injectionException) {
                    throw new ContainerException(
                        sprintf('An error was encountered creating %s', $id),
                        previous: $injectionException
                    );
                }
            }

            public function has(string $id): bool {
                $anyDefined = 0;
                foreach ($this->injector->inspect($id) as $definitions) {
                    $anyDefined += count($definitions);
                }
                return $anyDefined > 0;
            }
        };
    }

    private function createInjector(ContainerDefinition $containerDefinition, array $activeProfiles) : Injector {
        $injector = new Injector();
        $servicePrepareDefinitions = $containerDefinition->getServicePrepareDefinitions();
        $useServiceDefinitions = $containerDefinition->getInjectServiceDefinitions();
        $injectScalarDefinitions = $containerDefinition->getInjectScalarDefinitions();
        $serviceDelegateDefinitions = $containerDefinition->getServiceDelegateDefinitions();

        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $injector->share($serviceDefinition->getType());
        }

        $aliasedTypes = [];
        $aliasDefinitions = $containerDefinition->getAliasDefinitions();
        foreach ($aliasDefinitions as $aliasDefinition) {
            if (!in_array($aliasDefinition->getAbstractService(), $aliasedTypes)) {
                $typeAliasDefinitions = self::mapTypesAliasDefinitions($aliasDefinition->getAbstractService(), $aliasDefinitions, $activeProfiles);
                $aliasDefinition = null;
                if (count($typeAliasDefinitions) === 1) {
                    $aliasDefinition = $typeAliasDefinitions[0];
                } else {
                    /** @var AliasDefinition $typeAliasDefinition */
                    foreach ($typeAliasDefinitions as $typeAliasDefinition) {
                        if ($typeAliasDefinition->getConcreteService()->isPrimary()) {
                            $aliasDefinition = $typeAliasDefinition;
                            break;
                        }
                    }
                }

                if (isset($aliasDefinition)) {
                    $injector->alias(
                        $aliasDefinition->getAbstractService()->getType(),
                        $aliasDefinition->getConcreteService()->getType()
                    );
                }
            }
        }

        $preparedTypes = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            $type = $servicePrepareDefinition->getService()->getType();
            if (!in_array($type, $preparedTypes)) {
                $injector->prepare($type, function($object) use($servicePrepareDefinitions, $servicePrepareDefinition, $injector, $injectScalarDefinitions, $useServiceDefinitions, $type, $activeProfiles) {
                    $methods = self::mapTypesServicePrepares($type, $servicePrepareDefinitions);
                    foreach ($methods as $method) {
                        $scalarArgs = self::mapTypesScalarArgs($type, $method, $injectScalarDefinitions, $activeProfiles);
                        $serviceArgs = self::mapTypesServiceArgs($type, $method, $useServiceDefinitions);
                        $injector->execute([$object, $method], array_merge([], $scalarArgs, $serviceArgs));
                    }
                });
                $preparedTypes[] = $type;
            }
        }

        $typeArgsMap = [];
        foreach ($injectScalarDefinitions as $injectScalarDefinition) {
            $type = $injectScalarDefinition->getService()->getType();
            if (!isset($typeArgsMap[$type])) {
                $typeArgsMap[$type] = self::mapTypesScalarArgs($type, $injectScalarDefinition->getMethod(), $injectScalarDefinitions, $activeProfiles);
            }
        }

        foreach ($useServiceDefinitions as $useServiceDefinition) {
            $type = $useServiceDefinition->getService()->getType();
            $defineArgs = self::mapTypesServiceArgs($type, '__construct', $useServiceDefinitions);
            if (isset($typeArgsMap[$type])) {
                $typeArgsMap[$type] = array_merge($typeArgsMap[$type], $defineArgs);
            } else {
                $typeArgsMap[$type] = $defineArgs;
            }
        }

        foreach ($typeArgsMap as $type => $args) {
            $injector->define($type, $args);
        }

        foreach ($serviceDelegateDefinitions as $serviceDelegateDefinition) {
            $injector->delegate(
                $serviceDelegateDefinition->getServiceType()->getType(),
                [$serviceDelegateDefinition->getDelegateType(), $serviceDelegateDefinition->getDelegateMethod()]
            );
        }

        return $injector;
    }

    private static function mapTypesScalarArgs(string $type, string $method, array $injectScalarDefinitions, array $activeProfiles) : array {
        $args = [];
        /** @var InjectScalarDefinition $injectScalarDefinition */
        foreach ($injectScalarDefinitions as $injectScalarDefinition) {
            $scalarProfiles = $injectScalarDefinition->getProfiles()->getRuntimeValue();
            foreach ($activeProfiles as $activeProfile) {
                if (in_array($activeProfile, $scalarProfiles) && $injectScalarDefinition->getService()->getType() === $type && $injectScalarDefinition->getMethod() === $method) {
                    $args[':' . $injectScalarDefinition->getParamName()] = $injectScalarDefinition->getValue()->getRuntimeValue();
                }
            }
        }
        return $args;
    }

    private static function mapTypesServiceArgs(string $type, string $method, array $UseServiceDefinitions) : array {
        $args = [];
        /** @var InjectServiceDefinition $UseServiceDefinition */
        foreach ($UseServiceDefinitions as $UseServiceDefinition) {
            if ($UseServiceDefinition->getService()->getType() === $type && $UseServiceDefinition->getMethod() === $method) {
                $args[$UseServiceDefinition->getParamName()] = $UseServiceDefinition->getInjectedService()->getType();
            }
        }
        return $args;
    }

    static private function mapTypesServicePrepares(string $type, array $servicePreparesDefinition) : array {
        $methods = [];
        /** @var ServicePrepareDefinition $servicePrepareDefinition */
        foreach ($servicePreparesDefinition as $servicePrepareDefinition) {
            if ($servicePrepareDefinition->getService()->getType() === $type) {
                $methods[] = $servicePrepareDefinition->getMethod();
            }
        }
        return $methods;
    }

    static private function mapTypesAliasDefinitions(ServiceDefinition $serviceDefinition, array $aliasDefinitions, array $activeProfiles) : array {
        $aliases = [];
        /** @var AliasDefinition $aliasDefinition */
        foreach ($aliasDefinitions as $aliasDefinition) {
            $concreteProfiles = $aliasDefinition->getConcreteService()->getProfiles()->getRuntimeValue();
            if (empty($concreteProfiles)) {
                $concreteProfiles[] = 'default';
            }
            foreach ($activeProfiles as $activeProfile) {
                if (in_array($activeProfile, $concreteProfiles) && $aliasDefinition->getAbstractService()->getType() === $serviceDefinition->getType()) {
                    $aliases[] = $aliasDefinition;
                }
            }
        }
        return $aliases;
    }

}