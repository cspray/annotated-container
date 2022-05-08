<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface HasBackingContainer {

    public function getBackingContainer() : object;

}