<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\ContainerDefinition;

trait HasNoServiceDelegateDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    public function testHasNoServiceDelegateDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getServiceDelegateDefinitions());
    }

}