<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace JsonApi\Request;

// Mocks.
use Codeception\Util\Stub;
// Request.
use GoIntegro\Hateoas\JsonApi\Request\PaginationParser,
    GoIntegro\Hateoas\JsonApi\Request\Parser;

class PaginationParserTest extends \PHPUnit_Framework_TestCase
{
    const RESOURCE_CLASS = 'HateoasInc\\Bundle\\ExampleBundle\\Entity\\User';

    /**
     * @var array
     */
    private static $config = [
        'magic_services' => [
            [
                'resource_type' => 'users',
                'entity_class' => 'Entity\User'
            ]
        ]
    ];

    public function testParsingARequestWithPagination()
    {
        // Given...
        $has = function($param) { return in_array($param, ['page', 'size']); };
        $get = function($param) { return 'page' == $param ? 2 : 4; };
        $queryOverrides = ['has' => $has, 'get' => $get];
        $request = self::createRequest('/api/v1/users', $queryOverrides);
        $params = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\Params',
            ['primaryClass' => self::RESOURCE_CLASS]
        );
        $parser = new PaginationParser(
            self::createMetadataMiner(),
            self::$config
        );
        // When...
        $pagination = $parser->parse($request, $params);
        // Then...
        $this->assertNotNull($pagination);
        $this->assertNull($pagination->total);
        $this->assertEquals(2, $pagination->page);
        $this->assertEquals(4, $pagination->size);
        $this->assertEquals(4, $pagination->offset);
        $this->assertInstanceOf(
            'GoIntegro\Hateoas\Http\Url',
            $pagination->paginationlessUrl
        );
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
                'query' => $query,
                'getPathInfo' => $pathInfo,
                'getMethod' => $method,
                'getContent' => $body
            ]
        );

        return $request;
    }

    private static function createMetadataMiner()
    {
        return Stub::makeEmpty(
            'GoIntegro\Hateoas\Metadata\Resource\MetadataMinerInterface'
        );
    }
}
