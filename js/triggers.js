var conditionLabels = {
	'callsign': 'Callsign',
	'notCallsign': 'not Callsign',
	'fullCallsign': 'Full callsign',
	'notFullCallsign': 'not Full callsign',
	'spotter': 'Spotter callsign',
	'notSpotter': 'not Spotter callsign',
	'prefix': 'Prefix',
	'notPrefix': 'not Prefix',
	'spotterPrefix': 'Spotter prefix',
	'band': 'Band',
	'mode': 'Mode',
	'timeFrom': 'Time',
	'speedFrom': 'Speed',
	'snrFrom': 'SNR',
	'summitPointsFrom': 'Summit points',
	'summitActivationsFrom': 'Summit activations',
	'daysOfWeek': 'Days of week',
	'source': 'Source',
	'dxcc': 'DXCC',
	'callsignDxcc': 'Home DXCC',
	'spotterDxcc': 'Spotter DXCC',
	'bandslot': 'Band slots',
	'cq': 'CQ zone',
	'spotterCq': 'Spotter CQ zone',
	'continent': 'Continent',
	'spotterContinent': 'Spotter continent',
	'qsl': 'QSL methods',
	'state': 'State',
	'spotterState': 'Spotter state',
	'iotaGroupRef': 'IOTA group reference',
	'summitAssociation': 'Summit association',
	'summitRegion': 'Summit region',
	'summitRef': 'Summit reference',
	'summitRefs': 'Summit reference list',
	'wwffDivision': 'Park division',
	'wwffRef': 'Park reference',
	'wwffRefs': 'Park reference list',
	'wwbotaScheme': 'WWBOTA scheme',
};

var conditionsOrder = [
	'callsign', 'fullCallsign', 'spotter', 'prefix', 'spotterPrefix',
	'band', 'mode', 'timeFrom', 'daysOfWeek', 'source', 'speedFrom', 'snrFrom',
	'dxcc', 'callsignDxcc', 'spotterDxcc', 'bandslot',
	'cq', 'spotterCq', 'continent', 'spotterContinent',
	'qsl',
	'state', 'spotterState',
	'iotaGroupRef',
	'summitAssociation', 'summitRegion', 'summitRef', 'summitRefs', 'summitPointsFrom', 'summitActivationsFrom',
	'wwffDivision', 'wwffRef', 'wwffRefs',
	'wwffScheme',
	'notCallsign', 'notFullCallsign', 'notSpotter', 'notPrefix'
];

var conditionsGroups = [
	{title: 'Callsigns', conditions: ['callsign', 'fullCallsign', 'spotter', 'prefix', 'spotterPrefix']},
	{title: 'Basic', conditions: ['band', 'mode', 'timeFrom', 'daysOfWeek', 'source', 'speedFrom', 'snrFrom']},
	{title: 'DXCC', conditions: ['dxcc', 'callsignDxcc', 'spotterDxcc', 'bandslot']},
	{title: 'Zones', conditions: ['cq', 'spotterCq', 'continent', 'spotterContinent']},
	{title: 'QSL', conditions: ['qsl']},
	{title: 'WAS', conditions: ['state', 'spotterState']},
	{title: 'IOTA', conditions: ['iotaGroupRef']},
	{title: 'SOTA', conditions: ['summitAssociation', 'summitRegion', 'summitRef', 'summitRefs', 'summitPointsFrom', 'summitActivationsFrom']},
	{title: 'WWFF/POTA', conditions: ['wwffDivision', 'wwffRef', 'wwffRefs']},
	{title: 'WWBOTA', conditions: ['wwffScheme']},
	{title: 'Callsign exclusions', conditions: ['notCallsign', 'notFullCallsign', 'notSpotter', 'notPrefix']}
];

var conditionTypes = {
	'dxcc': 'int',
	'callsignDxcc': 'int',
	'spotterDxcc': 'int',
	'cq': 'int',
	'spotterCq': 'int',
	'itu': 'int',
	'speedFrom': 'int',
	'speedTo': 'int',
	'snrFrom': 'int',
	'snrTo': 'int',
	'summitPointsFrom': 'int',
	'summitPointsTo': 'int',
	'summitActivationsFrom': 'int',
	'summitActivationsTo': 'int'
};

