<?php

namespace Cspray\AnnotatedContainer\Serializer;

use Cspray\AnnotatedContainer\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\ConfigurationDefinitionBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidDefinitionException;
use Cspray\AnnotatedContainer\Helper\UnserializableObject;
use Cspray\AnnotatedContainer\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\ServicePrepareDefinitionBuilder;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\CardinalDirections;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

class XmlSerializerTest extends TestCase {

    public function testSerializingSingleConcreteService() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServiceWithExplicitProfiles() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>my-profile</profile>
        <profile>my-other-profile</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->withProfiles(['my-profile', 'my-other-profile'])
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServicesWithAliases() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
    </serviceDefinition>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions>
    <aliasDefinition>
      <abstractService>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</abstractService>
      <concreteService>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooImplementation</concreteService>
    </aliasDefinition>
  </aliasDefinitions>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract(Fixtures::implicitAliasedServices()->fooInterface())->build()
            )->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::implicitAliasedServices()->fooImplementation())->build()
            )->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract(Fixtures::implicitAliasedServices()->fooInterface())
                    ->withConcrete(Fixtures::implicitAliasedServices()->fooImplementation())
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServiceIsPrimary() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition isPrimary="true">
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation(), true)->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServiceWithName() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name>my-name</name>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->withName('my-name')
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingConfigurationDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions>
    <configurationDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name/>
    </configurationDefinition>
  </configurationDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->build()
            )->withConfigurationDefinition(
                ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingNamedConfigurationService() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions>
    <configurationDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name>config-name</name>
    </configurationDefinition>
  </configurationDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->build()
            )->withConfigurationDefinition(
                ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())
                    ->withName('config-name')
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServicePrepareDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions>
    <servicePrepareDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <method>setBar</method>
    </servicePrepareDefinition>
  </servicePrepareDefinitions>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract(Fixtures::interfacePrepareServices()->fooInterface())
                    ->build()
            )->withServicePrepareDefinition(
                ServicePrepareDefinitionBuilder::forMethod(
                    Fixtures::interfacePrepareServices()->fooInterface(),
                    'setBar'
                )->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServiceDelegateDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions>
    <serviceDelegateDefinition>
      <service>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</service>
      <delegateType>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceFactory</delegateType>
      <delegateMethod>createService</delegateMethod>
    </serviceDelegateDefinition>
  </serviceDelegateDefinitions>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract(Fixtures::delegatedService()->serviceInterface())->build()
            )->withServiceDelegateDefinition(
                ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
                    ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterStringValue() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('foobar'));
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</class>
          <method>__construct</method>
          <parameter>val</parameter>
        </classMethod>
      </target>
      <valueType>string</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectConstructorServices()->injectStringService())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectStringService())
                ->withMethod('__construct', stringType(), 'val')
                ->withValue('foobar')
                ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterIntValue() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize(42));
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\IntInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\IntInjectService</class>
          <method>__construct</method>
          <parameter>meaningOfLife</parameter>
        </classMethod>
      </target>
      <valueType>int</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectConstructorServices()->injectIntService())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectIntService())
                    ->withMethod('__construct', intType(), 'meaningOfLife')
                    ->withValue(42)
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterUnitEnumValue() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize(CardinalDirections::West));
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\EnumInjector</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\EnumInjector</class>
          <method>__construct</method>
          <parameter>directions</parameter>
        </classMethod>
      </target>
      <valueType>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\CardinalDirections</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectEnumConstructorServices()->enumInjector())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectEnumConstructorServices()->enumInjector())
                    ->withMethod('__construct', objectType(CardinalDirections::class), 'directions')
                    ->withValue(CardinalDirections::West)
                    ->build()
            )->build();

        $subject = new XmlSerializer();
        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterWithStore() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('key'));
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectCustomStoreServices\ScalarInjector</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectCustomStoreServices\ScalarInjector</class>
          <method>__construct</method>
          <parameter>key</parameter>
        </classMethod>
      </target>
      <valueType>string</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store>test-store</store>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;


        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectCustomStoreServices()->scalarInjector())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectCustomStoreServices()->scalarInjector())
                    ->withMethod('__construct', stringType(), 'key')
                    ->withStore('test-store')
                    ->withValue('key')
                    ->build()
            )->build();

        $subject = new XmlSerializer();
        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterExplicitProfiles() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('foobar'));
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</class>
          <method>__construct</method>
          <parameter>val</parameter>
        </classMethod>
      </target>
      <valueType>string</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>foo</profile>
        <profile>baz</profile>
      </profiles>
      <store/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectConstructorServices()->injectStringService())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectStringService())
                    ->withMethod('__construct', stringType(), 'val')
                    ->withValue('foobar')
                    ->withProfiles('foo', 'baz')
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectClassPropertyStringValue() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('my-api-key'));
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classProperty>
          <class>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</class>
          <property>key</property>
        </classProperty>
      </target>
      <valueType>string</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::configurationServices()->myConfig())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('my-api-key')
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectDefinitionWithUnserializableValueThrowsException() : void {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectEnumConstructorServices()->enumInjector())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectEnumConstructorServices()->enumInjector())
                    ->withMethod('__construct', objectType(UnserializableObject::class), 'directions')
                    ->withValue(new UnserializableObject())
                    ->build()
            )->build();

        $subject = new XmlSerializer();

        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('An InjectDefinition with a value that cannot be serialized was provided.');

        $subject->serialize($containerDefinition);
    }

    public function testDeserializingConcreteServiceDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->getType());
        self::assertSame(['default'], $serviceDefinition->getProfiles());
        self::assertNull($serviceDefinition->getName());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingNamedConcreteServiceDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name>my_service_name</name>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->getType());
        self::assertSame(['default'], $serviceDefinition->getProfiles());
        self::assertSame('my_service_name', $serviceDefinition->getName());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingPrimaryConcreteServiceDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition isPrimary="true">
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->getType());
        self::assertSame(['default'], $serviceDefinition->getProfiles());
        self::assertNull($serviceDefinition->getName());
        self::assertTrue($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingServiceDefinitionWithProfiles() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition isPrimary="false">
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>foo</profile>
        <profile>bar</profile>
        <profile>baz</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->getType());
        self::assertSame(['foo', 'bar', 'baz'], $serviceDefinition->getProfiles());
        self::assertNull($serviceDefinition->getName());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializeAbstractServiceDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;
        $subject = new XmlSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::implicitAliasedServices()->fooInterface(), $serviceDefinition->getType());
        self::assertSame(['default'], $serviceDefinition->getProfiles());
        self::assertNull($serviceDefinition->getName());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertFalse($serviceDefinition->isConcrete());
        self::assertTrue($serviceDefinition->isAbstract());
    }

    public function testDeserializeAliasDefinitions() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
    </serviceDefinition>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions>
    <aliasDefinition>
      <abstractService>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</abstractService>
      <concreteService>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooImplementation</concreteService>
    </aliasDefinition>
  </aliasDefinitions>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getAliasDefinitions());
        $aliasDefinition = $actual->getAliasDefinitions()[0];
        self::assertSame(Fixtures::implicitAliasedServices()->fooInterface(), $aliasDefinition->getAbstractService());
        self::assertSame(Fixtures::implicitAliasedServices()->fooImplementation(), $aliasDefinition->getConcreteService());
    }

    public function testDeserializeServicePrepareDefinitions() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions>
    <servicePrepareDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <method>setBar</method>
    </servicePrepareDefinition>
  </servicePrepareDefinitions>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getServicePrepareDefinitions());
        $prepareDefinition = $actual->getServicePrepareDefinitions()[0];
        self::assertSame(Fixtures::interfacePrepareServices()->fooInterface(), $prepareDefinition->getService());
        self::assertSame('setBar', $prepareDefinition->getMethod());
    }

    public function testDeserializeServiceDelegateDefinitions() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions>
    <serviceDelegateDefinition>
      <service>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</service>
      <delegateType>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceFactory</delegateType>
      <delegateMethod>createService</delegateMethod>
    </serviceDelegateDefinition>
  </serviceDelegateDefinitions>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getServiceDelegateDefinitions());
        $delegateDefinition = $actual->getServiceDelegateDefinitions()[0];

        self::assertSame(Fixtures::delegatedService()->serviceInterface(), $delegateDefinition->getServiceType());
        self::assertSame(Fixtures::delegatedService()->serviceFactory(), $delegateDefinition->getDelegateType());
        self::assertSame('createService', $delegateDefinition->getDelegateMethod());
    }

    public function testDeserializeInjectMethodParameter() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('foobar'));
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</class>
          <method>__construct</method>
          <parameter>val</parameter>
        </classMethod>
      </target>
      <valueType>string</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectConstructorServices()->injectStringService(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('__construct', $injectDefinition->getTargetIdentifier()->getMethodName());
        self::assertSame('val', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(stringType(), $injectDefinition->getType());
        self::assertSame('foobar', $injectDefinition->getValue());
        self::assertSame(['default'], $injectDefinition->getProfiles());
        self::assertNull($injectDefinition->getStoreName());
    }

    public function testDeserializeInjectDefinitionUnitEnumValueMethodParameter() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize(CardinalDirections::West));
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\EnumInjector</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\EnumInjector</class>
          <method>__construct</method>
          <parameter>directions</parameter>
        </classMethod>
      </target>
      <valueType>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\CardinalDirections</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectEnumConstructorServices()->enumInjector(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('__construct', $injectDefinition->getTargetIdentifier()->getMethodName());
        self::assertSame('directions', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(objectType(CardinalDirections::class), $injectDefinition->getType());
        self::assertSame(CardinalDirections::West, $injectDefinition->getValue());
        self::assertSame(['default'], $injectDefinition->getProfiles());
        self::assertNull($injectDefinition->getStoreName());
    }

    public function testDeserializeInjectDefinitionMethodParameterWithStore() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('key'));
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectCustomStoreServices\ScalarInjector</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectCustomStoreServices\ScalarInjector</class>
          <method>__construct</method>
          <parameter>key</parameter>
        </classMethod>
      </target>
      <valueType>string</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store>test-store</store>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectCustomStoreServices()->scalarInjector(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('__construct', $injectDefinition->getTargetIdentifier()->getMethodName());
        self::assertSame('key', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(stringType(), $injectDefinition->getType());
        self::assertSame('key', $injectDefinition->getValue());
        self::assertSame(['default'], $injectDefinition->getProfiles());
        self::assertSame('test-store', $injectDefinition->getStoreName());
    }

    public function testDeserializeInjectMethodWithProfiles() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('annotated container'));
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</class>
          <method>__construct</method>
          <parameter>val</parameter>
        </classMethod>
      </target>
      <valueType>string</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>foo</profile>
        <profile>baz</profile>
      </profiles>
      <store/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();

        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectConstructorServices()->injectStringService(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('__construct', $injectDefinition->getTargetIdentifier()->getMethodName());
        self::assertSame('val', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(stringType(), $injectDefinition->getType());
        self::assertSame('annotated container', $injectDefinition->getValue());
        self::assertSame(['foo', 'baz'], $injectDefinition->getProfiles());
        self::assertNull($injectDefinition->getStoreName());
    }

    public function testDeserializeInjectClassPropertyDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('annotated container'));
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classProperty>
          <class>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</class>
          <property>key</property>
        </classProperty>
      </target>
      <valueType>string</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isClassProperty()
        );
        self::assertSame(
            Fixtures::configurationServices()->myConfig(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('key', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(stringType(), $injectDefinition->getType());
        self::assertSame('annotated container', $injectDefinition->getValue());
        self::assertSame(['default'], $injectDefinition->getProfiles());
        self::assertNull($injectDefinition->getStoreName());
    }

    public function testDeserializeConfigurationDefinitions() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions>
    <configurationDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name>my-config</name>
    </configurationDefinition>
  </configurationDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getConfigurationDefinitions());
        $configurationDefinition = $actual->getConfigurationDefinitions()[0];

        self::assertSame(Fixtures::configurationServices()->myConfig(), $configurationDefinition->getClass());
        self::assertSame('my-config', $configurationDefinition->getName());
    }

}