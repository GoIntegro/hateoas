<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request,
    GoIntegro\Hateoas\Http\Url;
// Recursos.
use GoIntegro\Hateoas\JsonApi\DocumentPagination;
// Metadata.
use GoIntegro\Hateoas\Metadata\Resource\MetadataMinerInterface;

/**
 * @see http://jsonapi.org/format/#fetching
 */
class PaginationParser
{
    const ERROR_UNKNOWN_RESOURCE_TYPE = "The resource type is unknown.";

    /**
     * @var MetadataMinerInterface
     */
    private $metadataMiner;

    /**
     * @param MetadataMinerInterface $metadataMiner
     */
    public function __construct(MetadataMinerInterface $metadataMiner)
    {
        $this->metadataMiner = $metadataMiner;
    }

    /**
     * @param Request $request
     * @param Params $params
     * @return array
     */
    public function parse(Request $request, Params $params)
    {
        $pagination = new DocumentPagination;

        if (empty($params->primaryClass)) return $pagination;

        $pagination->page = (integer) $request->query->get('page');
        $resourceClassName
            = $this->metadataMiner->getResourceClass($params->primaryClass);
        $pagination->size = (integer) $request->query->get('size')
            ?: $resourceClassName->getProperty('pageSize')->getValue();
        $pagination->offset =
            ($pagination->page - DocumentPagination::COUNT_PAGES_FROM)
            * $pagination->size;
        $pagination->paginationlessUrl
            = $this->parsePaginationlessUrl($request);

        return $pagination;
    }

    /**
     * @param Request $request
     * @return string
     */
    private function parsePaginationlessUrl(Request $request)
    {
        $url = $request->getPathInfo();
        $query = $request->getQueryString();

        if (!empty($query)) {
            $params = explode('&', $query);
            $callback = function($pair) {
                list($key, $value) = explode('=', $pair);

                return !in_array($key, ['page', 'size']);
            };
            $params = array_filter($params, $callback);
            $url .= '?' . implode('&', $params);
        }

        return Url::fromString($url);
    }
}
