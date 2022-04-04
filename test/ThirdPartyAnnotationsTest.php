<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use PHPUnit\Framework\TestCase;

class ThirdPartyAnnotationsTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    public function testThirdPartyServiceTypeOnly() : void {
         $compiler = ContainerDefinitionCompilerFactory::withoutCache()->getCompiler();
         $containerDefinition = $compiler->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ThirdPartyServiceTypeOnly')->build());

         $this->assertServiceDefinitionsHaveTypes([
             DummyApps\SimpleServices\FooInterface::class
         ], $containerDefinition->getServiceDefinitions());
    }

}