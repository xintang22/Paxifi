<?php

use \Paxifi\Settings\Repository\EloquentCountryRepository;

class CountriesTableSeeder extends Seeder
{
    // From Drupal core
    protected $countries = [
        ["name" => "United States", "iso" => "US", "currency" => "USD", "sticker_price" => 5, "commission_rate" => 0],
        ["name" => "United Kingdom", "iso" => "UK", "currency" => "GBP", "sticker_price" => 4, "commission_rate" => 0],
        ["name" => "Australia", "iso" => "AU", "currency" => "AUD", "sticker_price" => 4, "commission_rate" => 0],
        ["name" => "Kenya", "iso" => "KE", "currency" => "INR", "sticker_price" => 4, "commission_rate" => 0],
        ["name" => "India", "iso" => "IN", "currency" => "KES", "sticker_price" => 4, "commission_rate" => 0],
        ["name" => "Other", "iso" => "Other", "currency" => "USD", "sticker_price" => 4, "commission_rate" => 0],
    ];

    public function run()
    {
        DB::table('countries')->truncate();

        foreach ($this->countries as $index => $country) {
            EloquentCountryRepository::create($country);
        }
    }


} 