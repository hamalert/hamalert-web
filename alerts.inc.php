<?php
require_once(__DIR__ . "/db.inc.php");

function formatTriggerComments($comments) {
	if (!$comments) {
		return '';
	}
	if (is_array($comments)) {
		return implode(', ', $comments);
	}
	return $comments;
}

// Renders a D-STAR reflector identifier (e.g. "REF030-C"), linking it to its
// dstarusers.org page when it's a REF-series reflector (the only series that
// site has pages for; XRF/DCS/XLX are left as plain text). The module suffix
// (e.g. "-C") is stripped from the URL but kept in the displayed text.
function formatDvReflector($reflector) {
	$html = htmlspecialchars($reflector);
	if (preg_match('/^(REF[A-Z0-9]*)(-[A-Z])?$/', strtoupper($reflector), $m)) {
		$url = "https://www.dstarusers.org/viewrepeater.php?system=" . rawurlencode($m[1]);
		return '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener">' . $html . '</a>';
	}
	return $html;
}

function formatSpotDetails($spot) {
	// D-STAR is keyed by mode, not source: source now names the feed (quadnet/ircddb/dstarusers).
	if ($spot['mode'] == 'dstar') {
		// Frequency first like other spots; nothing when the repeater's frequency is unknown
		// (a band guessed from the module letter is only used for matching)
		$details = isset($spot['frequency']) ? htmlspecialchars($spot['frequency'] . " MHz, ") : "";
		if (@$spot['dvEvent'] == 'linked') {
			$details .= "Linked " . htmlspecialchars(@$spot['dvNode']) . " to " . formatDvReflector(@$spot['dvReflector']);
		} else if (@$spot['dvReflector'] && @$spot['dvNode']) {
			$details .= "Active on " . formatDvReflector($spot['dvReflector']) . " via " . htmlspecialchars($spot['dvNode']);
		} else if (@$spot['dvReflector']) {
			// A dstarusers.org reflector-module report (e.g. "REF030-C") has no separate node -
			// the reflector itself is what was heard.
			$details .= "Active on " . formatDvReflector($spot['dvReflector']);
		} else {
			$details .= "Active on " . htmlspecialchars(@$spot['dvNode']);
		}
		if (@$spot['comment']) {
			$details .= ' "' . htmlspecialchars($spot['comment']) . '"';
		}
		if (isset($spot['dvDuration'])) {
			$details .= " (" . htmlspecialchars(number_format($spot['dvDuration'], 1)) . " s)";
		}
		// $details is already HTML (it embeds the reflector link) - return it directly
		// instead of falling through to the htmlspecialchars() escaping below.
		if (@$spot['rawText']) {
			$details .= '<br /><small class="text-muted">' . htmlspecialchars($spot['rawText']) . '</small>';
		}
		return $details;
	} else if (isset($spot['frequency'])) {
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
		$details = @$spot['mode'] ? strtoupper($spot['mode']) : "";
	}

	$html = htmlspecialchars($details);
	if (@$spot['rawText']) {
		$html .= '<br /><small class="text-muted">' . htmlspecialchars($spot['rawText']) . '</small>';
	}
	return $html;
}

// Renders a spot's displayed callsign (e.g. "N4EDO/P") linked to its QRZ.com
// page, keyed on the canonical callsign (e.g. "N4EDO") rather than the
// displayed one.
function formatSpotCallsign($spot) {
	$display = htmlspecialchars(@$spot['fullCallsign']);
	if (!@$spot['callsign']) {
		return $display;
	}
	$url = "https://www.qrz.com/db/" . rawurlencode($spot['callsign']);
	return '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener">' . $display . '</a>';
}

// Renders the "no alerts" info box or the alerts table for the last 100 alerts
// in the last 24 hours. Shared between alerts.php (full page) and
// ajax/alerts.php (auto-refresh fragment).
function renderAlertsTable() {
	global $config;

	$spots = getRecentSpots(86400, 100);

	if (!$spots) {
		?>
<div class="alert alert-info" role="alert">
	No alerts in the last 24 hours. Alerts appear here whenever one of your triggers matches a spot, whatever action it uses.
</div>
		<?php
	} else {
		?>
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
			<td><?php echo formatSpotCallsign($spot) ?></td>
			<td><?php echo formatSpotDetails($spot) ?></td>
			<td><?php echo htmlspecialchars(implode(', ', array_diff(@$spot['actions'] ?: [], ['myspot']))) ?></td>
			<td><?php echo htmlspecialchars(formatTriggerComments(@$spot['triggerComments'])) ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
		<?php
	}
}
