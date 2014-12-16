<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity;

// JSON-API.
use GoIntegro\Hateoas\JsonApi\ResourceEntityInterface;

interface DeleterInterface
{
    /**
     * @param ResourceEntityInterface $entity
     */
    public function delete(ResourceEntityInterface $entity);
}
