<?php
require_once("db.inc.php");
require_once('lib/threema_msgapi.phar');
	
use Threema\MsgApi\Connection;
use Threema\MsgApi\ConnectionSettings;
use Threema\MsgApi\Receiver;

refreshUser();	// app tokens could have changed externally

$enableAlerts = $_SESSION['user']['alerts'];
$appTitleFormat = @$_SESSION['user']['app']['titleFormat'];
$appBodyFormat = @$_SESSION['user']['app']['bodyFormat'];
$threemaId = @$_SESSION['user']['threemaId'];
$notificationUrl = @$_SESSION['user']['notificationUrl'];
$notificationMethod = @$_SESSION['user']['notificationMethod'];
$telnetPassword = @$_SESSION['user']['telnetPassword'];

$errors = array();
$warnings = array();
$infos = array();
if ($_POST) {
	$enableAlerts = $_POST['enableAlerts'];
	$appTitleFormat = trim($_POST['appTitleFormat'] ?? "");
	$appBodyFormat = trim($_POST['appBodyFormat'] ?? "");
	$threemaId = strtoupper($_POST['threemaId'] ?? "");
	$telnetPassword = $_POST['telnetPassword'];
	$notificationUrl = $_POST['notificationUrl'];
	$notificationMethod = $_POST['notificationMethod'];
	
	if ($threemaId) {
		if (!preg_match("/^[A-Z0-9]{8}$/", $threemaId)) {
			$errors[] = "The specified Threema ID is invalid.";
		}
	}
	if ($telnetPassword) {
		if (strlen($telnetPassword) < 6) {
			$errors[] = "The Telnet password must have at least 6 characters.";
		}
	}
	if ($notificationUrl) {
		if (!filter_var($notificationUrl, FILTER_VALIDATE_URL) ||
			!preg_match("/^https?/", $notificationUrl)) {
			$errors[] = "The specified notification URL is not a valid http or https URL.";
		}
		if (!in_array($notificationMethod, ["GET", "POST", "POST-JSON"])) {
			$errors[] = "Invalid notification method";
		}
	} else {
		$notificationMethod = null;
	}
	
	// If a new Threema ID has been specified, we need to validate it.
	$threemaMustValidate = false;
	if (!$errors && $threemaId && $threemaId !== $_SESSION['user']['threemaId']) {
		$threemaMustValidate = true;
		$validationCode = calcThreemaValidationCode($threemaId);
		
		if (@$_POST['threemaValidationCode']) {
			if ($validationCode === $_POST['threemaValidationCode']) {
				$threemaMustValidate = false;
			} else {
				$errors[] = "Threema verification code incorrect.";
			}
		} else {
			// Send validation code via message
			try {
				if (!sendThreemaMessage($threemaId, "Your HamAlert verification code is: $validationCode")) {
					$errors[] = "Could not send verification message to Threema ID $threemaId.";
				} else {
					$infos[] = "A Threema message with a verification code has been sent to you. Please enter the code below to finish linking your Threema ID.";
				}
			} catch (Exception $e) {
				$errors[] = "Could not send verification message to Threema ID $threemaId. Check that the ID exists.";
			}
		}
	}
	
	if (!$errors) {
		// Update push sounds
		foreach ($_POST as $pn => $pv) {
			if (preg_match("/^sound_(.+)$/", $pn, $matches)) {
				$token = $matches[1];
				if ($pv == 'default' || $pv == 'morse'|| $pv == 'blip')
					setOptionsForAppToken($token, $pv, @$_POST['pushenable_' . $token] ? false : true);
			}
		}
		
		$destinations = [
			'alerts' => $enableAlerts ? true : false,
			'notificationUrl' => $notificationUrl,
			'notificationMethod' => $notificationMethod,
			'telnetPassword' => $telnetPassword
		];

		if ($appTitleFormat || $appBodyFormat) {
			$app = [];
			if ($appTitleFormat) {
				$app['titleFormat'] = $appTitleFormat;
			}
			if ($appBodyFormat) {
				$app['bodyFormat'] = $appBodyFormat;
			}
			setApp($app);
		} else {
			setApp(null);
		}
		
		if (!$threemaMustValidate) {
			$destinations['threemaId'] = $threemaId;
		}

		updateDestinations($destinations);
		
		$updateOk = true;
	}
}

function calcThreemaValidationCode($threemaId) {
	// Calculate a (fixed) 6 digit validation code for a given Threema ID using
	// a secret HMAC key
	global $config;
	$hash = hash_hmac("sha256", $threemaId, $config['threema_validation_hashkey'], true);
	
	// Take first four bytes as integer and return modulo 1000000 result
	return sprintf("%06d", unpack("N", $hash)[1] % 1000000);
}

