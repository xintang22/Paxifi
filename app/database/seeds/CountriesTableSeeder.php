<?php

use \Paxifi\Settings\Repository\EloquentCountryRepository;

class CountriesTableSeeder extends Seeder
{
    // From Drupal core
    protected $countries = [
        ["name" => "United States", "iso" => "US", "currency" => "USD", "sticker_price" => 5, "commission_rate" => 0.05],
        ["name" => "United Kingdom", "iso" => "UK", "currency" => "GBP", "sticker_price" => 4, "commission_rate" => 0.05],
        ["name" => "China", "iso" => "CN", "currency" => "RMB", "sticker_price" => 4, "commission_rate" => 0.05],
    ];

    public function run()
    {
        DB::table('countries')->truncate();

        foreach ($this->countries as $index => $country) {
            EloquentCountryRepository::create($country);
        }
    }


} 