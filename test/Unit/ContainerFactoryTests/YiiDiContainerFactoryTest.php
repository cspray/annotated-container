<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactoryTests;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\YIiDiContainerFactory;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\AnnotatedContainer\Unit\ContainerFactoryTestCase;
use Cspray\Typiphy\ObjectType;
use Yiisoft\Di\Container;
use function Cspray\Typiphy\objectType;

class YiiDiContainerFactoryTest extends ContainerFactoryTestCase
{

    protected function getContainerFactory(ActiveProfiles $activeProfiles): ContainerFactory
    {
        return new YIiDiContainerFactory();
    }

    protected function getBackingContainerInstanceOf(): ObjectType
    {
        return objectType(Container::class);
    }

    public function testCreateArbitraryStoreOnConfigurationNotPresent()
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testConfigurationSharedInstance()
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testConfigurationValues()
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testNamedConfigurationInstanceOf()
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testLoggingConfiguration(): void
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testLoggingNamedConfiguration(): void
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testLoggingInjectNonServiceNotFromStoreConfigurationProperty(): void
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testLoggingInjectEnumFromConfigurationProperty(): void
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testLoggingInjectValueFromStoreForConfigurationProperty(): void
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testLoggingInjectServiceForConfigurationProperty(): void
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testCreatingConstructorPromotedConfiguration(): void
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testCreatingAliasedConfiguration(): void
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }

    public function testLoggingInjectPropertyArrayNotMultiline(): void
    {
        $this->markTestSkipped('Configuration is not supported yet');
    }


}
