<?php

use Paxifi\Payment\Repository\EloquentPaymentMethodsRepository;

class PaymentMethodsTableSeeder extends Seeder
{

    public function run()
    {
        DB::table('payment_methods')->truncate();

        EloquentPaymentMethodsRepository::create([
            'name' => 'cash',
            'description' => 'Pay by cash',
            'enabled' => false
        ]);

        EloquentPaymentMethodsRepository::create([
            'name' => 'paypal',
            'description' => 'Pay by paypal',
            'enabled' => false
        ]);
    }

} 