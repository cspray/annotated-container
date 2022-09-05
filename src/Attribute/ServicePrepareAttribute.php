<?php

namespace Cspray\AnnotatedContainer\Attribute;

/**
 * Can be implemented by your own classes to mark a Service method to be invoked after the Container has created it.
 *
 * The class that implements this interface should also be marked as an Attribute; it should be not repeatable and able
 * to target only a class method construct.
 */
interface ServicePrepareAttribute {

}