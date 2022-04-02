# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Adds ability to mark a Service as primary. If multiple aliases are found the one marked as primary will be used by default.
- Adds ability to define multiple InjectScalar and InjectEnv attributes and to specify a profile, or profiles, that each annotation belongs to.
### Changed

- Made several changes to the internal parsing of the codebase. This shouldn't have any changes in the public-facing API.
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

## [0.2.0] - 2022-03-22

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

## [0.1.0] - 2021-04-06

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


