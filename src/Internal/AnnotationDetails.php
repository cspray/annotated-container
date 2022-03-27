<?php

namespace Cspray\AnnotatedContainer\Internal;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use SplFileInfo;

class AnnotationDetails {

    public function __construct(
        private SplFileInfo $fileInfo,
        private ReflectionAttribute $reflectionAttribute,
        private ReflectionClass|ReflectionMethod|ReflectionParameter $reflectionClass,
        private ?AnnotationDetailsMetadata $annotationDetailsMetadata = null
    ) {
        if (!isset($this->annotationDetailsMetadata)) {
            $this->annotationDetailsMetadata = new AnnotationDetailsMetadata();
        }
    }

    public function getFile() : SplFileInfo {
        return $this->fileInfo;
    }

    public function getReflectionAttribute() : ReflectionAttribute {
        return $this->reflectionAttribute;
    }

    public function getReflection() : ReflectionClass|ReflectionMethod|ReflectionParameter {
        return $this->reflectionClass;
    }

    public function getAnnotationDetailsMetaData() : AnnotationDetailsMetadata {
        return $this->annotationDetailsMetadata;
    }

}