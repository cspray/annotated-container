<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Initializer;

interface ThirdPartyInitializerProvider {

    /**
     * @return ThirdPartyInitializer
     */
    public function thirdPartyInitializers() : array;
}
