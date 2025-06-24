<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Definition\Cache;

use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\StaticAnalysis\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CacheKeyTest extends TestCase {

    public static function optionsCacheKeyProvider() : array {
        return [
            [ContainerDefinitionAnalysisOptions::fromScanDirectories(['dir']), md5('dir')],
            [ContainerDefinitionAnalysisOptions::fromScanDirectories(['foo', 'bar', 'baz']), md5('barbazfoo')],
            [ContainerDefinitionAnalysisOptions::fromScanDirectoriesAndDefinitionProvider(
                ['foo'],
                new StubDefinitionProvider()
            ), md5('foo/' . StubDefinitionProvider::class)],
            [ContainerDefinitionAnalysisOptions::fromScanDirectoriesAndDefinitionProvider(
                ['qux', 'quz', 'quy'],
                new StubDefinitionProvider()
            ), md5('quxquyquz/' . StubDefinitionProvider::class)],
            [ContainerDefinitionAnalysisOptions::fromScanDirectoriesAndDefinitionProvider(
                ['zux', 'zuw', 'zuv'],
                $composite = new CompositeDefinitionProvider(
                    new StubDefinitionProvider(),
                    new StubDefinitionProvider()
                )
            ), md5('zuvzuwzux/' . $composite)]
        ];
    }

    #[DataProvider('optionsCacheKeyProvider')]
    public function testCreatedKeyIsExpectedFromAnalysisOptionsProvided(
        ContainerDefinitionAnalysisOptions $options,
        string $expectedKey
    ) : void {
        $actual = CacheKey::fromContainerDefinitionAnalysisOptions($options);

        self::assertSame($expectedKey, $actual->asString());
    }
}
