<?php $loginOptional = true; include('settings_begin.inc.php') ?>

<h1 class="page-header">Help</h1>

<div class="alert alert-info" role="alert">
	If you can't find the answer to your question here, try searching the <strong><a href="https://forum.hamalert.org/">HamAlert Forum</a></strong>. Perhaps somebody has already asked the same question there and got an answer, or you could ask there to get help from other users. <strong>Please try the forum first before emailing the author of HamAlert directly.</strong> Thanks!
</div>

<h3>I forgot my password, what can I do?</h3>

<p>You can reset your password here: <a href="https://hamalert.org/forgotpass">Forgot password</a>.</p>

<h3>How can I change my username/callsign?</h3>

<p>You can change it here: <a href="https://hamalert.org/change_username">Change username</a>.</p>

<h3>How can I receive alerts on my smartphone?</h3>

<p>There are two options:</p>

<ul>
	<li><strong>HamAlert app</strong>
		<p>The HamAlert app is available on <a href="https://play.google.com/store/apps/details?id=org.hamalert.app">Google Play</a> and the <a href="https://itunes.apple.com/us/app/hamalert/id1200759798?mt=8">App Store</a>. It can receive	push notifications for free, and shows spots from the past 24 hours. Install the app and log in with your HamAlert username/password. Any alerts for triggers with the “App” action enabled will then be sent to your smartphone.</p>
	</li>
	<li><strong>Threema</strong>
		<p>You can also get push notifications sent to <a href="https://threema.ch">Threema</a>, a secure encrypted mobile messenger. Set your Threema ID on the <a href="/destinations">Destinations</a> page, and enable the “Threema” action in your triggers.</p></li>
	</li>
</ul>


<h3>How can I receive alerts on my desktop computer?</h3>

<p>Here are some ideas:</p>

<ul>
	<li>Set up your favorite cluster monitoring or logging software to connect to the <a href="/destinations">Telnet interface</a> of HamAlert.</li>
	<li>Use the <a href="https://www.oe3ide.com/hamalert-cluster-client/">HamAlert Cluster Client for Windows</a> created by Ernst OE3IDE, which sends Windows taskbar notifications on new spots.</li>
</ul>


<h3>I'm not receiving any alerts – what's wrong?</h3>

<p>Please check/note the following:</p>

<ul>
	<li>Have you defined a <a href="triggers">trigger</a> with conditions that actually match the spot that you wanted to receive?</li>
	<li>Have you selected the desired actions (e.g. App, Telnet etc.) in the trigger definition?</li>
	<li>Are your <a href="destinations">Destinations</a> set up correctly?</li>
	<li>Are alerts enabled for your account? (checkbox at the top of the <a href="destinations">Destinations</a> page)</li>
	<li>Test your trigger(s) by <a href="simulate">simulating</a> a spot.</li>
	<li>Are you perhaps hitting a <a href="limits">limit</a>?</li>
	<li>Note that the HamAlert app only receives spots from triggers that have the “App” action enabled, and does not show spots retroactively
		(i.e. spots that occurred before a trigger was set up).</li>
	<li>For PSK reporter spots, a quorum of three different spotters must be reached for the same combination of callsign/band/mode until spots are processed (spots are held back until the quorum is reached). This is to prevent erroneous alerts due to spotters with a misconfigured band.</li>
	<li>Note that HamAlert ignores SOTAwatch spots that contain the word “test” or “testing”.</li>
</ul>


<h3>What is the difference between the “Callsign” and “Full callsign” conditions?</h3>

<p>The “full callsign” is exactly how the callsign was received from the source, including prefixes/suffixes, if any. So if you use a “full callsign” condition, the callsign in the spot must match 100% what you entered in the trigger condition. For the “callsign” condition, any prefixes/suffixes are stripped before comparison.<p>

<div class="panel panel-info" style="display: inline-block">
	<div class="panel-heading"><h3 class="panel-title">Example 1</h3></div>
	<div class="panel-body">
		Condition: <strong>Full callsign</strong> = HB9DQM<br />
		Spot: F/HB9DQM/P<br />
		<strong>The trigger will <em>not</em> be executed.</strong>
	</div>
