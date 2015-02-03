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
        $add = function($resource, $sort, $type) use (&$sorting) {
            $resource = Inflector::camelize($resource);
            if (is_string($sort)) $sort = explode(',', $sort);
            foreach ($sort as $field) {
                if ('-' != substr($field, 0, 1)) {
                    $order = Params::ASCENDING_ORDER;
                } else {
                    $order = Params::DESCENDING_ORDER;
                    $field = substr($field, 1);
                }

                $sorting[$type][$resource][$field] = $order;
            }
        };

        foreach ($sort as $resource => $sort) {
            if ($resource == $params->primaryType) {
                $add($resource, $sort, 'field');
            } elseif ($metadata->isToOneRelationship($resource)) {
                $add($resource, $sort, 'association');
            } else {
                $add($resource, $sort, 'custom');
            }
        }

        return $sorting;
    }
}
