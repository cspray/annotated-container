<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedTarget\AnnotatedTarget;

/**
 * Responsible for converting an AnnotatedTarget into the appropriate definition object.
 */
interface AnnotatedTargetDefinitionConverter {

    /**
     * Parse the information from the provided $target and return a corresponding definition object.
     *
     * Generally speaking, the conversion process should not attempt to apply any domain logic to the result of the
     * definition. The logic around parsing these definitions into a Container can be complex and inter-dependent on
     * multiple definition types. As this converter intakes one $target at a time it does not have sufficient context
     * to perform any operations on the resultant definition.
     *
     * @param AnnotatedTarget $target
     * @return ServiceDefinition|ServicePrepareDefinition|ServiceDelegateDefinition|InjectDefinition|ConfigurationDefinition
     */
    public function convert(AnnotatedTarget $target) : ServiceDefinition|ServicePrepareDefinition|ServiceDelegateDefinition|InjectDefinition|ConfigurationDefinition;

}