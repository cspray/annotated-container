<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
interface ScalarGetter {

    public function getString() : string;

    public function getInt() : int;

    public function getFloat() : float;

    public function getBool() : bool;

}