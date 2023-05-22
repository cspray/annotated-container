<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Auryn\Injector;
use Cspray\Typiphy\ObjectType;

final class AurynContainerFactoryState implements ContainerFactoryState {

    use HasMethodInjectState, HasPropertyInjectState, HasServicePrepareState;

    public readonly Injector $injector;

    /**
     * @var array<non-empty-string, ObjectType>
     */
    private array $nameTypeMap = [];


    public function __construct() {
        $this->injector = new Injector();
    }

    /**
     * @param non-empty-string $name
     * @param ObjectType $type
     * @return void
     */
    public function addNameType(string $name, ObjectType $type) : void {
        $this->nameTypeMap[$name] = $type;
    }

    /**
     * @param non-empty-string $name
     * @return ObjectType|null
     */
    public function getTypeForName(string $name) : ?ObjectType {
        return $this->nameTypeMap[$name] ?? null;
    }

}