function sendThreemaMessage($id, $message) {
	global $config;
	$settings = new ConnectionSettings(
		$config['threema_api_id'],
		$config['threema_api_secret']
	);

	touch('/tmp/threema_keystore.txt');
	$publicKeyStore = new Threema\MsgApi\PublicKeyStores\File('/tmp/threema_keystore.txt');
	$connector = new Connection($settings, $publicKeyStore);

	$e2eHelper = new \Threema\MsgApi\Helpers\E2EHelper(hex2bin($config['threema_api_privatekey']), $connector);
	$result = $e2eHelper->sendTextMessage($id, $message);
	return $result->isSuccess();
}

?>
<?php include('settings_begin.inc.php') ?>

<script type="text/javascript">
function removeDevice(el) {
	var li = $(el).parent('li');
	var token = li.data('token');
	if (!token)
		return;
	if (confirm("Are you sure you want to remove this device? It will no longer receive push notifications. If you use the app again on this device, it will reappear.")) {
		$.post({
			url: 'ajax/device_delete',
			data: {token: token},
			success: function(response) {
				li.remove();
			}
		});
	}
}

$(function() {
	$('.nav-tabs a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	})
});
</script>

<form method="post" class="limit-width">
	
<h1 class="page-header">Destinations <button type="submit" class="btn btn-primary" style="float: right">Save</button></h1>

<?php foreach ($errors as $error): ?>
<div class="alert alert-danger" role="alert">
	<?php echo $error ?>
</div>
<?php endforeach; ?>

<?php foreach ($warnings as $warning): ?>
<div class="alert alert-warning" role="alert">
	<?php echo $warning ?>
</div>
<?php endforeach; ?>

<?php foreach ($infos as $info): ?>
<div class="alert alert-info" role="alert">
	<?php echo $info ?>
</div>
<?php endforeach; ?>

<?php if (@$updateOk): ?>
<div class="alert alert-success alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	The destinations have been updated successfully.
</div>
<?php endif; ?>

	<div class="checkbox">
		<label>
			<input type="checkbox" id="enableAlerts" name="enableAlerts" value="1" <?php if ($enableAlerts) echo "checked"; ?> /> Enable alerts
		</label>
		<p class="help-block">Uncheck this option to temporarily disable alerts (e.g. on vacation).</p>
	</div>
	<hr />
	
	<fieldset>
		<legend>HamAlert App</legend>

		<div>
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#devices" aria-controls="devices" role="tab" data-toggle="tab">Devices</a></li>
				<li role="presentation"><a href="#appFormats" aria-controls="formats" role="tab" data-toggle="tab">Formats</a></li>
			</ul>
		</div>

		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="devices">
				<?php if (@$_SESSION['user']['appTokens']): ?>
				<ul id="linkedDevicesList">
					<?php $i = 0; foreach ($_SESSION['user']['appTokens'] as $appToken): ?>
					<li data-token="<?php echo htmlspecialchars($appToken['token'] ?? "") ?>"><?php echo htmlspecialchars($appToken['deviceName'] ?? "")?>
						<button type="button" class="btn btn-danger btn-xs" onclick='removeDevice(this); return false'>Remove</button>
						<br /><small class="text-muted">
							<?php if ($appToken['type'] == "apns")
								 	echo "iOS";
								else if ($appToken['type'] == "gcm")
									echo "Android";
								else
									echo "unknown";
							?> device,
							linked on <?php echo htmlspecialchars($appToken['addDate']->toDateTime()->format("Y-m-d")) ?>
						</small>
					
						<div class="checkbox">
							<label>
								<input type="checkbox" name="pushenable_<?php echo htmlspecialchars($appToken['token']) ?>" value="1" <?php if (!@$appToken['disable']) echo "checked"; ?> /> Enable push notifications
							</label>
						</div>
						<div class="sound">
							<label class="radio-inline">
								Sound:
							</label>
							<label class="radio-inline">
								<input type="radio" name="sound_<?php echo htmlspecialchars($appToken['token']) ?>" value="default" <?php if (@$appToken['sound'] == 'default' || !@$appToken['sound']) echo "checked" ?> /> Default
							</label>
							<label class="radio-inline">
								<input type="radio" name="sound_<?php echo htmlspecialchars($appToken['token']) ?>" value="blip" <?php if (@$appToken['sound'] == 'blip') echo "checked" ?> /> Blip
							</label>
							<label class="radio-inline">
								<input type="radio" name="sound_<?php echo htmlspecialchars($appToken['token']) ?>" value="morse" <?php if (@$appToken['sound'] == 'morse') echo "checked" ?> /> Morse
							</label>
						</div>
					</li>
					<?php $i++; endforeach; ?>
				</ul>
				<?php else: ?>
				<p><em>Currently no devices are linked to this account.</em></p>
				<?php endif; ?>
			</div>
			<div role="tabpanel" class="tab-pane" id="appFormats">
				<div class="form-group">
					<div class="form-group">
						<label for="appTitleFormat">Push notification title format</label>
						<input type="text" class="form-control appTitleFormat" id="appTitleFormat" name="appTitleFormat" placeholder="" value="<?php echo htmlspecialchars($appTitleFormat ?? "") ?>" />
					</div>
					<div class="form-group">
						<label for="appBodyFormat">Push notification body format</label>
						<textarea class="form-control appBodyFormat" id="appBodyFormat" name="appBodyFormat"><?php echo htmlspecialchars($appBodyFormat ?? "") ?></textarea>
					</div>
					<p class="help-block">Customize the title or body format by specifying your own template, with <a href="#urlparams" onclick="$('#urlParams').modal({})">variables</a> in curly brackets {}. 
						Variables from the spot are substituted into the push notification, without the curly brackets {}. All other letters, numbers, spaces, punctuation, and special characters are included in the push notification exactly as configured.<br />
						Leave blank to use the default format.<br />
						Example: <tt>Spot {fullCallsign} ({frequency} MHz, {mode}), spotted by {spotter}</tt></p>
				</div>
			</div>

			<?php if (!@$_GET['hidenav']):	/* don't upset Apple/Google by saying the (relatively other) unspeakable name */ ?>
			<p class="help-block">Get the free HamAlert app for <a href="https://play.google.com/store/apps/details?id=org.hamalert.app">Android</a> or <a href="https://itunes.apple.com/us/app/hamalert/id1200759798?mt=8">iOS</a> to receive alerts on your mobile phone.</p>
			<?php endif; ?>
		</div>
	</fieldset>
	
	<fieldset>
		<legend>Threema</legend>
		<div class="form-group">
			<label for="threemaId">Threema ID</label>
			<input type="text" class="form-control" id="threemaId" name="threemaId" placeholder="ABCDEFGH" style="text-transform: uppercase" value="<?php echo htmlspecialchars($threemaId ?? "") ?>" />
			<?php if (!@$_GET['hidenav']):	/* don't upset Apple/Google by saying the (relatively other) unspeakable name */ ?>
			<p class="help-block">Get the <a href="https://threema.ch/">Threema</a> secure messaging app for Android or iOS. Alerts will be sent from the ID *HAMALRT.</p>
			<?php endif; ?>
		</div>
	
		<?php if (@$threemaMustValidate): ?>
		<div class="form-group has-warning">
			<label class="control-label" for="threemaValidationCode">Threema verification code (sent to you via Threema)</label>
			<input type="text" class="form-control" id="threemaValidationCode" name="threemaValidationCode" placeholder="123456" />
		</div>
		<?php endif; ?>
	</fieldset>
	
	<fieldset>
		<legend>Telnet</legend>
		<div class="form-group">
			<label for="telnetPassword">Telnet password</label>
			<input type="text" class="form-control" id="telnetPassword" name="telnetPassword" value="<?php echo htmlspecialchars($telnetPassword ?? "") ?>">
			<p class="help-block">Choose a password for Telnet login (cluster emulation). It does not have to be very long/secure as it can only be used to receive spots. For security, it should be different from your regular HamAlert password, and you should not use a password that you use for anything else important, as Telnet connections are unencrypted.</p>
		</div>
		<p class="help-block">Simply connect to the following address and log in with your HamAlert username and the password that you have chosen above. You can also use your regular HamAlert password, but this is not recommended as Telnet connections are unencrypted and would thus expose your HamAlert credentials on the Internet.</p>
		<p class="telnethost"><span class="proto">telnet</span> hamalert.org 7300</p>
		<p class="help-block">You will receive alerts from triggers that have the “Telnet” action enabled. The <tt>sh/dx</tt> command is also supported (e.g. <tt>sh/dx 20</tt> to get the last 20 spots). Advanced users who wish to write their own Telnet clients may be interested in the <tt>set/json</tt> command that causes spots to be output as JSON for easier parsing, and the <tt>echo</tt> command that can be used for keepalive.</p>
		<p class="help-block">Windows users may also be interested in the <a href="https://www.oe3ide.com/hamalert-cluster-client/">HamAlert Cluster Client</a> by Ernst OE3IDE.</p>
	</fieldset>
	
	<fieldset>
		<legend>URL notifications</legend>
		<div class="form-group">
			<label for="notificationUrl">URL</label>
			<input type="text" class="form-control" id="notificationUrl" name="notificationUrl" placeholder="https://yourserver.com/notify" value="<?php echo htmlspecialchars($notificationUrl ?? "") ?>" />
			<div style="margin-top: 0.5em">
				<label class="radio-inline">
					<input type="radio" name="notificationMethod" id="notificationMethod_GET" value="GET" <?php if ($notificationMethod == 'GET' || !$notificationMethod) echo "checked" ?> /> GET
				</label>
				<label class="radio-inline">
					<input type="radio" name="notificationMethod" id="notificationMethod_POST" value="POST" <?php if ($notificationMethod == 'POST') echo "checked" ?> /> POST (Form)
				</label>
				<label class="radio-inline">
					<input type="radio" name="notificationMethod" id="notificationMethod_POST-JSON" value="POST-JSON" <?php if ($notificationMethod == 'POST-JSON') echo "checked" ?> /> POST (JSON)
				</label>
			</div>
			<p class="help-block">Use this to send alerts to any URL as a GET or POST request. <a href="#urlparams" onclick="$('#urlParams').modal({})">Parameter list</a></p>
		</div>
	</fieldset>
	
	<hr />
	<button type="submit" class="btn btn-primary">Save</button>
	<small class="text-muted" style="margin-left: 0.8em">Changes may take up to a minute to be applied.</small>
</form>

<div id="urlParams" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">URL notification parameters</h4>
			</div>
			<div class="modal-body">
				<p>GET or POST requests to the notification URL include the following parameters (unknown/irrelevant parameters will be omitted).</p>
				
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>Parameter</th>
							<th>Comments</th>
							<th>Example</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><tt>fullCallsign</tt></td>
							<td>with prefix/suffix</td>
							<td><strong>EA8/HB9DQM/P</strong></td>
						</tr>
						<tr>
							<td><tt>callsign</tt></td>
							<td>without prefix/suffix</td>
							<td><strong>HB9DQM</strong></td>
						</tr>
						<tr>
							<td><tt>frequency</tt></td>
							<td>in MHz</td>
							<td><strong>14.062</strong></td>
						</tr>
						<tr>
							<td><tt>band</tt></td>
							<td>40m, 20m, 2m, 70cm etc.</td>
							<td><strong>20m</strong></td>
						</tr>
						<tr>
							<td><tt>mode</tt></td>
							<td>cw, ssb, fm, dv, am, psk, rtty, jt, msk</td>
							<td><strong>cw</strong></td>
						</tr>
						<tr>
							<td><tt>modeDetail</tt></td>
							<td>For some digimodes, more detailled mode (e.g. jt65, jt9, psk63); otherwise same as <tt>mode</tt></td>
							<td><strong>jt65</strong></td>
						</tr>
						<tr>
							<td><tt>time</tt></td>
							<td>HH:MM (UTC)</td>
							<td><strong>11:47</strong></td>
						</tr>
						<tr>
							<td><tt>dxcc</tt></td>
							<td>DXCC number based on full callsign</td>
							<td><strong>29</strong></td>
						</tr>
						<tr>
							<td><tt>homeDxcc</tt></td>
							<td>DXCC number based on callsign without prefix/suffix</td>
							<td><strong>287</strong></td>
						</tr>
						<tr>
							<td><tt>spotterDxcc</tt></td>
							<td>DXCC number of the spotter</td>
							<td><strong>294</strong></td>
						</tr>
						<tr>
							<td><tt>cq</tt></td>
							<td>CQ zone number, multiple separated by comma</td>
							<td><strong>33</strong></td>
						</tr>
						<tr>
							<td><tt>continent</tt></td>
							<td>EU, AF, AS, NA, SA, OC</td>
							<td><strong>AF</strong></td>
						</tr>
						<tr>
							<td><tt>entity</tt></td>
							<td>DXCC entity based on full callsign</td>
							<td><strong>Canary Is.</strong></td>
						</tr>
						<tr>
							<td><tt>homeEntity</tt></td>
							<td>DXCC entity based on callsign without prefix/suffix</td>
							<td><strong>Switzerland</strong></td>
						</tr>
						<tr>
							<td><tt>spotterEntity</tt></td>
							<td>DXCC entity of the spotter</td>
							<td><strong>Wales</strong></td>
						</tr>
						<tr>
							<td><tt>spotter</tt></td>
							<td>Full spotter callsign</td>
							<td><strong>GW8IZR</strong></td>
						</tr>
						<tr>
							<td><tt>spotterCq</tt></td>
							<td>CQ zone number of the spotter, multiple separated by comma</td>
							<td><strong>14</strong></td>
						</tr>
						<tr>
							<td><tt>spotterContinent</tt></td>
							<td>EU, AF, AS, NA, SA, OC</td>
							<td><strong>EU</strong></td>
						</tr>
						<tr>
							<td><tt>rawText</tt></td>
							<td>Raw spot text, format depends on source</td>
							<td>
								<pre style="width: 25em">DX de GW8IZR-#:  14062.0  EA8/HB9DQM/P   CW    17 dB  23 WPM  CQ      1147Z</pre>
							</td>
						</tr>
						<tr>
							<td><tt>title</tt></td>
							<td>Spot title</td>
							<td><strong>RBN spot EA8/HB9DQM/P (14.062 CW)</strong></td>
						</tr>
						<tr>
							<td><tt>comment</tt></td>
							<td>Spot comment (only for cluster and SOTAwatch)</td>
							<td><strong>Manuel 599 here, tnx QSO!</strong></td>
						</tr>
						<tr>
							<td><tt>source</tt></td>
							<td>Spot source: 'rbn', 'sotawatch', 'cluster', 'pskreporter', 'pota' or 'wwff'</td>
							<td><strong>rbn</strong></td>
						</tr>
						<tr>
							<td><tt>speed</tt></td>
							<td>Speed in WPM (for RBN spots)</td>
							<td><strong>20</strong></td>
						</tr>
						<tr>
							<td><tt>snr</tt></td>
							<td>SNR in dB (for RBN and PSK Reporter spots)</td>
							<td><strong>33</strong></td>
						</tr>
						<tr>
							<td><tt>triggerComment</tt></td>
							<td>Trigger comment, multiple separated by comma</td>
							<td><strong>HB9 operators abroad</strong></td>
						</tr>
						<tr>
							<td><tt>qsl</tt></td>
							<td>QSL methods for callsign: 'eqsl', 'lotw' (according to LoTW and eQSL AG user lists)</td>
							<td><strong>eqsl,lotw</strong></td>
						</tr>
						<tr>
							<td><tt>state</tt></td>
							<td>State of callsign (currently US/CA only, according to government license databases), with ISO prefix</td>
							<td><strong>US_NY</strong></td>
						</tr>
						<tr>
							<td><tt>spotterState</tt></td>
							<td>State of spotter (currently US/CA only, according to government license databases), with ISO prefix</td>
							<td><strong>CA_QC</strong></td>
						</tr>
						<tr>
							<td><tt>iotaGroupRef</tt></td>
							<td>IOTA group reference *</td>
							<td><strong>AF-004</strong></td>
						</tr>
						<tr>
							<td><tt>iotaGroupName</tt></td>
							<td>IOTA group name *</td>
							<td><strong>Canary Islands</strong></td>
						</tr>
						<tr>
							<td><tt>summitName</tt></td>
							<td>SOTA summit name *</td>
							<td><strong>Cruz de Gala</strong></td>
						</tr>
						<tr>
							<td><tt>summitHeight</tt></td>
							<td>SOTA summit height (in m) *</td>
							<td><strong>1343</strong></td>
						</tr>
						<tr>
							<td><tt>summitPoints</tt></td>
							<td>SOTA summit points *</td>
							<td><strong>8</strong></td>
						</tr>
						<tr>
							<td><tt>summitRef</tt></td>
							<td>SOTA summit reference *</td>
							<td><strong>EA8/TF-007</strong></td>
						</tr>
						<tr>
							<td><tt>wwffName</tt></td>
							<td>Park name *</td>
							<td><strong>Parque Natural Jandia</strong></td>
						</tr>
						<tr>
							<td><tt>wwffDivision</tt></td>
							<td>Park division *</td>
							<td><strong>EAFF</strong></td>
						</tr>
						<tr>
							<td><tt>wwffRef</tt></td>
							<td>Park reference *</td>
							<td><strong>EAFF-0065</strong></td>
						</tr>
					</tbody>
				</table>
				
				<p class="help-text">* SOTA summit information is available for spots from SOTAwatch and from the DX cluster (if the cluster spot comment includes a valid SOTA reference). IOTA and WWFF references are extracted from SOTAwatch and DX cluster spot comments; only valid IOTA/WWFF/POTA references according to the relevant directories will be considered.</p>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php include('settings_end.inc.php') ?>
