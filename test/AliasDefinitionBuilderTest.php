<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\MultipleSimpleServices;
use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\objectType;

class AliasDefinitionBuilderTest extends TestCase {

    public function testWithConcreteImmutableBuilder() {
        $builder1 = AliasDefinitionBuilder::forAbstract(objectType(SimpleServices\FooInterface::class));
        $builder2 = $builder1->withConcrete(objectType(SimpleServices\FooImplementation::class));

        $this->assertNotSame($builder1, $builder2);
    }

    public function testWithConcreteReturnsCorrectServiceDefinitions() {
        $aliasDefinition = AliasDefinitionBuilder::forAbstract(objectType(SimpleServices\FooInterface::class))->withConcrete(objectType(SimpleServices\FooImplementation::class))->build();

        $this->assertSame(objectType(SimpleServices\FooInterface::class), $aliasDefinition->getAbstractService());
        $this->assertSame(objectType(SimpleServices\FooImplementation::class), $aliasDefinition->getConcreteService());
    }

}