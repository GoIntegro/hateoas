<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Config;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Metadata.
use GoIntegro\Hateoas\Metadata\Entity\MetadataCache;
// RAML.
use GoIntegro\Raml\DocNavigator;
// Utils.
use GoIntegro\Hateoas\Util;

interface ResourceEntityMapCache
{
    /**
     * @return boolean
     */
    public function isFresh();

    /**
     * @return array $map
     * @return self
     */
    public function keep(array $map);

    /**
     * @return array
     */
    public function read();
}