var arrayConditions = {
	'callsign': {
		maxDisplay: 0,
		maxDisplaySmall: 3,
		suffix: 'callsigns'
	},
	'fullCallsign': {
		maxDisplay: 0,
		maxDisplaySmall: 3,
		suffix: 'callsigns'
	},
	'spotter': {
		maxDisplay: 0,
		maxDisplaySmall: 3,
		suffix: 'callsigns'
	},
	'notCallsign': {
		maxDisplay: 0,
		maxDisplaySmall: 3,
		suffix: 'callsigns'
	},
	'notFullCallsign': {
		maxDisplay: 0,
		maxDisplaySmall: 3,
		suffix: 'callsigns'
	},
	'notSpotter': {
		maxDisplay: 0,
		maxDisplaySmall: 3,
		suffix: 'callsigns'
	},
	'prefix': {
		maxDisplay: 0,
		maxDisplaySmall: 5,
		suffix: 'prefixes'
	},
	'notPrefix': {
		maxDisplay: 0,
		maxDisplaySmall: 5,
		suffix: 'prefixes'
	},
	'spotterPrefix': {
		maxDisplay: 0,
		maxDisplaySmall: 5,
		suffix: 'prefixes'
	},
	'summitRef': {
		maxDisplay: 0,
		maxDisplaySmall: 2,
		suffix: 'summits'
	},
	'summitRefs': {
		maxDisplay: 0,
		maxDisplaySmall: 2,
		suffix: 'summits'
	},
	'mode': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'modes'
	},
	'band': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'bands'
	},
	'source': {
		maxDisplay: 0,
		maxDisplaySmall: 2,
		suffix: 'sources'
	},
	'dxcc': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'DXCCs'
	},
	'callsignDxcc': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'DXCCs'
	},
	'spotterDxcc': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'DXCCs'
	},
	'bandslot': {
		maxDisplay: 0,
		maxDisplaySmall: 0,
		suffix: 'slots'
	},
	'cq': {
		maxDisplay: 5,
		maxDisplaySmall: 8,
		suffix: 'zones'
	},
	'spotterCq': {
		maxDisplay: 5,
		maxDisplaySmall: 8,
		suffix: 'zones'
	},
	'continent': {
		maxDisplay: 2,
		maxDisplaySmall: 3,
		suffix: 'continents'
	},
	'spotterContinent': {
		maxDisplay: 2,
		maxDisplaySmall: 3,
		suffix: 'continents'
	},
	'summitAssociation': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'associations'
	},
	'summitRegion': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'regions'
	},
	'qsl': {
		maxDisplay: 2,
		maxDisplaySmall: 2,
		suffix: 'methods'
	},
	'state': {
		maxDisplay: 4,
		maxDisplaySmall: 6,
		suffix: 'states'
	},
	'spotterState': {
		maxDisplay: 4,
		maxDisplaySmall: 6,
		suffix: 'states'
	},
	'iotaGroupRef': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'groups'
	},
	'wwffDivision': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'divisions'
	},
	'wwffRef': {
		maxDisplay: 0,
		maxDisplaySmall: 2,
		suffix: 'parks'
	},
	'wwffRefs': {
		maxDisplay: 0,
		maxDisplaySmall: 2,
		suffix: 'parks'
	},
	'wwbotaScheme': {
		maxDisplay: 3,
		maxDisplaySmall: 5,
		suffix: 'schemes'
	},
};

var sources = {
	'sotawatch': 'SOTAwatch',
	'rbn': 'RBN',
	'cluster': 'Cluster',
	'pskreporter': 'PSK Reporter',
	'pota': 'POTA',
	'wwff': 'WWFFwatch',
	'wwbota': 'WWBOTA'
};

var modes = {
	'cw': 'CW',
	'ssb': 'SSB',
	'fm': 'FM',
	'dv': 'DV',
	'am': 'AM',
	'psk': 'PSK',
	'rtty': 'RTTY',
	'jt' : 'JT',
	'msk': 'MSK',
	'ft8': 'FT8',
	'ft4': 'FT4',
	'js8call': 'JS8Call',
	'varac': 'VarAC',
	'qra64': 'QRA64',
	'iscat': 'ISCAT',
	'fsk441': 'FSK441',
	't10': 'T10',
	'q65': 'Q65',
	'sstv': 'SSTV',
	'olivia': 'Olivia',
	'fst4': 'FST4',
	'data': 'DATA'
};

