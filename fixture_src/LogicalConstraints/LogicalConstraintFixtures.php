<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints;

use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceType;

final class LogicalConstraintFixtures {

    private function __construct() {}

    public static function duplicateServiceName() : DuplicateServiceNameFixture{
        return new DuplicateServiceNameFixture();
    }

    public static function duplicateServiceType() : DuplicateServiceTypeFixture {
        return new DuplicateServiceTypeFixture();
    }

    public static function privateServiceDelegate() : PrivateServiceDelegateFixture {
        return new PrivateServiceDelegateFixture();
    }

    public static function protectedServiceDelegate() : ProtectedServiceDelegateFixture {
        return new ProtectedServiceDelegateFixture();
    }

    public static function privateServicePrepare() : PrivateServicePrepareFixture {
        return new PrivateServicePrepareFixture();
    }

    public static function protectedServicePrepare() : ProtectedServicePrepareFixture {
        return new ProtectedServicePrepareFixture();
    }

}