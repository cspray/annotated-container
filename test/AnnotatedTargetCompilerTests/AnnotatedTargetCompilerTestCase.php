<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\AnnotatedTargetConsumer;
use Cspray\AnnotatedContainer\AnnotatedTargetProvider;
use Cspray\AnnotatedContainer\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionParameter;

abstract class AnnotatedTargetCompilerTestCase extends TestCase {

    protected AnnotatedTargetProvider $provider;

    protected function setUp(): void {
        $this->provider = $this->compileDirectories();
    }

    protected function getAnnotatedTargetForTargetReflectionClass(AnnotatedTargetProvider $targetProvider, string $classType) : ?AnnotatedTarget {
        foreach ($targetProvider->getTargets() as $target) {
            if ($target->getTargetReflection()->getName() === $classType) {
                return $target;
            }
        }
        return null;
    }

    protected function getAnnotatedTargetForTargetReflectionMethod(AnnotatedTargetProvider $targetProvider, string $classType, string $method) : ?AnnotatedTarget {
        foreach ($targetProvider->getTargets() as $target) {
            if ($target->getTargetReflection() instanceof ReflectionMethod && $target->getTargetReflection()->getDeclaringClass()->getName() === $classType && $target->getTargetReflection()->getName() === $method) {
                return $target;
            }
        }
        return null;
    }

    protected function getAnnotatedTargetsForTargetReflectParameter(AnnotatedTargetProvider $targetProvider, string $classType, string $method, string $paramName) : array {
        $targets = [];
        foreach ($targetProvider->getTargets() as $target) {
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

    protected function compileDirectories() : AnnotatedTargetProvider {
        $consumer = $this->getGatheringConsumer();
        (new PhpParserAnnotatedTargetParser())->parse($this->getDirectories(), $consumer);
        return $consumer;
    }

    abstract protected function getDirectories() : array;

    private function getGatheringConsumer() : AnnotatedTargetConsumer&AnnotatedTargetProvider {
        return new class implements AnnotatedTargetProvider, AnnotatedTargetConsumer {

            private array $targets = [];

            public function consume(AnnotatedTarget $annotatedTarget): void {
                $this->targets[] = $annotatedTarget;
            }

            public function getTargets(): array {
                return $this->targets;
            }
        };
    }

}