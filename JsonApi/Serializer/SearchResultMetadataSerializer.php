<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Serializer;

// JSON-API.
use GoIntegro\Hateoas\JsonApi\Document,
    GoIntegro\Hateoas\JsonApi\SearchResultResourceCollection;
// Utils.
use GoIntegro\Hateoas\Util\Inflector;

/**
 * @todo Move a un sub-namespace "JsonApi\Extension".
 */
class SearchResultMetadataSerializer implements DocumentSerializerInterface
{
    public function serialize(Document $document)
    {
        $json = [];

        if (
            $document->resources
                instanceof SearchResultResourceCollection
        ) {
            $searchResult = $document->resources->getSearchResult();

            foreach (['query', 'query-time', 'facets'] as $property) {
                $method = 'get' . Inflector::camelize($property, TRUE);
                $json[$property] = $searchResult->$method();
            }
        }

        return $json;
    }
}
