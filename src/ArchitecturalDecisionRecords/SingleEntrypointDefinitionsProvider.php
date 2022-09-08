<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ArchitecturalDecisionRecords;

use Attribute;
use Cspray\ArchitecturalDecision\DecisionStatus;
use Cspray\ArchitecturalDecision\DocBlockArchitecturalDecision;

/**
 * # Allow Single Entrypoint for DefinitionsProvider
 *
 * ## Context
 *
 * A DefinitionsProvider is intended to support adding third-party services that can't be annotated to a
 * ContainerDefinition. It could be beneficial to attach multiple consumers so that complex third-party service setup
 * does not have to happen entirely in 1 implementation.
 *
 * ## Decision
 *
 * We explicitly only allow one DefinitionsProvider to be configured when compiling your ContainerDefinition.
 *
 * It would be technically possible, and even straightforward, to allow configuring multiple providers. However, doing
 * so would have a significant cognitive overhead and, potentially, cause what services are used in a given situation to
 * be vague or unclear. Specifically, third-party packages could override your definitions without you being fully
 * aware of it.
 *
 * If you need to use multiple providers or providers implemented by third-parties then you're required to provide your
 * own entrypoint and compose them together or explicitly define which third-party provider you'd like to use. This way
 * you know precisely what code is determining the services for your application.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class SingleEntrypointDefinitionsProvider extends DocBlockArchitecturalDecision {
    public function getDate() : string {
        return '2022-07-19';
    }

    public function getStatus() : DecisionStatus {
        return DecisionStatus::Accepted;
    }
}