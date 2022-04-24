<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface ContainerDefinitionBuilderContext {

    public function getBuilder() : ContainerDefinitionBuilder;

    public function setBuilder(ContainerDefinitionBuilder $containerDefinitionBuilder);


}