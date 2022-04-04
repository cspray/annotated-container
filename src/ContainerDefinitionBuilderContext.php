<?php

namespace Cspray\AnnotatedContainer;

interface ContainerDefinitionBuilderContext {

    public function getBuilder() : ContainerDefinitionBuilder;

    public function setBuilder(ContainerDefinitionBuilder $containerDefinitionBuilder);


}