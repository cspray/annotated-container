<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

/**
 * Defines a method on a Service to be invoked when it is instantiated.
 *
 * @package Cspray\AnnotatedInjector\Attribute
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ServicePrepare {

}