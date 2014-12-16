<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity;

// Inflection.
use Doctrine\Common\Util\Inflector;
// ORM.
use Doctrine\ORM\EntityManagerInterface,
    Doctrine\ORM\ORMException;
// Validator.
use Symfony\Component\Validator\ValidatorInterface,
    Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;
// Metadata.
use GoIntegro\Hateoas\Metadata\Entity\MetadataCache;

class DefaultBuilder implements AbstractBuilderInterface
{
    use Validating, AltersEntities;

    const GET = 'get', ADD = 'add', SET = 'set';

    const AUTHOR_IS_OWNER = 'GoIntegro\\Hateoas\\Entity\\AuthorIsOwner',
        ERROR_COULD_NOT_CREATE = "Could not create the resource.";

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;
    /**
     * @var MetadataCache
     */
    private $metadataCache;

    /**
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param SecurityContextInterface $securityContext
     * @param MetadataCache $metadataCache
     */
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SecurityContextInterface $securityContext,
        MetadataCache $metadataCache
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->securityContext = $securityContext;
        $this->metadataCache = $metadataCache;
    }

    /**
     * @param string $class
     * @param array $fields
     * @param array $relationships
     * @param array $metadata
     * @return ResourceEntityInterface
     * @throws EntityConflictExceptionInterface
     * @throws ValidationExceptionInterface
     */
    public function create(
        $class,
        array $fields,
        array $relationships = [],
        array $metadata = []
    )
    {
        $class = $this->metadataCache->getReflection($class);
        $entity = $class->newInstance();

        if ($class->implementsInterface(self::AUTHOR_IS_OWNER)) {
            $entity->setOwner($this->securityContext->getToken()->getUser());
        }

        $this->setFields($class, $entity, $fields)
            ->setRelationships($class, $entity, $relationships)
            ->validate($entity);

        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (ORMException $e) {
            throw new PersistenceException(self::ERROR_COULD_NOT_CREATE);
        }

        return $entity;
    }
}