</div>

<div class="panel panel-info" style="display: inline-block; margin-left: 1em">
	<div class="panel-heading"><h3 class="panel-title">Example 2</h3></div>
	<div class="panel-body">
		Condition: <strong>Full callsign</strong> = HB9DQM/P<br />
		Spot: HB9DQM/P<br />
		<strong>The trigger will be executed.</strong>
	</div>
</div>

<div class="panel panel-info" style="display: inline-block; margin-left: 1em">
	<div class="panel-heading"><h3 class="panel-title">Example 3</h3></div>
	<div class="panel-body">
		Condition: <strong>Callsign</strong> = HB9DQM<br />
		Spot: F/HB9DQM/P<br />
		<strong>The trigger will be executed.</strong>
	</div>
</div>


<a name="clublog"></a><h3>How do I use the Club Log integration?</h3>

<script type="text/javascript">
	var sections = ['dxcc', 'bandslots']
	function showClubLogSection(name) {
		sections.forEach(function(section) {
			if (section == name) {
				$('#clubLogHelp_' + section).show();
				$('#clubLogTri_' + section).html('▾');
			} else {
				$('#clubLogHelp_' + section).hide();
				$('#clubLogTri_' + section).html('▸');
			}
		});
	}
</script>

<p>You can link your <a href="https://clublog.org">Club Log</a> account with your HamAlert account so that you can use Club Log's information
	on which DXCCs or band slots (DXCC + band) you have not worked yet.<p>

<p>To get started, perform the following steps:</p>

<ol class="clublogHelp">
	<li>Create an <a href="https://clublog.org/edituser.php?tab=7">Application Password</a> for HamAlert in your Club Log account.</li>
	<li>Go to the HamAlert <a href="account">Account</a> page and enter your Club Log email address and the Application Password that you just created.</li>
	<li>Create a new trigger by clicking the + button on the <a href="triggers">Triggers</a> page.</li>

	<li>Decide whether you're interested in unworked DXCCs only, or in unworked band slots.</li>
</ol>

<h4><a href="#" onclick="showClubLogSection('dxcc'); return false"><span id="clubLogTri_dxcc">▸</span> DXCCs only</a></h4>
<ol start="5" style="display: none" class="clublogHelp" id="clubLogHelp_dxcc">
	<li>Add a “DXCC“ condition.</li>
	<li>Click the “Load from Club Log” link below the menu.</li>
	<li>Enter the callsign to use when querying Club Log.
		<ul>
			<li>You may have multiple callsigns attached to your Club Log account.</li>
		</ul>
	</li>
	<li>Choose the modes to find unworked DXCCs for.
		<ul>
			<li>For example, if you choose “CW”, then DXCCs that you have only worked in SSB or data modes will be considered as unworked.</li>
			<li>Choose “All” if you don't care which mode you have worked a certain DXCC in, as long as you have worked it.</li>
			<li>The mode of an actual spot does not have to match this; use the separate “Mode” condition for that.</li>
		</ul>
	</li>
	<li>Choose the QSL statuses to find unworked DXCCs for.
		<ul>
			<li>If you check all of the options, then only those DXCCs will be treated as unworked that you have not worked at all, regardless of the QSL status.</li>
			<li>If you check e.g. “Confirmed”, then any DXCC that you have not confirmed on any band (even though you may have worked it), will be considered as unworked.</li>
		</ul>
	</li>
	<li>Click the button to load unworked DXCCs from Club Log and update the DXCC condition in your trigger.<br />
		<em>Note that this is a manual one-time load; if you work new DXCCs after loading the list, it will not be reflected automatically.</em></li>
</ol>

