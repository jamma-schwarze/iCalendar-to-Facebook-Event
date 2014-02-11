<?php

require_once "config.inc.php";

try {
	if (isset($_POST['updateWindowDays'])) {
		$updateWindowDays = $_POST['updateWindowDays'];
		if (empty($updateWindowDays)) {
			unset($_POST['updateWindowDays']);
		} else {
			if (! is_numeric ($updateWindowDays))
				throw new Exception ("Update window needs to be a number.");
			if ($updateWindowDays > $config['updateWindowMax'])
				throw new Exception ("Update window must be at most ".$config['updateWindowMax'].".");
		}
	}

	$database->updateSubscriptionData($_POST, $fbUserId);
	
	updatePageAccessToken ($_POST['subId'], $fbUserId);

	$msg = urlencode('Subscription was changed successfully!');
	header("Location: " . 'index.php?action=showSubscriptionList&success=1&successMsg=' . $msg);
	
} catch (Exception $e) {
	
	$errorMsg = urlencode('<p>Could not change subscription because there was an error.</p>' . $e->getMessage());
	header("Location: " . 'index.php?action=showSubscribeToiCalendar&editSub=1&error=1&errorMsg=' . $errorMsg . $fields);
}

?>
