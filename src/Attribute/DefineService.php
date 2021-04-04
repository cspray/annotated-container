<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

/**
 * Class DefineService
 * @package Cspray\AnnotatedInjector\Attribute
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class DefineService {

    public function __construct(
        private string $name
    ) {}

}