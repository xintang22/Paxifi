<?php namespace Paxifi\Test\Store\Repository\Product\Cost;

use Paxifi\Store\Repository\Category\EloquentCategoryRepository as Category;
use Paxifi\Store\Repository\Product\EloquentProductRepository as Product;
use Paxifi\Store\Repository\Product\Cost\EloquentCostRepository as Cost;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository as Driver;
use Faker\Factory as Faker;

class CostRepositoryObserverTest extends \TestCase
{

    public function setUp()
    {
        $this->refreshApplication();

        $this->app['artisan']->call('migrate');

        $this->seedTestData();

        Cost::flushEventListeners();
        Cost::boot();
    }

    public function testCorrectlyUpdateProductAverageCostAfterCostCreation()
    {
        $this->assertEquals(0.0, Product::find(1)->average_cost);

        Cost::create(array('cost' => 10.10, 'quantity' => 10, 'product_id' => 1,));

        $this->assertEquals(10.10, Product::find(1)->average_cost);

        Cost::create(array('cost' => 7.33, 'quantity' => 10, 'product_id' => 1,));

        $this->assertEquals(8.72, Product::find(1)->average_cost);
    }

    public function testCorrectlyUpdateProductAverageCostAfterCostUpdate()
    {
        Cost::create(array('cost' => 10.10, 'quantity' => 10, 'product_id' => 1,));

        Cost::create(array('cost' => 7.33, 'quantity' => 10, 'product_id' => 1,));

        $cost = Cost::find(1);

        $cost->cost = 6.00;

        $cost->save();

        $this->assertEquals(6.67, Product::find(1)->average_cost);
    }

    public function testCorrectlyUpdateProductAverageCostAfterCostDeletion()
    {
        $this->assertEquals(0, Product::find(1)->average_cost);

        Cost::create(array('cost' => 10.10, 'quantity' => 10, 'product_id' => 1,));

        $this->assertEquals(10.10, Product::find(1)->average_cost);

        Cost::find(1)->delete();

        $this->assertEquals(0, Product::find(1)->average_cost);

        Cost::create(array('cost' => 10.10, 'quantity' => 10, 'product_id' => 1,));

        Cost::create(array('cost' => 7.33, 'quantity' => 10, 'product_id' => 1,));

        Cost::find(2)->delete();

        $this->assertEquals(7.33, Product::find(1)->average_cost);
    }

    public function tearDown()
    {
        $this->app['artisan']->call('migrate:reset');
    }

    protected function seedTestData()
    {
        $faker = Faker::create();

        $store = Driver::create(
            array(
                'email' => $faker->email,
                'password' => \Hash::make($faker->name),
                'photo' => $faker->imageUrl(250, 250),
                'name' => $faker->name,
                'seller_id' => $faker->firstName,
                'address' => array(
                    'street' => $faker->streetAddress,
                    'city' => $faker->city,
                    'country' => $faker->country,
                    'postcode' => $faker->postcode
                ),
                'currency' => 'USD',
            )
        );

        Category::create(
            array(
                'name' => 'food',
                'description' => $faker->text(),
                'status' => 1,
            )
        );

        Product::create(
            array(
                'name' => $faker->name,
                'driver_id' => $store->id,
                'description' => $faker->text(),
                'photos' => $faker->imageUrl(250, 250),
                'tax' => $faker->randomFloat(2, 0, 2),
                'price' => $faker->randomFloat(1, 2, 10),
                'category_id' => 1,
                'quantity' => 0,
                'average_cost' => 0,
            )
        );

    }


}
 