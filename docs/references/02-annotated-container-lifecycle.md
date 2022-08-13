# Annotated Container Lifecycle

Annotated Container has a discrete lifecycle. Each phase of the lifecycle corresponds to an emitted event. The lifecycle defines whether we're still in compilation or creating the Container. Each lifecycle phase has a piece of data associated with it that will be available in the corresponding event.

## Lifecycle Phases

These are order-dependent, each phase will complete and its event emitted before moving on to the next phase.

| Lifecycle Phase |Associated Data|Notes|
|-----------------|---------------|---|
| BeforeCompile   |N/A|No data is provided, here to mark that the process has started|
 | AfterCompile|`Cspray\AnnotatedContainer\ContainerDefinition`|Access to the ContainerDefinition after all Attributes have been parsed and consumers have been triggered. The actual parsing of Attributes might be skipped in this phase if there's a cached ContainerDefinition present|
| BeforeContainerCreation|`Cspray\AnnotatedContainer\ContainerDefinition`|Access to the ContainerDefinition before it is used to create a Container. This should be the same object present in the AfterCompile phase|
| AfterContainerCreation|`Cspray\AnnotatedContainer\AnnotatedContainer`|The actual Container created from your ContainerDefinition. This should be the same object returned from the ContainerFactory for your backing implementation.|

