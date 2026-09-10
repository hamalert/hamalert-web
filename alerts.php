<?php
$pageTitle = 'Alerts';
require_once("db.inc.php");

$spots = getRecentSpots(86400, 100);

function formatTriggerComments($comments) {
	if (!$comments) {
		return '';
	}
	if (is_array($comments)) {
		return implode(', ', $comments);
	}
	return $comments;
}

function formatSpotDetails($spot) {
	if (isset($spot['frequency'])) {
		$details = $spot['frequency'] . " MHz";
		if (@$spot['mode']) {
			$details .= " " . strtoupper($spot['mode']);
		}
		if (@$spot['summitRef']) {
			$details .= ", SOTA " . $spot['summitRef'];
		}
		if (@$spot['wwffRef']) {
			$details .= ", WWFF " . $spot['wwffRef'];
		}
		if (@$spot['iotaGroupRef']) {
			$details .= ", IOTA " . $spot['iotaGroupRef'];
		}
	} else {
		// D-STAR presence spot: no frequency
		if (@$spot['dvEvent'] == 'linked') {
			$details = "Linked " . @$spot['dvNode'] . " to " . @$spot['dvReflector'];
		} else if (@$spot['dvReflector']) {
			$details = "Active on " . $spot['dvReflector'] . " via " . @$spot['dvNode'];
		} else {
			$details = "Active on " . @$spot['dvNode'];
		}
		if (@$spot['comment']) {
			$details .= ' "' . $spot['comment'] . '"';
		}
		if (isset($spot['dvDuration'])) {
			$details .= " (" . number_format($spot['dvDuration'], 1) . " s)";
		}
	}

	$html = htmlspecialchars($details);
	if (@$spot['rawText']) {
		$html .= '<br /><small class="text-muted">' . htmlspecialchars($spot['rawText']) . '</small>';
	}
	return $html;
}

include('settings_begin.inc.php');
?>
<h1 class="page-header">Alerts <small><a href="alerts">refresh</a></small></h1>

<p>The last 100 alerts sent to you in the last 24 hours, across all actions.</p>

<?php if (!$spots): ?>
<div class="alert alert-info" role="alert">
	No alerts in the last 24 hours. Alerts appear here whenever one of your triggers matches a spot, whatever action it uses.
</div>
<?php else: ?>
<div class="table-responsive">
<table class="table table-striped">
	<thead>
		<tr>
			<th>Time</th>
			<th>Source</th>
			<th>Callsign</th>
			<th>Details</th>
			<th>Actions</th>
			<th>Trigger comment</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($spots as $spot): ?>
		<tr>
			<td><?php echo htmlspecialchars($spot['receivedDate']->toDateTime()->format("Y-m-d H:i:s") . "Z") ?></td>
			<td><?php echo htmlspecialchars($config['sources'][@$spot['source']] ?? @$spot['source']) ?></td>
			<td><?php echo htmlspecialchars(@$spot['fullCallsign']) ?></td>
			<td><?php echo formatSpotDetails($spot) ?></td>
			<td><?php echo htmlspecialchars(implode(', ', array_diff(@$spot['actions'] ?: [], ['myspot']))) ?></td>
			<td><?php echo htmlspecialchars(formatTriggerComments(@$spot['triggerComments'])) ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
<?php endif; ?>

<?php include('settings_end.inc.php') ?>
