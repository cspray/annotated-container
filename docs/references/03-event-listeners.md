# Event Listener

## Bootstrap Listeners

These listeners are invoked from the bootstrapping context of Annotated Container.

### `Cspray\AnnotatedContainer\Bootstrap\Listener\BeforeBootstrap`

The first event invoked, before any bootstrapping is performed and provides the configuration that will be used for 
bootstrapping.

**Available Data**

- `Cspray\AnnotatedContainer\Bootstrap\Configuration\BootstrappingConfiguration`

### `Cspray\AnnotatedContainer\Bootstrap\Listener\AfterBootstrap`

The last event invoked, after all static analysis has concluded and a fully-wired container has been constructed.

**Available Data**

- `Cspray\AnnotatedContainer\Bootstrap\Configuration\BootstrappingConfiguration`
- `Cspray\AnnotatedContainer\Definition\ContainerDefinition`
- `Cspray\AnnotatedContainer\AnnotatedContainer`
- `Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics`