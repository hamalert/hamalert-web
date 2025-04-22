<?php
require_once("db.inc.php");

$errors = [];
$limits = [
	[
		"name" => "limit",
		"title" => "Overall",
		"checkboxText" => "Limit the number of alerts"
	],
	[
		"name" => "limitPerCallsign",
		"title" => "Per callsign",
		"checkboxText" => "Limit the number of alerts for the same callsign"
	],
	[
		"name" => "limitPerCallsignBandMode",
		"title" => "Per callsign, band and mode",
		"checkboxText" => "Limit the number of alerts for the same callsign, band and mode"
	],
	[
		"name" => "limitPerCallsignFreqMode",
		"title" => "Per callsign, frequency and mode",
		"checkboxText" => "Limit the number of alerts for the same callsign, frequency and mode",
		"help" => "Frequencies are rounded to 1 kHz for comparison.",
		"separateSotaWatch" => true
	]
];

if (!$_SESSION['user']['limit']) {
	$_SESSION['user']['limit'] = [
		'count' => 100,
		'interval' => 3600
	];
}

if (@$_POST['save']) {
	// Force enable overall limit
	$_POST['limit_enable'] = true;
	
	foreach ($limits as $limit) {
		if (@$_POST[$limit['name'] . '_enable']) {
			if (!@$_POST[$limit['name'] . '_count'] || !@$_POST[$limit['name'] . '_interval'] || !preg_match("/^\d+$/", $_POST[$limit['name'] . '_count']) || !preg_match("/^\d+$/", $_POST[$limit['name'] . '_interval'])) {
				$errors[] = "Limits must be specified with integer values.";
			} else if ($_POST[$limit['name'] . '_interval'] < 1) {
				$errors[] = "The minimum allowed interval is one minute.";
			} else if ($_POST[$limit['name'] . '_interval'] > 3600) {
				$errors[] = "The maximum allowed interval is one day (3600 minutes).";
			} else if ($limit['name'] == 'limit' && (($_POST['limit_count'] * 60 / $_POST['limit_interval']) > 100)) {
				$errors[] = "The overall limit cannot be disabled, and must be set to at most 100 messages per hour.";
				$_SESSION['user']['limit'] = [
					'count' => 100,
					'interval' => 3600
				];
			} else {
				setLimit($limit['name'], (int)$_POST[$limit['name'] . '_count'], (int)$_POST[$limit['name'] . '_interval']*60);
			}
		} else {
			deleteLimit($limit['name']);
		}
	}
	setLimitSeparateSotaWatch(@$_POST['limitSeparateSotaWatch']);
	if (!$errors) {
		$updateOk = true;
	}
}

?>
<?php include('settings_begin.inc.php') ?>
<h1 class="page-header">Limits</h1>

<div class="alert alert-info" role="alert">
	Use this page to limit the number of alerts that you receive. It is recommended to at least limit the number
	of alerts for the same callsign, frequency and mode to avoid receiving duplicate alerts. All enabled limits are applied;
	i.e. if any of the enabled limits are exceeded for a given spot, no alert will be sent.
</div>

<?php foreach ($errors as $error): ?>
<div class="alert alert-danger" role="alert">
	<?php echo $error ?>
</div>
<?php endforeach; ?>

<?php if (@$updateOk): ?>
<div class="alert alert-success alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	The limits have been updated successfully.
</div>
<?php endif; ?>

<script type="text/javascript">
var limits = [];
function limit_change() {
	limits.forEach(function(element) {
		enableDisableLimit(element);
	});
}

function enableDisableLimit(limitName) {
	var limit_disabled = !$('#' + limitName + '_enable').prop('checked');
	$('#' + limitName + '_count').prop('disabled', limit_disabled);
	$('#' + limitName + '_interval').prop('disabled', limit_disabled);
	
	if (limit_disabled) {
		$('#' + limitName + '_count').val('');
		$('#' + limitName + '_interval').val('');
	}
}

function resetLimitExceededCount() {
	var l = Ladda.create(document.querySelector('#resetLimitExceededCountBtn'));
	l.start();
	$.post('ajax/reset_limit_exceeded_count')
		.done(function() {
			updateLimitExceededCount();
		})
		.always(function() {
			l.stop();
		});
}

