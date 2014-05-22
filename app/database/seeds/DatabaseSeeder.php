<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $this->call('DriversTableSeeder');

        $this->call('ProductsTableSeeder');

        $this->call('CategoriesTableSeeder');

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
		// $this->call('UserTableSeeder');
	}

}