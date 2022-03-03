<?php

namespace Cspray\AnnotatedContainer;

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

    protected function assertAliasDefinitionsMap(array $expectedAliasMap, array $aliasDefinitions) : void {
        if (($countExpected = count($expectedAliasMap)) !== ($countActual = count($aliasDefinitions))) {
            $this->fail("Expected ${countExpected} AliasDefinitions but received ${countActual}");
        }

        $actualMap = [];
        foreach ($aliasDefinitions as $aliasDefinition) {
            $this->assertInstanceOf(AliasDefinition::class, $aliasDefinition);
            $actualMap[] = [
                $aliasDefinition->getAbstractService()->getType(),
                $aliasDefinition->getConcreteService()->getType()
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
            $key = $servicePrepareDefinition->getService()->getType();
            $actualMap[] = [$key, $servicePrepareDefinition->getMethod()];
        }

        array_multisort($actualMap);
        array_multisort($expectedServicePrepare);
        $this->assertEquals($expectedServicePrepare, $actualMap);
    }

    protected function assertUseScalarParamValues(array $expectedValueMap, array $UseScalarDefinitions) : void {
        if (($countExpected = count($expectedValueMap)) !== ($countActual = count($UseScalarDefinitions))) {
            $this->fail("Expected ${countExpected} InjectScalarDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($UseScalarDefinitions as $UseScalarDefinition) {
            $this->assertInstanceOf(InjectScalarDefinition::class, $UseScalarDefinition);
            $key = sprintf(
                "%s::%s(%s)",
                $UseScalarDefinition->getService()->getType(),
                $UseScalarDefinition->getMethod(),
                $UseScalarDefinition->getParamName()
            );
            $actualMap[$key] = $UseScalarDefinition->getValue();
        }

        ksort($actualMap);
        ksort($expectedValueMap);
        $this->assertEquals($expectedValueMap, $actualMap);
    }

    protected function assertUseServiceParamValues(array $expectedValueMap, array $UseServiceDefinitions) : void {
        if (($countExpected = count($expectedValueMap)) !== ($countActual = count($UseServiceDefinitions))) {
            $this->fail("Expected ${countExpected} InjectServiceDefinition but received ${countActual}");
        }

        $actualMap = [];
        foreach ($UseServiceDefinitions as $UseServiceDefinition) {
            $this->assertInstanceOf(InjectServiceDefinition::class, $UseServiceDefinition);
            $key = sprintf(
                "%s::%s(%s)",
                $UseServiceDefinition->getService()->getType(),
                $UseServiceDefinition->getMethod(),
                $UseServiceDefinition->getParamName()
            );
            $actualMap[$key] = $UseServiceDefinition->getInjectedService()->getType();
        }

        ksort($actualMap);
        ksort($expectedValueMap);
        $this->assertEquals($expectedValueMap, $actualMap);
    }

}