<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

trait HasNoServiceDelegateDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    public function testHasNoServiceDelegateDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getServiceDelegateDefinitions());
    }

}