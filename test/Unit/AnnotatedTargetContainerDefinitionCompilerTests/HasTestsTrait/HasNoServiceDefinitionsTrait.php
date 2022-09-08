<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

trait HasNoServiceDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    public function testHasNoServiceDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getServiceDefinitions());
    }

}