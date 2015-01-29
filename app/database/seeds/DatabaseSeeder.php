<?php

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $this->call('CategoriesTableSeeder');

        $this->call('IssueTypesTableSeeder');

        $this->call('OAuthSeeder');

        $this->call('PaymentMethodsTableSeeder');

        $this->call('NotificationTypesTableSeeder');

        $this->call('ProblemTypesTableSeeder');

        $this->call('TaxRatesTableSeeder');

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

}