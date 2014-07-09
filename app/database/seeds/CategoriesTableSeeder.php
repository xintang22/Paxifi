<?php

use Paxifi\Store\Repository\Category\EloquentCategoryRepository;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->truncate();

        EloquentCategoryRepository::create(
            array(
                'name' => 'food',
                'description' => 'Quae voluptatibus molestiae fugiat error deserunt. Voluptatem quia distinctio in. Similique ducimus pariatur est quo.',
                'status' => 1,
            )
        );

        EloquentCategoryRepository::create(
            array(
                'name' => 'drinks',
                'description' => 'Quae voluptatibus molestiae fugiat error deserunt. Voluptatem quia distinctio in. Similique ducimus pariatur est quo.',
                'status' => 1,
            )
        );

        EloquentCategoryRepository::create(
            array(
                'name' => 'tools',
                'description' => 'Quae voluptatibus molestiae fugiat error deserunt. Voluptatem quia distinctio in. Similique ducimus pariatur est quo.',
                'status' => 0,
            )
        );

    }
} 