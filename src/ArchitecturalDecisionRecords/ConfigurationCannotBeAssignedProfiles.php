<?php

namespace Cspray\AnnotatedContainer\ArchitecturalDecisionRecords;

use Attribute;
use Cspray\ArchitecturalDecision\DecisionStatus;
use Cspray\ArchitecturalDecision\DocBlockArchitecturalDecision;

/**
 * # Configuration instances cannot be assigned profiles
 *
 * ## Context
 *
 * Configuration instances are classes with properties that can have arbitrary values injected into them with the
 * #[Inject] Attribute. Like a Service, Configuration instances are shared with the Container. Unlike a Service,
 * Configuration cannot be assigned an explicit profile.
 *
 * ## Decision
 *
 * We explicitly do no allow setting a profile on a Configuration. The Configuration is meant to use #[Inject] Attributes
 * to define values. Any value that should only be injected when certain profiles are active should have that reflected
 * in the #[Inject] Attribute. This way just 1 Configuration instance is required and any profile-specific values are
 * defined on the value itself.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ConfigurationCannotBeAssignedProfiles extends DocBlockArchitecturalDecision {

    public function getDate() : string {
        return '2022-08-10';
    }

    public function getStatus() : DecisionStatus {
        return DecisionStatus::Accepted;
    }
}