<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\ContainerDefinition;

trait HasNoConfigurationDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    final public function testHasNoConfigurationDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getConfigurationDefinitions());
    }

}