var continents = {
	'AF': 'Africa',
	'AN': 'Antarctica',
	'AS': 'Asia',
	'EU': 'Europe',
	'NA': 'North America',
	'OC': 'Oceania',
	'SA': 'South America'
};

var actionsMap = {
	'app': 'App',
	'threema': 'Threema',
	'telnet': 'Telnet',
	'url': 'URL'
};

var bands = {
	'vlf': 'VLF',
	'lf': 'LF',
	'mf': 'MF',
	'hf': 'HF',
	'vhf': 'VHF',
	'uhf': 'UHF',
	'shf': 'SHF',
	'ehf': 'EHF',
	'---': '---',
	'2200m': '2200m',
	'600m': '600m',
	'160m': '160m',
	'80m': '80m',
	'60m': '60m',
	'40m': '40m',
	'30m': '30m',
	'20m': '20m',
	'17m': '17m',
	'15m': '15m',
	'12m': '12m',
	'11m': '11m',
	'10m': '10m',
	'8m': '8m',
	'6m': '6m',
	'4m': '4m',
	'2m': '2m',
	'1.25m': '1.25m',
	'70cm': '70cm',
	'23cm': '23cm',
	'13cm': '13cm',
	'9cm': '9cm',
	'6cm': '6cm',
	'3cm': '3cm',
	'3cm_qo100': '3cm (QO-100 NB)'
};

var bandSubtexts = {
	'vlf': '< 30 kHz',
	'lf': '30–300 kHz',
	'mf': '300 kHz – 3 MHz',
	'hf': '3–30 MHz',
	'vhf': '30–300 MHz',
	'uhf': '300 MHz – 3 GHz',
	'shf': '3–30 GHz',
	'ehf': '30–300 GHz'
};

var qslMethods = {
	'lotw': 'LoTW',
	'eqsl': 'eQSL AG'
};

var states = {
	"US_AL": "Alabama",
	"US_AK": "Alaska",
	"US_AZ": "Arizona",
	"US_AR": "Arkansas",
	"US_CA": "California",
	"US_CO": "Colorado",
	"US_CT": "Connecticut",
	"US_DE": "Delaware",
	"US_DC": "District Of Columbia",
	"US_FL": "Florida",
	"US_GA": "Georgia",
	"US_HI": "Hawaii",
	"US_ID": "Idaho",
	"US_IL": "Illinois",
	"US_IN": "Indiana",
	"US_IA": "Iowa",
	"US_KS": "Kansas",
	"US_KY": "Kentucky",
	"US_LA": "Louisiana",
	"US_ME": "Maine",
	"US_MD": "Maryland",
	"US_MA": "Massachusetts",
	"US_MI": "Michigan",
	"US_MN": "Minnesota",
	"US_MS": "Mississippi",
	"US_MO": "Missouri",
	"US_MT": "Montana",
	"US_NE": "Nebraska",
	"US_NV": "Nevada",
	"US_NH": "New Hampshire",
	"US_NJ": "New Jersey",
	"US_NM": "New Mexico",
	"US_NY": "New York",
	"US_NC": "North Carolina",
	"US_ND": "North Dakota",
	"US_OH": "Ohio",
	"US_OK": "Oklahoma",
	"US_OR": "Oregon",
	"US_PA": "Pennsylvania",
	"US_RI": "Rhode Island",
	"US_SC": "South Carolina",
	"US_SD": "South Dakota",
	"US_TN": "Tennessee",
	"US_TX": "Texas",
	"US_UT": "Utah",
	"US_VT": "Vermont",
	"US_VA": "Virginia",
	"US_WA": "Washington",
	"US_WV": "West Virginia",
	"US_WI": "Wisconsin",
	"US_WY": "Wyoming",
	"CA_AB": "Alberta",
	"CA_BC": "British Columbia",
	"CA_MB": "Manitoba",
	"CA_NB": "New Brunswick",
	"CA_NL": "Newfoundland and Labrador",
	"CA_NS": "Nova Scotia",
	"CA_ON": "Ontario",
	"CA_PE": "Prince Edward Island",
	"CA_QC": "Quebec",
	"CA_SK": "Saskatchewan"
};

