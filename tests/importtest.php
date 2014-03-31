<?php
chdir( ".." );

require_once 'include/initialize.php';


$importer = new iCalcreatorImporter();

#$calUrl = '/tmp/lz-1.ics';
#$calUrl = 'tests/lz-2.ics';

#$calUrl = 'file:///Users/maurobieg/Sites/calendar_to_facebook_tschurrn/tests/files/g.ics';

#$calUrl = 'http://ctip.org.uk/index.php?option=com_eventlist&view=eventlist&format=ical&Itemid=2';

$calUrl = 'https://ical2fb.lichtzentrum-meissen.de/tests/xxx.ics';

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

if (php_sapi_name() != "cli")
{
	echo '<html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8"></head>';
	echo "<body><pre>";
}

var_dump($importer->subscription);

$moduleNames = array();
$moduleNames[] = 'standardModule';

echo "apply modules...\n";

//TODO: select right modules
$importer->applyModules($moduleNames);

var_dump($importer->subscription);
?>
