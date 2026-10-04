#!/usr/bin/env bash
# Entrypoint for the hamalert-web-dev image.
#
# The application source is bind-mounted at /var/www/html, so the things a
# normal deploy would do once at image-build time (composer install,
# generating config.inc.php from the sanitized template) have to happen here
# instead, on every container start -- but only the first time, since both
# outputs land in the developer's checkout on the host (both are gitignored;
# see the note in .gitignore).
set -e

cd /var/www/html

# --- Composer dependencies ---------------------------------------------------
# vendor/ is only partly committed (vendor/autoload.php and
# vendor/fguillot/json-rpc are checked in; everything composer.json/
# composer.lock also pulls in -- mongodb/mongodb, psr/log, the symfony
# polyfills -- is not). Detect that by checking for vendor/mongodb rather
# than vendor/ itself, since vendor/ always exists in the checkout.
if [ ! -d vendor/mongodb ]; then
	echo "[entrypoint] vendor/mongodb not found; running composer install..."
	export COMPOSER_ALLOW_SUPERUSER=1
	composer install --no-interaction --no-dev
else
	echo "[entrypoint] vendor/mongodb already present; skipping composer install."
fi

# --- config.inc.php ----------------------------------------------------------
# config.inc.php is gitignored (it normally carries real secrets) and is not
# present in a fresh checkout. Generate a dev one from the sanitized
# config_clean.inc.php template, substituting only the handful of keys that
# need to point at this docker-compose-style dev setup; every other key
# (including secrets that are meaningless in dev, like forgotpass_hashkey)
# is left exactly as the template has it.
if [ ! -f config.inc.php ]; then
	echo "[entrypoint] config.inc.php not found; generating it from config_clean.inc.php..."

	GEN_CONFIG_PHP="$(mktemp)"
	cat > "$GEN_CONFIG_PHP" <<'PHP'
<?php
// Generates config.inc.php from config_clean.inc.php by substituting the
// value of a few 'key' => '...' entries, keeping everything else byte-for-
// byte identical to the template (including formatting/comments).
$src = 'config_clean.inc.php';
$dst = 'config.inc.php';

$mongodbUri = getenv('MONGODB_URI') ?: 'mongodb://hamalert-dev-mongo:27017/hamalert';
$selfUrl = getenv('SELF_URL') ?: 'http://localhost:8081';
$simulateSpotUrl = getenv('SIMULATE_SPOT_URL') ?: 'http://host.docker.internal:1983/sendSpot';

$replacements = [
	'mongodb_uri' => $mongodbUri,
	'self_url' => $selfUrl,
	'simulate_spot_url' => $simulateSpotUrl,
	'simulate_spot_url_test' => $simulateSpotUrl,
];

$content = file_get_contents($src);
if ($content === false) {
	fwrite(STDERR, "[entrypoint] ERROR: could not read $src\n");
	exit(1);
}

foreach ($replacements as $key => $value) {
	// Matches:   'key' => 'anything-without-a-single-quote',
	// and replaces just the value, keeping indentation/trailing comma as-is.
	$pattern = "/(" . preg_quote("'$key'", '/') . "\s*=>\s*')[^']*(')/";
	$escapedValue = addcslashes($value, "\\'");
	$newContent = preg_replace($pattern, '${1}' . $escapedValue . '${2}', $content, 1, $count);
	if ($count !== 1) {
		fwrite(STDERR, "[entrypoint] WARNING: did not find a '$key' => '...' line in $src; leaving it untouched\n");
		continue;
	}
	$content = $newContent;
}

if (file_put_contents($dst, $content) === false) {
	fwrite(STDERR, "[entrypoint] ERROR: could not write $dst\n");
	exit(1);
}

echo "[entrypoint] generated $dst with:\n";
foreach (array_keys($replacements) as $key) {
	foreach (preg_split('/\R/', $content) as $line) {
		if (strpos($line, "'$key'") !== false) {
			echo "  " . trim($line) . "\n";
		}
	}
}
PHP

	php "$GEN_CONFIG_PHP"
	rm -f "$GEN_CONFIG_PHP"
else
	echo "[entrypoint] config.inc.php already present; leaving it as-is."
fi

exec "$@"
