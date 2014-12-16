<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity;

// Inflection.
use Doctrine\Common\Util\Inflector;
// JSON-API.
use GoIntegro\Hateoas\JsonApi\ResourceEntityInterface;

trait AltersEntities
{
    /**
     * @param \ReflectionClass $class
     * @param ResourceEntityInterface $entity
     * @param array $fields
     * @return self
     */
    protected function setFields(
        \ReflectionClass $class,
        ResourceEntityInterface $entity,
        array $fields
    )
    {
        foreach ($fields as $field => $value) {
            $method = self::SET . Inflector::camelize($field);

            if ($class->hasMethod($method)) $entity->$method($value);
        }

        return $this;
    }

    /**
     * @param \ReflectionClass $class
     * @param ResourceEntityInterface $entity
     * @param array $fields
     * @return self
     */
    protected function setRelationships(
        \ReflectionClass $class,
        ResourceEntityInterface $entity,
        array $fields
    )
    {
        foreach ($relationships as $relationship => $value) {
            $camelCased = Inflector::camelize($relationship);

            if (is_array($value)) {
                $getter = self::GET . $camelCased;
                $singular = Inflector::singularize($camelCased);
                $remover = self::REMOVE . $singular;
                $adder = self::ADD . $singular;

                // @todo Improve algorithm.
                foreach ($entity->$getter() as $item) $entity->$remover($item);

                foreach ($value as $item) $entity->$adder($item);
            } else {
                $method = self::SET . $camelCased;

                if ($class->hasMethod($method)) $entity->$method($value);
            }
        }

        return $this;
    }
}
