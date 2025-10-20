var currentTrigger;
var dxccs, dxccKeyValueList;
var associations, associationsKeyValueList;
var wwffDivisions, wwffDivisionsKeyValueList;
var iotaGroups, iotaGroupsKeyValueList;
var hasChanges;

var summitRefRegex = /^(.+)\/(.+)-(\d+)$/;
var wwffRefRegex = /^(.+)-(\d+)$/;

var editorFunctions = {
	'band': function(conditionName) {
		return conditionValuePicker(conditionName, objectToKeyValueList(bands, bandSubtexts));
	},
	'source': function(conditionName) {
		return conditionValuePicker(conditionName, objectToKeyValueList(sources));
	},
	'mode': function(conditionName) {
		return conditionValuePicker(conditionName, objectToKeyValueList(modes));
	},
	'dxcc': dxccEditor,
	'callsignDxcc': dxccEditor,
	'callsign': callsignEditor,
	'notCallsign': callsignEditor,
	'fullCallsign': callsignEditor,
	'notFullCallsign': callsignEditor,
	'prefix': callsignEditor,
	'notPrefix': callsignEditor,
	'spotterPrefix': callsignEditor,
	'timeFrom': function(conditionName) {
		return conditionTimeInput('timeFrom', 'timeTo')
	},
	'speedFrom': function(conditionName) {
		return conditionSpeedInput('speedFrom', 'speedTo')
	},
	'snrFrom': function(conditionName) {
		return conditionSnrInput('snrFrom', 'snrTo')
	},
	'summitPointsFrom': function(conditionName) {
		return conditionSummitPointsInput('summitPointsFrom', 'summitPointsTo')
	},
	'summitActivationsFrom': function(conditionName) {
		return conditionSummitActivationsInput('summitActivationsFrom', 'summitActivationsTo')
	},
	'daysOfWeek': function(conditionName) {
		return conditionDaysOfWeek('daysOfWeek')
	},
	'summitAssociation': function(conditionName) {
		return conditionValuePickerAsync(conditionName, function(callback) {
			fetchAssociations(function(associations, associationsKeyValueList) {
				callback(associationsKeyValueList);
			})
		});
	},
	'summitRegion': function(conditionName) {
		return conditionValuePickerAsync(conditionName, function(callback) {
			fetchAssociations(function(associations, associationsKeyValueList) {
				// Has an association already been selected?
				if (currentTrigger.conditions.summitAssociation &&
						(!Array.isArray(currentTrigger.conditions.summitAssociation) || currentTrigger.conditions.summitAssociation.length == 1)) {
					// Make region list
					var assoc = currentTrigger.conditions.summitAssociation;
					if (Array.isArray(assoc))
						assoc = assoc[0];
					callback(makeRegionsKeyValueListForAssociation(associations[assoc]));
				} else {
					// Show empty dummy list
					callback([]);
				}
			})
		});
	},
	'summitRef': function(conditionName) {
		return conditionValuePickerAsync(conditionName, function(callback) {
			// Has a single association + region already been selected?
			if (currentTrigger.conditions.summitAssociation && currentTrigger.conditions.summitRegion) {
				var summitAssociation = currentTrigger.conditions.summitAssociation;
				if (Array.isArray(summitAssociation)) {
					if (summitAssociation.length == 1) {
						summitAssociation = summitAssociation[0];
					} else {
						callback([]);
					}
				}
				
				var summitRegion = currentTrigger.conditions.summitRegion;
				if (Array.isArray(summitRegion)) {
					if (summitRegion.length == 1) {
						summitRegion = summitRegion[0];
					} else {
						callback([]);
					}
				}
				
				fetchSummitsForRegion(summitAssociation, summitRegion, function(summits) {
					// Make summit list
					callback(makeSummitsKeyValueList(summits));
				});
			} else {
				// Show empty dummy list
				callback([]);
			}
		});
	},
	'summitRefs': function(conditionName) {
		return conditionTextArea(conditionName)
	},
	'spotter': callsignEditor,
	'notSpotter': callsignEditor,
	'spotterDxcc': dxccEditor,
	'cq': function(conditionName) {
		return conditionValuePicker(conditionName, makeCqZonesKeyValueList(cqZones));
	},
	'spotterCq': function(conditionName) {
		return conditionValuePicker(conditionName, makeCqZonesKeyValueList(cqZones));
	},
	'continent': function(conditionName) {
		return conditionValuePicker(conditionName, objectToKeyValueList(continents));
	},
	'spotterContinent': function(conditionName) {
		return conditionValuePicker(conditionName, objectToKeyValueList(continents));
	},
	'qsl': function(conditionName) {
		return conditionValuePicker(conditionName, objectToKeyValueList(qslMethods));
	},
	'state': function(conditionName) {
		return conditionValuePicker(conditionName, makeStatesKeyValueList(states, statesShort));
	},
	'spotterState': function(conditionName) {
		return conditionValuePicker(conditionName, makeStatesKeyValueList(states, statesShort));
	},
	'wwffDivision': function(conditionName) {
		return conditionValuePickerAsync(conditionName, function(callback) {
			fetchWwffDivisions(function(wwffDivisions, wwffDivisionsKeyValueList) {
				callback(wwffDivisionsKeyValueList);
			})
		});
	},
	'wwffRef': function(conditionName) {
		return conditionValuePickerAsync(conditionName, function(callback) {
			// Has a single division already been selected?
			if (currentTrigger.conditions.wwffDivision) {
				var wwffDivision = currentTrigger.conditions.wwffDivision;
				if (Array.isArray(wwffDivision)) {
					if (wwffDivision.length == 1) {
						wwffDivision = wwffDivision[0];
					} else {
						callback([]);
					}
				}
				
				fetchWwffParksForDivision(wwffDivision, function(parks) {
					// Make park list
					callback(makeWwffParksKeyValueList(parks));
				});
			} else {
				// Show empty dummy list
				callback([]);
			}
		});
	},
	'wwffRefs': function(conditionName) {
		return conditionTextArea(conditionName)
	},
	'iotaGroupRef': function(conditionName) {
		return conditionValuePickerAsync(conditionName, function(callback) {
			fetchIotaGroups(function(iotaGroups, iotaGroupsKeyValueList) {
				callback(iotaGroupsKeyValueList);
			})
		});
	},
	'bandslot': function(conditionName) {
		return conditionBandslot(conditionName);
	}
};

