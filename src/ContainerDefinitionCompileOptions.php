<?php

namespace Cspray\AnnotatedContainer;

interface ContainerDefinitionCompileOptions {

    public function getScanDirectories() : array;

    public function getProfiles() : array;

}