<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity;

interface BuilderInterface extends GenericBuilderInterface
{
    /**
     * @param array $fields
     * @param array $relationships
     * @param array $metadata
     * @return \GoIntegro\Hateoas\JsonApi\ResourceEntityInterface
     * @throws \GoIntegro\Hateoas\Entity\Validation\EntityConflictExceptionInterface
     * @throws \GoIntegro\Hateoas\Entity\Validation\ValidationExceptionInterface
     */
    public function create(
        array $fields,
        array $relationships = [],
        array $metadata = []
    );
}
