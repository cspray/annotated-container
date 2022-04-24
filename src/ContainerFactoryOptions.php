<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface ContainerFactoryOptions {

    public function getActiveProfiles() : array;

}