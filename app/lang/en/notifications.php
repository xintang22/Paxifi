<?php 
return array(
    'billing' => 'Commission amount of :commission has been deducted.',
    'ranking' => [
        'up' => 'You got a thumbs up.',
        'down' => 'You got a thumbs down.',
    ],
    'stock_reminder' => ':product_name out of stock!',
    'sales' => [
        "cash" => [
            'waiting' => ':currency :amount cash payment is waiting for confirmation.',
            'received' => ':currency :amount cash payment received.',
            'canceled' => ':currency :amount cash payment was canceled.'
        ],
        "paypal" => [
            'completed' => 'You received :currency :amount via PayPal.',
        ]
    ],
    'emails' => 'You have a new email, please check your email inbox.',
    'no_new_notifications' => 'You don\'t have new notifications.',

    // CRUD
    'created' => ':type notification has been created.',
    'deleted' => 'Notifications have been deleted successfully.',
    'no_available_resources' => 'No new notifications available.'
);