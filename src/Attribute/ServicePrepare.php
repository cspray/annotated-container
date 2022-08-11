<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

/**
 * Defines a method on a Service to be invoked when it is instantiated.
 *
 * @package Cspray\AnnotatedContainer\Attribute
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class ServicePrepare implements ServicePrepareAttribute {}