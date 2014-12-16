<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity\Validation;

// Validator.
use Symfony\Component\Validator\Exception\ValidatorException;

class ValidationException
    extends ValidatorException
    implements ValidationExceptionInterface
{
}
