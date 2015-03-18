<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Request;

// Mocks.
use Codeception\Util\Stub;

use Symfony\Component\HttpFoundation\Request;

class SortingParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParsingARequestWithSorting()
    {
        /* Given... (Fixture) */
        $mm = self::createMetadataMiner();
        $has = function($param) { return 'sort' == $param; };
        $get = function() { return 'surname,name,-registered-date'; };
        $queryOverrides = ['has' => $has, 'get' => $get];
        $request = self::createRequest('/users', $queryOverrides);
        $params = self::createParams();
        $parser = new SortingParser($mm);
        /* When... (Action) */
        $actual = $parser->parse($request, $params);
        /* Then... (Assertions) */
        $expected = ['field' => [
            'users' => [
                'surname' => 'ASC',
                'name' => 'ASC',
                'registeredDate' => 'DESC'
            ]
        ]];
        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $pathInfo
     * @param array $queryOverrides
     * @param string $method
     * @param string $body
     * @return Request
     */
    private static function createRequest(
        $pathInfo,
        array $queryOverrides,
        $method = Parser::HTTP_GET,
        $body = NULL
    )
    {
        $defaultOverrides = [
            'getIterator' => function() { return new \ArrayIterator([]); }
        ];
        $queryOverrides = array_merge($defaultOverrides, $queryOverrides);
        $query = Stub::makeEmpty(
            'Symfony\Component\HttpFoundation\ParameterBag',
            $queryOverrides
        );
        $request = Stub::makeEmpty(
            'Symfony\Component\HttpFoundation\Request',
            [
                'request' => new \stdClass,
                'attributes' => new \stdClass,
                'cookies' => new \stdClass,
                'files' => new \stdClass,
                'server' => new \stdClass,
                'headers' => new \stdClass,
                'query' => $query,
                'getPathInfo' => $pathInfo,
                'getMethod' => $method,
                'getContent' => $body
            ]
        );

        return $request;
    }

    /**
     * @return Params
     */
    private static function createParams()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\Params',
            [
                'primaryClass' => 'HateoasInc\\Bundle\\ExampleBundle\\Entity\\User',
                'primaryType' => 'users'
            ]
        );
    }

    /**
     * @return \GoIntegro\Hateoas\Metadata\Resource\ResourceMetadata
     */
    private static function createMetadataMiner()
    {
        $metadata = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Metadata\\Resource\\ResourceMetadata',
            ['isField' => TRUE]
        );

        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Metadata\\Resource\\MetadataMinerInterface',
            ['mine' => $metadata]
        );
    }
}