$(function() {
	var editorDialogHtml = $.templates('#editorDialogTmpl').render({actions: actionsMap});
	$('body').append(editorDialogHtml);
	$.views.tags("condEditor", conditionEditor);
	$.views.helpers("conditionHelpTexts", conditionHelpTexts);
	$.views.helpers("arrayConditions", arrayConditions);
	
	// Keep the user from accidentally closing the modal by clicking in the backdrop when there are changes
	$('#editorDialog').on('hide.bs.modal', function(e) {
		if (hasChanges) {
			if (!confirm("You have made changes to this trigger. If you are sure you want to leave without saving, press OK. Otherwise press Cancel to stay in the editor.")) {
				e.preventDefault();
			}
		}
	});
	
	$(window).bind('beforeunload', function(e){
	    if (hasChanges) {
	    	return "You have made changes to this trigger. Are you sure you want to leave the page, losing the changes?";
	    }
	});
});

function dxccEditor(conditionName) {
	return conditionValuePickerAsync(conditionName, function(callback) {
		fetchDxccs(function(dxccs, dxccKeyValueList) {
			callback(dxccKeyValueList);
		})
	});
}

function callsignEditor(conditionName) {
	if (Array.isArray(currentTrigger.conditions[conditionName]))
		return conditionTextArea(conditionName);
	else
		return conditionTextInput(conditionName);
}

function fetchDxccs(callback) {
	if (dxccs) {
		callback(dxccs, dxccKeyValueList);
		return;
	}
	
	$.get({
		url: 'ajax/dxccs',
		success: function(newDxccs) {
			dxccs = newDxccs;
			dxccKeyValueList = makeDxccKeyValueList(dxccs);
			callback(dxccs, dxccKeyValueList);
		}
	});
}

function fetchAssociations(callback) {
	if (associations) {
		callback(associations, associationsKeyValueList);
		return;
	}
	
	$.get({
		url: 'ajax/associations',
		success: function(newAssociations) {
			associations = {};
			newAssociations.forEach(function(association) {
				associations[association.Association] = association;
			});
			associationsKeyValueList = makeAssociationsKeyValueList(newAssociations);
			callback(associations, associationsKeyValueList);
		}
	});
}

function fetchWwffDivisions(callback) {
	if (wwffDivisions) {
		callback(wwffDivisions, wwffDivisionsKeyValueList);
		return;
	}
	
	$.get({
		url: 'ajax/wwff_divisions',
		success: function(newWwffDivisions) {
			wwffDivisions = {};
			newWwffDivisions.forEach(function(division) {
				wwffDivisions[division.division] = division;
			});
			wwffDivisionsKeyValueList = makeWwffDivisionsKeyValueList(newWwffDivisions);
			callback(wwffDivisions, wwffDivisionsKeyValueList);
		}
	});
}

function fetchIotaGroups(callback) {
	if (iotaGroups) {
		callback(iotaGroups, iotaGroupsKeyValueList);
		return;
	}
	
	$.get({
		url: 'ajax/iota_groups',
		success: function(newIotaGroups) {
			iotaGroups = {};
			newIotaGroups.forEach(function(iotaGroup) {
				iotaGroups[iotaGroup.ref] = iotaGroup.name;
			});
			iotaGroupsKeyValueList = makeIotaGroupsKeyValueList(newIotaGroups);
			callback(iotaGroups, iotaGroupsKeyValueList);
		}
	});
}

