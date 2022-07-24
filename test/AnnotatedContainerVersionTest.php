<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use PHPUnit\Framework\TestCase;

class AnnotatedContainerVersionTest extends TestCase {

    public function testGetApiVersionReturnsVersionFileContents() : void {
        $expected = trim(file_get_contents(dirname(__DIR__) . '/VERSION'));
        self::assertSame(
            $expected,
            AnnotatedContainerVersion::getVersion()
        );
    }

}
