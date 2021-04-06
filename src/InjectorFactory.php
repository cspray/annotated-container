<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

interface InjectorFactory {

    public function createContainer(InjectorDefinition $injectorDefinition);

}