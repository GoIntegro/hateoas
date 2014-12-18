<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Util;

// Reflexión.
use ReflectionMethod;

class Reflection
{
    public static function isMethodGetter(ReflectionMethod $method)
    {
        return self::isMethod($method, 'get');
    }

    public static function isMethodInjector(ReflectionMethod $method)
    {
        return self::isMethod($method, 'inject');
    }

    private static function isMethod(ReflectionMethod $method, $prefix)
    {
        return $method->isPublic()
            && !$method->isStatic()
            && 0 === $method->getNumberOfRequiredParameters()
            && $prefix === substr($method->getShortName(), 0, strlen($prefix));
    }

    /**
     * @param \ReflectionClass $class
     * @param array $params
     * @return ResourceEntityInterface
     */
    private static function instance(
        \ReflectionClass $class, array $params = []
    )
    {
        $args = [];

        foreach ($class->getConstructor()->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $params)) {
                $args[$parameter->getPosition()] = $params[$name];
            }
        }

        ksort($args); // Indexed using the parameter position.

        return $class->newInstanceArgs($args);
    }
}
