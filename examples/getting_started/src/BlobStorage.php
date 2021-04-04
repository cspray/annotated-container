<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
interface BlobStorage {

    #[ServicePrepare]
    public function attach(BlobStorageObserver $storageObserver) : void;

    public function store(string $blob) : void;

}