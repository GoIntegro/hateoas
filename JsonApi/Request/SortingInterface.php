<?php
/**
 * @copyright 2015 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Sebastián Mensi <sebastian.mensi@gointegro.com>
 */

namespace GoIntegro\Hateoas\JsonApi\Request;

// ORM.
use Doctrine\ORM\QueryBuilder;

interface SortingInterface
{
    /**
     * @param string $class
     * @return boolean
     * @see \Symfony\Component\Security\Core\Authorization\Voter
     */
    public function supportsClass($class);

    /**
     * @param QueryBuilder $qb
     * @param array $sorts
     * @param string $alias
     * @return QueryBuilder
     */
    public function sort(QueryBuilder $qb, array $sorts, $alias = 'e');
}
