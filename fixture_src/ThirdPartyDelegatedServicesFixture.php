<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\AnnotatedContainerFixture\ThirdPartyDelegatedServices\LoggerFactory;
use Cspray\Typiphy\ObjectType;
use Psr\Log\LoggerInterface;
use function Cspray\Typiphy\objectType;

final class ThirdPartyDelegatedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ThirdPartyDelegatedServices';
    }

    public function loggerFactory() : ObjectType {
        return objectType(LoggerFactory::class);
    }
}