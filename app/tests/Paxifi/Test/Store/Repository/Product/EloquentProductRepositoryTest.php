<?php namespace Paxifi\Test\Store\Repository\Product;

use Paxifi\Store\Repository\Category\EloquentCategoryRepository as Category;
use Paxifi\Store\Repository\Product\EloquentProductRepository as Product;
use Paxifi\Store\Repository\Product\Cost\EloquentCostRepository as Cost;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository as Driver;
use Faker\Factory as Faker;

class EloquentProductRepositoryTest extends \TestCase
{
    public function testUpdateZeroInventory()
    {
        $product = Product::find(1);

        $this->assertEquals(0, $product->inventory);

        Product::find(1)->updateInventory();

        $this->assertEquals(0, $product->inventory);
    }

    public function testUpdateProductInventoryByOne()
    {
        Cost::create(array('unit_cost' => 10.10, 'inventory' => 10, 'product_id' => 1,));
        Cost::create(array('unit_cost' => 10.00, 'inventory' => 10, 'product_id' => 1,));

        $this->assertEquals(20, Product::find(1)->inventory);

        Product::find(1)->updateInventory();

        $this->assertEquals(19, Product::find(1)->inventory);

        Cost::create(array('unit_cost' => 12.10, 'inventory' => 10, 'product_id' => 1,));

        $this->assertEquals(29, Product::find(1)->inventory);
    }

    public function testUpdateProductInventoryByMultiple()
    {
        Cost::create(array('unit_cost' => 10.10, 'inventory' => 10, 'product_id' => 1,));
        Cost::create(array('unit_cost' => 10.00, 'inventory' => 10, 'product_id' => 1,));

        $this->assertEquals(20, Product::find(1)->inventory);

        Product::find(1)->updateInventory(3);

        $this->assertEquals(17, Product::find(1)->inventory);

        Cost::create(array('unit_cost' => 12.10, 'inventory' => 10, 'product_id' => 1,));

        $this->assertEquals(27, Product::find(1)->inventory);
    }

    public function setUp()
    {
        parent::setUp();

        $this->app['artisan']->call('migrate');

        $this->seedTestData();

        Cost::flushEventListeners();
        Cost::boot();
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
                'tax_amount' => $faker->randomFloat(2, 0, 2),
                'unit_price' => $faker->randomFloat(1, 2, 10),
                'category_id' => 1,
                'inventory' => 0,
                'average_cost' => 0,
            )
        );

    }

}
 