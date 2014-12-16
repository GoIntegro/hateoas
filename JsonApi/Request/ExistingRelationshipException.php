<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Request;

// JSON-API
use GoIntegro\Hateoas\JsonApi\Exception\ConflictException;

class ExistingRelationshipException extends ConflictException
{
}
