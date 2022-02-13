<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(environments: ['prod'])]
class ProdBlobStorage extends AbstractBlobStorage implements BlobStorage {

    protected function getDescriptor() : string {
        return 'prod blob storage';
    }

}