<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use ArrayIterator;
use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\AnnotatedContainer\Exception\ParameterNotFoundException;
use Cspray\Typiphy\ObjectType;
use Traversable;

function autowiredParams(AutowireableParameter... $parameters) : AutowireableParameterSet {
    return new class(...$parameters) implements AutowireableParameterSet {

        /**
         * @var AutowireableParameter[]
         */
        private array $parameters = [];

        public function __construct(AutowireableParameter... $parameters) {
            array_map(fn($p) => $this->add($p), $parameters);
        }

        public function add(AutowireableParameter $autowireableParameter) : void {
            foreach ($this->parameters as $parameter) {
                if ($parameter->getName() === $autowireableParameter->getName()) {
                    throw new InvalidParameterException(sprintf(
                        'A parameter named "%s" has already been added to this set.', $parameter->getName()
                    ));
                }
            }
            $this->parameters[] = $autowireableParameter;
        }

        public function get(int $index) : AutowireableParameter {
            if (!$this->has($index)) {
                throw new ParameterNotFoundException(sprintf('There is no parameter found at index %s', $index));
            }
            return $this->parameters[$index];
        }

        public function has(int $index) : bool {
            return isset($this->parameters[$index]);
        }

        public function getIterator() : Traversable {
            return new ArrayIterator($this->parameters);
        }

        public function count() : int {
            return count($this->parameters);
        }
    };
}

function serviceParam(string $name, ObjectType $objectType) : AutowireableParameter {
    if (empty($name)) {
        throw new InvalidParameterException('A parameter name must have a non-empty value.');
    }
    return new class($name, $objectType) implements AutowireableParameter {

        public function __construct(
            private readonly string $name,
            private readonly ObjectType $value
        ) {}

        public function getName() : string {
            return $this->name;
        }

        public function getValue() : ObjectType {
            return $this->value;
        }

        public function isServiceIdentifier() : bool {
            return true;
        }
    };
}

function rawParam(string $name, mixed $value) : AutowireableParameter {
    if (empty($name)) {
        throw new InvalidParameterException('A parameter name must have a non-empty value.');
    }
    return new class($name, $value) implements AutowireableParameter {

        public function __construct(
            private readonly string $name,
            private readonly mixed $value
        ) {}

        public function getName() : string {
            return $this->name;
        }

        public function getValue() : mixed {
            return $this->value;
        }

        public function isServiceIdentifier() : bool {
            return false;
        }
    };
}