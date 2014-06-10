<?php

use Paxifi\Order\Repository\EloquentOrderRepository;
use Faker\Factory as Faker;

class OrdersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('orders')->truncate();

        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {

            EloquentOrderRepository::create(
                array(
                    'total_costs' => 50.50,
                    'total_sales' => 70,
                    'total_items' => $faker->randomNumber(null, 10),
                    'buyer_email' => $faker->email,
                    'feedback' => 1,
                    'comment' => $faker->text(),
                )
            );

        }


    }

}