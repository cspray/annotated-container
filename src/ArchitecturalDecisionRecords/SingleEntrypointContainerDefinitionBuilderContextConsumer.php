<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ArchitecturalDecisionRecords;

use Attribute;
use Cspray\ArchitecturalDecision\DecisionStatus;
use Cspray\ArchitecturalDecision\DocBlockArchitecturalDecision;

/**
 * # Allow Single Entrypoint for ContainerDefinitionBuilderContextConsumer
 *
 * ## Context
 *
 * A ContainerDefinitionBuilderContextConsumer is primarily intended to support adding third-party services that can't
 * be annotated to a ContainerDefinition. It could be beneficial to attach multiple consumers so that complex third-party
 * service setup does not have to happen entirely in 1 implementation.
 *
 * ## Decision
 *
 * We explicitly only allow one ContainerDefinitionBuilderContextConsumer to be configured when compiling your
 * ContainerDefinition.
 *
 * It would be technically possible, and even straightforward, to allow configuring multiple context consumers. However,
 * doing so would have a significant cognitive overhead and, potentially, cause what services are used in a given
 * situation to be vague or unclear. Specifically, third-party packages could provide a context consumer that overrides
 * your definitions without you being fully aware of it.
 *
 * If you need to use multiple consumers or consumers implemented by third-parties then you're required to provide your
 * own entrypoint and compose them together or explicitly define which third-party consumer you'd like to use. This way
 * you know precisely what code is determining the services for your application.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class SingleEntrypointContainerDefinitionBuilderContextConsumer extends DocBlockArchitecturalDecision {
    public function getDate() : string {
        return '2022-07-19';
    }

    public function getStatus() : DecisionStatus {
        return DecisionStatus::Accepted;
    }
}