<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\StaticAnalysisAnnotatedTargetParser;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionParameter;

abstract class AnnotatedTargetParserTestCase extends TestCase {

    /**
     * @var AnnotatedTarget[]
     */
    protected array $targets = [];

    protected function setUp(): void {
        foreach ($this->parseDirectories() as $target) {
            $this->targets[] = $target;
        }
    }

    protected function getAnnotatedTargetForTargetReflectionClass(string $classType) : ?AnnotatedTarget {
        foreach ($this->targets as $target) {
            if ($target->getTargetReflection()->getName() === $classType) {
                return $target;
            }
        }
        return null;
    }

    protected function getAnnotatedTargetForTargetReflectionMethod(string $classType, string $method) : ?AnnotatedTarget {
        foreach ($this->targets as $target) {
            if ($target->getTargetReflection() instanceof ReflectionMethod && $target->getTargetReflection()->getDeclaringClass()->getName() === $classType && $target->getTargetReflection()->getName() === $method) {
                return $target;
            }
        }
        return null;
    }

    protected function getAnnotatedTargetsForTargetReflectParameter(string $classType, string $method, string $paramName) : array {
        $targets = [];
        foreach ($this->targets as $target) {
            if (
                $target->getTargetReflection() instanceof ReflectionParameter &&
                $target->getTargetReflection()->getDeclaringClass()->getName() === $classType &&
                $target->getTargetReflection()->getDeclaringFunction()->getName() === $method &&
                $target->getTargetReflection()->getName() === $paramName
            ) {
                $targets[] = $target;
            }
        }
        return $targets;
    }

    private function parseDirectories() : Generator {
        yield from (new StaticAnalysisAnnotatedTargetParser())->parse($this->getDirectories());
    }

    abstract protected function getDirectories() : array;

}