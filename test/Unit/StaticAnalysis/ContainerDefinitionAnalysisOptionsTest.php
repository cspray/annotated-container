<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis;

use Cspray\AnnotatedContainer\StaticAnalysis\CallableDefinitionProvider;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use PHPUnit\Framework\TestCase;

class ContainerDefinitionAnalysisOptionsTest extends TestCase {

    public function testScanDirectoriesAreThosePassedIn() : void {
        $compilerOptions = ContainerDefinitionAnalysisOptions::fromScanDirectories(
            [Fixtures::singleConcreteService()->getPath()]
        );

        self::assertSame(
            [Fixtures::singleConcreteService()->getPath()],
            $compilerOptions->scanDirectories()
        );
    }

    public function testByDefaultDefinitionProviderIsNull() : void {
        $compilerOptions = ContainerDefinitionAnalysisOptions::fromScanDirectories(
            [Fixtures::singleConcreteService()->getPath()]
        );

        self::assertNull($compilerOptions->definitionProvider());
    }

    public function testWithDefinitionProviderReturnsCorrectInstance() : void {
        $compilerOptions = ContainerDefinitionAnalysisOptions::fromScanDirectoriesAndDefinitionProvider(
            [Fixtures::singleConcreteService()->getPath()],
            $expected = new CallableDefinitionProvider(function() {
            })
        );

        self::assertSame($expected, $compilerOptions->definitionProvider());
    }
}
