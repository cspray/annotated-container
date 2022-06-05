# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased Changes

## [0.5.0](https://github.com/cspray/annotated-container/tree/v.0.5.0) - 2022-06-04

### Added

- A new `fixture_src/` directory that stores example source code used for the automated test suite.
- Several improvements to the way that Fixtures are handled in the test suite such that the code examples in `fixture_src/` have a first-class representation in the test suite through the `Cspray\AnnotatedContainerFixture\Fixtures` class.
- Implementations for 

### Changed

- Improved many test cases, especially testing definition creation, to be more thorough and improve the test:assertion ratio.

### Fixed

- A bug where fetching a named service by name and then by type would result in non-shared instances.
- A bug where injecting services by name would not work appropriately.

### Removed

- Removed reliance on the `cspray/annotated-container-dummy-apps` package. All code required for the test suite is located in the newly added `fixture_src/` directory.

## [0.4.0](https://github.com/cspray/annotated-container/tree/v0.4.0) - 2022-05-08

> This version introduces many backwards breaking changes. This release also represents a significant step towards a stable API. This level of BC break is not expected in future versions.

### Added

- Added the `cspray/typiphy` package.
- Added a new `#[Inject]` Attribute with the flexibility to handle use cases from previous Inject* Attributes.
- Added a `ParameterStore` that allows injecting arbitrary values at runtime with `#[Inject]`.
- Added an `AutowireableFactory` interface that allows for creating arbitrary objects and defining parameters that cannot be autowired. This interface is intended to allow for the autowire object construction without depending on a Container directly.
- Added new `compiler()` and `containerFactory()` functions for easily interacting with the library in the most common scenarios.
- Added a new `#[Configuration]` Attribute that allows properties to have arbitrary values populated with `#[Inject]`.
- Added a new `PhpDiContainerFactory` for creating a Container with PHP-DI. This class is only available if php-di/php-di is explicitly installed.

### Changed

- Changed many definitions to no longer require other definitions, instead relying on an `ObjectType` from the Typiphy package. This allows for greater flexibility in creating ContainerDefinition and makes it more clear that we're really requiring an ObjectType in a lot of places, not necessarily the entire ServiceDefinition.
- Moved the `AurynContainerFactory` into the `Cspray\AnnotatedContainer\ContainerFactory` namespace. This class is now only available if rdlowrey/auryn is explicitly installed.

### Removed

- Many internal classes were removed to simplify static parsing process. The static parsing is now largely identifying which classes, methods, or parameters to target for reflection when the container is created.
- Removed the `#[InjectScalar]`, `#[InjectService]`, and `#[InjectEnv]`. All uses can now be covered by the new `#[Inject]` Attribute.

## [0.3.0](https://github.com/cspray/annotated-container/tree/v0.3.0) - 2022-04-16

### Added

- Adds ability to mark a Service as primary. If multiple aliases are found the one marked as primary will be used by default.
- Add ability to define multiple InjectScalar and InjectEnv attributes and to specify a profile, or profiles, that each annotation belongs to.
- Add ability to name a Service by passing an argument and allow retrieval with an arbitrary value without having to use the type as an identifier.
- Add ability to define a Service that gets recreated every time you get it from the Container.

### Changed

- Made several changes to the internal parsing of the codebase. This shouldn't have any changes in the public-facing API. In theory these improvements should increase the performance and memory efficiency when parsing. The primary intent of the changes was to make parsing easier to follow and reason about.
- The ContainerDefinitionCompiler is no longer aware of active profiles and will provide a ContainerDefinition with all parsed annotations. It is the responsibility of the ContainerFactory to parse out information relating to active profiles. This change was necessary to accommodate profiles as arguments, including profiles defined as constants.

### Fixed

- An error in the README documentation referencing an incorrect variable.
- Directory paths in all tests point to new directory structure.
- A dev-only dependency, `mikey179/vfsStream` was inadvertently included in the `require` section. This dependency is now properly a `require-dev` dependency.
- Arguments passed to Attributes better differentiates between compile and runtime values by introducing an AnnotationValue. Many 

### Removed

- Extracted the Symfony Console tool into its own repo, `cspray/annotated-container-cli`. Removed the dependency on `symfony/console`.
- Extracted the code examples used in unit tests to its own repo, `cspray/annotated-container-dummy-apps`.
- Removed the `#[ServiceProfile]` Attribute, you can now define profiles for a Service directly on the `#[Service]` annotation by passing a profiles argument.

## [0.2.0](https://github.com/cspray/annotated-container/tree/v0.2.0) - 2022-03-22

### Added

- Support for creating a PSR-11 Container
- Allow to define multiple active profiles at compile time
- Allow defining multiple profiles that a service belongs to
- Improved the way many Definition objects are built
- Allow merging multiple ContainerDefinitions
- Allow a ContainerDefinition to be serialized and deserialized
- Introduced a first-pass CLI tool for compiling a ContainerDefinition and caching it to file
- Allow for using a factory to create a service

### Changed

- Renamed many classes, properties, and methods to reflect `Container` instead of `Injector`
- Renamed `UseScalar` -> `InjectScalar`
- Renamed `UseScalarFromEnv` -> `InjectEnv`
- Rename `UseService`-> `InjectService`

## [0.1.0](https://github.com/cspray/annotated-container/tree/v0.1.0) - 2021-04-06

### Added

- `Service` Attribute to define an interface or class as a shared service or alias.
- `ServicePrepare` Attribute to define a method to be invoked after object creation.
- `UseService` Attribute to define a parameter on a `Service` constructor or `ServicePrepare` method to use the
  provided type to resolve the dependency.
- `UseScalar` Attribute to define a hardcoded, non-object value on a `Service` constructor or `ServicePrepare`
  method.
- `UseScalarFromEnv` Attribute to define a hardcoded, non-object value to be determined by an environment variable
  on a `Service` constructor or `ServicePrepare` method.
- `InjectorDefinitionCompiler` to turn annotated PHP source code in a directory into an `InjectorDefinition` which defines how to construct
  the corresponding `Injector`. An implementation using PHP-Parser is also provided.
- `InjectorFactory` to take an `InjectorDefinition` and turn it into a DI container. An implementation that 
  wires an Auryn `Injector` is also provided.


