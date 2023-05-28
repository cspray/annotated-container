<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServicePrepare;

use Attribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class DummyPrepare implements ServicePrepareAttribute {

}