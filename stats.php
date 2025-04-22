<?php
$loginOptional = true;
require_once("db.inc.php");

$stats = getStats();

$yesterdayStats = makeStatsDisplay($stats[0], false);
if ($stats[1]['alerts']) {
	$todayStats = makeStatsDisplay($stats[1]);
}

function makeStatsDisplay($stats, $showTriggerUsers = true) {
	$res = [
		[
			'value' => array_sum($stats['spots']),
			'label' => 'spots processed'
		],
		[
			'value' => array_sum($stats['alerts']),
			'label' => 'alerts sent'
		],
	];
	
	if ($showTriggerUsers) {
		$res[] = ['line' => true];
		$res[] = [
			'value' => $stats['triggers'],
			'label' => 'triggers defined'
		];
		$res[] = [
			'value' => $stats['users'],
			'label' => 'users registered'
		];
	}
	
	return $res;
}

function printStats($stats) {
	foreach ($stats as $stat) {
		if (@$stat['line']) {
			echo "<hr />\n";
			continue;
		}
		echo '<div class="stat"><span class="statValue">' . number_format($stat['value'], 0, ".", '<span style="font-size:50%;"> </span>') . '</span> <span class="statLabel">' . $stat['label'] . '</span></div>';
	}
}

function printStatsTable($stats) {
	global $config;
	
	// Sort descending
	arsort($stats['spots']);
	arsort($stats['alerts']);
	
	echo <<<EOD
	<table class="table statstable" style="display: none">
		<thead>
			<tr>
				<th>Source</th>
				<th class="val1"># Spots</th>
				<th>Destination</th>
				<th class="val2"># Alerts</th>
			</tr>
		</thead>
		<tbody>

EOD;

	// Fold email 2 stats into email
	if (@$stats['alerts']['email2']) {
		$stats['alerts']['email'] += $stats['alerts']['email2'];
		unset($stats['alerts']['email2']);
	}

	$spotsKeys = array_keys($stats['spots']);
	$alertsKeys = array_keys($stats['alerts']);
	for ($i = 0; $i < count($spotsKeys) || $i < count($alertsKeys); $i++) {
		if (@$spotsKeys[$i]) {
			$spotSource = $config['sources'][$spotsKeys[$i]];
			$spotValue = $stats['spots'][$spotsKeys[$i]];
		} else {
			$spotSource = $spotValue = "";
		}

		if (@$alertsKeys[$i]) {
			$alertAction = $config['actions'][$alertsKeys[$i]];
			$alertValue = $stats['alerts'][$alertsKeys[$i]];
		} else {
			$alertAction = $alertValue = "";
		}
		
		echo "<tr><td>$spotSource</td><td class=\"val1\">$spotValue</td><td>$alertAction</td><td class=\"val2\">$alertValue</td></tr>\n";
	}
	
	echo <<<EOD
		</tbody>
	</table>

EOD;
}

include('settings_begin.inc.php');
?>

<script src="js/moment.min.js"></script>
<script src="js/chart.umd.js"></script>
<script src="js/chartjs-adapter-moment.js"></script>

<script type="text/javascript">	
$(function() {
	let ctx = document.getElementById("statsChart").getContext('2d');
	$.ajax({
		url: '/ajax/spotStats',
		success: function(data, status, xhr) {
			let datasets = [];
			for (let i = 0; i < data.sources.length; i++) {
				datasets.push({
					label: data.sources[i],
					data: data.data[i],
					borderWidth: 1,
					pointRadius: 0,
					//fill: true
				});
			}
			
			// Fix for stacked area transparency color mixing, see https://github.com/chartjs/chart.js/issues/2380#issuecomment-287535063
			datasets[0].fill = 'origin';
			
			let dateLabels = data.xlabels.map(function(dateStr) {
				return moment(dateStr);
			});
			
			let myChart = new Chart(ctx, {
				type: 'line',
				data: {
					labels: dateLabels,
					datasets: datasets
				},
				options: {
					elements: {
						line: {
							fill: '-1' // by default, fill lines to the previous dataset
						}
					},
					tooltips: {
						mode: 'index',
						intersect: false,
						position: 'nearest'
					},
				    interaction: {
				      mode: 'nearest',
				      axis: 'x',
				      intersect: false
				    },
					scales: {
						x: {
							type: 'time',
							time: {
								tooltipFormat: 'll'
							}
						},
						y: {
							stacked: true,
							ticks: {
								callback: function(value, index, ticks) {
									return value.toLocaleString();
								}
							}
						}
					}
				}
			});
		}
	});
});
</script>

<h1 class="page-header">Stats</h1>

<?php if ($todayStats): ?>
<div class="panel panel-primary statsPanel">
	<div class="panel-heading"><h2 class="panel-title">Today <small>UTC, until now</small></h2></div>
	<div class="panel-body">
		<?php printStats($todayStats); ?>
	</div>
	<div class="panel-footer"><button class="btn btn-default" onclick="$(this).parents('div.panel').find('table').show(); $(this).parents('div.panel-footer').hide()">Details</button></div>
	<?php printStatsTable($stats[1]); ?>
</div>
<?php endif; ?>

<div class="panel panel-info statsPanel">
	<div class="panel-heading"><h2 class="panel-title">Yesterday</h2></div>
	<div class="panel-body">
		<?php printStats($yesterdayStats); ?>
	</div>
	<?php printStatsTable($stats[0]); ?>
</div>

<div id="chartPanel" class="panel panel-default">
	<div class="panel-heading"><h2 class="panel-title">Spots per day</h2></div>
	<canvas id="statsChart" height="400"></canvas>
</div>

<div><small>Last update: <?php echo date("Y-m-d H:i e") ?></small></div>

<?php include('settings_end.inc.php') ?>