function fetchSummitsForRegion(association, region, callback) {	
	$.get({
		url: 'ajax/summits',
		data: {association: association, region: region},
		success: function(summits) {
			callback(summits);
		}
	});
}

function fetchWwffParksForDivision(division, callback) {	
	$.get({
		url: 'ajax/wwff_parks',
		data: {division: division},
		success: function(parks) {
			callback(parks);
		}
	});
}

function loadSotaCompleteCandidates() {
	var l = Ladda.create(document.querySelector('#loadSotaCompleteCandidatesBtn'));
	l.start();
	$.get('ajax/complete_candidates')
		.done(function(summitRefs) {
			currentTrigger.conditions.summitRefs = summitRefs;
			updateConditionsTable();
		})
		.always(function() {
			l.stop();
		});
}

function loadDxccsClublog() {
	var l = Ladda.create(document.querySelector('#loadDxccsClublogBtn'));
	l.start();

	var call = $('#dxccClublogCallsign').val();
	var mode = $('input[name=dxccClublogModes]:checked').val();
	var status = $("input[name=dxccClublogStatus]:checked").map(function(){
		return $(this).val();
	}).get();
	var date = $('input[name=dxccClublogDateFilter]:checked').val();

	$.get('ajax/unworked_dxccs', {call: call, mode: mode, date: date, status: status.join(',')})
		.done(function(dxccs) {
			currentTrigger.conditions.dxcc = dxccs;
			updateConditionsTable();
		})
		.always(function() {
			l.stop();
		});
}

function startTriggerEditor(trigger, forCloning) {
	currentTrigger = trigger;
	hasChanges = false;
	
	// Add summit association and region if we have a reference (for dropdowns)
	if (currentTrigger.conditions.summitRef) {
		// Summit reference list?
		if (Array.isArray(currentTrigger.conditions.summitRef)) {
			currentTrigger.conditions.summitRefs = currentTrigger.conditions.summitRef;
			delete currentTrigger.conditions.summitRef;
		} else {
			var matches = summitRefRegex.exec(currentTrigger.conditions.summitRef);
			if (matches) {
				currentTrigger.conditions.summitAssociation = matches[1];
				currentTrigger.conditions.summitRegion = matches[2];
			}
		}
	}
	
	// Add park division if we have a reference (for dropdowns)
	if (currentTrigger.conditions.wwffRef) {
		// WWFF division list?
		if (Array.isArray(currentTrigger.conditions.wwffRef)) {
			currentTrigger.conditions.wwffRefs = currentTrigger.conditions.wwffRef;
			delete currentTrigger.conditions.wwffRef;
		} else {
			var matches = wwffRefRegex.exec(currentTrigger.conditions.wwffRef);
			if (matches) {
				currentTrigger.conditions.wwffDivision = matches[1];
			}
		}
	}
	
	if (!currentTrigger.options)
		currentTrigger.options = {};
	
	$('#errorAlert').remove();
	updateConditionsTable();
	updateActions();
	updateComment();
	checkActionWarnings();
	checkClublogWarning();
	
	if (forCloning) {
		$('#editorDialog .modal-title').text("Clone trigger");
	} else {
		$('#editorDialog .modal-title').text("Edit trigger");
	}
	
	if (currentTrigger.disabled) {
		$('#disabledWarning').show();
	} else {
		$('#disabledWarning').hide();
	}
	
	if (currentTrigger.useless) {
		$('#uselessWarning').show();
	} else {
		$('#uselessWarning').hide();
	}
	
	$('#editorDialog').modal();
	
	$(document).on('keypress', keyPressed);
}

function cancelEditor() {
	hasChanges = false;
	$('#editorDialog').modal('hide');
	$(document).off('keypress', keyPressed);
}

function updateConditionsTable() {
	var conditionsTableHtml = $.templates('#editorConditionsTmpl').render({conditions: currentTrigger.conditions});
	$('#editorDialog table.editorConditionsTable tbody').html(conditionsTableHtml);
	$('.selectpicker').selectpicker('render');
	$('[data-toggle="tooltip"]').tooltip();
	updateBandslotModeWarning();
}

function updateActions() {
	$('#actionsGroup input').each(function(index, element) {
		if (element.id.startsWith("action_")) {
			var action = element.id.replace("action_", "");
			$(element).prop('checked', currentTrigger.actions.includes(action));
		}
	});
}

function updateComment() {
	$('#comment').val(currentTrigger.comment);
}

function updateConditionValue(conditionName, value) {
	if (conditionTypes[conditionName] == 'int') {
		if (Array.isArray(value)) {
			value = value.map(function(x) {
				return parseInt(x);
			});
		} else {
			value = parseInt(value);
		}
	}
	if (arrayConditions[conditionName] && !Array.isArray(value)) {
		value = uniq(value.trim().split(/[\s,]+/));
		if (value.length == 1)
			value = value[0];
	}
	currentTrigger.conditions[conditionName] = value;
	if (conditionName == "summitAssociation") {
		// Only reload if summitRegion or summitRefs is set too
		if (currentTrigger.conditions.summitRegion !== undefined || currentTrigger.conditions.summitRefs !== undefined) {
			updateConditionsTable();
		}
	} else if (conditionName == "summitRegion" || conditionName == "summitRefs" || conditionName == "wwffDivision" || conditionName == "wwffRefs") {
		// Reload regions and references
		updateConditionsTable();
	}
	hasChanges = true;
}

