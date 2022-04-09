<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Attribute\InjectEnv;
use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\InjectService;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Attribute\ThirdPartyInjectScalar;
use Cspray\AnnotatedContainer\Attribute\ThirdPartyService;
use Cspray\AnnotatedContainer\Attribute\ThirdPartyServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ThirdPartyServicePrepare;

/**
 * @Internal
 */
enum AttributeType : string {
    case InjectEnv = InjectEnv::class;
    case InjectScalar = InjectScalar::class;
    case InjectService = InjectService::class;
    case Service = Service::class;
    case ServiceDelegate = ServiceDelegate::class;
    case ServicePrepare = ServicePrepare::class;
}