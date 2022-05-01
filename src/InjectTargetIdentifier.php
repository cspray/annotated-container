<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;

interface InjectTargetIdentifier {

    public function isMethodParameter() : bool;

    public function isClassProperty() : bool;

    /**
     * The name of the parameter or property that should have a value injected.
     */
    public function getName() : string;

    public function getClass() : ObjectType;

    public function getMethodName() : ?string;

}