<?php
/**
 * @copyright 2015 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Sebastián Mensi <sebastian.mensi@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Request;

// ORM.
use Doctrine\ORM\QueryBuilder;

class DefaultSorting implements SortingInterface
{
    /**
     * @see SortingInterface::supportsClass
     */
    public function supportsClass($class)
    {
        return is_a($class, 'GoIntegro\\Hateoas\\JsonApi\\ResourceEntityInterface', TRUE);
    }

    /**
     * @see SortingInterface::filter
     */
    public function sort(
        QueryBuilder $qb, array $sorts, $alias = 'e'
    ) {
        foreach($sorts as $type => $sorting) {
            foreach($sorting as $resourceName => $sort) {
                foreach($sort as $field => $order) {
                    if('association' == $type) {
                        $namespace = $alias . '.' . $resourceName;
                        $qb->leftJoin($namespace, $resourceName);
                        $field = $resourceName . '.' . $field;
                    } elseif ('field' == $type) {
                        $field = $alias . '.' . $field;
                    } else {
                        break;
                    }
                    $qb->addOrderBy($field, $order);
                }
            }
        }

        return $qb;
    }
}
