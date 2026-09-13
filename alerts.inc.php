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
		return '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener" onclick="event.stopPropagation()">' . $html . '</a>';
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
	return '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener" onclick="event.stopPropagation()">' . $display . '</a>';
}

// Friendly labels for known spot fields, used by renderSpotDetailTable() below.
// Anything not listed here falls back to its raw field name, so unrecognized/
// future fields still show up rather than being silently dropped.
$GLOBALS['SPOT_FIELD_LABELS'] = [
	'source' => 'Source',
	'time' => 'Time',
	'date' => 'Date',
	'fullCallsign' => 'Callsign',
	'callsign' => 'Canonical callsign',
	'prefix' => 'Prefix',
	'mode' => 'Mode',
	'modeDetail' => 'Mode detail',
	'dvEvent' => 'D-STAR event',
	'dvNode' => 'Repeater/node',
	'dvReflector' => 'Reflector',
	'dvSuffix' => 'Suffix',
	'dvDuration' => 'Duration',
	'dvReflectorSource' => 'Reflector source',
	'spotter' => 'Spotter',
	'spotterPrefix' => 'Spotter prefix',
	'band' => 'Band',
	'bandIsGuessed' => 'Band guessed',
	'frequency' => 'Frequency',
	'frequencySource' => 'Frequency source',
	'comment' => 'Comment / TX message',
	'title' => 'Title',
	'rawText' => 'Raw text',
	'receivedDate' => 'Received',
	'dxcc' => 'DXCC',
	'callsignDxcc' => 'Callsign DXCC',
	'spotterDxcc' => 'Spotter DXCC',
	'state' => 'State',
	'qsl' => 'QSL',
	'actions' => 'Actions',
	'triggerComments' => 'Trigger comments',
	'summitRef' => 'SOTA reference',
	'summitName' => 'SOTA name',
	'summitPoints' => 'SOTA points',
	'summitAssociation' => 'SOTA association',
	'summitRegion' => 'SOTA region',
	'wwffRef' => 'WWFF reference',
	'wwffName' => 'WWFF name',
	'wwffProgram' => 'WWFF program',
	'wwffDivision' => 'WWFF division',
	'iotaGroupRef' => 'IOTA group',
	'speed' => 'Speed',
	'snr' => 'SNR',
];

// True if $arr is a plain sequential list (0, 1, 2, ...) rather than an
// associative array - used to decide how to render a nested array value.
function isListArray($arr) {
	return $arr === [] || array_keys($arr) === range(0, count($arr) - 1);
}

// Renders an arbitrary spot field value (scalar, BSON date/id, or nested
// array/object) as safe HTML. Sequential arrays are joined with ", ";
// associative arrays (nested objects) are rendered as "key: value" lines.
// The result is already HTML-escaped where needed - callers must not
// htmlspecialchars() it again.
function formatSpotFieldValue($value) {
	if ($value instanceof MongoDB\BSON\UTCDateTime) {
		// Same format as the Time column in the overview table.
		return htmlspecialchars($value->toDateTime()->format("Y-m-d H:i:s") . "Z");
	}
	if ($value instanceof MongoDB\BSON\ObjectId) {
		return htmlspecialchars((string)$value);
	}
	if (is_bool($value)) {
		return $value ? 'Yes' : 'No';
	}
	if ($value === null) {
		return '';
	}
	if (is_array($value)) {
		if (isListArray($value)) {
			return implode(', ', array_map('formatSpotFieldValue', $value));
		}
		$lines = [];
		foreach ($value as $k => $v) {
			$lines[] = '<strong>' . htmlspecialchars($k) . ':</strong> ' . formatSpotFieldValue($v);
		}
		return implode('<br />', $lines);
	}
	return htmlspecialchars((string)$value);
}