var statesShort = {
	"US_AL": "AL",
	"US_AK": "AK",
	"US_AZ": "AZ",
	"US_AR": "AR",
	"US_CA": "CA",
	"US_CO": "CO",
	"US_CT": "CT",
	"US_DE": "DE",
	"US_DC": "DC",
	"US_FL": "FL",
	"US_GA": "GA",
	"US_HI": "HI",
	"US_ID": "ID",
	"US_IL": "IL",
	"US_IN": "IN",
	"US_IA": "IA",
	"US_KS": "KS",
	"US_KY": "KY",
	"US_LA": "LA",
	"US_ME": "ME",
	"US_MD": "MD",
	"US_MA": "MA",
	"US_MI": "MI",
	"US_MN": "MN",
	"US_MS": "MS",
	"US_MO": "MO",
	"US_MT": "MT",
	"US_NE": "NE",
	"US_NV": "NV",
	"US_NH": "NH",
	"US_NJ": "NJ",
	"US_NM": "NM",
	"US_NY": "NY",
	"US_NC": "NC",
	"US_ND": "ND",
	"US_OH": "OH",
	"US_OK": "OK",
	"US_OR": "OR",
	"US_PA": "PA",
	"US_RI": "RI",
	"US_SC": "SC",
	"US_SD": "SD",
	"US_TN": "TN",
	"US_TX": "TX",
	"US_UT": "UT",
	"US_VT": "VT",
	"US_VA": "VA",
	"US_WA": "WA",
	"US_WV": "WV",
	"US_WI": "WI",
	"US_WY": "WY",
	"CA_AB": "AB",
	"CA_BC": "BC",
	"CA_MB": "MB",
	"CA_NB": "NB",
	"CA_NL": "NL",
	"CA_NS": "NS",
	"CA_ON": "ON",
	"CA_PE": "PE",
	"CA_QC": "QC",
	"CA_SK": "SK"
};

var conditionValueMaps = {
	'source': sources,
	'mode': modes,
	'continent': continents,
	'spotterContinent': continents,
	'band': bands,
	'qsl': qslMethods,
	'state': statesShort,
	'spotterState': statesShort
};

var conditionValueSubtexts = {
	'band': bandSubtexts
};

var cqZones = {
	'1': 'Northwestern Zone of North America',
	'2': 'Northeastern Zone of North America',
	'3': 'Western Zone of North America',
	'4': 'Central Zone of North America',
	'5': 'Eastern Zone of North America',
	'6': 'Southern Zone of North America',
	'7': 'Central American Zone',
	'8': 'West Indies Zone',
	'9': 'Northern Zone of South America',
	'10': 'Western Zone of South America',
	'11': 'Central Zone of South America',
	'12': 'Southwest Zone of South America',
	'13': 'Southeast Zone of South America',
	'14': 'Western Zone of Europe',
	'15': 'Central European Zone',
	'16': 'Eastern Zone of Europe',
	'17': 'Western Zone of Siberia',
	'18': 'Central Siberian Zone',
	'19': 'Eastern Siberian Zone',
	'20': 'Balkan Zone',
	'21': 'Southwestern Zone of Asia',
	'22': 'Southern Zone of Asia',
	'23': 'Central Zone of Asia',
	'24': 'Eastern Zone of Asia',
	'25': 'Japanese Zone',
	'26': 'Southeastern Zone of Asia',
	'27': 'Philippine Zone',
	'28': 'Indonesian Zone',
	'29': 'Western Zone of Australia',
	'30': 'Eastern Zone of Australia',
	'31': 'Central Pacific Zone',
	'32': 'New Zealand Zone',
	'33': 'Northwestern Zone of Africa',
	'34': 'Northeastern Zone of Africa',
	'35': 'Central Zone of Africa',
	'36': 'Equatorial Zone of Africa',
	'37': 'Eastern Zone of Africa',
	'38': 'South African Zone',
	'39': 'Madagascar Zone',
	'40': 'North Atlantic Zone'
};