function updateDaysOfWeekConditionValue(conditionName) {
	var daysOfWeek = [];
	for (var i = 0; i < 7; i++) {
		if ($('#' + conditionName + "_" + i).is(':checked')) {
			daysOfWeek.push(i);
		}
	}
	
	if (daysOfWeek.length == 0) {
		daysOfWeek = undefined;
	}
	updateConditionValue(conditionName, daysOfWeek);
}

function keyPressed(e) {
	if (e.which == 13) {
		saveTrigger();
	}
}

function saveTrigger() {
	// get changes from currently editing field
	$(':focus').blur();
	
	// pull in comment and actions
	currentTrigger.comment = $('#comment').val();
	currentTrigger.actions = [];
	$.each(actionsMap, function(key, value) {
		if ($('#action_' + key).is(':checked')) {
			currentTrigger.actions.push(key);
		}
	});
	
	if (validateTrigger()) {
		// If there is a summit reference, we don't need association/region (it's just for the dropdowns)
		if (currentTrigger.conditions.summitRef) {
			delete currentTrigger.conditions.summitAssociation;
			delete currentTrigger.conditions.summitRegion;
		} else if (currentTrigger.conditions.summitRefs) {
			// Put summitRefs back into summitRef field
			currentTrigger.conditions.summitRef = currentTrigger.conditions.summitRefs;
		}
		delete currentTrigger.conditions.summitRefs;
		
		// If there is a park reference, we don't need division (it's just for the dropdown)
		if (currentTrigger.conditions.wwffRef) {
			delete currentTrigger.conditions.wwffDivision;
		} else if (currentTrigger.conditions.wwffRefs) {
			// Put wwffRefs back into wwffRef field
			currentTrigger.conditions.wwffRef = currentTrigger.conditions.wwffRefs;
		}
		delete currentTrigger.conditions.wwffRefs;
		
		// If there is no bandslot condition, we don't need clublog options
		if (currentTrigger.conditions.bandslot === undefined) {
			delete currentTrigger.options.clublog;
		} else {
			currentTrigger.options.clublog.forceUpdate = true;
		}
		
		// Normalize array conditions with only one element
		$.each(arrayConditions, function(condition) {
			if (condition == "bandslot")
				return;
			
			if (Array.isArray(currentTrigger.conditions[condition]) && currentTrigger.conditions[condition].length == 1) {
				currentTrigger.conditions[condition] = currentTrigger.conditions[condition][0];
			}
		});
		
		var l = Ladda.create(document.querySelector('#saveButton'));
		l.start();
		
		$.ajax({
			type: "POST",
			url: 'ajax/trigger_update',
			data: JSON.stringify(currentTrigger),
			contentType: "application/json",
			success: function(result) {
				l.stop();
				cancelEditor();
				reloadTriggers();
			},
			error: function(xhr, status, error) {
				l.stop();
				var errorAlertHtml = $.templates('#errorAlertTmpl').render({errors: [xhr.responseText]});
				$('#editorDialog .modal-body').prepend(errorAlertHtml);
			}
		});
	}	
}

function validateTrigger() {
	var errors = [];
	
	// Check that there is at least one condition
	if ($.isEmptyObject(currentTrigger.conditions)) {
		errors.push("At least one condition is required.");
	}
	
	// Check that all defined conditions have a value
	$.each(currentTrigger.conditions, function(key, value) {
		var error = validateCondition(key, value);
		if (error !== undefined) {
			$('#condrow_' + key).addClass('danger');
			errors.push(error);
		} else {
			$('#condrow_' + key).removeClass('danger');
		}
	});

	// Check for "silly" triggers that only have one very generic condition
	let isSilly = false;
	if (Object.keys(currentTrigger.conditions).length === 1) {
		if (currentTrigger.conditions.band &&
			currentTrigger.conditions.band.some(band => 
				band === "hf" ||
				(band.slice(-1) === "m" && band.slice(0, -1) <= 160 && band.slice(0, -1) >= 10)
			)) {
			// Only one HF band condition is silly
			isSilly = true;
		} else if (currentTrigger.conditions.mode &&
			currentTrigger.conditions.mode.some(mode => 
				mode === "cw" || mode === "ssb" || mode === "fm" || mode === "ft8"
			)) {
			// Only one common mode condition is silly
			isSilly = true;
		} else if (currentTrigger.conditions.source &&
			currentTrigger.conditions.source.some(source => 
				source === "cluster" || source === "rbn" || source === "pskreporter"
			)) {
			// Only one common source condition is silly
			isSilly = true;
		} else if (currentTrigger.conditions.continent ||
				   currentTrigger.conditions.cq ||
				   currentTrigger.conditions.spotterContinent ||
				   currentTrigger.conditions.spotterCq) {
			isSilly = true;
		}
	}
	if (isSilly) {
		errors.push("Your trigger is too unspecific and will match too many spots. Please add more conditions, or make your conditions more specific.");
	}

	$('#errorAlert').remove();
	if (errors.length > 0) {
		var errorAlertHtml = $.templates('#errorAlertTmpl').render({errors: errors});
		$('#editorDialog .modal-body').prepend(errorAlertHtml);
		return false;
	} else {
		return true;
	}
}

