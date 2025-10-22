<script type="text/javascript">
	var username = '<?php echo $_SESSION['user']['username'] ?>';
	var haveClublogInfo = <?php if (@$_SESSION['user']['clublog']) echo "true"; else echo "false"; ?>;
</script>
	
<script src="js/trigger_editor.js?v=<?php echo filemtime(__DIR__ . "/js/trigger_editor.js") ?>" type="text/javascript" charset="utf-8"></script>

<script id="editorDialogTmpl" type="text/x-jsrender">
<div id="editorDialog" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit trigger</h4>
			</div>
			<div class="modal-body">
				<div id="disabledWarning" style="display: none" class="alert alert-info" role="alert">
				 	<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
					This trigger is currently disabled.
				</div>
				<div id="uselessWarning" style="display: none" class="alert alert-warning" role="alert">
				 	<span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
					This trigger has been automatically disabled because it matched more than 10000 spots per day.
					Please make it more specific by adding or refining conditions. <a href="/help#trigger_auto_disable">More info</a>
				</div>
				<div id="noActionsWarning" style="display: none" class="alert alert-info" role="alert">
				 	<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
					No actions are currently enabled for this trigger, so no alerts will be sent.
				</div>
				<div id="noClublogWarning" style="display: none" class="alert alert-warning" role="alert">
				 	<span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
					You need to set up your Club Log login details on the <a href="account">Account</a> page for band slot conditions to work.
				</div>
				
				<div>
					<label>Conditions</label>
					<table class="table table-rounded table-condensed editorConditionsTable" style="width: auto">
						<tbody>
						</tbody>
					</table>
					<p class="help-block marginbot">All conditions must match for this trigger to be executed.</p>
					<label>Actions</label>
					<div id="actionsGroup" class="form-group">
						{{props actions}}
						<label class="checkbox-inline">
						  <input type="checkbox" id="action_{{>key}}" name="action_{{>key}}" onchange="checkActionWarnings()" /> <label for="action_{{>key}}">{{:prop}}</label>
						</label>
						{{/props}}
						
						<div id="actionWarning_app" style="display: none" class="alert alert-warning" role="alert">
							<p><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span> You need to install the HamAlert app and sign in within the
								app in order to receive alerts on your mobile device.</p>
							<p>Download the HamAlert app for <a href="https://play.google.com/store/apps/details?id=org.hamalert.app">Android</a> or <a href="https://itunes.apple.com/us/app/hamalert/id1200759798?mt=8">iOS</a>.</p>
						</div>
						<div id="actionWarning_threema" style="display: none" class="alert alert-warning" role="alert">
						 	<span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
							You must enter a Threema ID on the <a href="destinations">Destinations</a> page in order to receive alerts via Threema.
						</div>
						<div id="actionWarning_url" style="display: none" class="alert alert-warning" role="alert">
						 	<span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
							You must enter a notification URL on the <a href="destinations">Destinations</a> page in order to receive alerts via URL.
						</div>
					</div>
					<div class="form-group">
						<label for="comment">Comment</label>
						<input type="text" class="form-control" id="comment" placeholder="Comment" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<small class="text-muted" style="margin-right: 0.8em">Changes may take up to a minute to be applied.</small>
				<button type="button" class="btn btn-default" onclick="cancelEditor()">Cancel</button>
				<button type="button" id="saveButton" class="btn btn-primary ladda-button" data-style="zoom-in" onclick="saveTrigger()">Save</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</script>

