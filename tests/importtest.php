<?php
chdir( ".." );

require_once 'include/initialize.php';


$importer = new iCalcreatorImporter();

$calUrl = '/tmp/lz-1.ics';
$calUrl = 'tests/lz-2.ics';

#$calUrl = 'file:///Users/maurobieg/Sites/calendar_to_facebook_tschurrn/tests/files/g.ics';

#$calUrl = 'http://ctip.org.uk/index.php?option=com_eventlist&view=eventlist&format=ical&Itemid=2';

$subId = null;

#$eventXProperties = array('ATTACH');
$eventXProperties = null;

$importer->downloadAndParse(
	$calUrl,
	strtotime( $config['defaultWindowOpen'] ), 
	strtotime( $config['defaultWindowClose'] ),
	$subId,
	$eventXProperties
);

var_dump($importer->subscription);

?>
