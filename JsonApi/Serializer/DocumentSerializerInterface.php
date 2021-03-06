<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Serializer;

// Recursos REST.
use GoIntegro\Hateoas\JsonApi\Document;

interface DocumentSerializerInterface
{
    /**
     * Retorna una estructura de datos serializable.
     * @param Document $document
     * @return array
     */
    public function serialize(Document $document);
}
