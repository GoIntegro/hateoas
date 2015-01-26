<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Util;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Paginadores
use Doctrine\ORM\Tools\Pagination\Paginator;
// Colecciones
use GoIntegro\Hateoas\Collections\PaginatedCollection;
// Request.
use GoIntegro\Hateoas\JsonApi\Request;

class RepositoryHelper
{
    const RESOURCE_ENTITY_INTERFACE = 'GoIntegro\\Hateoas\\JsonApi\\ResourceEntityInterface';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var array
     */
    private $filters = [];

    /**
     * @param EntityManagerInterface
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Helper method to paginate a query using the HATEOAS request parameters.
     * @param Request\Params $request
     * @return PaginatedCollection
     */
    public function findByRequestParams(Request\Params $params)
    {
        return $this->findPaginated(
            $params->primaryClass,
            $params->filters,
            $params->getPageOffset(),
            $params->getPageSize(),
            $params->sorting
        );
    }

    /**
     * Helper method to paginate "find by" queries.
     * @param string $entityClass
     * @param array $criteria
     * @param integer $offset
     * @param integer $limit
     * @return PaginatedCollection
     */
    public function findPaginated(
        $entityClass,
        array $criteria,
        $offset = Request\Params::DEFAULT_PAGE_OFFSET,
        $limit = Request\Params::DEFAULT_PAGE_SIZE,
        $sorting = []
    )
    {
        $qb = $this->entityManager
            ->getRepository($entityClass)
            ->createQueryBuilder('e')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        foreach ($this->filters as $filter) {
            if ($filter->supportsClass($entityClass)) {
                $qb = $filter->filter($qb, $criteria, 'e');
            }
        }

        // @todo Esto se podría abstraer en un DefaultSorting que se pueda extender
        // @todo Faltaría definir la forma de filtrar por campos de entidades relacionadas
        foreach($sorting as $entityName => $sort) {
            foreach($sort as $field => $direction) {
                $qb->addOrderBy('e.' . $field, $direction);
            }
        }

        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        $collection = new PaginatedCollection($paginator);

        return $collection;
    }

    /**
     * @param Request\FilterInterface $filter
     * @return self
     */
    public function addFilter(Request\FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }
}
