<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Profiles;
use function DI\autowire;
use function DI\get;

final class PhpDiContainerFactoryState implements ContainerFactoryState {

    use HasMethodInjectState, HasServicePrepareState;

    private array $services = [];

    private array $definitions = [];

    private array $serviceKeys = [];

    public function __construct() {
        $this->services[] = AutowireableFactory::class;
        $this->services[] = AutowireableInvoker::class;
        $this->services[] = Profiles::class;
    }

    public function getDefinitions() : array {
        return $this->definitions;
    }

    public function getServices() : array {
        return $this->services;
    }

    public function addService(string $service) : void {
        $this->services[] = $service;
    }

    public function autowireService(string $service) : void {
        $this->definitions[$service] = autowire();
    }

    public function referenceService(string $name, string $service) : void {
        $this->definitions[$name] = get($service);
    }

    public function factoryService(string $name, \Closure $closure) : void {
        $this->definitions[$name] = $closure;
    }

    public function setServiceKey(string $serviceType, string $key) : void {
        $this->serviceKeys[$serviceType] = $key;
    }

    public function getServiceKey(string $serviceType) : ?string {
        return $this->serviceKeys[$serviceType] ?? null;
    }

}