function validateCondition(conditionName) {
	var value = currentTrigger.conditions[conditionName];
	
	if (conditionName == "bandslot") {
		// Check that Club Log info is supplied
		if (!currentTrigger.options.clublog.callsign)
			return "You must enter the callsign to use for querying Club Log.";
		if (currentTrigger.options.clublog.status.length == 0)
			return "You must select at least one status to consider when querying Club Log.";
		if (!currentTrigger.options.clublog.modes)
			return "You must choose the mode to consider when querying Club Log.";
		return undefined;
	}
	
	if (value === undefined || value === null || value === "" || (Array.isArray(value) && value.length == 0)) {
		return "The condition '" + conditionLabels[conditionName] + "' cannot be empty."
	}
	
	// Check time
	if (conditionName == "timeFrom") {
		var timeRegex = /^(?:\d|[01]\d|2[0-3]):[0-5]\d$/;
		if (!timeRegex.test(currentTrigger.conditions.timeFrom) || !timeRegex.test(currentTrigger.conditions.timeTo)) {
			return "Time must be specified in HH:MM format, and both fields are required.";
		}
	}
	
	// Check speed
	if (conditionName == "speedFrom") {
		var speedRegex = /^\d+$/;
		if (!speedRegex.test(currentTrigger.conditions.speedFrom) || !speedRegex.test(currentTrigger.conditions.speedTo) || currentTrigger.conditions.speedFrom > currentTrigger.conditions.speedTo) {
			return "Speed must be specified in WPM, both fields are required and the first must be lower than the second.";
		}
	}
	
	// Check SNR
	if (conditionName == "snrFrom") {
		var snrRegex = /^-?\d+$/;
		if (!snrRegex.test(currentTrigger.conditions.snrFrom) || !snrRegex.test(currentTrigger.conditions.snrTo) || currentTrigger.conditions.snrFrom > currentTrigger.conditions.snrTo) {
			return "SNR must be specified in dB, both fields are required and the first must be lower than the second.";
		}
	}
	
	// Check summit points
	if (conditionName == "summitPointsFrom") {
		var summitPointsRegex = /^\d+$/;
		if (!summitPointsRegex.test(currentTrigger.conditions.summitPointsFrom) || !summitPointsRegex.test(currentTrigger.conditions.summitPointsTo) ||
			 currentTrigger.conditions.summitPointsFrom < 1 || currentTrigger.conditions.summitPointsTo > 10 || currentTrigger.conditions.summitPointsFrom > currentTrigger.conditions.summitPointsTo) {
			return "Summit points must be between 1 and 10, both fields are required and the first must be lower than the second.";
		}
	}
	
	// Check summit activations
	if (conditionName == "summitActivationsFrom") {
		var summitActivationsRegex = /^\d+$/;
		if (!summitActivationsRegex.test(currentTrigger.conditions.summitActivationsFrom) || !summitActivationsRegex.test(currentTrigger.conditions.summitActivationsTo) ||
			 currentTrigger.conditions.summitActivationsFrom > currentTrigger.conditions.summitActivationsTo) {
			return "Summit activations must be numeric, both fields are required and the first must be lower than the second.";
		}
	}
	
	// Check days of week
	if (conditionName == "daysOfWeek") {
		if (value.length >= 7) {
			return "Selecting all days of week is not useful; remove the days of week condition.";
		}
	}
	
	// Check callsigns
	if ((conditionName == "callsign" || conditionName == "fullCallsign" || conditionName == "spotter" || conditionName == "notCallsign" || conditionName == "notFullCallsign" || conditionName == "notSpotter") && value !== "") {
		if (!Array.isArray(value))
			value = [value];
		for (var i = 0; i < value.length; i++) {
			var callsign = value[i].toUpperCase();
			value[i] = callsign;
			var callsignRegex = /^[A-Z0-9\/-]{3,20}$/;
			if (!callsignRegex.test(callsign)) {
				return "Invalid callsign '" + callsign + "'";
			}
	
			// Check that callsign does not have prefix/suffix
			if ((conditionName == "callsign" || conditionName == "notCallsign") && callsign.indexOf("/") !== -1) {
				return "Callsign must not contain any prefixes/suffixes (use full callsign instead).";
			}
		}
		value.sort();
		currentTrigger.conditions[conditionName] = value;
	}

	// Check prefixes
	if (conditionName == "prefix" || conditionName == "notPrefix" || conditionName == "spotterPrefix") {
		if (!Array.isArray(value))
			value = [value];
		for (var i = 0; i < value.length; i++) {
			var prefix = value[i].toUpperCase();
			value[i] = prefix;
			var prefixRegex = /^[A-Z0-9]{1,8}$/;
			if (!prefixRegex.test(prefix)) {
				return "Invalid prefix '" + prefix + "'";
			}
		}
		value.sort();
		currentTrigger.conditions[conditionName] = value;
	}
	
	// Check summit refs list
	if (conditionName == 'summitRefs') {
		if (!Array.isArray(value)) {
			value = [value];
		}
		for (var i = 0; i < value.length; i++) {
			value[i] = value[i].toUpperCase();
			if (!summitRefRegex.test(value[i])) {
				return "Invalid summit reference in list: " + value[i];
			}
		}
	}
	
	// Check park refs list
	if (conditionName == 'wwffRefs') {
		if (!Array.isArray(value)) {
			value = [value];
		}
		for (var i = 0; i < value.length; i++) {
			value[i] = value[i].toUpperCase();
			if (!wwffRefRegex.test(value[i])) {
				return "Invalid park reference in list: " + value[i];
			}
		}
	}
	
	// All OK
	return undefined;
}

