# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `Service` Attribute to define an interface or class as a shared service or alias.
- `ServicePrepare` Attribute to define a method to be invoked after object creation.
- `DefineService` Attribute to define a parameter on a `Service` constructor or `ServicePrepare` method to use the
  provided type to resolve the dependency.
- `DefineScalar` Attribute to define a hardcoded, non-object value on a `Service` constructor or `ServicePrepare`
  method.
- `DefineScalarFromEnv` Attribute to define a hardcoded, non-object value to be determined by an environment variable
  on a `Service` constructor or `ServicePrepare` method.
- Compiler to turn annotated PHP source code in a directory into an `InjectorDefinition` which defines how to construct
  the corresponding `Injector`.
- A factory to take an `InjectorDefinition` and turn it into an `Injector`.

## [0.1.0] - 2021-04-??
