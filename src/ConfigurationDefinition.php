<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;

interface ConfigurationDefinition {

    public function getClass() : ObjectType;

}