<?php

/*
 * initializes a few things
 */

function calendarToFacebookAutoloader($class_name) {
	$file = 'classes/' . $class_name . '.php';
	if(file_exists($file))
		include_once $file;
}
spl_autoload_register('calendarToFacebookAutoloader');


date_default_timezone_set('UTC');

require_once 'config.inc.php';

$logger = new Logger();
$database = new Database();

$logger->setLogToDb($database);

//code for a GUI might set this to true in order to prevent logging the error but instead letting it propagate to the GUI
$propagateExceptions = false;

//when a PHP warning comes along it is simply logged
set_error_handler('customWarningHandler', E_WARNING);
function customWarningHandler($errNo, $errMsg) {
	$logger->warning('Error No '.$errNo.': '.$errMsg.'. In File '.$errFile .'on Line '.$errLine);
}

//when a PHP error comes along an Exception is thrown
set_error_handler('customErrorHandler', E_ALL & ~E_WARNING);
function customErrorHandler($errNo, $errMsg, $errFile, $errLine) {
	throw new Exception('Error No '.$errNo.': '.$errMsg.'. In File '.$errFile .' on Line '.$errLine);
}

//ignore deprecated warnings
set_error_handler('customDeprecatedHandler', E_DEPRECATED);
function customDeprecatedHandler($errNo, $errMsg) {
	
}



/* FACEBOOK */

require_once 'lib/facebook-php-sdk/src/facebook.php';

$facebook = new Facebook(array(
		'appId'  => $config['facebookAppId'],
		'secret' => $config['facebookSecret'],
		'cookie' => true,
		'fileUpload' => true
	));


/* Utility function to get page access token and store it in database.
 *
 * If the subscription is not a page, this function does nothing.
 */
function updatePageAccessToken ($subId, $fbUserId) {
	global $database, $facebook, $access_token;

	$sub = $database->getSubscription($subId, $fbUserId);

	# refresh page access token for pages
	if (isset ($sub->fbPageId) && !empty($sub->fbPageId)) {
		$fbPageId = $sub->fbPageId;
		$fbPageAccessToken = null;
		try
		{
			$userPages = $facebook->api('/me/accounts', 'GET', $access_token);
			# data looks like this:
			# [
			#     {
			#          "id": "192896444056133"
			#          "category": "Consulting/business services", 
			#          "category_list": [
			#            {  "id": "176831012360626", "name": "Professional Services" }
			#                 ], 
			#          "name": "Lichtzentrum des schwarzen Lotus des Ostens", 
			#          "access_token": "XYYZZ...",
			#          "perms": [ "ADMINISTER", "EDIT_PROFILE", "CREATE_CONTENT", "MODERATE_CONTENT", "CREATE_ADS", "BASIC_ADMIN" ],
			#     }, ... ]
			foreach ($userPages['data'] as $page)
			{
				if ($page['id'] == $fbPageId)
				{
					# check required permissions
					if (! (in_array("CREATE_CONTENT", $page['perms'])
						|| in_array("ADMINISTER", $page['perms'])))
						throw Exception("You do not have enough priviledges to create content for page ".$page['name']."!");
					$fbPageAccessToken = $page['access_token'];
					break;
				}
			}

		}
		catch (FacebookApiException $e)
		{
			$logger->error("Could not get page access token.", $e);
		}

		if ($fbPageAccessToken != null) {
			$database->updatePageAccessToken ($subId, $fbUserId, $fbPageAccessToken);
		}
	}
}

?>
