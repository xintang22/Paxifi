<?php namespace Paxifi\Test\Store\Controller;

class CategoriesControllerTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['artisan']->call('migrate');

    }

    public function testGetEnabledCategories()
    {
        $this->seed('CategoriesTableSeeder');

        $response = $this->call('get', 'products/categories');

        $this->assertResponseoK();

        $this->assertCount(2, json_decode($response->getContent())->data);
    }

    public function tearDown()
    {
        $this->app['artisan']->call('migrate:reset');
    }
}
 