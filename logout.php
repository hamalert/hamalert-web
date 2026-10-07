<?php

session_start([
	'cookie_secure' => true,
	'cookie_httponly' => true,
]);
session_destroy();
header("Location: login");
exit;
