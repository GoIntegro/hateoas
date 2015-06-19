<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Serializer;

// Recursos REST.
use GoIntegro\Hateoas\JsonApi\ResourceEntityInterface,
    GoIntegro\Hateoas\JsonApi\Document,
    GoIntegro\Hateoas\JsonApi\EntityResource,
    GoIntegro\Hateoas\JsonApi\ResourceCollectionInterface,
    GoIntegro\Hateoas\JsonApi\ResourceCollection,
    GoIntegro\Hateoas\JsonApi\DocumentResource;
// Metadata.
use GoIntegro\Hateoas\Metadata\Resource\ResourceRelationship;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

class LinkedResourcesSerializer implements DocumentSerializerInterface
{
    const RECURSION_DEPTH_LIMIT = 3;

    const ACCESS_VIEW = 'view';

    const ERROR_RECURSION_DEPTH = "The recursion level is too deep.",
        ERROR_INHERITANCE_MAPPING = "The mapping inheritance in the ORM for the relationship \"%s\" is not being properly handled.",
        ERROR_LINK_ONLY_RELATIONSHIP = "The relationship \"%s\" cannot be included, possibly because of its size. You must fetch this resource by getting %s.",
        ERROR_UNKOWN_RELATIONSHIP = "The relationship \"%s\" does not exist.";

    private $document;
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function serialize(Document $document)
    {
        // @todo Pass as argument.
        $this->document = $document;

        $resourcesSerialization = new LinkedResourcesSerialization;
        $relationOfRelation = [];
        $this->processLinkedResources(
            $this->document->resources,
            $this->document->include,
            $resourcesSerialization
        );

        return $resourcesSerialization->getLinkedResources();
    }

    /**
     * @param ResourceCollectionInterface $resources
     * @param array $include
     * @param LinkedResourcesSerialization $resourcesSerialization
     * @param array &$relationOfRelation
     * @todo Este método merece un refactor.
     */
    private function processLinkedResources(
        ResourceCollectionInterface $resources,
        array $include,
        LinkedResourcesSerialization $resourcesSerialization,
        $depth = 0
    )
    {
        if (self::RECURSION_DEPTH_LIMIT <= $depth) {
            throw new InclusionDepthLimitException(
                self::ERROR_RECURSION_DEPTH
            );
        }

        foreach ($include as $relationships) {
            $metadata = $resources->getMetadata();
            $relationshipName = $relationships[$depth];
            $linkedResources = [];

            if ($metadata->isToOneRelationship($relationshipName)) {
                $this->processToOneRelationship(
                    $resources,
                    $relationshipName,
                    $linkedResources,
                    $resourcesSerialization
                );
            } elseif ($metadata->isToManyRelationship($relationshipName)) {
                $this->processToManyRelationship(
                    $resources,
                    $relationshipName,
                    $linkedResources,
                    $resourcesSerialization
                );
            } elseif ($metadata->isLinkOnlyRelationship($relationshipName)) {
                $urlTemplate = $resources->getMetadata()
                    ->relationships
                    ->linkOnly[$relationshipName]
                    ->byPrimaryUrl;
                $message = sprintf(
                    self::ERROR_LINK_ONLY_RELATIONSHIP,
                    $relationshipName,
                    $urlTemplate
                );
                throw new InvalidRelationshipException($message);
            } else {
                $message = sprintf(
                    self::ERROR_UNKOWN_RELATIONSHIP, $relationshipName
                );
                throw new InvalidRelationshipException($message);
            }

            if (
                isset($relationships[$depth + 1]) && !empty($linkedResources)
            ) {
                $this->processLinkedResources(
                    ResourceCollection::buildFromArray($linkedResources),
                    [$relationships],
                    $resourcesSerialization,
                    $depth + 1
                );
            }
        }
    }