function checkActionWarnings() {
	$.each(actionStatus, function(action, status) {
		if (!status && $('#action_' + action).is(':checked')) {
			$('#actionWarning_' + action).show();
		} else {
			$('#actionWarning_' + action).hide();
		}
	});
	
	// Check if at least one action is enabled
	var anyActionsEnabled = false;
	$('#actionsGroup input').each(function(index, element) {
		if (element.id.startsWith("action_")) {
			if ($(this).is(':checked')) {
				anyActionsEnabled = true;
			}
		}
	});
	
	if (!anyActionsEnabled)
		$('#noActionsWarning').show();
	else
		$('#noActionsWarning').hide();
}

function checkClublogWarning() {
	if (currentTrigger.conditions.bandslot !== undefined && !haveClublogInfo)
		$('#noClublogWarning').show();
	else
		$('#noClublogWarning').hide();
}

function conditionEditor(conditionName) {
	if (editorFunctions[conditionName]) {
		return editorFunctions[conditionName](conditionName);
	}
	return "";
}

function addCondition(conditionName) {
	var conditionName;

	if (!conditionName) {
		conditionName = $('#newCondition').val();
	}

	if (!conditionName || currentTrigger.conditions[conditionName] !== undefined)
		return;
	
	// Can only add summit region if association is already set
	if (conditionName === 'summitRegion' && currentTrigger.conditions['summitAssociation'] === undefined) {
		currentTrigger.conditions['summitAssociation'] = "";
	} else if (conditionName == 'summitRef') {
		// Force association/region in order to set a summit reference (so we get the dropdowns)
		if (currentTrigger.conditions['summitAssociation'] === undefined)
			currentTrigger.conditions['summitAssociation'] = "";
		if (currentTrigger.conditions['summitRegion'] === undefined)
			currentTrigger.conditions['summitRegion'] = "";
		
		// summitRef and summitRefs are mutually exclusive
		delete(currentTrigger.conditions['summitRefs']);
	} else if (conditionName == 'summitRefs') {
		// summitRef and summitRefs are mutually exclusive
		delete(currentTrigger.conditions['summitRef']);
	} else if (conditionName == 'wwffRef') {
		// Force division in order to set a WWFF reference (so we get the dropdown)
		if (currentTrigger.conditions['wwffDivision'] === undefined)
			currentTrigger.conditions['wwffDivision'] = "";
	} else if (conditionName == 'wwffRefs') {
		// wwffRef and wwffRefs are mutually exclusive
		delete(currentTrigger.conditions['wwffRef']);
	} else if (conditionName == 'bandslot') {
		// Bandslot requires band condition
		if (!currentTrigger.conditions['band'])
			currentTrigger.conditions['band'] = "";
		if (!currentTrigger.options.clublog)
			currentTrigger.options.clublog = {modes: 'all', status: ['confirmed', 'worked', 'verified'], callsign: username};
	}
	
	if (conditionName == 'bandslot')
		currentTrigger.conditions[conditionName] = [];
	else
		currentTrigger.conditions[conditionName] = "";
	updateConditionsTable();
	checkClublogWarning();
	hasChanges = true;
}

function deleteCondition(conditionName) {
	delete currentTrigger.conditions[conditionName];
	if (conditionName == 'summitAssociation')
		delete currentTrigger.conditions['summitRegion'];
	if (conditionName == 'summitAssociation' || conditionName == 'summitRegion')
		delete currentTrigger.conditions['summitRef'];
	if (conditionName == 'wwffDivision')
		delete currentTrigger.conditions['wwffRef'];
	if (conditionName == 'band')
		delete currentTrigger.conditions['bandslot'];
	updateConditionsTable();
	checkClublogWarning();
	hasChanges = true;
}

