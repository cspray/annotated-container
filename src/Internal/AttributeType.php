<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

/**
 * @Internal
 */
enum AttributeType : string {
    case Inject = Inject::class;
    case Service = Service::class;
    case ServiceDelegate = ServiceDelegate::class;
    case ServicePrepare = ServicePrepare::class;
}