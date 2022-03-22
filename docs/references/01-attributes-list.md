# AnnotatedContainer Attributes

The following Attributes are made available through this library. All Attributes listed are under the namespace
`Cspray\AnnotatedContainer\Attribute`.

| Attribute Name             | Target | Description                                                                                                                                                                                                                        |
|----------------------------| --- |------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Service`                  |`Attribute::TARGET_CLASS`| Describes an interface, abstract class, or concrete class as being a service. Will share and alias the types into the Injector based on what's annotated.                                                                          |
| `ServiceProfile` | `Attribute::TARGET_CLASS` | Defines an explicit Profile that a Service is associated with; if not present the Profile will be implicitly set to 'default'                                                                                                      |
| `ServicePrepare`           |`Attribute::TARGET_METHOD`| Describes a method, on an interface or class, that should be invoked when that type is created.                                                                                                                                    |
| `InjectScalar`             |`Attribute::TARGET_PARAMETER`| Defines a scalar parameter on a Service constructor or ServicePrepare method. The value will be the exact value passed to this Attribute.                                                                                          |
| `InjectEnv`                |`Attribute::TARGET_PARAMETER`| Defines a scalar parameter on a Service constructor or ServicePrepare method. The value will be taken from an environment variable matching this Attribute's value. The value is gathered at runtime when constructing the object. |
| `InjectService`            |`Attribute::TARGET_PARAMETER`| Defines a Service parameter on a Service constructor or ServicePrepare method.                                                                                                                                                     |
| `ServiceDelegate`          |`Attribute::TARGET_METHOD`| Defines a method that will be used to generate a defined type.                                                                                                                                                                     |

