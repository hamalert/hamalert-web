<?php

function verifyCaptchaToken($token) {
	global $config;

	if (!$token) {
		return false;
	}

	$url = rtrim($config['captcha']['api_endpoint'], '/') . '/' . rawurlencode($config['captcha']['site_key']) . '/siteverify';
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
		'secret' => $config['captcha']['secret_key'],
		'response' => $token,
	]));
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);

	$response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($response === false) {
		syslog(LOG_ERR, "Cap siteverify request failed: " . curl_error($curl));
		return false;
	}

	$data = json_decode($response, true);
	if ($status >= 500 || !is_array($data)) {
		syslog(LOG_ERR, "Cap siteverify failed (HTTP $status)");
		return false;
	}
	if (empty($data['success']) && ($data['error'] ?? '') === 'Invalid site key or secret') {
		syslog(LOG_ERR, "Cap siteverify rejected the configured secret");
	}

	return !empty($data['success']);
}
