<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(environments: ['dev'])]
class DevBlobStorage extends AbstractBlobStorage implements BlobStorage {

    protected function getDescriptor() : string {
        return 'dev blob storage';
    }

}