<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Security;

// Exception.
use GoIntegro\Hateoas\JsonApi\Exception\LogicException;

/**
 * When access controls are not being enforced properly through filters.
 */
interface VoterFilterException extends LogicException
{
}
