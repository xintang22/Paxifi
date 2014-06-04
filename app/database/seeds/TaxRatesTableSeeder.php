<?php

class TaxRatesTableSeeder extends Seeder
{
    protected $taxRates = [
        [
            'category' => 'standard',
            'country' => 'UK',
            'amount' => '0.2000',
        ],

        [
            'category' => 'reduced',
            'country' => 'UK',
            'amount' => '0.0500',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'AK', // Alaska
            'postcode' => '99555',
            'city' => 'Aleknagik',
            'amount' => '0.0500',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'AK', // Alaska
            'postcode' => '99621',
            'city' => 'Kwethluk',
            'amount' => '0.0300',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'AK', // Alaska
            'postcode' => '99750',
            'city' => 'Kivalina',
            'amount' => '0.0200',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'AK', // Alaska
            'postcode' => '99824',
            'city' => 'Juneau Burrough',
            'amount' => '0.0500',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'AK', // Alaska
            'postcode' => '99923',
            'city' => 'Alaska State',
            'amount' => '0.0000',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'CA', // California
            'postcode' => '90230',
            'city' => 'Culver City',
            'amount' => '0.0950',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'CA', // California
            'postcode' => '90631',
            'city' => 'La Habra',
            'amount' => '0.0850',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'CA', // California
            'postcode' => '90637',
            'city' => 'La Mirada',
            'amount' => '0.1000',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'CA', // California
            'postcode' => '91770',
            'city' => 'Los Angeles CO',
            'amount' => '0.0900',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'CA', // California
            'postcode' => '91901',
            'city' => 'San Diego County',
            'amount' => '0.0800',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'HI', // Hawaii
            'postcode' => '96701',
            'city' => 'Honolulu',
            'amount' => '0.0450',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'HI', // Hawaii
            'postcode' => '96729',
            'city' => 'Maui County',
            'amount' => '0.0400',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'HI', // Hawaii
            'postcode' => '96755',
            'city' => 'Hawaii County',
            'amount' => '0.0400',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'NY', // New York
            'postcode' => '10001',
            'city' => 'New York City',
            'amount' => '0.08875',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'NY', // New York
            'postcode' => '10501',
            'city' => 'Westchester County',
            'amount' => '0.07375',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'NY', // New York
            'postcode' => '10509',
            'city' => 'Putnam County',
            'amount' => '0.08375',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'NY', // New York
            'postcode' => '10708',
            'city' => 'Yonkers',
            'amount' => '0.08375',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'NY', // New York
            'postcode' => '10950',
            'city' => 'Orange County',
            'amount' => '0.08125',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'NY', // New York
            'postcode' => '10001',
            'city' => 'New York City',
            'amount' => '0.08875',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'WA', // Washington
            'postcode' => '98015',
            'city' => 'Bellevue',
            'amount' => '0.09500',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'WA', // Washington
            'postcode' => '98028',
            'city' => 'Kenmore',
            'amount' => '0.09500',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'WA', // Washington
            'postcode' => '98243',
            'city' => 'San Juan County',
            'amount' => '0.08100',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'WA', // Washington
            'postcode' => '98303',
            'city' => 'Pierce County Non-RTA',
            'amount' => '0.07900',
        ],

        [
            'category' => 'standard',
            'country' => 'US',
            'state' => 'WA', // Washington
            'postcode' => '99008',
            'city' => 'Lincoln County',
            'amount' => '0.07700',
        ],
    ];

    public function run()
    {
        DB::table('tax_rates')->truncate();

        foreach ($this->taxRates as $tax) {

            $tax['created_at'] = $tax['updated_at'] = \Carbon\Carbon::now();

            DB::table('tax_rates')->insert($tax);
        }
    }
} 