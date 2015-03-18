<?php
/**
 * @copyright 2015 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Sebastián Mensi <sebastian.mensi@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;
// Metadata.
use GoIntegro\Hateoas\Metadata\Resource\MetadataMinerInterface;
// Inflector.
use GoIntegro\Hateoas\Util\Inflector;

/**
 * @see http://jsonapi.org/format/#fetching
 */
class SortingParser
{
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
        $sorting = [];
        if (!$request->query->has('sort')) return $sorting;
        if (empty($params->primaryClass)) return $sorting;

        $sort = $request->query->get('sort');
        if (!is_array($sort)) {
            $sort = [$params->primaryType => $sort];
        }

        $metadata = $this->metadataMiner->mine($params->primaryClass);

        foreach ($sort as $resource => $sort) {
            if (is_string($sort)) $sort = explode(',', $sort);
            foreach ($sort as $field) {
                if ('-' != substr($field, 0, 1)) {
                    $order = Params::ASCENDING_ORDER;
                } else {
                    $order = Params::DESCENDING_ORDER;
                    $field = substr($field, 1);
                }
                if ($resource == $params->primaryType && $metadata->isField($field)) {
                    $type = 'field';
                } elseif ($metadata->isToOneRelationship($resource)) {
                    $type = 'association';
                } else {
                    $type = 'custom';
                }

                $field = Inflector::camelize($field);
                $camelizeResource = Inflector::camelize($resource);
                $sorting[$type][$camelizeResource][$field] = $order;
            }
        }

        return $sorting;
    }
}
