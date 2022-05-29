<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\AliasDefinition;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\ContainerDefinition;

trait HasAliasDefinitionTestsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    abstract protected function aliasProvider() : array;

    final public function testExpectedAliasCount() : void {
        $expectedCount = count($this->aliasProvider());

        $this->assertSame($expectedCount, count($this->getSubject()->getAliasDefinitions()));
    }

    /**
     * @dataProvider aliasProvider
     */
    final public function testExpectedAliasDefinition(ExpectedAliasDefinition $expectedAliasDefinition) : void {
        $concreteDefinitionsMatchingAbstract = array_filter(
            $this->getSubject()->getAliasDefinitions(),
            fn(AliasDefinition $aliasDefinition) => $aliasDefinition->getAbstractService() === $expectedAliasDefinition->abstractType
        );
        $concreteTypes = array_map(fn(AliasDefinition $aliasDefinition) => $aliasDefinition->getConcreteService(), $concreteDefinitionsMatchingAbstract);

        $this->assertContains($expectedAliasDefinition->concreteType, $concreteTypes);
    }

}