<?php $initialScale = 0.6; include('settings_begin.inc.php') ?>
<?php

refreshUser();	// app tokens could have changed externally

$actionStatus = array(
	'threema' => (@$_SESSION['user']['threemaId']) ? true : false,
	'url' => (@$_SESSION['user']['notificationUrl']) ? true : false,
	'app' => (@$_SESSION['user']['appTokens'] && count($_SESSION['user']['appTokens']) > 0) ? true : false
);

?>
<script src="js/jquery.csv.min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/triggers.js?v=<?php echo filemtime(__DIR__ . "/js/triggers.js") ?>" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">

var triggersById = {};
var actionStatus = <?php echo json_encode($actionStatus) ?>;
var maxTriggers = <?php echo json_encode($config['max_triggers']) ?>;

$(function() {	
	reloadTriggers();
	
	$.views.tags("cond", conditionLabel);
	$.views.tags("condVal", conditionValue);
	$.views.tags("actions", actionsList);
	$.views.helpers("conditionsOrder", conditionsOrder);
	$.views.helpers("conditionsGroups", conditionsGroups);
	$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
		if (jqxhr.status == 401) {
			document.location.href = '/login';
			return;
		}
		
		if (thrownError)
			alert("An AJAX error has occurred. Try reloading the page.\nError: " + thrownError + ", " + jqxhr.responseText);
	});
});

function reloadTriggers() {
	triggersById = {};
	$.get({
		url: 'ajax/triggers',
		cache: false,
		success: function(triggers) {
			let numTotal = 0, numActive = 0, numPaused = 0;
			triggers.forEach(function(trigger) {
				triggersById[trigger._id] = trigger;
				if (trigger.disabled) {
					numPaused++;
				} else {
					numActive++;
				}
				numTotal++;
			});
			
			var html = $.templates('#triggerBodyTmpl').render({triggers: triggers});
			$('#triggersBody').html(html);
			$('table.triggers').show();
			if (triggers.length > 0) {
				$('#app-store-info').hide();
			} else {
				$('#app-store-info').show();
			}
			$('#triggerSummary').text(numTotal + ' total, ' + numActive + ' active, ' + numPaused + ' paused');
		}
	});
}

function addTrigger() {
	if (Object.keys(triggersById).length >= maxTriggers) {
		alert("You already have " + maxTriggers + " triggers; unfortunately you cannot add any more. Please delete one or more triggers and try again.");
		return;
	}
	startTriggerEditor({conditions: {}, comment: "", actions: ['app']});
}

function editTrigger(button) {
	var id = $(button).parents('tr.trigger').data('id');
	var trigger = triggersById[id];
	
	// start trigger editor on a copy of the trigger object
	startTriggerEditor($.extend(true, {}, trigger));
}

function cloneTrigger(button) {
	var id = $(button).parents('tr.trigger').data('id');
	var trigger = triggersById[id];
	
	// start trigger editor on a copy of the trigger object
	var clonedTrigger = $.extend(true, {}, trigger);
	
	// remove ID so we get a new one
	delete clonedTrigger._id;
	startTriggerEditor(clonedTrigger, true);
}

function deleteTrigger(button) {
	var tr = $(button).parents('tr.trigger');
	var id = tr.data('id');
	var comment = triggersById[id].comment;
	tr.addClass('info');
	BootstrapDialog.show({
		title: 'Delete trigger?',
		message: 'Are you sure you want to delete the trigger "' + comment + '"?',
		type: BootstrapDialog.TYPE_DANGER,
		buttons: [{
			label: 'Cancel',
			cssClass: 'btn-default',
			action: function(dialog) {
				dialog.close();
			}
		}, {
			label: 'Delete',
			cssClass: 'btn-primary btn-danger',
			action: function(dialog) {
				$.post({
					url: 'ajax/trigger_delete',
					data: {id: id},
					success: function(triggers) {
						reloadTriggers();
						dialog.close();
					}
				});
			}
		}],
		onhide: function(dialog) {
			tr.removeClass('info');
		}
	});
}

function disableTrigger(button, disabled) {
	var id = $(button).parents('tr.trigger').data('id');
	
	$.post({
		url: 'ajax/trigger_disable',
		data: {id: id, disabled: disabled ? 1 : 0},
		success: function(triggers) {
			reloadTriggers();
		}
	});
}
</script>

<h1 class="page-header">Triggers <span id="triggerSummary"></span></h1>

<?php if (@$_SESSION['user']['clublog']['invalid']): ?>
<div class="alert alert-warning" role="alert">
	The Club Log credentials that you have entered are invalid. Club Log updates have been disabled.
	Please set your Club Log email and password again on the <a href="/account">account page</a> to restart them.
</div>
<?php endif; ?>

