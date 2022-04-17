<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\Typiphy\Type;

final class EnvironmentParameterStore implements ParameterStore {

    public function getName() : string {
        return 'env';
    }

    public function fetch(Type $type, string $key) : string|array|false {
        $value = getenv($key);
        if ($value === false) {
            throw new InvalidParameterException(sprintf(
                'The key "%s" is not available in store "%s".',
                $key,
                $this->getName()
            ));
        }
        return $value;
    }
}