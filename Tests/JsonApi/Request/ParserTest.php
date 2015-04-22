<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Request;

// Mocks.
use Codeception\Util\Stub;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    const API_BASE_URL = '/api/v1';

    public function testParsingASimpleRequest()
    {
        // Given...
        $request = self::createRequest(
            '/api/v1/users/1/linked/groups',
            ['has' => function() { return FALSE; }]
        );
        $parser = new Parser(
            self::createResourceEntityMapper(),
            self::createDocNavigator(),
            self::createFilterParser(),
            self::createSortingParser(),
            self::createPaginationParser(),
            self::createBodyParser(),
            self::createActionParser(),
            self::createParamEntityFinder(),
            self::createLocaleNegotiator(),
            self::createMetadataMiner(),
            self::API_BASE_URL
        );
        // When...
        $params = $parser->parse($request);
        // Then...
        $this->assertEquals('users', $params->primaryType);
        $this->assertContains('1', $params->primaryIds);
        $this->assertEquals('groups', $params->relationship);
    }

    public function testParsingARequestWithSparseFields()
    {
        // Given...
        $has = function($param) { return 'fields' == $param; };
        $get = function() { return 'name,surname,email'; };
        $queryOverrides = ['has' => $has, 'get' => $get];
        $request = self::createRequest('/api/v1/users', $queryOverrides);
        $parser = new Parser(
            self::createResourceEntityMapper(),
            self::createDocNavigator(),
            self::createFilterParser(),
            self::createSortingParser(),
            self::createPaginationParser(),
            self::createBodyParser(),
            self::createActionParser(),
            self::createParamEntityFinder(),
            self::createLocaleNegotiator(),
            self::createMetadataMiner(),
            self::API_BASE_URL
        );
        // When...
        $params = $parser->parse($request);
        // Then...
        $this->assertEquals('users', $params->primaryType);
        $this->assertEmpty($params->primaryIds);
        $this->assertNull($params->relationship);
        $this->assertEquals(
            ['users' => ['name', 'surname', 'email']],
            $params->sparseFields
        );
    }

    public function testParsingARequestWithInclude()
    {
        // Given...
        $has = function($param) { return 'include' == $param; };
        $get = function() { return 'platform.account,workspaces-joined'; };
        $queryOverrides = ['has' => $has, 'get' => $get];
        $request = self::createRequest('/api/v1/users', $queryOverrides);
        $parser = new Parser(
            self::createResourceEntityMapper(),
            self::createDocNavigator(),
            self::createFilterParser(),
            self::createSortingParser(),
            self::createPaginationParser(),
            self::createBodyParser(),
            self::createActionParser(),
            self::createParamEntityFinder(),
            self::createLocaleNegotiator(),
            self::createMetadataMiner(),
            self::API_BASE_URL
        );
        // When...
        $params = $parser->parse($request);
        // Then...
        $this->assertEquals('users', $params->primaryType);
        $this->assertEmpty($params->primaryIds);
        $this->assertNull($params->relationship);
        $this->assertEquals(
            [['platform', 'account'], ['workspaces-joined']],
            $params->include
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
     * @return \GoIntegro\Hateoas\JsonApi\Request\FilterParser
     */
    private static function createFilterParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\FilterParser'
        );
    }

    /**
     * @return \GoIntegro\Hateoas\JsonApi\Request\SortingParser
     */
    private static function createSortingParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\SortingParser'
        );
    }

    /**
     * @return \GoIntegro\Hateoas\JsonApi\Request\PaginationParser
     */
    private static function createPaginationParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\PaginationParser'
        );
    }

    /**
     * @return \GoIntegro\Hateoas\JsonApi\Request\BodyParser
     */
    private static function createBodyParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\BodyParser'
        );
    }

    /**
     * @return \GoIntegro\Hateoas\JsonApi\Request\ActionParser
     */
    private static function createActionParser()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\ActionParser'
        );
    }

    /**
     * @return \GoIntegro\Hateoas\JsonApi\Request\ParamEntityFinder
     */
    private static function createParamEntityFinder()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\ParamEntityFinder'
        );
    }

    /**
     * @return \GoIntegro\Hateoas\JsonApi\Request\LocaleNegotiator
     */
    private static function createLocaleNegotiator()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\LocaleNegotiator'
        );
    }

    /**
     * @return \GoIntegro\Raml\DocNavigator
     */
    private static function createDocNavigator()
    {
        $ramlDoc = Stub::makeEmpty(
            'GoIntegro\\Raml\\RamlDoc',
            ['isDefined' => TRUE]
        );

        return Stub::makeEmpty(
            'GoIntegro\\Raml\\DocNavigator',
            ['getDoc' => $ramlDoc]
        );
    }

    /**
     * @return \GoIntegro\Raml\DocNavigator
     */
    private static function createResourceEntityMapper()
    {
        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Config\\ResourceEntityMapper',
            ['map' => ['users' => 'Entity\User'],
            'getResourcesConfig' => new \GoIntegro\Hateoas\Config\Resources()]
        );
    }

    /**
     * @return \GoIntegro\Hateoas\Metadata\Resource\MetadataMinerInterface
     */
    private static function createMetadataMiner()
    {
        $metadata = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Metadata\\Resource\\ResourceMetadata',
            ['isRelationship' => TRUE, 'isLinkOnlyRelationship' => FALSE]
        );

        return Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Metadata\\Resource\\MetadataMinerInterface',
            ['mine' => $metadata]
        );
    }
}
