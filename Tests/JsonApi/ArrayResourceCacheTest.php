<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace JsonApi;

// Mocks.
use Codeception\Util\Stub;
// Recursos.
use GoIntegro\Hateoas\JsonApi\ArrayResourceCache,
    GoIntegro\Hateoas\JsonApi\ResourceEntityInterface;

class ArrayResourceCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testAddingAResource()
    {
        /* Given... (Fixture) */
        $entity = Stub::makeEmpty('GoIntegro\Hateoas\JsonApi\ResourceEntityInterface');
        $entityResource = Stub::makeEmpty(
            'GoIntegro\Hateoas\JsonApi\EntityResource',
            ['entity' => $entity]
        );
        $classReflection = Stub::makeEmpty(
            'ReflectionClass',
            ['getName' => function() { return 'GoIntegro\Entity\User'; }]
        );
        $metadataCache = Stub::makeEmpty(
            'GoIntegro\Hateoas\Metadata\Entity\MetadataCache',
            [
                'getReflection'
                    => function($object) use ($entity, $classReflection) {
                        if ($object instanceof ResourceEntityInterface) {
                            return $classReflection;
                        }
                    }
            ]
        );
        $metadataMiner = Stub::makeEmpty(
            'GoIntegro\Hateoas\Metadata\Resource\MetadataMinerInterface'
        );
        $serviceContainer = Stub::makeEmpty(
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );
        $resourceCache = new ArrayResourceCache(
            $metadataCache, $metadataMiner, $serviceContainer
        );
        /* When... (Action) */
        $sameCache = $resourceCache->addResource($entityResource);
        $sameResource = $resourceCache->getResourceForEntity($entity);
        /* Then... (Assertions) */
        $this->assertSame($resourceCache, $sameCache);
        $this->assertSame($entityResource, $sameResource);
    }
}
