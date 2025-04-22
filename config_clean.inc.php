<?php

// Sanitized config with API keys, passwords etc. removed, not for use in production

$config = array(
	'admin_email' => 'mk@neon1.net',
	'mongodb_uri' => 'mongodb://hamalert:<redacted>@127.0.0.1:27017/hamalert',
	'self_url' => 'https://hamalert.org',
	'forgotpass_hashkey' => '<redacted>',
	'forgotpass_link_expiration' => 86400,
	'mail_from' => 'HamAlert <do-not-reply@hamalert.org>',
	'mail_return_path' => '<do-not-reply@hamalert.org>',

	'master_password' => '<redacted>',
	'master_allowed_ips' => ['<redacted>'],
	
	'threema_api_id' => '*HAMALRT',
	'threema_api_secret' => '<redacted>',
	'threema_api_privatekey' => '<redacted>',
	'threema_validation_hashkey' => '<redacted>',
	
	'simulate_spot_url' => 'http://localhost:1983/sendSpot',
	'simulate_spot_url_test' => 'http://localhost:11983/sendSpot',
	
	'max_triggers' => 100,
	
	'matcher_rpc_url' => 'http://localhost:1984/RPC',
	
	'myspot_hashkey' => '<redacted>',

	'change_email_hashkey' => '<redacted>',

	'clublog' => [
		'apikey' => '<redacted>',
		'qslStatusValues' => [
			"confirmed" => 1,
			"worked" => 2,
			"verified" => 3
		],
		'modeValues' => [
			"all" => 0,
			"cw" => 1,
			"phone" => 2,
			"data" => 3
		]
	],
	
	'sources' => [
		'rbn' => 'RBN',
		'cluster' => 'Cluster',
		'sotawatch' => 'SOTAwatch',
		'pskreporter' => 'PSK Reporter',
		'pota' => 'POTA',
		'wwff' => 'WWFFwatch'
	],

	'actions' => [
		'app' => 'App',
		'threema' => 'Threema',
		'url' => 'URL',
		'telnet' => 'Telnet'
	],

	'discourse_connect_secret' => '<redacted>'
);
