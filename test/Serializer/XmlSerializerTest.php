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

    use ContainerDefinitionAssertionsTrait;

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

    public function testServiceIsPrimary() : void {
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

    public function testServiceWithName() : void {
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

    public function testConfigurationDefinition() : void {
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

    public function testNamedConfigurationService() : void {
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

    public function testServicePrepareDefinition() : void {
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

    public function testServiceDelegateDefinition() : void {
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

    public function testInjectMethodParameterStringValue() : void {
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

    public function testInjectMethodParameterIntValue() : void {
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

    public function testInjectMethodParameterUnitEnumValue() : void {
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

    public function testInjectMethodParameterWithStore() : void {
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

    public function testInjectMethodParameterExplicitProfiles() : void {
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

    public function testInjectClassPropertyStringValue() : void {
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

        $this->assertServiceDefinitionsHaveTypes(
            [Fixtures::singleConcreteService()->fooImplementation()->getName()],
            $actual->getServiceDefinitions()
        );
    }

    public function testInjectDefinitionWithUnserializableValueThrowsException() : void {
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

}