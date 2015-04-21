<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Config;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Metadata.
use GoIntegro\Hateoas\Metadata\Entity\MetadataCache;
// RAML.
use GoIntegro\Raml\DocNavigator;
// Utils.
use GoIntegro\Hateoas\Util;

//Resources Config
use GoIntegro\Hateoas\Config\ResourcesConfig as ResourcesConfigInterface;

class ResourceEntityMapper
{
    const RESOURCE_ENTITY_INTERFACE = 'GoIntegro\\Hateoas\\JsonApi\\ResourceEntityInterface';

    const ERROR_MISSING_ENTITY = "No entity matches the resource \"%s\".",
        ERROR_ENTITIES_PER_RESOURCE = "The resource \"%s\" listed in the RAML doc matches the following entity class names: \"%s\". If you want to keep the short-names of these resource entities you need to map all but one of them to other resource types in the bundle configuration.";

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var MetadataCache
     */
    private $metadataCache;
    /**
     * @var DocNavigator
     */
    private $docNavigator;
    /**
     * @var ResourceEntityMapCache
     */
    private $mapCache;
    /**
     * @var array
     */
    private $indexedClassNames;
    
    /**
     * @var ResourcesConfigInterface
     */
    private $resourcesConfig;

    /**
     * @param EntityManagerInterface $em
     * @param MetadataCache $metadataCache
     * @param DocNavigator $docNavigator
     * @param ResourceEntityMapCache $mapCache
     */
    public function __construct(
        EntityManagerInterface $em,
        MetadataCache $metadataCache,
        DocNavigator $docNavigator,
        ResourceEntityMapCache $mapCache,
        ResourcesConfigInterface $resourcesConfig
    )
    {
        $this->em = $em;
        $this->metadataCache = $metadataCache;
        $this->docNavigator = $docNavigator;
        $this->mapCache = $mapCache;
	$this->resourcesConfig = $resourcesConfig;
        $this->indexedClassNames = $this->indexEntityClassNames();
    }

    /**
     * @return array
     */
    private function indexEntityClassNames()
    {
        $indexedClassNames = [];
        $entityClassNames = $this->em->getConfiguration()
            ->getMetadataDriverImpl()
            ->getAllClassNames();

        foreach ($entityClassNames as $name) {
            // @todo Support subtypes.
            $resourceType = Util\Inflector::typify($name);
            $indexedClassNames[$resourceType][] = $name;
        }
        
        //Config class override
        foreach ($this->getResourcesConfig()->getAll() as $resource) {
            if (!empty($resource->type) && !empty($resource->class)) {
                $indexedClassNames[$resource->type] = array($resource->class);
            }
        }
        return $indexedClassNames;
    }

    /**
     * @return array
     * @throws ResourceEntityMappingException
     * @todo The configuration doesn't actually allow overridding resource type to entity class mappings as the error message suggests. Oops.
     */
    public function map()
    {
        if ($this->mapCache->isFresh()) {
            return $this->mapCache->read();
        }

        $map = [];

        foreach ($this->docNavigator->getDoc()->getResources() as $type) {
            $resourceClasses = $this->getResourceClasses($type);

            if (1 < count($resourceClasses)) {
                $message = sprintf(
                    self::ERROR_ENTITIES_PER_RESOURCE,
                    $type,
                    implode(', ', $resourceClasses)
                );
                throw new ResourceEntityMappingException($message);
            }

            $map[$type] = reset($resourceClasses);
        }

        $this->mapCache->keep($map);

        return $map;
    }

    /**
     * @param string $type
     * @return array
     * @throws ResourceEntityMappingException
     */
    private function getResourceClasses($type)
    {
        if (empty($this->indexedClassNames[$type])) {
            $message = sprintf(self::ERROR_MISSING_ENTITY, $type);
            throw new ResourceEntityMappingException($message);
        }

        $resourceClasses = [];

        foreach ($this->indexedClassNames[$type] as $className) {
            $class = $this->metadataCache->getReflection($className);

            if ($class->implementsInterface(self::RESOURCE_ENTITY_INTERFACE)) {
                $resourceClasses[] = $className;
            }
        }

        return $resourceClasses;
    }
    
    /**
     * 
     * @return ResourcesConfigInterface
     */
    public function getResourcesConfig()
    {
        return $this->resourcesConfig;
    }
}

