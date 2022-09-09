<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;

trait ContainerDefinitionAssertionsTrait /** extends \PHPUnit\TestCase */ {

    protected function assertServiceDefinitionsHaveTypes(array $expectedTypes, array $serviceDefinitions) : void {
        if (($countExpected = count($expectedTypes)) !== ($countActual = count($serviceDefinitions))) {
            $this->fail("Expected ${countExpected} ServiceDefinitions but received ${countActual}");
        }

        $actualTypes = [];
        foreach ($serviceDefinitions as $serviceDefinition) {
            $this->assertInstanceOf(ServiceDefinition::class, $serviceDefinition);
            $actualTypes[] = $serviceDefinition->getType();
        }

        $this->assertEqualsCanonicalizing($expectedTypes, $actualTypes);
    }

    protected function assertServiceDefinitionIsPrimary(array $serviceDefinitions, string $serviceDefinitionType) : void {
        $serviceDefinition = $this->getServiceDefinition($serviceDefinitions, $serviceDefinitionType);
        if ($serviceDefinition === null) {
            $this->fail("Expected $serviceDefinitionType to be present in the provided collection but it is not.");
        }

        $this->assertTrue($serviceDefinition->isPrimary());
    }

    protected function assertServiceDefinitionIsNotPrimary(array $serviceDefinitions, string $serviceDefinitionType) : void {
        $serviceDefinition = $this->getServiceDefinition($serviceDefinitions, $serviceDefinitionType);
        if ($serviceDefinition === null) {
            $this->fail("Expected $serviceDefinitionType to be present in the provided collection but it is not.");
        }

        $this->assertFalse($serviceDefinition->isPrimary());
    }

    protected function assertAliasDefinitionsMap(array $expectedAliasMap, array $aliasDefinitions) : void {
        if (($countExpected = count($expectedAliasMap)) !== ($countActual = count($aliasDefinitions))) {
            $this->fail("Expected ${countExpected} AliasDefinitions but received ${countActual}");
        }

        $actualMap = [];
        foreach ($aliasDefinitions as $aliasDefinition) {
            $this->assertInstanceOf(AliasDefinition::class, $aliasDefinition);
            $actualMap[] = [
                $aliasDefinition->getAbstractService()->getName(),
                $aliasDefinition->getConcreteService()->getName()
            ];
        }

        array_multisort($actualMap);
        array_multisort($expectedAliasMap);
        $this->assertEquals($expectedAliasMap, $actualMap);
    }

    protected function assertServicePrepareTypes(array $expectedServicePrepare, array $servicePrepareDefinitions) : void {
        if (($countExpected = count($expectedServicePrepare)) !== ($countActual = count($servicePrepareDefinitions))) {
            $this->fail("Expected ${countExpected} ServicePrepareDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            $this->assertInstanceOf(ServicePrepareDefinition::class, $servicePrepareDefinition);
            $key = $servicePrepareDefinition->getService()->getName();
            $actualMap[] = [$key, $servicePrepareDefinition->getMethod()];
        }

        array_multisort($actualMap);
        array_multisort($expectedServicePrepare);
        $this->assertEquals($expectedServicePrepare, $actualMap);
    }

    /**
     * @param ServiceDefinition[] $serviceDefinitions
     * @param string $serviceDefinitionType
     * @return ServiceDefinition|null
     */
    protected function getServiceDefinition(array $serviceDefinitions, string $serviceDefinitionType) : ?ServiceDefinition {
        foreach ($serviceDefinitions as $serviceDefinition) {
            if ($serviceDefinitionType === $serviceDefinition->getType()->getName()) {
                return $serviceDefinition;
            }
        }

        return null;
    }

    /**
     * @param ConfigurationDefinition[] $configurationDefinitions
     * @param string $type
     * @return ConfigurationDefinition|null
     */
    protected function getConfigurationDefinition(array $configurationDefinitions, string $type) : ?ConfigurationDefinition {
        foreach ($configurationDefinitions as $configurationDefinition) {
            if ($configurationDefinition->getClass()->getName() === $type) {
                return $configurationDefinition;
            }
        }

        return null;
    }

}