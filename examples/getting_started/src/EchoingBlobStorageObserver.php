<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class EchoingBlobStorageObserver implements BlobStorageObserver {

    public function onBlobStored(string $blob) : void {
        echo "Stored blob: \"${blob}\"", PHP_EOL;
    }

}