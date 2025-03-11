<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

interface ThirdPartyInitializerProvider {

    /**
     * @return list<ThirdPartyInitializer>
     */
    public function thirdPartyInitializers() : array;
}
