<?php
$loginOptional = true;
$pageTitle = 'About';
include('settings_begin.inc.php') ?>

<h1 class="page-header">About HamAlert</h1>

<h3>What is HamAlert?</h3>

<p class="lead">HamAlert is a system that allows you to get notifications when a desired station appears on 
	the <a href="https://dxwatch.com/">DX cluster</a>, the <a href="http://www.reversebeacon.net">Reverse Beacon Network</a>,
	<a href="https://sotawatch.sota.org.uk">SOTAwatch</a>, <a href="https://pota.app">POTA</a>, <a href="https://wwff.co/spotline/">WWFF Spotline</a>, or <a href="https://pskreporter.info">PSK Reporter</a>.
	No need to keep checking these resources manually if you're looking for a certain callsign, DXCC, CQ zone, IOTA island, SOTA summit or WWFF/POTA reference.</p>
		
	<p>You can receive alerts via Push notifications, Threema, Telnet or URL GET/POST. You can also filter spots by various criteria, including:</p>

<ul>
	<li>DXCC (both actual and callsign home DXCC)</li>
	<li>Callsign</li>
	<li>IOTA group reference</li>
	<li>SOTA summit reference</li>
	<li>WWFF/POTA division/reference</li>
	<li>CQ zone</li>
	<li>Continent</li>
	<li>Band</li>
	<li>Mode</li>
	<li>Time and days of week</li>
	<li>Source</li>
	<li>Spotter callsign and DXCC</li>
</ul>

<p>Furthermore, you can set limits on the number of alerts in a certain time period so you don't receive duplicate spots,
	but are still notified if for example the station changes to another frequency.</p>

<h3>DXCC lookups</h3>

<p>Accurately determining the DXCC and CQ zone of a particular callsign is no easy feat. Luckily, the team at <a href="http://clublog.org">Club Log</a> are experts at this and
   provide an API that HamAlert uses. Thank you, Club Log, for making this API available!</p>


<h3>Digimode spots</h3>

<p>Digimode spots (with the “PSK reporter” source) are obtained from <a href="https://pskreporter.info">pskreporter.info</a>. Thanks to Philip Gladstone for running the system and letting HamAlert access its data!</p>


<h3>Author</h3>

<p>HamAlert was created by Manuel Kasper (HB9DQM). Instead of emailing the author with questions, issues or feature requests, please search or post on the <a href="https://forum.hamalert.org/">HamAlert Forum</a>. Thanks!</p>


<h3>Source code</h3>

<p>The source code for HamAlert can be found on <a href="https://github.com/hamalert">GitHub</a>.</p>


<h3>Privacy Policy</h3>

<p>See the <a href="privacy">Privacy Policy</a> for HamAlert.</p>

<?php include('settings_end.inc.php') ?>
