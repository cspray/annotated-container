<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface BlobStorageObserver {

    public function onBlobStored(string $blob) : void;

}