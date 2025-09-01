<?php
$pageTitle = 'Simulate';
include('settings_begin.inc.php') ?>

<script src="js/triggers.js?v=<?php echo filemtime(__DIR__ . "/js/triggers.js") ?>" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
	$(function() {
		// Load modes and sources
		$('#modeTd').append(makeDynamicSelect('mode', modes));
		$('#sourceTd').append(makeDynamicSelect('source', sources));
		
		$('.selectpicker').selectpicker('render');
		$('input').keydown(function() {
			changeSpot();
		});
		$('select').change(function() {
			changeSpot();
		});
		
		$(document).keypress(function(e) {
			if (e.which == 13) {
				sendSpot();
			}
		});
	});
	
	function changeSpot() {
		$('#spotSent').hide();
	}
	
	function sendSpot() {
		$('#spotSent').hide();
		$('#spotAlert').hide();
		
		var spot = {
			fullCallsign: $('#fullCallsign').val(),
			frequency: $('#frequency').val(),
			summitRef: $('#summitRef').val(),
			mode: $('#mode').val(),
			source: $('#source').val(),
			spotter: $('#spotter').val(),
			comment: $('#comment').val()
		};
		
		if (!spot.fullCallsign || !spot.frequency || !spot.mode || !spot.source || !spot.spotter) {
			$('#fillInFieldsAlert').show();
			return;
		}
		
		$('#fillInFieldsAlert').hide();
		
		$.post({
			url: 'ajax/simulateSpot',
			data: JSON.stringify({spot: spot}),
			contentType: "application/json",
			success: function(response) {
				if (response.success) {
					$('#spotSent').show();
				} else {
					var errorsHtml = response.errors.map(function(error) {
						return "<li>" + htmlEscape(error) + "</li>";
					});
					
					$('#spotAlert').html('Could not send spot. Errors: <ul>' + errorsHtml.join("") + "</ul>");
					$('#spotAlert').show();
				}
			},
			error: function(xhr, textStatus, errorThrown) {
				alert('Sending spot failed. Error: ' + xhr.responseText);
			}
		});
	}
	
	function makeDynamicSelect(name, map) {
		var select = $('<select>', {
			id: name,
			class: 'selectpicker',
			'data-width': 'auto',
			'data-live-search': 'true'
		});
		select.append($('<option>', {
			text: "",
			value: ""
		}));
		$.each(map, function(key, value) {
			select.append($('<option>', {
				text: value,
				value: key
			}));
		});
		return select;
	}
</script>
	
<style type="text/css" media="screen">
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    /* display: none; <- Crashes Chrome on hover */
    -webkit-appearance: none;
    margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
}
</style>

<h1 class="page-header">Simulate</h1>

<div class="alert alert-info" role="alert">
	Use this page to simulate spots and test your triggers and alert destinations.
	Spots sent from this page will be processed according to your triggers, and alerts will be sent.
	Limits do not apply for simulated spots, and alerts will only be sent to you and not to other users.
</div>

<div id="spotSent" class="alert alert-success" role="alert" style="display: none">
	Spot sent successfully.
</div>

<div id="fillInFieldsAlert" class="alert alert-danger" role="alert" style="display: none">
	Please fill in all required fields.
</div>

<div id="spotAlert" class="alert alert-danger" role="alert" style="display: none"></div>

<form id="simulateForm">
	<table class="table table-rounded table-condensed table-responsive editorConditionsTable">
		<tbody>
			<tr>
				<th>Full callsign</th>
				<td><input type="text" class="form-control" id="fullCallsign" placeholder="Full callsign" /></td>
			</tr>
			<tr>
				<th>Frequency</th>
				<td>
					<div class="input-group">
						<input type="number" step="any" class="form-control" id="frequency" placeholder="14.060" />
						<div class="input-group-addon">MHz</div>
					</div>
				</td>
			</tr>
			<tr>
				<th>Summit reference</th>
				<td><input type="text" class="form-control" id="summitRef" placeholder="XX/YY-000" style="text-transform: uppercase" />
				<p class="help-block">optional</p></td>
			</tr>
			<tr>
				<th>Mode</th>
				<td id="modeTd"></td>
			</tr>
			<tr>
				<th>Source</th>
				<td id="sourceTd"></td>
			</tr>
			<tr>
				<th>Spotter</th>
				<td><input type="text" class="form-control" id="spotter" placeholder="Spotter" /></td>
			</tr>
			<tr>
				<th>Comment</th>
				<td><input type="text" class="form-control" id="comment" placeholder="" />
				<p class="help-block">The comment field is parsed for WWFF, POTA and SOTA references.</p></td>
			</tr>
		</tbody>
	</table>
	
	<button type="button" class="btn btn-primary" onclick="sendSpot(); return false" style="margin-top: 1em">Send</button>
</form>

<?php include('settings_end.inc.php') ?>
