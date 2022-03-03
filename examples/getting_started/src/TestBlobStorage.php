<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceProfile;

#[Service]
#[ServiceProfile(['test'])]
class TestBlobStorage extends AbstractBlobStorage implements BlobStorage {

    protected function getDescriptor() : string {
        return 'test blob storage';
    }

}