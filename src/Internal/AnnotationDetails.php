<?php

namespace Cspray\AnnotatedContainer\Internal;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use SplFileInfo;

/**
 * @Internal
 */
final class AnnotationDetails {

    public function __construct(
        private SplFileInfo $fileInfo,
        private AttributeType $attributeType,
        private AnnotationArguments $annotationArguments,
        private ReflectionClass|ReflectionMethod|ReflectionParameter $reflectionClass,
    ) {}

    public function getFile() : SplFileInfo {
        return $this->fileInfo;
    }

    public function getAttributeType() : AttributeType {
        return $this->attributeType;
    }

    /**
     * The exact type returned is dependent on the Attribute that these details represent.
     *
     * ReflectionClass Attributes
     * - Service
     * ReflectionMethodAttributes
     * - ServicePrepare
     * - ServiceDelegate
     * ReflectionParameter
     * - InjectService
     * - InjectScalar
     * - InjectEnv
     *
     * The ServiceProfile Attribute would not have an AnnotationDetails created for it. Whatever profile is present
     * will be
     *
     * @return ReflectionClass|ReflectionMethod|ReflectionParameter
     */
    public function getReflection() : ReflectionClass|ReflectionMethod|ReflectionParameter {
        return $this->reflectionClass;
    }

    public function getAnnotationArguments() : AnnotationArguments {
        return $this->annotationArguments;
    }

}