var conditionHelpTexts = {
	'callsign': 'The callsign without any prefixes/suffixes.',
	'callsign_array': 'The callsigns without any prefixes/suffixes, separated with commas, spaces or line breaks.',
	'notCallsign': 'The callsign <strong>to exclude</strong>, without any prefixes/suffixes.',
	'notCallsign_array': 'The callsigns <strong>to exclude</strong>, without any prefixes/suffixes, separated with commas, spaces or line breaks.',
	'fullCallsign': 'The full callsign, including any prefixes/suffixes.',
	'fullCallsign_array': 'The full callsigns, including any prefixes/suffixes, separated with commas, spaces or line breaks.',
	'notFullCallsign': 'The full callsign <strong>to exclude</strong>, including any prefixes/suffixes.',
	'notFullCallsign_array': 'The full callsigns <strong>to exclude</strong>, including any prefixes/suffixes, separated with commas, spaces or line breaks.',
	'spotter': 'The exact spotter callsign, including any prefixes/suffixes.',
	'spotter_array': 'The exact spotter callsigns, including any prefixes/suffixes, separated with commas, spaces or line breaks.',
	'notSpotter': 'The exact spotter callsign <strong>to exclude</strong>, including any prefixes/suffixes.',
	'notSpotter_array': 'The exact spotter callsigns <strong>to exclude</strong>, including any prefixes/suffixes, separated with commas, spaces or line breaks.',
	'prefix': 'The prefix is the beginning of the callsign up to and including any digits.<br />Examples: HB9 for HB9DQM, HB90 for HB90DQM, DL for DL/HB9DQM.<br /><strong>Don\'t use this for matching countries/DXCCs; use a DXCC condition instead.</strong>',
	'prefix_array': 'The prefix is the beginning of the callsign up to and including any digits.<br />Examples: HB9 for HB9DQM, HB90 for HB90DQM, DL for DL/HB9DQM.<br />Separate multiple prefixes with commas, spaces or line breaks.<br /><strong>Don\'t use this for matching countries/DXCCs; use a DXCC condition instead.</strong>',
	'notPrefix': 'The prefix <strong>to exclude</strong>, i.e. the beginning of the callsign up to and including any digits.<br />Examples: HB9 for HB9DQM, HB90 for HB90DQM, DL for DL/HB9DQM.<br /><strong>Don\'t use this for matching countries/DXCCs; use a DXCC condition instead.</strong>',
	'notPrefix_array': 'The prefixes <strong>to exclude</strong>, i.e. the beginning of the callsign up to and including any digits.<br />Examples: HB9 for HB9DQM, HB90 for HB90DQM, DL for DL/HB9DQM.<br />Separate multiple prefixes with commas, spaces or line breaks.<br /><strong>Don\'t use this for matching countries/DXCCs; use a DXCC condition instead.</strong>',
	'spotterPrefix': 'The prefix is the beginning of the callsign up to and including any digits.<br />Examples: HB9 for HB9DQM, HB90 for HB90DQM, DL for DL/HB9DQM.<br /><strong>Don\'t use this for matching countries/DXCCs; use a DXCC condition instead.</strong>',
	'spotterPrefix_array': 'The prefix is the beginning of the callsign up to and including any digits.<br />Examples: HB9 for HB9DQM, HB90 for HB90DQM, DL for DL/HB9DQM.<br />Separate multiple prefixes with commas, spaces or line breaks.<br /><strong>Don\'t use this for matching countries/DXCCs; use a DXCC condition instead.</strong>',
	'timeFrom': 'All times are in UTC (Z).',
	'dxcc': 'The actual DXCC of the full callsign, considering prefixes/suffixes (if any).',
	'callsignDxcc': 'The home DXCC of the callsign, ignoring any prefix/suffix.<br /><small>DXCC information provided by <a href="https://clublog.org/">Club Log</a></small>',
	'summitRefs': 'Type/paste a list of summit references, separated with commas, spaces or line breaks, here.',
	'spotterDxcc': '<small>DXCC information provided by <a href="https://clublog.org/">Club Log</a></small>',
	'bandslot': 'This condition matches missing band slots from your <a href="https://clublog.org/">Club Log</a> DXCC chart.<br />See the <a href="help#clublog">Help</a> page for more information on how to use this.',
	'cq': '<small>CQ zone information provided by <a href="https://clublog.org/">Club Log</a></small>',
	'mode': 'Note: Cluster spots may not have mode information (CW and SSB will be guessed according to the band plan if unambiguous, and most digimodes are recognized in spot comments).',
	'wwffRefs': 'Type/paste a list of park references, separated with commas, spaces or line breaks, here.',
	'qsl': '<small>LoTW: callsign has uploaded QSOs within the last 12 months according to <a href="https://lotw.arrl.org/lotw-user-activity.csv" target="_blank">this list</a>.<br />eQSL: callsign is on <a href="https://www.eqsl.cc/qslcard/DownloadedFiles/AGMemberList.txt" target="_blank">AG member list</a>.</small>',
	'state': '<small>Data obtained from FCC ULS and Government of Canada databases, updated weekly. Park state from POTA spots may override the callsign\'s home state.</small>',
	'spotterState': '<small>Data obtained from FCC ULS and Government of Canada database, updated weekly.</small>',
	'wwffRef': '<small>If you want to match any park reference in the division, then please don\'t “Select All” – instead, simply remove the “Park reference” condition and leave only the division.</small>'
};

