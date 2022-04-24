<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class ServiceDelegate {

    public function __construct(public readonly string $service) {}

}