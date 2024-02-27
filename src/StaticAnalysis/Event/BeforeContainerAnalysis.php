<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis\Event;

use Cspray\AnnotatedContainer\Event\AbstractEvent;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;

/**
 * @extends AbstractEvent<ContainerDefinitionAnalysisOptions>
 */
final class BeforeContainerAnalysis extends AbstractEvent {

}