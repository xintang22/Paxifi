<?php

class CountriesTableSeeder extends Seeder
{
    // From Drupal core
    protected $countries = [
        ["name" => "United States", "ios" => "US", "currency" => "USD", "sticker" => 5, "commission_rate" => 0.05],
        ["name" => "United Kingdom", "ios" => "UK", "currency" => "GBP", "sticker" => 4, "commission_rate" => 0.05],
    ];

    public function run()
    {
        DB::table('countries')->truncate();

        $countries = array();

        natcasesort($this->countries);

        foreach ($this->countries as $country => $index) {
            $countries[] = array(
                'name' => $country['name'],
                'iso' => $country['ios'],
                'currency' => $country['currency'],
                'sticker' => $country['sticker'],
                'commission_rate' => $country['commission_rate']
            );
        }

        DB::table('countries')->insert($countries);
    }


} 