<?php

# several pages do not require login, so do not impose
# additional overhead
$pages_without_login = array(
	'showDocs',
	'showImprint',
	'showPolicy'
	);

# if no action given, display docs
$pageAction = "showDocs";

if (isset($_GET['action'])) {
	$pageAction = $_GET['action'];
}

if (in_array ($pageAction, $pages_without_login)) {
	require "pages/$pageAction.php";
}
else
{
	require_once 'include/initialize.php';

	if ($config['debugWithoutFacebook'])
		$fbUserId = $config['debugWithoutFacebookUserId'];
	else
		$fbUserId = $facebook->getUser();

	$logger->setCurrentFbUserId($fbUserId);

	if ($fbUserId) {
		//if user logged in store access token in db

		if (!$config['debugWithoutFacebook']) {
			$token = $facebook->getAccessToken();
			if ($token) {
				// get long-lived access token
				if ($facebook->setExtendedAccessToken()) {
					$token = $facebook->getAccessToken();
				}
			}
			$database->storeAccessToken($token, $fbUserId);
			# for FB API calls
			$access_token = array('access_token' => $token);
		}
		
		$propagateExceptions = true;
		
		if ( isset($_GET['action']) ) {
			switch ($_GET['action']) {
				case "showSubscribeToiCalendar":
					require 'pages/subscribeToiCalendarPage.php';
					break;
				case "doUpdate":
					require 'pages/doUpdatePage.php';
					break;
				case "doSubscribe":
					require 'pages/doSubscribePage.php';
					break;
				case "doUnsubscribe":
					require 'pages/doUnsubscribePage.php';
					break;
				case "showErrorLog":
					require 'pages/showErrorLogPage.php';
					break;
				case "doEditSubscription":
					require 'pages/doEditSubscriptionPage.php';
					break;
				case "doDeactivate":
					require 'pages/doDeactivatePage.php';
					break;
				case "doActivate":
					require 'pages/doActivatePage.php';
					break;
				default:
					require 'pages/subscriptionListPage.php';
			}
		} else {
			require 'pages/subscriptionListPage.php';
		}
		
	} else {
		//user not logged in yet	
		require 'pages/loginPage.php';
	}
}

?>
