<?php

use SimpleSoftwareIO\Cache\Cacheable;
use SimpleSoftwareIO\Cache\QueryCache;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    use Cacheable;
}

class CacheableTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function the_correct_cache_store_is_returned_when_a_cache_store_is_set()
    {
        $model = new class extends EloquentModel {
            use Cacheable;

            protected $cacheStore= 'fooStore';
        };

        $this->assertEquals('fooStore', $model->getCacheStore());
    }

    /** @test */
    public function null_is_returned_when_a_cache_store_is_not_set()
    {
        $model = new Model;

        $this->assertNull($model->getCacheStore());
    }

    /** @test */
    public function cache_busting_returns_true_when_cache_busting_is_set()
    {
        $model = new class extends EloquentModel {
            use Cacheable;

            protected $cacheBusting = true;
        };

        $this->assertTrue($model->getCacheBusting());
    }

    /** @test */
    public function cache_busting_returns_false_when_no_cache_busting_is_set()
    {
        $model = new Model;

        $this->assertFalse($model->getCacheBusting());
    }

    /** @test */
    public function thirty_minutes_are_returned_when_no_cache_length_is_set()
    {
        $model = new Model();

        $this->assertEquals(30, $model->getCacheLength());
    }

    /** @test */
    public function the_correct_length_is_returned_when_a_length_is_set()
    {
        $model = new class extends EloquentModel {
            use Cacheable;

            protected $cacheLength = 45;
        };

        $this->assertEquals(45, $model->getCacheLength());
    }

    /** @test */
    public function insert_and_update_bust_the_cache_when_busting_is_enabled()
    {
        $model = Mockery::mock('Model[queryCache]')
            ->shouldAllowMockingProtectedMethods();

        $queryCache = Mockery::mock(QueryCache::class);
        $queryCache->shouldReceive('flush')
            ->once();

        $model->shouldReceive('queryCache')
            ->once()
            ->andReturn($queryCache);

        $model->bust();

        $model->finishSave([]);
    }

    /** @test */
    public function insert_and_update_do_not_bust_the_cache_when_busting_is_disabled()
    {
        $model = Mockery::mock('Model[queryCache]')
            ->shouldAllowMockingProtectedMethods();

        $model->shouldReceive('queryCache')
            ->times(0);

        $model->dontBust();

        $model->finishSave([]);
    }

    /** @test */
    public function delete_should_bust_the_cache_when_busting_is_enabled()
    {
        $model = Mockery::mock('Model[queryCache]')
            ->shouldAllowMockingProtectedMethods();

        $queryCache = Mockery::mock(QueryCache::class);
        $queryCache->shouldReceive('flush')
            ->once();

        $model->shouldReceive('queryCache')
            ->once()
            ->andReturn($queryCache);

        $model->bust();

        $model->delete();
    }

    /** @test */
    public function delete_should_not_bust_the_cache_when_busting_is_disabled()
    {
        $model = Mockery::mock('Model[queryCache]')
            ->shouldAllowMockingProtectedMethods();

        $model->shouldReceive('queryCache')
            ->times(0);

        $model->dontBust();

        $model->delete();
    }

    /** @test */
    public function bust_enables_cache_busting()
    {
        $model = new Model;

        $model->bust();

        $this->assertTrue($model->isBusting());
    }

    /** @test */
    public function dont_bust_disables_cache_busting()
    {
        $model = new Model;

        $model->dontBust();

        $this->assertFalse($model->isBusting());
    }
}