// Renders a two-column key/value table with every field of $spot (except
// _id and user_id, which are internal), for the expandable detail row on
// the Alerts page. Known fields get a friendly label (see
// $GLOBALS['SPOT_FIELD_LABELS']); unknown fields fall back to their raw
// key so future spot fields still show up here automatically.
function renderSpotDetailTable($spot) {
	global $config;
	$labels = $GLOBALS['SPOT_FIELD_LABELS'];
	$excludedKeys = ['_id', 'user_id'];

	$rows = [];
	foreach ($spot as $key => $value) {
		if (in_array($key, $excludedKeys, true)) {
			continue;
		}

		// dxcc is a nested object - render it as "291 (United States)" plus
		// separate CQ/ITU/continent rows rather than one raw "key: value" blob.
		if ($key === 'dxcc' && is_array($value)) {
			$entity = trim((isset($value['dxcc']) ? $value['dxcc'] : '') . (isset($value['country']) ? ' (' . $value['country'] . ')' : ''));
			if ($entity !== '') {
				$rows[] = ['DXCC', htmlspecialchars($entity)];
			}
			if (isset($value['cq'])) {
				$rows[] = ['CQ zone', formatSpotFieldValue($value['cq'])];
			}
			if (isset($value['itu'])) {
				$rows[] = ['ITU zone', formatSpotFieldValue($value['itu'])];
			}
			if (isset($value['continent'])) {
				$rows[] = ['Continent', formatSpotFieldValue($value['continent'])];
			}
			continue;
		}

		switch ($key) {
			case 'fullCallsign':
				// Reuse the same QRZ-linked rendering as the overview's Callsign column.
				$rows[] = [$labels[$key], formatSpotCallsign($spot)];
				continue 2;
			case 'dvReflector':
				// Reuse the dstarusers.org-linked rendering used in the overview's Details column.
				$rows[] = [$labels[$key], formatDvReflector($value)];
				continue 2;
			case 'actions':
				$rows[] = [$labels[$key], htmlspecialchars(implode(', ', (array)$value))];
				continue 2;
			case 'triggerComments':
				$rows[] = [$labels[$key], htmlspecialchars(formatTriggerComments($value))];
				continue 2;
			case 'dvDuration':
				$rows[] = [$labels[$key], htmlspecialchars(number_format($value, 1) . ' s')];
				continue 2;
			case 'source':
				$rows[] = [$labels[$key], htmlspecialchars($config['sources'][$value] ?? $value)];
				continue 2;
		}

		$label = $labels[$key] ?? $key;
		$rows[] = [$label, formatSpotFieldValue($value)];
	}

	ob_start();
	?>
<table class="table table-condensed alert-detail-table">
	<tbody>
		<?php foreach ($rows as $row): ?>
		<tr>
			<th class="alert-detail-key"><?php echo htmlspecialchars($row[0]) ?></th>
			<td><?php echo $row[1] ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
	<?php
	return ob_get_clean();
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
		<?php foreach ($spots as $spot): $spotId = htmlspecialchars((string)$spot['_id']); ?>
		<tr class="alert-row" data-spotid="<?php echo $spotId ?>" onclick="toggleSpotDetails(this)">
			<td><span class="alert-toggle glyphicon glyphicon-chevron-right" aria-hidden="true"></span> <?php echo htmlspecialchars($spot['receivedDate']->toDateTime()->format("Y-m-d H:i:s") . "Z") ?></td>
			<td><?php echo htmlspecialchars($config['sources'][@$spot['source']] ?? @$spot['source']) ?></td>
			<td><?php echo formatSpotCallsign($spot) ?></td>
			<td><?php echo formatSpotDetails($spot) ?></td>
			<td><?php echo htmlspecialchars(implode(', ', array_diff(@$spot['actions'] ?: [], ['myspot']))) ?></td>
			<td><?php echo htmlspecialchars(formatTriggerComments(@$spot['triggerComments'])) ?></td>
		</tr>
		<tr class="alert-detail-row" data-spotid="<?php echo $spotId ?>" style="display:none">
			<td colspan="6"><?php echo renderSpotDetailTable($spot) ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
		<?php
	}
}