    /**
     * @param ResourceCollectionInterface $resources
     * @param $relationshipName
     * @param LinkedResourcesSerialization $resourcesSerialization
     * @param array &$linkedResources
     */
    private function processToOneRelationship(
        ResourceCollectionInterface $resources,
        $relationshipName,
        array &$linkedResources,
        LinkedResourcesSerialization $resourcesSerialization
    )
    {
        foreach ($resources as $resource) {
            if ($resource->isRelationshipBlacklisted(
                $relationshipName
            )) {
                $message = sprintf(
                    self::ERROR_UNKOWN_RELATIONSHIP, $relationshipName
                );
                throw new InvalidRelationshipException($message);
            }

            $relationship = $resource->getMetadata()
                ->relationships
                ->toOne[$relationshipName];
            // @todo Refactorizar; la siguiente línea es un hack.
            $entity
                = $resource->callGetter($relationshipName);
            EntityResource::validateToOneRelation(
                $entity, $relationshipName
            );

            if (is_null($entity)) {
                continue;
            }

            $this->addLinkedResource(
                $relationship, $entity, $resourcesSerialization
            );

            $linkedResource
                = $resourcesSerialization->getLinkedResource(
                    $relationship->type,
                    EntityResource::getStringId($entity)
                );

            if (!empty($linkedResource)) {
                $linkedResources[] = $linkedResource;
            }
        }
    }

    /**
     * @param ResourceCollectionInterface $resources
     * @param $relationshipName
     * @param LinkedResourcesSerialization $resourcesSerialization
     * @param array &$linkedResources
     */
    private function processToManyRelationship(
        ResourceCollectionInterface $resources,
        $relationshipName,
        array &$linkedResources,
        LinkedResourcesSerialization $resourcesSerialization
    )
    {
        foreach ($resources as $resource) {
            if ($resource->isRelationshipBlacklisted(
                $relationshipName
            )) {
                $message = sprintf(
                    self::ERROR_UNKOWN_RELATIONSHIP, $relationshipName
                );
                throw new InvalidRelationshipException($message);
            }

            $relationship = $resource->getMetadata()
                ->relationships
                ->toMany[$relationshipName];
            // @todo Refactorizar; la siguiente línea es un hack.
            $collection
                = $resource->callGetter($relationshipName);
            $collection
                = EntityResource::normalizeToManyRelation(
                    $collection, $relationshipName
                );

            // @todo Mover.
            foreach ($collection as $entity) {
                $this->addLinkedResource(
                    $relationship, $entity, $resourcesSerialization
                );

                $linkedResource
                    = $resourcesSerialization->getLinkedResource(
                        $relationship->type,
                        EntityResource::getStringId($entity)
                    );

                if (!empty($linkedResource)) {
                    $linkedResources[] = $linkedResource;
                } else {
                    throw new SerializationException(sprintf(
                        self::ERROR_INHERITANCE_MAPPING, $relationship->type
                    ));
                }
            }
        }
    }

    /**
     * @param array $relationship
     * @param ResourceEntityInterface $entity
     * @param LinkedResourcesSerialization $resourcesSerialization
     */
    protected function addLinkedResource(
        ResourceRelationship $relationship,
        ResourceEntityInterface $entity,
        LinkedResourcesSerialization $resourcesSerialization
    )
    {
        if (
            !$this->securityContext->isGranted(static::ACCESS_VIEW, $entity)
            || $this->document->linkedResources->hasResource(
                $relationship->type, EntityResource::getStringId($entity)
            )
        ) {
            return;
        }

        $resource = $this->document
            ->linkedResources
            ->addResourceForEntity($entity);
        $resourceObject
            = $this->serializeResourceObject($resource);
        $resourcesSerialization->addLinkedResource(
            $resource,
            $resourceObject
        );
    }

    /**
     * @param DocumentResource $resource
     * @return array
     */
    protected function serializeResourceObject(EntityResource $resource)
    {
        $metadata = $resource->getMetadata();
        $fields = isset($this->document->sparseFields[$metadata->type])
            ? $this->document->sparseFields[$metadata->type]
            : [];

        $serializer = new ResourceObjectSerializer(
            $resource, $this->securityContext, $fields
        );

        return $serializer->serialize();
    }
}
