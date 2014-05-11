<?php

use Paxifi\Store\Repository\EloquentDriverRepository as Driver;
use Faker\Factory as Faker;

class DriversTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('drivers')->truncate();

        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {

            Driver::create(
                array(
                    'email' => $faker->email,
                    'password' => Hash::make($faker->name),
                    'photo' => $faker->imageUrl(250, 250),
                    'name' => $faker->name,
                    'address' => array(
                        'street' => $faker->streetAddress,
                        'city' => $faker->city,
                        'country' => $faker->country,
                        'postcode' => $faker->postcode
                    ),
                    'currency' => 'USD',
                )
            );

        }

    }

}