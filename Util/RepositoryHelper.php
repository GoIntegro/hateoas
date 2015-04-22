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
use GoIntegro\Hateoas\Collections\PaginatedCollection,
    Doctrine\Common\Collections\ArrayCollection;
// Request.
use GoIntegro\Hateoas\JsonApi\Request;
// Listener
use Gedmo\Translatable\TranslatableListener;

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
     * @var array
     */
    private $sorts = [];

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
     * @return ArrayCollection|PaginatedCollection
     */
    public function findByRequestParams(Request\Params $params)
    {       
        if (empty($params->pagination) &&
            isset($params->resourceConfig) && 
            isset($params->resourceConfig->defaults) &&
            isset($params->resourceConfig->defaults->pagination) &&
            !$params->resourceConfig->defaults->pagination
            ) {
            return $this->findAll(
                $params->primaryClass,
                $params->filters,
                $params->sorting,
                $params->translatable
            );
        } else {
            return $this->findPaginated(
                $params->primaryClass,
                $params->filters,
                $params->sorting,
                $params->getPageOffset(),
                $params->getPageSize(),
                $params->translatable
            );
        }
    }
    
    /**
     * @param string $entityClass
     * @param array $criteria
     * @param array $sort
     * @param boolean $translatable
     * @return QueryBuilder
     */      
    private function getQueryBuilder(
        $entityClass,
        array $criteria,
        $sorting = [],
        $translatable = false
    )
    {
        $qb = $this->entityManager
            ->getRepository($entityClass)
            ->createQueryBuilder('e');

        foreach ($this->filters as $filter) {
            if ($filter->supportsClass($entityClass)) {
                $qb = $filter->filter($qb, $criteria, 'e');
            }
        }

        foreach($this->sorts as $sort) {
            if ($sort->supportsClass($entityClass)) {
                $qb = $sort->sort($qb, $sorting, 'e');
            }
        }

        $query = $qb->getQuery();
        if($translatable) {
            $query->setHint(
                \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            );
            $query->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1);
        }            
        
        return $qb;
        
    }
    
    /**
     * @param string $entityClass
     * @param array $criteria
     * @param array $sort
     * @param boolean $translatable
     * @return ArrayCollection
     */    
    public function findAll($entityClass,
        array $criteria,
        $sorting = [],
        $translatable = false
    )
    {
        $qb = $this->getQueryBuilder($entityClass, $criteria, $sorting, $translatable);
        
        return new ArrayCollection($qb->getQuery()->getResult());
    }

    /**
     * Helper method to paginate "find by" queries.
     * @param string $entityClass
     * @param array $criteria
     * @param array $sort
     * @param integer $offset
     * @param integer $limit
     * @param boolean $translatable
     * @return PaginatedCollection
     */
    public function findPaginated(
        $entityClass,
        array $criteria,
        $sorting = [],
        $offset = Request\Params::DEFAULT_PAGE_OFFSET,
        $limit = Request\Params::DEFAULT_PAGE_SIZE,
        $translatable = false
    )
    {
        $qb = $this->getQueryBuilder($entityClass, $criteria, $sorting, $translatable);
        
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($qb->getQuery());
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

    /**
     * @param Request\SortingInterface $sort
     * @return self
     */
    public function addSorting(Request\SortingInterface $sort)
    {
        $this->sorts[] = $sort;
    }
}
