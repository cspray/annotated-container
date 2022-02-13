<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FirstMixedDefinesImplementation implements MixedDefinesInterface {

}