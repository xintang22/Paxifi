<?php

use Faker\Factory as Faker;
use Paxifi\Store\Repository\Category\EloquentCategoryRepository;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->truncate();

        $faker = Faker::create();

        EloquentCategoryRepository::create(
            array(
                'name' => 'food',
                'description' => $faker->text(),
                'status' => 1,
            )
        );

        EloquentCategoryRepository::create(
            array(
                'name' => 'drinks',
                'description' => $faker->text(),
                'status' => 1,
            )
        );

        EloquentCategoryRepository::create(
            array(
                'name' => 'tools',
                'description' => $faker->text(),
                'status' => 0,
            )
        );

    }
} 