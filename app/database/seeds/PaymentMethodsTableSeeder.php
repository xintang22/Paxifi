<?php

use Paxifi\Payment\Repository\EloquentPaymentMethodsRepository;

class PaymentMethodsTableSeeder extends Seeder {

    public function run()
    {
        DB::table('payment_methods')->truncate();

        EloquentPaymentMethodsRepository::create(
            array(
                'name' => 'cash',
                'description' => 'Pay by cash',
                'enabled' => true
            )
        );

        EloquentPaymentMethodsRepository::create(
            array(
                'name' => 'paypal',
                'description' => 'Pay by paypal',
                'enabled' => false
            )
        );
    }

} 