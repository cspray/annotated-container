<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface ScalarGetter {

    public function getString() : string;

    public function getInt() : int;

    public function getFloat() : float;

    public function getBool() : bool;

}