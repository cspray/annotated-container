# AnnotatedContainer Attributes

The following Attributes are made available through this library. All Attributes listed are under the namespace
`Cspray\AnnotatedContainer\Attribute`.

| Attribute Name       | Target                                                     | Description                                                                                                                                                                                                        |
|----------------------|------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Service`            | `Attribute::TARGET_CLASS`                                  | Describes an interface, abstract class, or concrete class as being a service. Will share and alias the types into the Injector based on what's annotated.                                                          |
| `ServicePrepare`     | `Attribute::TARGET_METHOD`                                 | Describes a method, on an interface or class, that should be invoked when that type is created.                                                                                                                    |
| `ServiceDelegate`    | `Attribute::TARGET_METHOD`                                 | Defines a method that will be used to generate a defined type.                                                                                                                                                     |
| `Inject`             | `Attribute::TARGET_PARAMETER`, `Attribute::TARGET_PROPERTY` | Defines a value that should be injected, can handle either scalar or service values based on the type-hint of the parameter. Inject Attributes on properties are only recognized if on `[#Configuration]` objects. |
| `Configuration`      | `Attribute::TARGET_CLASS` | Defines an instance as being a Configuration object. Properties can have values injected into them.                                                                                                                |                                                                                                                         
