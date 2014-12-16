<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity;

// Inflection.
use Doctrine\Common\Util\Inflector;
// JSON-API.
use GoIntegro\Hateoas\JsonApi\ResourceEntityInterface;
// ORM.
use Doctrine\ORM\EntityManagerInterface,
    Doctrine\ORM\ORMException,
    Gedmo\Exception as GedmoException;
// Validator.
use Symfony\Component\Validator\ValidatorInterface;
// Metadata.
use GoIntegro\Hateoas\Metadata\Entity\MetadataCache;

class DefaultMutator implements MutatorInterface
{
    use Validating, AltersEntities;

    const GET = 'get', REMOVE = 'remove', ADD = 'add', SET = 'set';

    const TRANSLATION_ENTITY = 'Gedmo\\Translatable\\Entity\\Translation';

    const ERROR_COULD_NOT_UPDATE = "Could not update the resource.",
        ERROR_UNTRANSLATABLE_FIELD = "The field \"%s\" is not translatable.",
        ERROR_TRANSLATION_FAILED = "The field \"%s\" could not be translated.";

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var MetadataCache
     */
    private $metadataCache;

    /**
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param MetadataCache $metadataCache
     */
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        MetadataCache $metadataCache
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->metadataCache = $metadataCache;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @param array $fields
     * @param array $relationships
     * @param array $metadata
     * @return ResourceEntityInterface
     * @throws EntityConflictExceptionInterface
     * @throws Validation\ValidationExceptionInterface
     */
    public function update(
        ResourceEntityInterface $entity,
        array $fields,
        array $relationships = [],
        array $metadata = []
    )
    {
        $class = $this->metadataCache->getReflection($entity);
        $translations = !empty($metadata['translations'])
            ? $metadata['translations']
            : [];

        $this->setFields($class, $entity, $fields)
            ->setRelationships($class, $entity, $relationships)
            ->updateTranslations($entity, $translations)
            ->validate($entity);

        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (ORMException $e) {
            throw new PersistenceException(self::ERROR_COULD_NOT_UPDATE);
        }

        return $entity;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @param array $translations
     * @return ResourceEntityInterface
     */
    private function updateTranslations(
        ResourceEntityInterface $entity, array $translations
    )
    {
        $repository = $this->em->getRepository(self::TRANSLATION_ENTITY);

        foreach ($translations as $locale => $fields) {
            foreach ($fields as $field => $value) {
                try {
                    $repository->translate($entity, $field, $locale, $value);
                } catch (GedmoException\InvalidArgumentException $e) {
                    $message
                        = sprintf(self::ERROR_UNTRANSLATABLE_FIELD, $field);
                    throw new TranslationException($message, NULL, $e);
                } catch (GedmoException $e) {
                    $message
                        = sprintf(self::ERROR_TRANSLATION_FAILED, $field);
                    throw new TranslationException($message, NULL, $e);
                }
            }
        }

        return $this;
    }
}
