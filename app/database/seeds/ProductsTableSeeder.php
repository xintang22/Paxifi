<?php

use Illuminate\Database\Seeder;
use Paxifi\Store\Repository\Product\EloquentProductRepository as Product;
use Paxifi\Store\Repository\Product\Cost\EloquentCostRepository as Cost;
use Faker\Factory as Faker;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        /**
         * Truncate Product Tables before seed faker data
         */
        DB::table('products')->truncate();
        DB::table('product_costs')->truncate();

        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {

            $product = Product::create(
                array(
                    'name' => $faker->name,
                    'driver_id' => $faker->randomNumber(null, 10),
                    'description' => $faker->text(),
                    'photos' => $faker->imageUrl(250, 250),
                    'tax' => $faker->randomFloat(2, 0, 2),
                    'price' => $faker->randomFloat(1, 2, 10),
                    'category_id' => $faker->randomNumber(null, 10),
                    'quantity' => 0,
                    'average_cost' => 0,
                )
            );

            for ($j = 0; $j < 5; $j++) {

                Cost::create(
                    array(
                        'cost' => $faker->randomFloat(1, 2, 10),
                        'quantity' => 10,
                        'product_id' => $product->id,
                    )
                );

            }

        }
    }
}