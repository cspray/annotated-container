<?php declare(strict_types=1);

namespace Acme\AnnotatedContainerDemo;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceProfile;

#[Service]
#[ServiceProfile(['prod'])]
class ProdBlobStorage extends AbstractBlobStorage implements BlobStorage {

    protected function getDescriptor() : string {
        return 'prod blob storage';
    }

}