<h4><a href="#" onclick="showClubLogSection('bandslots');  return false"><span id="clubLogTri_bandslots">▸</span> Band slots</a></h4>
<ol start="5" style="display: none" class="clublogHelp" id="clubLogHelp_bandslots">
	<li>Add a “Band slots” condition.</li>
	<li>Enter the callsign to use when querying Club Log.
		<ul>
			<li>You may have multiple callsigns attached to your Club Log account.</li>
		</ul>
	</li>
	<li>Choose the modes to find unworked band slots for.
		<ul>
			<li>For example, if you choose “CW”, then you will still get alerts for band slots that you have only worked in SSB or data modes.</li>
			<li>Choose “All” if you don't care which mode you have worked a certain band slot in, as long as you have worked it.</li>
			<li>The mode of an actual spot does not have to match this; use the separate “Mode” condition for that.</li>
		</ul>
	</li>
	<li>Choose the QSL statuses to find unworked band slots for.
		<ul>
			<li>If you check all of the options, then only those band slots will be treated as unworked that you have not worked at all, regardless of the QSL status.</li>
			<li>If you check e.g. “Confirmed”, then you will still get alerts for any band slots that you have worked, but not confirmed.</li>
		</ul>
	</li>
	<li>Choose the bands to consider when looking for unworked band slots (the band condition is mandatory).</li>
	<li>Add additional conditions to prevent receiving alerts for stations that you cannot hear/work anyway.
		<ul>
			<li>Hint: add a “Spotter continent” condition to only consider spots from spotters on the same continent as you.</li>
			<li>Or even better, for CW operators: try adding the callsign of your nearest <a href="http://reversebeacon.net">RBN</a> node as a “Spotter callsign” condition!</li>
			<li>Too many false alarms from the RBN? Add a “Source” condition and set it to Cluster.</li>
		</ul>
	</li>
	<li>Save the trigger and wait for a few minutes until the band slots data has been loaded (refresh the triggers page to see the new numbers).<br />
		<em>The band slot information from Club Log is automatically updated once a day, or shortly after you create or save a trigger with a band slots
	condition. Refresh the triggers page to see the updated band slots count and date.</em></li>
</ol>


<h3>How can I delete spots in the HamAlert app?</h3>

<p>Swipe left to delete spots in the app. Spots will also disappear automatically after 24 hours.<p>


<h3>I'm getting too many alerts, what can I do?</h3>

<p>Try to make your triggers more specific by adding more conditions, so the alerts that you get match more precisely what you are interested in. You can also set <a href="limits">limits</a> on the
	number of alerts per time interval — either on a general basis or just for the same callsign/band/mode. However, it is more useful to reduce the amount of alerts that you get by 
	making specific triggers, as setting low limits will just cause you to miss alerts on a seemingly random basis (whenever the limit was reached).</p>

<p>If you are using the HamAlert app and want to temporarily stop push notifications, you can do so conveniently within the app using the switch in the left side menu.</p>


<a name="trigger_auto_disable"></a><h3>Why has my trigger been automatically disabled?</h3>

<p>HamAlert automatically disables triggers that match more than 10000 spots per day. If a trigger matches so many spots, it is a clear indication that the trigger conditions are
	too broad, making the trigger relatively useless, since it will only match a random set of spots (moderated by the rate limits). Furthermore, it causes unnecessary load on the
	HamAlert backend, because many spots will match such triggers, only for the prepared alert to be suppressed by rate limiting.</p>

<p>Please add or refine the conditions on such triggers so that they are more specific and will only match the spots that you are really interested in. This is for your own good,
	as irrelevant alerts will only waste your time, and it helps HamAlert by not putting unnecessary load on its backend.</p>


<h3>What does this cost?</h3>

<p>Nothing! I intend to make HamAlert available for free to the amateur radio community for as long as I can afford the time (and money) to keep the system running and capable of handling the number of users. While I try my best to keep the system available and performing at all times, there are obviously no guarantees.</p>


<h3>Where can I get an image with my last spot to put on my QRZ.com page?</h3>

<p>On the <a href="/account">account page</a> (after logging in) – look for “MySpot”.</p>


<?php include('settings_end.inc.php') ?>
