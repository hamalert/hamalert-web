<?php
$loginOptional = true;
require_once("db.inc.php");

$imagesDir = "myspot_media/images";
$fontsDir = "myspot_media/fonts";

$callsign = @$_GET['c'];
if (!$callsign) {
	http_response_code(400);
	exit;
}

$h = substr(hash_hmac("sha256", $callsign, $config['myspot_hashkey']), 0, 16);
if ($h !== @$_GET['h']) {
	http_response_code(403);
	exit;
}

$days = 0;
$hours = 1;
if (@$_GET['a']) {
	$hours = (int)$_GET['a'];
}
$text = "no spots within the last ";
$hours_rem = $hours;
if ($hours >= 24) {
	$days = intdiv($hours, 24);
	if ($days > 1)
		$text .= "$days days ";
	else
		$text .= "day ";
	$hours_rem -= $days * 24;
}
if ($hours_rem > 1) {
	$text .= "$hours_rem hours";
} else if ($hours_rem == 1) {
	if ($days > 0)
		$text .= "1 hour";
	else
		$text .= "hour";
}
$spot = getMySpotForCallsign($callsign, $hours * 3600);
if (!$spot) {
	$im = makeMySpotImage($callsign, "off air", $text, "last updated on " . date("Y-m-d H:i:s") . "Z", @$_GET['dark'], true, @$_GET['hr']);
} else {
	$freqMode = $spot['frequency'] . " MHz";
	$addInfo = null;
	if (@$spot['mode']) {
		$freqMode .= " (" . strtoupper($spot['mode']) . ")";
	}
	if (@$spot['summitRef']) {
		$addInfo = "SOTA: " . $spot['summitRef'] . " " . $spot['summitName'];
	} else if (@$spot['wwffRef']) {
		$addInfo = "WWFF: " . $spot['wwffRef'] . " " . $spot['wwffName'];
	}
	} else if (@$spot['wwbotaRef']) {
		$addInfo = "WWBOTA: " . $spot['wwbotaRef'] . " " . $spot['wwbotaName'];
	}
	$spotInfo = "last spotted on " . $spot['receivedDate']->toDateTime()->format("Y-m-d H:i:s") . "Z\nby " . $spot['spotter'] . " via " . $config['sources'][$spot['source']];
	if (preg_match("/^SIMULATED/", @$spot['rawText']))
		$spotInfo .= " (simulated)";
	$im = makeMySpotImage($spot['fullCallsign'], $freqMode, $addInfo, $spotInfo, @$_GET['dark'], false, @$_GET['hr']);
}

header("Content-Type: image/png");
imagepng($im);
imagedestroy($im);


function makeMySpotImage($callsign, $freqMode, $addInfo, $spotInfo, $dark = false, $offAir = false, $hires = false) {
	global $imagesDir, $fontsDir;
	
	if ($hires) {
		$im = imagecreatefrompng($dark ? "$imagesDir/myspot_dark_bg_hr.png" : "$imagesDir/myspot_light_bg_hr.png");
	} else {
		$im = imagecreatefrompng($dark ? "$imagesDir/myspot_dark_bg.png" : "$imagesDir/myspot_light_bg.png");
	}
	
	$mul = ($hires ? 2 : 1);
	$textXpos = $mul * 57;
	$width = $mul * 400;
	
	if ($dark) {
		$textColor = imagecolorexact($im, 255, 255, 255);
		$textColor2 = imagecolorexact($im, 249, 121, 95);
		$textColor_offair = imagecolorexact($im, 128, 128, 128);
		$textColor_offair2 = imagecolorexact($im, 96, 96, 96);
	} else {
		$textColor = imagecolorexact($im, 0, 0, 0);
		$textColor2 = imagecolorexact($im, 201, 82, 58);
		$textColor_offair = imagecolorexact($im, 128, 128, 128);
		$textColor_offair2 = imagecolorexact($im, 192, 192, 192);
	}
	
	imagefttext($im, $mul * 15, 0, $textXpos, $mul * 25, $textColor, "$fontsDir/Inter_18pt-Light.ttf", $callsign);

	$freqYpos = $addInfo ? $mul * 66 : $mul * 76;
	imagefttext($im, $mul * 18, 0, $textXpos, $freqYpos, $offAir ? $textColor_offair : $textColor, "$fontsDir/Inter_18pt-Bold.ttf", $freqMode);

	// Additional info (SOTA etc.): shorten if necessary
	if ($addInfo) {
		$addInfo = substr($addInfo, 0, $mul * 70);	// anything longer than that will never fit
		$addInfoShort = null;
		$bbox = imagettfbbox($mul * 12, 0, "$fontsDir/Inter_18pt-Bold.ttf", $addInfo);
		$numCharsStripped = 0;
		while ($bbox[4] > ($width - $textXpos - $mul * 6)) {
			$numCharsStripped++;
			$addInfoShort = substr($addInfo, 0, -$numCharsStripped) . "...";
			$bbox = imageftbbox($mul * 12, 0, "$fontsDir/Inter_18pt-Bold.ttf", $addInfoShort);
		}
	
		imagefttext($im, $mul * 12, 0, $textXpos, $mul * 90, $offAir ? $textColor_offair2 : $textColor2, "$fontsDir/Inter_18pt-Bold.ttf", $addInfoShort ?? $addInfo);
	}

	imagefttext($im, $mul * 10, 0, $textXpos, $mul * 123, $textColor, "$fontsDir/Inter_18pt-Regular.ttf", $spotInfo, ['linespacing' => 1.2]);
	return $im;
}
