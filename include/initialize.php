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
			# fetch access token directly
			$page = $facebook->api("/$fbPageId?fields=access_token", 'GET', $access_token);
			$fbPageAccessToken = $page['access_token'];
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
