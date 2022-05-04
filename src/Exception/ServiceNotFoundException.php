<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends Exception implements NotFoundExceptionInterface {

}