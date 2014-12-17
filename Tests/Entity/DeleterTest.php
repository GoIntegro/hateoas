<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity;

// Mocks.
use Codeception\Util\Stub;

class DeleterTest extends \PHPUnit_Framework_TestCase
{
    public function testDeletingWithDefaultService()
    {
        /* Given... (Fixture) */
        $params = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\Params',
            ['primaryType' => 'users']
        );
        $entity = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\ResourceEntityInterface'
        );
        $defaultDeleter = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\DeleterInterface',
            ['create' => Stub::once()]
        );
        $postDeleter = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\DeleterInterface',
            ['create' => Stub::never()]
        );
        $deleter = new Deleter;
        $deleter->addDeleter($defaultDeleter, Deleter::DEFAULT_DELETER)
            ->addDeleter($postDeleter, 'posts');
        /* When... (Action) */
        $entity = $deleter->delete($params, $entity);
        /* Then... (Assertions) */
    }

    public function testDeletingWithCustomService()
    {
        /* Given... (Fixture) */
        $params = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\Request\\Params',
            ['primaryType' => 'users']
        );
        $entity = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\JsonApi\\ResourceEntityInterface'
        );
        $defaultDeleter = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\DeleterInterface',
            ['create' => Stub::never()]
        );
        $userDeleter = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\DeleterInterface',
            ['create' => Stub::once()]
        );
        $deleter = new Deleter;
        $deleter->addDeleter($defaultDeleter, Deleter::DEFAULT_DELETER)
            ->addDeleter($userDeleter, 'users');
        /* When... (Action) */
        $entity = $deleter->delete($params, $entity);
        /* Then... (Assertions) */
    }
}
