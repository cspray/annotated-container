<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
interface BlobStorage {

    #[ServicePrepare]
    public function attach(BlobStorageObserver $storageObserver) : void;

    public function store(string $blob) : void;

}