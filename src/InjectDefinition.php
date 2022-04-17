<?php

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeUnion;

interface InjectDefinition {

    public function getTargetIdentifier() : InjectTargetIdentifier;

    public function getType() : Type|TypeUnion;

    public function getValue() : mixed;

    public function getProfiles() : array;

    public function getStoreName() : ?string;

}