function updateLimitExceededCount() {
	$.get('ajax/limit_exceeded_count')
		.done(function(data) {
			$('#limitExceededCount').text(data.limitExceededCount);
			if (data.limitExceededCount > 0) {
				$('#limitExceededCount').addClass('label-warning');
				$('#limitExceededCount').removeClass('label-success');
			} else {
				$('#limitExceededCount').addClass('label-success');
				$('#limitExceededCount').removeClass('label-warning');
			}
			
			if (data.limitExceededCountSince) {
				var date = new Date(parseInt(data.limitExceededCountSince));
				$('#limitExceededCountSince').text('Last reset: ' + date.toLocaleString());
				$('#limitExceededCountSince').show();
			} else {
				$('#limitExceededCountSince').hide();
			}
		});
}

$(function() {
	limit_change();
	updateLimitExceededCount();
});
</script>

<form method="post" class="form-inline limitForm">
<?php

foreach ($limits as $limit) {
	printLimitPanel($limit['name'], $limit['title'], $limit['checkboxText'], @$limit['help'], @$limit['separateSotaWatch']);
}

function printLimitPanel($limitName, $title, $checkboxText, $help, $separateSotaWatch) {
	?>
	<script type="text/javascript">
	limits.push('<?php echo $limitName ?>');
	</script>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo $title ?></h3>
		</div>
		<div class="panel-body row">
			<div class="<?php if ($limitName == "limit") echo 'col-md-8'; else echo 'col-md-12'; ?>">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="<?php echo $limitName ?>_enable" name="<?php echo $limitName ?>_enable" onchange="limit_change()" <?php if (@$_SESSION['user'][$limitName]) echo "checked" ?> <?php if ($limitName == "limit") echo "disabled"; ?> /> <?php echo $checkboxText ?>
				</label>
			</div>
			<div>
				<input type="number" class="form-control" id="<?php echo $limitName ?>_count" name="<?php echo $limitName ?>_count" size="3" value="<?php echo @$_SESSION['user'][$limitName]['count'] ?>" /> per
				<input type="number" class="form-control" id="<?php echo $limitName ?>_interval" name="<?php echo $limitName ?>_interval"  value="<?php echo @$_SESSION['user'][$limitName]['interval']/60 ?>" /> minute(s)
				<?php if ($limitName == "limit"): ?>
				<p class="help-block">The overall limit cannot be disabled, and must be set to at most 100 messages per hour.</p>
				<?php endif; ?>
			</div>
			<?php if ($help): ?>
			<p class="help-block"><?php echo $help ?></p>
			<?php endif; ?>
			<?php if ($separateSotaWatch): ?>
			<hr />
			<div class="checkbox">
				<label>
					<input type="checkbox" id="limitSeparateSotaWatch" name="limitSeparateSotaWatch" <?php if (@$_SESSION['user']['limitSeparateSotaWatch']) echo "checked" ?> /> Treat SOTAwatch spots separately
				</label>
			<p class="help-block">This option counts SOTAwatch spots separately from other spots. This allows you to receive SOTAwatch spots even if you have received an RBN or cluster spot for the same callsign, frequency and mode before. The other limits still apply.</p>
			</div>
			<?php endif; ?>
			</div>
			
			<?php if ($limitName == "limit"): ?>
			<div class="col-md-4">
				<p style="margin-bottom: 0">Number of times exceeded: <span id="limitExceededCount" class="label label-primary"></span>
				<button type="button" class="btn btn-primary btn-xs ladda-button" data-style="zoom-in" id="resetLimitExceededCountBtn" onclick="resetLimitExceededCount()"><span class="ladda-label">Reset counter</span></button>
				<br /><span id="limitExceededCountSince"></span>
				</p>
			</div>
			<?php endif; ?>
		</div>
	</div>
<?php
}

?>
	
	<button type="submit" name="save" value="1" class="btn btn-primary">Save</button>
	<small class="text-muted" style="margin-left: 0.8em">Changes may take up to a minute to be applied.</small>
</form>

<?php include('settings_end.inc.php') ?>
