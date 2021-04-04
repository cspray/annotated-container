<?php declare(strict_types=1);

namespace Acme\AnnotatedInjectorDemo;

use Cspray\AnnotatedInjector\Attribute\ServiceSetup;

abstract class AbstractBlobStorage implements BlobStorage {

    private array $observers = [];

    public function attach(BlobStorageObserver $storageObserver) : void {
        $this->observers[] = $storageObserver;
    }

    public function store(string $blob) : void {
        echo $this->getDescriptor(), PHP_EOL;
        foreach ($this->observers as $observer) {
            $observer->onBlobStored($blob);
        }
    }

    abstract protected function getDescriptor() : string;

}