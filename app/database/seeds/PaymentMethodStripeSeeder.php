<?php

use Paxifi\Payment\Repository\EloquentPaymentMethodsRepository;

class PaymentMethodStripeSeeder extends Seeder {
    public function run()
    {
        EloquentPaymentMethodsRepository::create([
            'name' => 'stripe',
            'description' => 'Pay by stripe',
            'enabled' => false
        ]);
    }
}