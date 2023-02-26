<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

interface ObserverFactory {

    public function createObserver(string $observer) : PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver;

}