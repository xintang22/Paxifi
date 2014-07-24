<?php 
return array(
    'billing' => '',
    'ranking' => [
        'up' => 'You got a thumbs up.',
        'down' => 'You got a thumbs down.',
    ],
    'stock_reminder' => ':product_name out of stock!',
    'sales' => [
        "cash" => [
            'waiting' => ':currency :amount cash is waiting for confirmation.',
            'received' => ':currency :amount cash sales was received.',
            'canceled' => ':currency :amount cash sales was canceled.'
        ],
        "paypal" => [
            'completed' => ':amount of :product_name sold. Payment is complete.',
        ]
    ],
    'emails' => 'You got a new email.',
    'no_new_notifications' => 'You don\'t have new notifications.',

    // CRUD
    'created' => ':type notification has been created.',
    'deleted' => 'Notifications have been deleted successfully.',
    'no_available_resources' => 'No new notifications available.'
);