<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AutowireableParameter {

    public function getName() : string;

    public function getValue() : mixed;

    public function isServiceIdentifier() : bool;

}