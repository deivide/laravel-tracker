<?php

namespace PragmaRX\Tracker\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Database\UniqueConstraintViolationException;
use PragmaRX\Tracker\Data\Repositories\Repository;

class FakeModel
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}

class ConcreteRepository extends Repository
{
    private $findResults = [];
    private $findCallCount = 0;
    private $createException = null;
    public $createdModel = null;

    public function __construct()
    {
        // Skip parent constructor - we mock everything
    }

    public function setFindResults(array $results)
    {
        $this->findResults = $results;
        $this->findCallCount = 0;
    }

    public function setCreateException($exception)
    {
        $this->createException = $exception;
    }

    protected function findByKeys($attributes, $keys, $otherModel = null)
    {
        $result = $this->findResults[$this->findCallCount] ?? null;
        $this->findCallCount++;
        return $result;
    }

    public function create($attributes, $model = null)
    {
        if ($this->createException) {
            throw $this->createException;
        }

        $this->createdModel = new FakeModel(99);
        return $this->createdModel;
    }

    public function getFindCallCount()
    {
        return $this->findCallCount;
    }
}

class RepositoryFindOrCreateTest extends TestCase
{
    private ConcreteRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new ConcreteRepository();

        // Mock the cache to always miss
        $cache = \Mockery::mock(\PragmaRX\Tracker\Support\Cache::class);
        $cache->shouldReceive('findCached')->andReturn([null, 'cache-key']);
        $cache->shouldReceive('cachePut');

        $reflection = new \ReflectionClass(Repository::class);
        $prop = $reflection->getProperty('cache');
        $prop->setAccessible(true);
        $prop->setValue($this->repo, $cache);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_when_not_found()
    {
        $this->repo->setFindResults([null]); // first find returns null

        $created = false;
        $id = $this->repo->findOrCreate(['kind' => 'Computer', 'platform' => 'iOS'], null, $created);

        $this->assertTrue($created);
        $this->assertEquals(99, $id);
    }

    /** @test */
    public function it_returns_existing_when_found()
    {
        $existing = new FakeModel(42);

        $this->repo->setFindResults([$existing]);

        $created = false;
        $id = $this->repo->findOrCreate(['kind' => 'Computer', 'platform' => 'iOS'], null, $created);

        $this->assertFalse($created);
        $this->assertEquals(42, $id);
    }

    /** @test */
    public function it_handles_race_condition_with_unique_constraint_violation()
    {
        // First find returns null (not found)
        // Create throws UniqueConstraintViolationException (another request inserted first)
        // Second find returns the existing record
        $existing = new FakeModel(42);

        $this->repo->setFindResults([null, $existing]);

        $previous = new \PDOException('Duplicate entry', '23000');
        $exception = new UniqueConstraintViolationException(
            'tracker',
            'insert into tracker_devices ...',
            [],
            $previous
        );
        $this->repo->setCreateException($exception);

        $created = false;
        $id = $this->repo->findOrCreate(
            ['kind' => 'Computer', 'model' => '0', 'platform' => 'iOS', 'platform_version' => '26.1'],
            null,
            $created
        );

        $this->assertFalse($created, 'Should not be marked as created when recovered from race condition');
        $this->assertEquals(42, $id, 'Should return the existing record ID');
        $this->assertEquals(2, $this->repo->getFindCallCount(), 'Should have called find twice (before and after failed insert)');
    }

    /** @test */
    public function it_handles_race_condition_with_query_exception_duplicate_entry()
    {
        // Same scenario but with generic QueryException (older Laravel versions)
        $existing = new FakeModel(42);

        $this->repo->setFindResults([null, $existing]);

        $previous = new \PDOException('Duplicate entry', '23000');
        $exception = new \Illuminate\Database\QueryException(
            'tracker',
            'insert into tracker_devices ...',
            [],
            $previous
        );
        // Set errorInfo for MySQL duplicate entry code
        $reflection = new \ReflectionClass($exception);
        $prop = $reflection->getProperty('errorInfo');
        $prop->setAccessible(true);
        $prop->setValue($exception, ['23000', '1062', 'Duplicate entry']);

        $this->repo->setCreateException($exception);

        $created = false;
        $id = $this->repo->findOrCreate(
            ['kind' => 'Computer', 'model' => '0', 'platform' => 'iOS', 'platform_version' => '26.1'],
            null,
            $created
        );

        $this->assertFalse($created);
        $this->assertEquals(42, $id);
    }

    /** @test */
    public function it_rethrows_non_duplicate_query_exceptions()
    {
        $this->repo->setFindResults([null]);

        $previous = new \PDOException('Connection refused', '08001');
        $exception = new \Illuminate\Database\QueryException(
            'tracker',
            'insert into tracker_devices ...',
            [],
            $previous
        );
        $reflection = new \ReflectionClass($exception);
        $prop = $reflection->getProperty('errorInfo');
        $prop->setAccessible(true);
        $prop->setValue($exception, ['08001', '2003', 'Connection refused']);

        $this->repo->setCreateException($exception);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->repo->findOrCreate(
            ['kind' => 'Computer', 'platform' => 'iOS'],
            null,
            $created
        );
    }
}
