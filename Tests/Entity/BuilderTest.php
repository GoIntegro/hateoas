<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity;

// Mocks.
use Codeception\Util\Stub;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildingWithDefaultService()
    {
        /* Given... (Fixture) */
        $params = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\Params',
            ['primaryType' => 'users']
        );
        $defaultBuilder = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\AbstractBuilderInterface',
            ['create' => Stub::once()]
        );
        $postBuilder = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\BuilderInterface',
            ['create' => Stub::never()]
        );
        $builder = new Builder;
        $builder->addBuilder($defaultBuilder, Builder::DEFAULT_BUILDER)
            ->addBuilder($postBuilder, 'posts');
        /* When... (Action) */
        $entity = $builder->create($params, [], []);
        /* Then... (Assertions) */
    }

    public function testBuildingWithCustomService()
    {
        /* Given... (Fixture) */
        $params = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\Params',
            ['primaryType' => 'users']
        );
        $defaultBuilder = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\AbstractBuilderInterface',
            ['create' => Stub::never()]
        );
        $userBuilder = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\BuilderInterface',
            ['create' => Stub::once()]
        );
        $builder = new Builder;
        $builder->addBuilder($defaultBuilder, Builder::DEFAULT_BUILDER)
            ->addBuilder($userBuilder, 'users');
        /* When... (Action) */
        $entity = $builder->create($params, [], []);
        /* Then... (Assertions) */
    }
}
