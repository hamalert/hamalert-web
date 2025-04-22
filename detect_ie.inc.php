<?php if (isset($_SERVER['HTTP_USER_AGENT']) && !preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])):?>
<script type="text/javascript">
$(function() {
	var ieVersion = detectIE();
	if (ieVersion !== false && ieVersion < 12) {
		$('#ieWarning').show();
		$('.hide-ie').hide();
	} else if (detectOldBrowser()) {
		$('#oldBrowserWarning').show();
		$('.hide-ie').hide();
	}
});

function detectOldBrowser() {
	var arr = [];
	if (!arr.includes) {
		return true;
	}
	return false;
}

function detectIE() {
	var ua = window.navigator.userAgent;

	var msie = ua.indexOf('MSIE ');
	if (msie > 0) {
		// IE 10 or older => return version number
		return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
	}

	var trident = ua.indexOf('Trident/');
	if (trident > 0) {
		// IE 11 => return version number
		var rv = ua.indexOf('rv:');
		return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
	}

	var edge = ua.indexOf('Edge/');
	if (edge > 0) {
		// Edge (IE 12+) => return version number
		return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
	}

	// other browser
	return false;
}
</script>

<div id="ieWarning" class="alert alert-danger" role="alert" style="display: none; margin-top: 1em">
	<strong>Internet Explorer is not supported.</strong>
	Please upgrade to a more modern browser, e.g. Edge, Chrome or Firefox.
</div>

<div id="oldBrowserWarning" class="alert alert-danger" role="alert" style="display: none; margin-top: 1em">
	<strong>You are using an old, unsupported browser.</strong>
	Please upgrade to the latest version of Chrome, Firefox, Safari, Opera or Edge in order to use HamAlert.
</div>
<?php endif; ?>