// Returns an array of key/value pairs, sorted by DXCC primary prefix
function makeDxccKeyValueList(dxccs) {
	return dxccs.map(function(dxcc) {
		var dxccPad = ("00" + dxcc.dxcc).slice(-3);
		return [dxcc.dxcc, dxccPad + ' - ' + dxcc.country, dxcc.prefixes];
	});
}

// Returns an array of key/value pairs
function makeAssociationsKeyValueList(associations) {
	return associations.map(function(value) {
		return [value.Association, value.Association + ' - ' + value.Name];
	});
}

// Returns an array of key/value pairs
function makeRegionsKeyValueListForAssociation(association) {
	return association.Regions.map(function(value) {
		return [value.Region, value.Region + ' - ' + value.Name];
	});
}

// Returns an array of key/value pairs, sorted by state
function makeStatesKeyValueList(states, statesShort) {
	var kvl = [];
	var lastCountry = null;
	Object.keys(states).forEach(function(state) {
		var country = state.substring(0, 2);
		if (lastCountry && lastCountry != country) {
			kvl.push(['---']);
		}
		lastCountry = country;
		kvl.push([state, states[state], statesShort[state]]);
	});
	return kvl;
}

function makeSummitsKeyValueList(summits) {
	return summits.map(function(value) {
		return [value.Ref, value.Ref + ' - ' + value.Name];
	});
}

// Returns an array of key/value pairs
function makeWwffDivisionsKeyValueList(wwffDivisions) {
	var kvl = wwffDivisions.map(function(value) {
		return [value.division, value.name, value.program != 'wwff' ? value.program.toUpperCase() : undefined];
	});
	// add "any" entry
	kvl.unshift(['*', '* (any)']);
	return kvl;
}

function makeWwffParksKeyValueList(parks) {
	return parks.map(function(value) {
		// Limit park name length to avoid breaking popup
		if (value.Name.length > 60) {
			value.Name = value.Name.substring(0, 60) + '...';
		}
		return [value.Ref, value.Ref + ' - ' + value.Name];
	});
}

// Returns an array of key/value pairs
function makeIotaGroupsKeyValueList(iotaGroups) {
	var kvl = iotaGroups.map(function(value) {
		return [value.ref, value.ref + ' - ' + cutOff(value.name, 50)];
	});
	// add "any" entry
	kvl.unshift(['*', '* (any)']);
	return kvl;
}

function makeCqZonesKeyValueList(cqZones) {
	var keyValueList = [];
	$.each(cqZones, function(key, value) {
		keyValueList.push([key, key + ' - ' + value]);
	});
	return keyValueList;
}

// Make a generic dropdown picker with the given array of arrays as the available key/value pairs
function conditionValuePicker(conditionName, keyValueList) {
	var selectedKeys = {};
	if (Array.isArray(currentTrigger.conditions[conditionName])) {
		currentTrigger.conditions[conditionName].forEach(function(key) {
			selectedKeys[key] = true;
		});
	} else {
		selectedKeys[currentTrigger.conditions[conditionName]] = true;
	}
	return $.templates('#conditionValuePickerTmpl').render({
		conditionName: conditionName,
		keyValueList: keyValueList,
		selectedKeys: selectedKeys,
		clublogModes: clublogModes,
		clublogQslStatus: clublogQslStatus,
		clublogDateFilters: clublogDateFilters,
		dxccClublogCallsign: username,
		haveClublogInfo: haveClublogInfo
	});
}

function conditionValuePickerAsync(conditionName, callback) {
	var dummyDivId = conditionName + "_dummy";
	
	// Call callback first to obtain list of values
	var html;
	callback(function(keyValueList) {
		html = conditionValuePicker(conditionName, keyValueList);
		$('#' + dummyDivId).replaceWith(html);
		$('.selectpicker').selectpicker('render');
	});
	
	if (html) {
		// The callback completed immediately, so no need for a dummy div
		return html;
	} else {
		// Return dummy div that we can replace
		return "<div id=\"" + dummyDivId + "\">loading...</div>";
	}
}

function conditionTextInput(conditionName) {
	return $.templates('#conditionTextInputTmpl').render({
		conditionName: conditionName,
		value: currentTrigger.conditions[conditionName]
	});
}

function conditionTextArea(conditionName) {
	var val;
	if (Array.isArray(currentTrigger.conditions[conditionName]))
		val = currentTrigger.conditions[conditionName].join(", ");
	else
		val = currentTrigger.conditions[conditionName];
	return $.templates('#conditionTextAreaTmpl').render({
		conditionName: conditionName,
		value: val,
		options: currentTrigger.options
	});
}

function conditionTimeInput(conditionName1, conditionName2) {
	return $.templates('#conditionTimeInputTmpl').render({
		conditionName1: conditionName1,
		conditionName2: conditionName2,
		value1: currentTrigger.conditions[conditionName1],
		value2: currentTrigger.conditions[conditionName2]
	});
}

