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

    public function tearDown()
    {
        m::close();

        $this->app['artisan']->call('migrate:reset');
    }
}
 