var daysOfWeek = [
	'Sun',
	'Mon',
	'Tue',
	'Wed',
	'Thu',
	'Fri',
	'Sat'
];

var clublogModes = {
	'all': 'All',
	'cw': 'CW',
	'phone': 'Phone',
	'data': 'Data'
};

var clublogQslStatus = {
	'confirmed': 'Confirmed',
	'worked': 'Worked',
	'verified': 'Verified'
};

var clublogDateFilters = {
	'0': 'All time',
	'1': 'Last 12 months',
	'3': 'This year',
	'4': 'Last year'
};


function conditionLabel(conditionName) {
	return conditionLabels[conditionName];
}

function conditionValue(conditionName, conditionValue) {
	var map = conditionValueMaps[conditionName];
	if (map && map[conditionValue]) {
		return map[conditionValue];
	} else if (conditionName == "dxcc" || conditionName == "callsignDxcc" || conditionName == "spotterDxcc") {
		if (Array.isArray(conditionValue)) {
			var conditionValuePretty = conditionValue.map(function(x) {
				return ("00" + x).slice(-3);
			});
			return listConditionValue(conditionName, conditionValuePretty);
		} else {
			return ("00" + conditionValue).slice(-3);
		}
	} else if (conditionName == "summitRef" && Array.isArray(conditionValue)) {
		return listConditionValue("summitRefs", conditionValue);
	} else if (conditionName == "wwffRef" && Array.isArray(conditionValue)) {
		return listConditionValue("wwffRefs", conditionValue);
	} else if (arrayConditions[conditionName] && Array.isArray(conditionValue)) {
		return listConditionValue(conditionName, conditionValue);
	} else if (conditionName == "daysOfWeek") {
		return conditionValue.map(function(x) {
			return daysOfWeek[x];
		}).join(", ");
	} else {
		return htmlEscape(conditionValue);
	}
}

function listConditionValue(conditionName, conditionValue) {
	var map = conditionValueMaps[conditionName];
	var conditionValuesMapped = conditionValue;
	if (map) {
		conditionValuesMapped = conditionValuesMapped.map(function(x) {
			if (map[x])
				return map[x];
			else
				return x;
		});
	}
	
	// Show directly?
	if (conditionValue.length > 0 && conditionValue.length <= arrayConditions[conditionName].maxDisplay) {
		return htmlEscape(conditionValuesMapped.join(", "));
	}
	
	var html = conditionValue.length + " " + arrayConditions[conditionName].suffix;
	
	if (arrayConditions[conditionName].maxDisplaySmall) {
		// Make preview
		var preview = conditionValuesMapped.slice(0, arrayConditions[conditionName].maxDisplaySmall);
		preview = preview.join(", ");
		if (conditionValue.length > arrayConditions[conditionName].maxDisplaySmall) {
			preview += ", ...";
		}
		html += "<br /><small>" + htmlEscape(preview) + "</small>";
	}
	return html;
}

function actionsList(actions) {
	return actions.map(function(val) {
		return actionsMap[val];
	}).join(", ");
}

function listToKeyValueList(list) {
	var keyValueList = list.map(function(el) {
		return [el,el];
	});
	return keyValueList;
}

function objectToKeyValueList(obj, subtexts) {
	var keyValueList = [];
	$.each(obj, function(key, value) {
		if (subtexts && subtexts[key])
			keyValueList.push([key, value, subtexts[key]]);
		else
			keyValueList.push([key, value]);
	});
	return keyValueList;
}

function htmlEscape(str) {
	str = String(str);
    return str
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function cutOff(str, maxLen) {
	if (str.length <= maxLen)
		return str;
	
	return str.substr(0, maxLen - 3) + '...';
}

function uniq(a) {
	return a.sort().filter(function(item, pos, ary) {
		return !pos || item != ary[pos - 1];
	})
}
