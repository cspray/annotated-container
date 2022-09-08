<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\Compile\DefinitionProviderContext;
use Cspray\AnnotatedContainer\Compile\DefinitionProvider;
use Cspray\Typiphy\ObjectType;
use function Cspray\AnnotatedContainer\service;

final class StubContextConsumerWithDependencies implements DefinitionProvider {

    public function __construct(private readonly ObjectType $service) {}

    public function consume(DefinitionProviderContext $context) : void {
        service($context, $this->service);
    }
}