<?php /*
<div class="alert alert-warning alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<p>The PSK Reporter and RBN sources currently ignore FT4/FT8 spots on HF between 80-10m for the <a href="https://clublog.org/mostwanted.php">30 least wanted DXCCs</a> due to spot overload in the backend. <a href="news">More info</a></p>
</div>
*/ ?>

<div id="app-store-info" class="alert alert-info" style="display: none" role="info">
	<p>Download the free HamAlert app on your smartphone in order to receive notifications about spots.</p>
	<p><a href="https://itunes.apple.com/us/app/hamalert/id1200759798?mt=8"><img alt="Download on the App Store" src="/images/app_store.svg" style="height: 40px; margin-right: 12px" /></a>
	<a href="https://play.google.com/store/apps/details?id=org.hamalert.app&hl=en&gl=US"><img alt="Get it on Google Play" src="/images/google-play-badge.png" style="height: 40px" /></a></p>
</div>

<table class="table table-striped table-hover table-bordered triggers" style="display: none">
	<thead>
		<tr>
			<th>Conditions</th>
			<th>Actions</th>
			<th>Comment</th>
			<th><button type="button" class="btn btn-default" onclick="addTrigger()"><span class="glyphicon glyphicon-plus"></span></button></th>
		</tr>
	</thead>
	<tbody id="triggersBody">
		
	</tbody>
</table>

<script id="triggerBodyTmpl" type="text/x-jsrender">
	{{if triggers.length == 0}}
	<tr class="trigger">
		<td class="notriggertext" colspan="4">You don't have any triggers yet. Add a trigger to start receiving alerts.</td>
	</tr>
	{{else}}
	{{for triggers}}
	<tr class="trigger {{if disabled}}trigger-disabled{{/if}} {{if useless}}trigger-useless{{/if}}" data-id="{{>_id}}">
		<td>
			<table class="table table-bordered table-condensed conditionsTable" style="width: auto">
				{{for ~conditionsOrder ~conditions=conditions}}
				{{if ~conditions[#data] !== undefined}}
				<tr>
					<th>{{cond #data /}}</th>
					<td>
						{{if #data === 'timeFrom'}}
						{{condVal timeFrom ~conditions.timeFrom /}}Z – {{condVal timeTo ~conditions.timeTo /}}Z
						{{else #data === 'speedFrom'}}
						{{condVal speedFrom ~conditions.speedFrom /}} … {{condVal speedTo ~conditions.speedTo /}} WPM
						{{else #data === 'snrFrom'}}
						{{condVal snrFrom ~conditions.snrFrom /}} … {{condVal snrTo ~conditions.snrTo /}} dB
						{{else #data === 'summitPointsFrom'}}
						{{condVal summitPointsFrom ~conditions.summitPointsFrom /}} … {{condVal summitPointsTo ~conditions.summitPointsTo /}} pt
						{{else #data === 'summitActivationsFrom'}}
						{{condVal summitActivationsFrom ~conditions.summitActivationsFrom /}} … {{condVal summitActivationsTo ~conditions.summitActivationsTo /}} act.
						{{else}}
						{{condVal #data ~conditions[#data] /}}
						{{/if}}
					</td>
				</tr>
				{{/if}}
				{{/for}}
			</table>
		</td>
		<td>
			{{actions actions /}}
		</td>
		<td>{{>comment}}{{if useless}}
			<div class="alert alert-warning alert-useless" role="alert">
				<span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span> <strong>Ambiguous trigger</strong>
				<p>This trigger has been automatically disabled because it matched more than 10000 spots per day.
					Please make it more specific by adding or refining conditions. <a href="/help#trigger_auto_disable">More info</a></p>
			</div>
		{{/if}}</td>
		<td class="trigger-buttons">
			<div class="btn-group" role="group">
				<button type="button" class="btn btn-default" title="edit" onclick="editTrigger(this)"><span class="glyphicon glyphicon-pencil"></span></button>
				<button type="button" class="btn btn-default" title="clone" onclick="cloneTrigger(this)"><span class="glyphicon glyphicon-duplicate"></span></button>
				{{if disabled}}
				<button type="button" class="btn btn-default" title="disabled - click to enable" onclick="disableTrigger(this, false)"><span class="glyphicon glyphicon-play"></span></button>
				{{else}}
				<button type="button" class="btn btn-default" title="enabled - click to disable" onclick="disableTrigger(this, true)"><span class="glyphicon glyphicon-pause" style="color: #1b82e2"></span></button>
				{{/if}}
				<button type="button" class="btn btn-default" title="delete" onclick="deleteTrigger(this)"><span class="glyphicon glyphicon-trash"></span></button>
			</div>
		</td>
	</tr>
	{{/for}}
	{{/if}}
</script>

<?php include("trigger_editor.inc.php") ?>

<small class="text-muted">Changes may take up to a minute to be applied.</small>

<?php include('settings_end.inc.php') ?>
