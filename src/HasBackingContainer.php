<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * An interface exposed by Annotated Container that provides access to the Container that implements the functionality
 * for a given instance.
 */
interface HasBackingContainer {

    /**
     * Will return whatever Container implementation powers the given Annotated Container.
     *
     * @return object
     */
    public function getBackingContainer() : object;

}