<?php

use Paxifi\Store\Repository\Category\EloquentCategoryRepository;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->truncate();

        EloquentCategoryRepository::create(
            array(
                'name' => 'Snacks',
                'description' => 'Quae voluptatibus molestiae fugiat error deserunt. Voluptatem quia distinctio in. Similique ducimus pariatur est quo.',
                'enabled' => 1,
            )
        );

        EloquentCategoryRepository::create(
            array(
                'name' => 'Drinks',
                'description' => 'Quae voluptatibus molestiae fugiat error deserunt. Voluptatem quia distinctio in. Similique ducimus pariatur est quo.',
                'enabled' => 1,
            )
        );

        EloquentCategoryRepository::create(
            array(
                'name' => 'Health',
                'description' => 'Quae voluptatibus molestiae fugiat error deserunt. Voluptatem quia distinctio in. Similique ducimus pariatur est quo.',
                'enabled' => 1,
            )
        );

        EloquentCategoryRepository::create(
            array(
                'name' => 'Medicine',
                'description' => 'Quae voluptatibus molestiae fugiat error deserunt. Voluptatem quia distinctio in. Similique ducimus pariatur est quo.',
                'enabled' => 1,
            )
        );

        EloquentCategoryRepository::create(
            array(
                'name' => 'Tourism',
                'description' => 'Quae voluptatibus molestiae fugiat error deserunt. Voluptatem quia distinctio in. Similique ducimus pariatur est quo.',
                'enabled' => 1,
            )
        );

    }
} 