<script id="editorConditionsTmpl" type="text/x-jsrender">
	{{for ~conditionsOrder ~conditions=conditions}}
	{{if ~conditions[#data] !== undefined}}
	<tr id="condrow_{{:#data}}">
		<th>{{cond #data /}}</th>
		<td>{{condEditor #data /}}</td>
		<td class="actionbuttons">
			<div class="btn-group btn-group-sm" role="group">
				<button type="button" class="btn btn-default" onclick="deleteCondition('{{:#data}}')"><span class="glyphicon glyphicon-trash"></span></button>
			</div>
		</td>
	</tr>
	{{/if}}
	{{/for}}
	<tr class="addcondrow">
		<th colspan="3">
			<select class="form-control selectpicker" data-title="Add a condition" data-width="auto" data-live-search="true" id="newCondition" onchange="addCondition()">
				{{for ~conditionsGroups ~conditions=conditions}}
				<optgroup label="{{:#data.title}}">
				{{for #data.conditions ~conditions=~conditions}}
				{{if (~conditions[#data] === undefined) && (#data !== 'callsign' || ~conditions['fullCallsign'] === undefined) && (#data !== 'fullCallsign' || ~conditions['callsign'] === undefined)}}
				<option value="{{>#data}}">{{cond #data /}}</option>
				{{/if}}
				{{/for}}
				</optgroup>
				{{/for}}
			</select>
		</th>
	</tr>
</script>

<script id="conditionValuePickerTmpl" type="text/x-jsrender">
	<select class="form-control selectpicker" data-width="auto" data-live-search="true" data-show-subtext="true" data-actions-box="true" id="condition_{{:conditionName}}" onchange="updateConditionValue('{{:conditionName}}', $(this).val())" {{if ~arrayConditions[conditionName]}}multiple data-selected-text-format="count &gt; 2"{{/if}}>
		{{if ~arrayConditions[conditionName]}}
		{{else}}
		<option value=""></option>
		{{/if}}
		{{for keyValueList ~selectedKeys=selectedKeys}}
		{{if #data[0] == '---'}}
		<option value="" data-divider="true"></option>
		{{else}}
		<option value="{{:#data[0]}}" {{if ~selectedKeys[#data[0]]}}selected{{/if}} {{if #data[2]}}data-subtext="{{>#data[2]}}"{{/if}}>{{>#data[1]}}</option>
		{{/if}}
		{{/for}}
	</select>
	{{if conditionName == "iotaGroupRef"}}
	<div style="margin-top: 0.5em">
		<input type="file" id="iotaCsvFile" onchange="loadIotaCsv()" />
	</div>
	<p class="help-block">Download a QSO CSV file from your account at <a href="https://www.iota-world.org/my-application/download-qsoss.html">iota-world.org</a> and upload it here to automatically select all IOTA references that you have not worked yet.</p>
	{{/if}}
	{{if ~conditionHelpTexts[conditionName]}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName]}}</p>
	{{/if}}
	{{if conditionName == "dxcc"}}
	{{if haveClublogInfo}}
	<a href="#" id="showDxccClublogFormLink" onclick="showDxccClublogForm(); return false">▸ Load from Club Log</a>
	<div style="display: none; margin-top: 1em" class="clublogForm" id="dxccClublogForm">
		<div class="form-group">
			<label for="clublogCallsign">Callsign</label>
			<input type="text" class="form-control" id="dxccClublogCallsign" placeholder="Callsign" value="{{>dxccClublogCallsign}}" />
		</div>
		
		<label>Modes</label>
		<div class="form-group">
			{{props clublogModes ~options=options}}
			<label class="radio-inline">
				<input type="radio" name="dxccClublogModes" id="dxccClublogModes_{{>key}}" value="{{>key}}" {{if key == "all"}}checked="checked"{{/if}}> <label class="normal" for="dxccClublogModes_{{>key}}">{{>prop}}</label>
			</label>
			{{/props}}
		</div>
		
		<label>QSL status not passing</label>
		<div class="form-group">
			{{props clublogQslStatus ~options=options}}
			<label class="checkbox-inline">
				<input type="checkbox" name="dxccClublogStatus" id="dxccClublogStatus_{{>key}}" value="{{>key}}" checked="checked"> <label class="normal" for="dxccClublogStatus_{{>key}}">{{>prop}}</label>
			</label>
			{{/props}}
		</div>
		
		<label>Date filter</label>
		<div class="form-group">
			{{props clublogDateFilters ~options=options}}
			<label class="radio-inline">
				<input type="radio" name="dxccClublogDateFilter" id="dxccClublogDateFilter_{{>key}}" value="{{>key}}" {{if key == "0"}}checked="checked"{{/if}}> <label class="normal" for="dxccClublogDateFilter_{{>key}}">{{>prop}}</label>
			</label>
			{{/props}}
		</div>
		
		<button id="loadDxccsClublogBtn" class="btn btn-info ladda-button" data-style="zoom-in" type="button" onclick="loadDxccsClublog()"><span class="ladda-label">Load unworked DXCCs from Club Log</span></button>

		<p class="help-block">This is a manual one-time load; if you work new DXCCs after loading the list, this will not be reflected here automatically.</p>
	</div>
	{{else}}
	<p class="help-block">Add your Club Log login details on the <a href="account">Account</a> page to load missing DXCCs from Club Log.</p>
	{{/if}}
	{{/if}}
</script>

<script id="conditionTextInputTmpl" type="text/x-jsrender">
	<div class="form-inline">
	<input type="text" class="form-control" id="{{:conditionName}}" placeholder="{{cond conditionName /}}" value="{{>value}}" onchange="updateConditionValue('{{:conditionName}}', $(this).val())" />
	{{if ~arrayConditions[conditionName]}}
	<button class="btn btn-default" onclick="switchToArray('{{:conditionName}}')">List</button>
	{{/if}}
	{{if ~conditionHelpTexts[conditionName]}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName]}}</p>
	{{/if}}
	</div>
</script>

<script id="conditionTextAreaTmpl" type="text/x-jsrender">
	<textarea class="form-control" id="{{:conditionName}}" placeholder="{{cond conditionName /}}" rows="5" onchange="updateConditionValue('{{:conditionName}}', $(this).val())">{{>value}}</textarea>
	{{if ~arrayConditions[conditionName] && ~conditionHelpTexts[conditionName + '_array']}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName + '_array']}}</p>
	{{else ~conditionHelpTexts[conditionName]}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName]}}</p>
	{{/if}}
	{{if conditionName == "summitRefs"}}
	<div>
		<button style="margin-top: 1em" id="loadSotaCompleteCandidatesBtn" class="btn btn-info ladda-button" data-style="zoom-in" type="button" onclick="loadSotaCompleteCandidates()" data-toggle="tooltip" data-placement="right" title="Click here to load a list of summits that you have activated, but not chased, from the SOTA database"><span class="ladda-label">Load SOTA Complete candidates</span></button>
		<label style="margin-left: 1em"><input type="checkbox" id="autoUpdateSotaCompleteCandidates" value="1" onchange="updateAutoUpdateSotaCompleteCandidates()" {{if options.autoUpdateSotaCompleteCandidates}}checked="checked"{{/if}} /> Auto-update daily (around midnight UTC)</label>
	</div>
	{{/if}}
</script>

<script id="conditionTimeInputTmpl" type="text/x-jsrender">
	<div class="form-inline">
		<input type="text" class="form-control timeInput" id="{{:conditionName1}}" placeholder="00:00" value="{{>value1}}" onchange="updateConditionValue('{{:conditionName1}}', $(this).val())" /> –
		<input type="text" class="form-control timeInput" id="{{:conditionName2}}" placeholder="00:00" value="{{>value2}}" onchange="updateConditionValue('{{:conditionName2}}', $(this).val())" />
	</div>
	{{if ~conditionHelpTexts[conditionName1]}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName1]}}</p>
	{{/if}}
</script>

<script id="conditionSpeedInputTmpl" type="text/x-jsrender">
	<div class="form-inline">
		<input type="number" class="form-control speedInput" id="{{:conditionName1}}" placeholder="20" value="{{>value1}}" onchange="updateConditionValue('{{:conditionName1}}', $(this).val())" /> –
		<input type="number" class="form-control speedInput" id="{{:conditionName2}}" placeholder="30" value="{{>value2}}" onchange="updateConditionValue('{{:conditionName2}}', $(this).val())" />
		WPM
	</div>
	{{if ~conditionHelpTexts[conditionName1]}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName1]}}</p>
	{{/if}}
</script>

<script id="conditionSnrInputTmpl" type="text/x-jsrender">
	<div class="form-inline">
		<input type="number" class="form-control snrInput" id="{{:conditionName1}}" placeholder="6" value="{{>value1}}" onchange="updateConditionValue('{{:conditionName1}}', $(this).val())" /> –
		<input type="number" class="form-control snrInput" id="{{:conditionName2}}" placeholder="60" value="{{>value2}}" onchange="updateConditionValue('{{:conditionName2}}', $(this).val())" />
		dB
	</div>
	{{if ~conditionHelpTexts[conditionName1]}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName1]}}</p>
	{{/if}}
</script>

<script id="conditionSummitPointsInputTmpl" type="text/x-jsrender">
	<div class="form-inline">
		<input type="number" class="form-control summitPointsInput" id="{{:conditionName1}}" min="1" max="10" placeholder="1" value="{{>value1}}" onchange="updateConditionValue('{{:conditionName1}}', $(this).val())" /> –
		<input type="number" class="form-control summitPointsInput" id="{{:conditionName2}}" min="1" max="10" placeholder="10" value="{{>value2}}" onchange="updateConditionValue('{{:conditionName2}}', $(this).val())" />
		pt
	</div>
	{{if ~conditionHelpTexts[conditionName1]}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName1]}}</p>
	{{/if}}
</script>

<script id="conditionSummitActivationsInputTmpl" type="text/x-jsrender">
	<div class="form-inline">
		<input type="number" class="form-control summitActivationsInput" id="{{:conditionName1}}" min="0" placeholder="0" value="{{>value1}}" onchange="updateConditionValue('{{:conditionName1}}', $(this).val())" /> –
		<input type="number" class="form-control summitActivationsInput" id="{{:conditionName2}}" min="0" placeholder="100" value="{{>value2}}" onchange="updateConditionValue('{{:conditionName2}}', $(this).val())" />
	</div>
	{{if ~conditionHelpTexts[conditionName1]}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName1]}}</p>
	{{/if}}
</script>

<script id="conditionDaysOfWeekTmpl" type="text/x-jsrender">
	<div class="form-group daysOfWeek">
		{{for daysOfWeek ~value=value ~conditionName=conditionName}}
		<label class="checkbox-inline">
		  <input type="checkbox" id="{{:~conditionName}}_{{:#index}}" name="{{:~conditionName}}_{{:#index}}" onchange="updateDaysOfWeekConditionValue('{{:~conditionName}}')" {{if ~value.indexOf(#index)!=-1}}checked="checked"{{/if}} /> <label for="{{:~conditionName}}_{{:#index}}">{{:#data}}</label>
		</label>
		{{/for}}
	</div>
	
	{{if ~conditionHelpTexts[conditionName]}}
	<p class="help-block">{{:~conditionHelpTexts[conditionName]}}</p>
	{{/if}}
</script>

<script id="conditionBandslotTmpl" type="text/x-jsrender">
	<div class="clublogForm">
		<div class="form-group">
			<label for="clublogCallsign">Callsign</label>
			<input type="text" class="form-control" id="clublogCallsign" placeholder="Callsign" value="{{>options.clublog.callsign}}" onchange="updateClublog()" />
		</div>
		
		<label>Modes</label>
		<div class="form-group">
			{{props clublogModes ~options=options}}
			<label class="radio-inline">
				<input type="radio" name="clublogModes" onchange="updateClublog()" id="clublogModes_{{>key}}" value="{{>key}}" {{if ~options.clublog.modes == key}}checked="checked"{{/if}}> <label class="normal" for="clublogModes_{{>key}}">{{>prop}}</label>
			</label>
			{{/props}}
		</div>

		<div id="bandslotModeInfo" style="display: none" class="alert alert-info" role="alert">
			<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
			This only controls which modes to consider when generating the list of unworked band slots. <a href="#" onclick="addCondition('mode'); return false">Add a Mode condition</a> as well
			to only match spots with a certain mode.
		</div>
		
		<label>QSL status not passing</label>
		<div class="form-group">
			{{props clublogQslStatus ~options=options}}
			<label class="checkbox-inline">
				<input type="checkbox" name="clublogStatus" onchange="updateClublog()" id="clublogStatus_{{>key}}" value="{{>key}}" {{if ~options.clublog.status.indexOf(key) != -1}}checked="checked"{{/if}}> <label class="normal" for="clublogStatus_{{>key}}">{{>prop}}</label>
			</label>
			{{/props}}
		</div>
		
		<label>Date filter</label>
		<div class="form-group">
			{{props clublogDateFilters ~options=options}}
			<label class="radio-inline">
				<input type="radio" name="clublogDateFilter" onchange="updateClublog()" id="clublogDateFilter_{{>key}}" value="{{>key}}" {{if ~options.clublog.date == key}}checked="checked"{{/if}}> <label class="normal" for="clublogDateFilter_{{>key}}">{{>prop}}</label>
			</label>
			{{/props}}
		</div>
		
		<hr />
		<div>Current number of unworked band slots: <span class="label label-info" style="margin-right: 2em">{{:numBandslots}}</span>
		Last update: <span class="label label-default">{{:clublogLastUpdate}}</span></div>
	</div>
	
	<p class="help-block">{{:~conditionHelpTexts[conditionName]}}</p>
</script>

<script id="errorAlertTmpl" type="text/x-jsrender">
	<div id="errorAlert" class="alert alert-danger" role="alert">
		<p>Please correct the following errors:</p>
		
		<ul>
			{{for errors}}
			<li>{{>#data}}</li>
			{{/for}}
		</ul>
	</div>
</script>
