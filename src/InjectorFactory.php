<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface InjectorFactory {

    public function createContainer(InjectorDefinition $injectorDefinition);

}