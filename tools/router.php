<?php
// Router for PHP's built-in web server, for local development only:
//   php -S 127.0.0.1:8081 tools/router.php
// Replicates the .htaccess rewrite (RewriteRule ^([a-zA-Z0-9_]+)$ $1.php) for the site root
// and for /ajax/, and serves static files as-is. Production uses Apache; this file is not used there.
$docroot = dirname(__DIR__);
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Static files (css, js, images, fonts)
if ($uri !== '/' && file_exists($docroot . $uri) && !is_dir($docroot . $uri)) {
	return false;
}

$path = trim($uri, '/');
if ($path === '') {
	$path = 'index';
}

// /ajax/foo -> ajax/foo.php (the scripts use cwd-relative includes, so chdir first)
if (preg_match('#^([a-zA-Z0-9_]+)/([a-zA-Z0-9_]+)$#', $path, $m) && file_exists("$docroot/{$m[1]}/{$m[2]}.php")) {
	chdir("$docroot/{$m[1]}");
	require "$docroot/{$m[1]}/{$m[2]}.php";
	return true;
}

// foo -> foo.php
if (preg_match('#^[a-zA-Z0-9_]+$#', $path) && file_exists("$docroot/$path.php")) {
	chdir($docroot);
	require "$docroot/$path.php";
	return true;
}

chdir($docroot);
return false;
