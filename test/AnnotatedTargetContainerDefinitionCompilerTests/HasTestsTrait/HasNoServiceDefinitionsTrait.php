<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\ContainerDefinition;

trait HasNoServiceDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    public function testHasNoServiceDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getServiceDefinitions());
    }

}