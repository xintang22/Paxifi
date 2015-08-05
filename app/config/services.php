<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, Mandrill, and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/

	'mailgun' => array(
		'domain' => getenv('MAIL_DOMAIN'),
		'secret' => getenv('MAIL_SECRET'),
	),

	'mandrill' => array(
		'secret' => getenv('MAIL_SECRET'),
	),

	'stripe' => array(
		'model'  => 'User',
		'secret' => '',
	),

	'notifications' => array(

		'environment' => getenv('NOTIFICATION_ENV'),

		'apns' => array(
			'certificate' => getenv('NOTIFICATION_APNS_CERTIFICATE'),
			'passphrase' => getenv('NOTIFICATION_APNS_PASSPHRASE'),
		),

		'gcm' => array(
			'apiKey' => getenv('NOTIFICATION_GCM_APIKEY'),
		),

        'jpush' => array(
            'appKey' => getenv('NOTIFICATION_JPUSH_APP_KEY'),
            'masterSecret' => getenv('NOTIFICATION_JPUSH_MASTER_SECRET'),
        ),
	),

);
