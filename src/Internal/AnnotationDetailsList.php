<?php

namespace Cspray\AnnotatedContainer\Internal;

/**
 * @Internal
 */
final class AnnotationDetailsList {

    /**
     * @var AnnotationDetails[]
     */
    private array $details = [];

    public function add(AnnotationDetails $annotationDetails) {
        $this->details[] = $annotationDetails;
    }

    /**
     * @param AttributeType $attributeType
     * @return AnnotationDetails[]
     */
    public function getSubsetForAttributeType(AttributeType $attributeType) : array {
        $validDetails = [];
        foreach ($this->details as $detail) {
            if ($detail->getAttributeType() === $attributeType) {
                $validDetails[] = $detail;
            }
        }
        return $validDetails;
    }

    public function merge(AnnotationDetailsList $annotationDetailsMap) : void {
        $this->details = array_merge($this->details, $annotationDetailsMap->details);
    }

}