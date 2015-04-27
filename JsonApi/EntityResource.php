<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi;

// Interfaces.
use GoIntegro\Hateoas\JsonApi\ResourceEntityInterface;
// Metadata.
use GoIntegro\Hateoas\Metadata\Resource\ResourceMetadata;
// Datos.
use GoIntegro\Hateoas\Util\Inflector;
// Colecciones.
use Doctrine\Common\Collections\Collection as CollectionInterface,
    Doctrine\Common\Collections\ArrayCollection;

class EntityResource implements DocumentResource
{
    const DEFAULT_PAGE_SIZE = 10;

    const ERROR_NOT_RESOURCE_ENTITY = "The relationship \"%s\" contains a \"%s\", an entity implementing GoIntegro\\Hateoas\\JsonApi\\ResourceEntityInterface was expected.",
        ERROR_TO_MANY_RELATION_NULL = "The to-many relationship \"%s\" returns neither an array nor a collection. The corresponding property needs to be initialized with either during the construction of the entity.",
        ERROR_NOT_ENTITY_COLLECTION = "The relationship \"%s\" contains a \"%s\", a collection of entities that implements Doctrine\\Common\\Collections\\Collection was expected.",
        ERROR_INACCESSIBLE_MEMBER = "For some reason the field is known or is a relationship, but its value can't be accessed.",
        ERROR_UNCALLABLE_GETTER = "The \"%s\" getter cannot be invoked.";

    /**
     * @var ResourceEntityInterface
     */
    public $entity;
    /**
     * @var string
     * @todo ¿Tiene sentido esta copia sólo para que sea string?
     */
    public $id;
    /**
     * @var ResourceMetadata
     */
    private $metadata;

    /**
     * @var array
     */
    public static $fieldWhitelist = [];
    /**
     * @var array
     */
    public static $fieldBlacklist = [];
    /**
     * @var array
     */
    public static $relationshipBlacklist = [];
    /**
     * Para quitar relaciones del campo "links" del resource object.
     * Es práctico cuando tenemos relaciones "to-many" demasiado voluminosas,
     * cuya lista completa de Ids preferimos no incluir.
     * Se detectan automáticamente cuando la relación está mapeada pero no
     * existe un getter correspondiente.
     * @var array
     * @see http://jsonapi.org/format/#document-structure-url-templates
     */
    public static $linkOnlyRelationships = [];
    /**
     * @var string
     */
    public static $pageSize = self::DEFAULT_PAGE_SIZE;

    public function __construct(
        ResourceEntityInterface $entity,
        ResourceMetadata $metadata
    )
    {
        $this->entity = $entity;
        $this->metadata = $metadata;
        $this->id = (string) $entity->getId();
    }

    /**
     * @see DocumentResource::getMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param string $field
     */
    public function isFieldBlacklisted($field)
    {
        return in_array($field, static::$fieldBlacklist);
    }

    /**
     * @param string $field
     */
    public function isRelationshipBlacklisted($field)
    {
        return in_array($field, static::$relationshipBlacklist);
    }

    /**
     * Obtiene el Id de una entidad.
     * @return string
     * @todo Evitar hacer type-casting hasta la serialización.
     */
    public static function getStringId(ResourceEntityInterface $entity = NULL)
    {
        return is_null($entity)
            ? NULL
            : (string) $entity->getId();
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function callGetter($field)
    {
        $camelized = Inflector::camelize($field, TRUE);
        $getter = 'get' . $camelized;
        $injector = 'inject' . $camelized;

        if (is_callable([$this, $injector])) {
            return $this->$injector();
        } elseif (
            $this->metadata->isField($field)
            || $this->metadata->isRelationship($field)
        ) {
            if (is_callable([$this->entity, $getter])) {
                return $this->entity->$getter();
            }

            throw new \LogicException(self::ERROR_INACCESSIBLE_MEMBER);
        }

        throw new \Exception(sprintf(self::ERROR_UNCALLABLE_GETTER, $getter));
    }

    /**
     * Valida el contenido de una relación "a uno".
     * @param mixed $relation
     * @param string $relationship
     * @throws \Exception
     * @todo Mover.
     */
    public static function validateToOneRelation($relation, $relationship)
    {
        if (
            !is_null($relation)
            && !$relation instanceof ResourceEntityInterface
        ) {
            $type = is_object($relation)
                ? get_class($relation) : gettype($relation);
            throw new \Exception(sprintf(
                self::ERROR_NOT_RESOURCE_ENTITY, $relationship, $type
            ));
        }
    }

    /**
     * Normaliza una colección de una relación "a muchos".
     * @param mixed $relation
     * @param string $relationship
     * @return ArrayCollection
     * @throws \Exception
     * @todo Mover.
     */
    public static function normalizeToManyRelation($relation, $relationship)
    {
        if (is_array($relation)) {
            $relation = new ArrayCollection($relation);
        } elseif (is_null($relation)) {
            throw new \Exception(sprintf(
                self::ERROR_TO_MANY_RELATION_NULL, $relationship
            ));
        } elseif (!$relation instanceof CollectionInterface) {
            $type = is_object($relation)
                ? get_class($relation)
                : gettype($relation);

            throw new \Exception(sprintf(
                self::ERROR_NOT_ENTITY_COLLECTION, $relationship, $type
            ));
        }

        return $relation;
    }
}
