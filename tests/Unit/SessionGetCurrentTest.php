<?php

namespace PragmaRX\Tracker\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PragmaRX\Tracker\Data\Repositories\Session;
use ReflectionProperty;

class FakeSessionModel
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}

class ConcreteSessionRepository extends Session
{
    public function __construct()
    {
        // Skip the parent constructor — this test only exercises getCurrent().
    }
}

class SessionGetCurrentTest extends TestCase
{
    /**
     * The regression this guards: a visitor whose session already exists takes
     * the "known" branch, where only $currentModel is filled. Reading the
     * repository model there handed back a blank instance, so callers of
     * Tracker::currentSession() silently got null ids and null relations.
     */
    public function testReturnsTheCurrentSessionWhenTheSessionIsKnown()
    {
        $repository = new ConcreteSessionRepository();

        $current = new FakeSessionModel(4242);
        $this->setProperty($repository, Session::class, 'currentModel', $current);
        $this->setProperty($repository, Session::class, 'model', new FakeSessionModel(null));

        $this->assertSame($current, $repository->getCurrent());
        $this->assertSame(4242, $repository->getCurrent()->id);
    }

    /**
     * On the create branch findOrCreate() assigns the repository model and no
     * current model is set, so that one still has to answer.
     */
    public function testFallsBackToTheRepositoryModelWhenThereIsNoCurrentSession()
    {
        $repository = new ConcreteSessionRepository();

        $created = new FakeSessionModel(7);
        $this->setProperty($repository, Session::class, 'currentModel', null);
        $this->setProperty($repository, Session::class, 'model', $created);

        $this->assertSame($created, $repository->getCurrent());
    }

    private function setProperty($object, $class, $name, $value)
    {
        $property = new ReflectionProperty($class, $name);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
