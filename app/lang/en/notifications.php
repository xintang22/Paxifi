<?php 
return array(
    'billing' => 'Commission fee of :currency :commission has been deducted from your PayPal account.',
    'ranking' => [
        'up' => 'You received a thumbs up.',
        'down' => 'You recieved a thumbs down.',
    ],
    'stock_reminder' => ':product_name is out of stock!',
    'stock_almost_reminder' => ':product_name is near out of stock!',
    'sales' => [
        "cash" => [
            'waiting' => 'The cash payment of :currency :amount is waiting for your confirmation.',
            'received' => 'You received a cash payment of :currency :amount .',
            'canceled' => 'The cash payment of :currency :amount canceled by you.'
        ],
        "paypal" => [
            'completed' => 'You received an amount of :currency :amount via PayPal.',
        ],
        "stripe" => [
            'completed' => 'You received an amount of :currency :amount via Stripe.',
        ]
    ],
    'emails' => 'You have a new email, please check your email inbox.',
    'no_new_notifications' => 'You don\'t have any new notifications.',

    // CRUD
    'created' => ':type notification has been created.',
    'deleted' => 'Notifications have been deleted successfully.',
    'no_available_resources' => 'No new notifications available.'
);