function conditionSpeedInput(conditionName1, conditionName2) {
	return $.templates('#conditionSpeedInputTmpl').render({
		conditionName1: conditionName1,
		conditionName2: conditionName2,
		value1: currentTrigger.conditions[conditionName1],
		value2: currentTrigger.conditions[conditionName2]
	});
}

function conditionSnrInput(conditionName1, conditionName2) {
	return $.templates('#conditionSnrInputTmpl').render({
		conditionName1: conditionName1,
		conditionName2: conditionName2,
		value1: currentTrigger.conditions[conditionName1],
		value2: currentTrigger.conditions[conditionName2]
	});
}

function conditionSummitPointsInput(conditionName1, conditionName2) {
	return $.templates('#conditionSummitPointsInputTmpl').render({
		conditionName1: conditionName1,
		conditionName2: conditionName2,
		value1: currentTrigger.conditions[conditionName1],
		value2: currentTrigger.conditions[conditionName2]
	});
}

function conditionSummitActivationsInput(conditionName1, conditionName2) {
	return $.templates('#conditionSummitActivationsInputTmpl').render({
		conditionName1: conditionName1,
		conditionName2: conditionName2,
		value1: currentTrigger.conditions[conditionName1],
		value2: currentTrigger.conditions[conditionName2]
	});
}

function conditionDaysOfWeek(conditionName) {
	return $.templates('#conditionDaysOfWeekTmpl').render({
		conditionName: conditionName,
		value: currentTrigger.conditions[conditionName],
		daysOfWeek: daysOfWeek
	});
}

function conditionBandslot(conditionName) {
	var clublogLastUpdate = 'never';
	if (currentTrigger.options.clublog.lastUpdate) {
		clublogLastUpdate = new Date();
		clublogLastUpdate.setTime(currentTrigger.options.clublog.lastUpdate['$date']['$numberLong']);
	}
	
	return $.templates('#conditionBandslotTmpl').render({
		conditionName: conditionName,
		value: currentTrigger.conditions[conditionName],
		options: currentTrigger.options,
		clublogLastUpdate: clublogLastUpdate.toLocaleString(),
		clublogModes: clublogModes,
		clublogQslStatus: clublogQslStatus,
		clublogDateFilters: clublogDateFilters,
		numBandslots: currentTrigger.conditions[conditionName].length
	});
}

function switchToArray(conditionName) {
	if (arrayConditions[conditionName]) {
		if (!Array.isArray(currentTrigger.conditions[conditionName])) {
			currentTrigger.conditions[conditionName] = [currentTrigger.conditions[conditionName]];
			updateConditionsTable();
		}
	}
}

function loadIotaCsv() {
	var file = $('#iotaCsvFile').prop("files")[0];
	if (!file)
		return;
	
	var reader = new FileReader();
	reader.readAsText(file);
	reader.onload = function(event) {
		var csv = event.target.result;
		var data;
		try {
			data = $.csv.toArrays(csv);
			if (data[0][0] != "Ref. No.")
				throw new Error("This file does not appear to be a IOTA QSO CSV file.");
		} catch (e) {
			alert(e.message);
			$('#iotaCsvFile').wrap('<form>').closest('form').get(0).reset();
			$('#iotaCsvFile').unwrap();
			return;
		}
		var unworkedIotaGroups = new Set(Object.keys(iotaGroups));
		for (var i = 1; i < data.length; i++) {
			unworkedIotaGroups.delete(data[i][0]);
		}
		
		currentTrigger.conditions.iotaGroupRef = Array.from(unworkedIotaGroups);
		updateConditionsTable();
	}
	reader.onerror = function() {
		alert('Unable to read ' + file.fileName);
	}
}

function updateAutoUpdateSotaCompleteCandidates() {
	if ($('#autoUpdateSotaCompleteCandidates').is(':checked')) {
		currentTrigger.options.autoUpdateSotaCompleteCandidates = true;
	} else {
		delete currentTrigger.options.autoUpdateSotaCompleteCandidates;
	}
}

function updateClublog() {
	currentTrigger.options.clublog.callsign = $('#clublogCallsign').val();
	currentTrigger.options.clublog.modes = $('input[name=clublogModes]:checked').val();
	currentTrigger.options.clublog.status = $("input[name=clublogStatus]:checked").map(function(){
		return $(this).val();
	}).get();
	currentTrigger.options.clublog.date = parseInt($('input[name=clublogDateFilter]:checked').val());

	updateBandslotModeWarning();
}

function updateBandslotModeWarning() {
	if (!currentTrigger.conditions.bandslot)
		return;

	if (currentTrigger.options.clublog.modes != 'all' && currentTrigger.conditions.mode === undefined) {
		$('#bandslotModeInfo').show();
	} else {
		$('#bandslotModeInfo').hide();
	}
}

function showDxccClublogForm() {
	$('#dxccClublogForm').show();
	$('#showDxccClublogFormLink').hide();
}
