<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * Define a scalar value that should be injected into a specific method's parameter.
 */
interface InjectScalarDefinition {

    /**
     * The Service that requires a scalar value to be injected into one of its methods.
     *
     * @return ServiceDefinition
     */
    public function getService() : ServiceDefinition;

    /**
     * The name of the method on getService() that needs to have a scalar value injected into it.
     *
     * @return string
     */
    public function getMethod() : string;

    /**
     * The name of the parameter on getMethod() that needs to have a scalar value injected into it.
     *
     * @return string
     */
    public function getParamName() : string;

    /**
     * An enum declaring what scalar type the given parameter is marked as.
     *
     * @return ScalarType
     */
    public function getParamType() : ScalarType;

    /**
     * The value that should be injected into the scalar.
     *
     * @return string|int|float|bool|array
     */
    public function getValue() : string|int|float|bool|array;

}