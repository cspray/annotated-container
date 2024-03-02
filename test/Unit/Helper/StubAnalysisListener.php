<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\Listener\AfterContainerAnalysis;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedContainerDefinitionFromCache;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedInjectDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedServiceDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedServiceDelegateDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedServicePrepareDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\BeforeContainerAnalysis;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedTarget\AnnotatedTarget;

class StubAnalysisListener implements BeforeContainerAnalysis,
    AnalyzedServiceDefinitionFromAttribute,
    AnalyzedServiceDelegateDefinitionFromAttribute,
    AnalyzedServicePrepareDefinitionFromAttribute,
    AnalyzedInjectDefinitionFromAttribute,
    AnalyzedContainerDefinitionFromCache,
    AfterContainerAnalysis {

    /**
     * @var list<AnalysisEvents>
     */
    private array $triggeredEvents = [];

    /**
     * @return list<AnalysisEvents>
     */
    public function getTriggeredEvents() : array {
        return $this->triggeredEvents;
    }

    public function handleAnalyzedContainerDefinitionFromCache(ContainerDefinition $containerDefinition, string $cacheFile) : void {
        $this->triggeredEvents[] = AnalysisEvents::AnalyzedContainerDefinitionFromCache;
    }

    public function handleAnalyzedInjectDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, InjectDefinition $injectDefinition) : void {
        $this->triggeredEvents[] = AnalysisEvents::AnalyzedInjectDefinitionFromAttribute;
    }

    public function handleAnalyzedServiceDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServiceDefinition $serviceDefinition) : void {
        $this->triggeredEvents[] = AnalysisEvents::AnalyzedServiceDefinitionFromAttribute;
    }

    public function handleAnalyzedServiceDelegateDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServiceDelegateDefinition $definition) : void {
        $this->triggeredEvents[] = AnalysisEvents::AnalyzedServiceDelegateDefinitionFromAttribute;
    }

    public function handleAnalyzedServicePrepareDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServicePrepareDefinition $definition) : void {
        $this->triggeredEvents[] = AnalysisEvents::AnalyzedServicePrepareDefinitionFromAttribute;
    }

    public function handleBeforeContainerAnalysis(ContainerDefinitionAnalysisOptions $analysisOptions) : void {
        $this->triggeredEvents[] = AnalysisEvents::BeforeContainerAnalysis;
    }

    public function handleAfterContainerAnalysis(ContainerDefinitionAnalysisOptions $analysisOptions, ContainerDefinition $containerDefinition) : void {
        $this->triggeredEvents[] = AnalysisEvents::AfterContainerAnalysis;
    }
}
