<?php
use Paxifi\Store\Repository\Product\Problem\EloquentProblemTypesRepository;

class ProblemTypesTableSeeder extends Seeder {

    public function run()
    {
        DB::table('problem_types')->truncate();

        EloquentProblemTypesRepository::create(
            array(
                'name' => 'Product Damaged',
                'description' => 'Product get damaged.',
                'enabled' => true
            )
        );

        EloquentProblemTypesRepository::create(
            array(
                'name' => 'Product Expired',
                'description' => 'Product get expired.',
                'enabled' => true
            )
        );

        EloquentProblemTypesRepository::create(
            array(
                'name' => 'Bad Quality',
                'description' => 'The product has a bad quality.',
                'enabled' => true
            )
        );

        EloquentProblemTypesRepository::create(
            array(
                'name' => 'Wrong Product Description',
                'description' => 'Wrong product description',
                'enabled' => true
            )
        );

        EloquentProblemTypesRepository::create(
            array(
                'name' => 'Others',
                'description' => 'Other issues',
                'enabled' => true
            )
        );
    }

} 