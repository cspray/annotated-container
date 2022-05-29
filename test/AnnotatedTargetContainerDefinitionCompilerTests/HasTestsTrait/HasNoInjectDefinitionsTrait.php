<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\ContainerDefinition;

trait HasNoInjectDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    final public function testHasNoInjectDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getInjectDefinitions());
    }

}