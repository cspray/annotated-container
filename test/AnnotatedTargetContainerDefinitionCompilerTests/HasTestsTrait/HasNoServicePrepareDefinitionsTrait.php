<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\ContainerDefinition;

trait HasNoServicePrepareDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    final public function testHasNoServicePrepareDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getServicePrepareDefinitions());
    }

}