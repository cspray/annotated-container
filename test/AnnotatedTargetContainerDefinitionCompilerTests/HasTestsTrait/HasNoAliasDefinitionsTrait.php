<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\ContainerDefinition;

trait HasNoAliasDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    public function testHasNoAliasDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getAliasDefinitions());
    }

}