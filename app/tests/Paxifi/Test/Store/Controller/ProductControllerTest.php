<?php namespace Paxifi\Test\Store\Controller;

use Paxifi\Store\Repository\Category\EloquentCategoryRepository as Category;
use Paxifi\Store\Repository\Product\EloquentProductRepository as Product;
use Paxifi\Store\Repository\Product\Cost\EloquentCostRepository as Cost;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository as Driver;
use Faker\Factory as Faker;

class ProductControllerTest extends \TestCase
{
    protected $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker::create();
    }

    public function testCreateProduct()
    {
        $data = array(
            'name' => $this->faker->name,
            'driver_id' => 1,
            'description' => $this->faker->text(),
            'photos' => array(
                array(
                    'order' => 1,
                    'url' => $this->faker->imageUrl(250, 250),
                ),
                array(
                    'order' => 2,
                    'url' => $this->faker->imageUrl(250, 250),
                ),
                array(
                    'order' => 3,
                    'url' => $this->faker->imageUrl(250, 250),
                ),
            ),
            'tax' => $this->faker->randomFloat(2, 0, 2),
            'price' => $this->faker->randomFloat(1, 2, 10),
            'category_id' => 1,
            'inventory' => 0,
            'average_cost' => 0,
            'costs' => array(
                array('cost' => 10.70, 'inventory' => 10,),
                array('cost' => 5.25, 'inventory' => 20,),
                array('cost' => 7.00, 'inventory' => 15,),
            )
        );

    }

    public function testUpdateProduct()
    {

    }

    public function testDeleteProduct()
    {

    }

    public function testGetAllProducts()
    {

    }

    public function testGetSingleProduct()
    {

    }

    protected function seedTestData()
    {
        Driver::create(
            array(
                'email' => $this->faker->email,
                'password' => \Hash::make($this->faker->name),
                'photo' => $this->faker->imageUrl(250, 250),
                'name' => $this->faker->name,
                'seller_id' => $this->faker->firstName,
                'address' => array(
                    'street' => $this->faker->streetAddress,
                    'city' => $this->faker->city,
                    'country' => $this->faker->country,
                    'postcode' => $this->faker->postcode
                ),
                'currency' => 'USD',
            )
        );

        Category::create(
            array(
                'name' => 'food',
                'description' => $this->faker->text(),
                'status' => 1,
            )
        );
    }
}
 