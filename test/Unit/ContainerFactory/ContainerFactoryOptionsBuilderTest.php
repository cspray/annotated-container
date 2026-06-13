<?php

namespace Cspray\AnnotatedContainer\Unit\ContainerFactory;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\Profiles;
use PHPUnit\Framework\TestCase;

final class ContainerFactoryOptionsBuilderTest extends TestCase {

    public function testGetProfiles() : void {
        $options = ContainerFactoryOptions::fromProfiles(Profiles::fromList(['default', 'dev', 'local']));

        self::assertSame(['default', 'dev', 'local'], $options->profiles()->toArray());
    }
}
