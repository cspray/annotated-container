<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

enum AnalysisEvents {

    case AnalyzedContainerDefinitionFromCache;

    case AnalyzedInjectDefinitionFromAttribute;

    case AnalyzedServiceDefinitionFromAttribute;

    case AnalyzedServiceDelegateDefinitionFromAttribute;

    case AnalyzedServicePrepareDefinitionFromAttribute;

    case BeforeContainerAnalysis;

    case AfterContainerAnalysis;

}
