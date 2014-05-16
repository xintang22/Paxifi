<?php namespace Paxifi\Test\Store\Controller;

use Mockery as m;
use Faker\Factory as Faker;

class DriverControllerTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['artisan']->call('migrate');
    }

    public function testRegisterNewDriver()
    {
        $faker = Faker::create();

        $email = $faker->email;

        $postData = array(
            'email' => $email,
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
        );

        $response = $this->call('post', 'drivers', $postData);

        $this->assertResponseStatus(201);

        $this->assertContains($email, $response->getContent());
    }

    public function testSellerIdIsAvailable()
    {
        $response = $this->call('get', 'drivers/seller_id', array('id' => 'Foo123'));

        $this->assertResponseOk();

        $this->assertContains('success', $response->getContent());
    }

    public function testSellerIdIsNotAvailable()
    {
        $faker = Faker::create();

        $email = $faker->email;

        $postData = array(
            'email' => $email,
            'password' => \Hash::make($faker->name),
            'photo' => $faker->imageUrl(250, 250),
            'name' => $faker->name,
            'seller_id' => 'Foo123',
            'address' => array(
                'street' => $faker->streetAddress,
                'city' => $faker->city,
                'country' => $faker->country,
                'postcode' => $faker->postcode
            ),
            'currency' => 'USD',
        );

        $this->call('post', 'drivers', $postData);

        $response = $this->call('get', 'drivers/seller_id', array('id' => 'Foo123'));

        $this->assertResponseStatus(400);

        $this->assertContains('error', $response->getContent());
    }

    public function tearDown()
    {
        m::close();

        $this->app['artisan']->call('migrate:reset');
    }
}
 