<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity;

// JSON-API.
use GoIntegro\Hateoas\JsonApi\ResourceEntityInterface;

interface MutatorInterface
{
    /**
     * @param ResourceEntityInterface $entity
     * @param array $fields
     * @param array $relationships
     * @param array $metadata
     * @return \GoIntegro\Hateoas\JsonApi\ResourceEntityInterface
     * @throws \GoIntegro\Hateoas\Entity\Validation\EntityConflictExceptionInterface
     * @throws \GoIntegro\Hateoas\Entity\Validation\ValidationExceptionInterface
     */
    public function update(
        ResourceEntityInterface $entity,
        array $data,
        array $relationships = [],
        array $metadata = []
    );
}
