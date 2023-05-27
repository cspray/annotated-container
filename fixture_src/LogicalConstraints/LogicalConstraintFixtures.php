<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints;

final class LogicalConstraintFixtures {

    private function __construct() {}

    public static function duplicateServiceName() : DuplicateServiceNameFixture{
        return new DuplicateServiceNameFixture();
    }

    public static function privateServiceDelegate() : PrivateServiceDelegateFixture {
        return new PrivateServiceDelegateFixture();
    }

    public static function protectedServiceDelegate() : ProtectedServiceDelegateFixture {
        return new ProtectedServiceDelegateFixture();
    }

}