<?php namespace Paxifi\Test\Store\Controller;

use Paxifi\Store\Repository\Driver\DriverRepository;

class RatingControllerTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['artisan']->call('migrate');

        $this->seed('DriversTableSeeder');
    }

    public function testThumbsUp()
    {

        $this->call('post', 'drivers/1/rating', array('type' => 'up'));

        $this->assertResponseOk();

        $driver = DriverRepository::find(1);

        $this->assertEquals(1, $driver->thumbs_up);

    }

    public function testThumbsDown()
    {

        $this->call('post', 'drivers/1/rating', array('type' => 'down'));

        $this->assertResponseOk();

        $driver = DriverRepository::find(1);

        $this->assertEquals(1, $driver->thumbs_down);
    }

    public function tearDown()
    {
        $this->app['artisan']->call('migrate:reset');
    }

}
 