<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\InjectTargetType;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\InjectDefinition;
use PHPUnit\Framework\TestCase;

trait HasInjectDefinitionTestsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    abstract protected function injectProvider() : array;

    final public function testInjectDefinitionCount() : void {
        $expectedCount = count($this->injectProvider());

        $this->assertSame($expectedCount, count($this->getSubject()->getInjectDefinitions()));
    }

    /**
     * @dataProvider injectProvider
     */
    final public function testInjectDefinition(ExpectedInject $expectedInject) : void {
        (new AssertExpectedInjectDefinition($this))->assert($expectedInject, $this->getSubject());
    }

}