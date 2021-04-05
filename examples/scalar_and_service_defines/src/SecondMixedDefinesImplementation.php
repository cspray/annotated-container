<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class SecondMixedDefinesImplementation implements MixedDefinesInterface {

}