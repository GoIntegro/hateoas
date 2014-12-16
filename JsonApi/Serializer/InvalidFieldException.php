<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 * @author Tito Miguel Costa <titomiguelcosta@gmail.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Serializer;

// JSON-API.
use GoIntegro\Hateoas\JsonApi;

class InvalidFieldException extends JsonApi\Exception\BadRequestException
{
    /**
     * @var string
     */
    private $field;
    /**
     * @var string
     */
    private $resource;

    /**
     * @param string $field
     * @param JsonApi\EntityResource $resource
     * @param string $message
     * @param integer $code
     * @param \Exception $exception
     */
    public function __construct(
        $field,
        $resource,
        $message = "",
        $code = 0,
        \Exception $previous = NULL
    )
    {
        parent::__construct($message, $code, $previous);

        $this->field = $field;
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return JsonApi\EntityResource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
