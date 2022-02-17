<?php

namespace Cspray\AnnotatedContainer;

interface ContainerDefinitionSerializer {

    public function serialize(ContainerDefinition $containerDefinition, ContainerDefinitionSerializerOptions $options = null) : string;

    public function deserialize(string $serializedDefinition) : ContainerDefinition;

}