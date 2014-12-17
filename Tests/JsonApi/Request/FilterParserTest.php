<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Request;

// Mocks.
use Codeception\Util\Stub;

class FilterParserTest extends \PHPUnit_Framework_TestCase
{
    const API_BASE_URL = '/api/v1',
        HTTP_PUT_BODY = <<<'JSON'
{
    "users": {
        "id": "7",
        "name": "John",
        "surname": "Connor"
    }
}
JSON;

    public function testParsingARequestWithFilters()
    {
        /* Given... (Fixture) */
        $mm = self::createMetadataMiner();
        $request = self::createRequest();
        $params = self::createParams();
        $parser = new FilterParser($mm);
        /* When... (Action) */
        $actual = $parser->parse($request, $params);
        /* Then... (Assertions) */
        $expected = ['field' => ['name' => ['John']]];
        $this->assertSame($expected, $actual);
    }

    /**
     * @return Request
     */
    private static function createRequest()
    {
        $request = Stub::makeEmpty(
            'Symfony\\Component\\HttpFoundation\\Request',
            ['query' => ['name' => "John"]]
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
                'primaryClass'
                    => 'HateoasInc\\Bundle\\ExampleBundle\\Entity\\User'
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
