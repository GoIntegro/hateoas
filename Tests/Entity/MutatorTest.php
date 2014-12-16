<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Hateoas\Entity;

// Mocks.
use Codeception\Util\Stub;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class MutatorTest extends TestCase
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
        $defaultMutator = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\MutatorInterface',
            ['create' => Stub::once()]
        );
        $postMutator = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\MutatorInterface',
            ['create' => Stub::never()]
        );
        $mutator = new Mutator;
        $mutator->addMutator($defaultMutator, Mutator::DEFAULT_MUTATOR)
            ->addMutator($postMutator, 'posts');
        /* When... (Action) */
        $entity = $mutator->update($params, $entity, [], []);
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
        $defaultMutator = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\MutatorInterface',
            ['create' => Stub::never()]
        );
        $userMutator = Stub::makeEmpty(
            'GoIntegro\\Hateoas\\Entity\\MutatorInterface',
            ['create' => Stub::once()]
        );
        $mutator = new Mutator;
        $mutator->addMutator($defaultMutator, Mutator::DEFAULT_MUTATOR)
            ->addMutator($userMutator, 'users');
        /* When... (Action) */
        $entity = $mutator->update($params, $entity, [], []);
        /* Then... (Assertions) */
    }
}
