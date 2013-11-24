<?php

/**
 * Reads new and updated events from the db and publishes them
 *
 * @author maurobieg
 */
class Publisher {
	
	public function publishSubscription($subId) {
		//creates new events as well as updates existing one on facebook
		
		global $database;
		global $logger;
		global $facebook;
		global $config;
		
		$STH = $database->selectUserIdAndAccessToken($subId);
		$row = $STH->fetch();
		$fbUserId = $row->fbUserId;
		$token = $row->fbAccessToken;
		
		
		$STH = $database->selectNewEvents($subId);
		
		$thereIsAnotherRow = ( $row = $STH->fetch() );
		while ($thereIsAnotherRow) {
			//create new events
			
			$logger->setCurrentOurEventId($row->ourEventId);
			
			$fbEventArray = array(
			    'name' => $row->fbName,
			    'description' => $row->fbDescription,
			    'start_time' => date('c',$row->fbStartTime),
			    'end_time' => date('c',$row->fbEndTime),
			    'location' => $row->fbLocation,
			    'privacy_type' => $row->fbPrivacy, //or 'privacy' ?
			    'access_token' => $token,
			);
			
			if ($row->state == 'new' || $row->fbEventId == null) {
				$action = "create";
				if( isset($row->fbPageId) && $row->fbPageId ) {
					$fbEventArray['page_id'] = $row->fbPageId;
					$page = $row->fbPageId . '/events';
				} else {
					//post to profile
					$page = '/me/events';
				}
			} elseif ($row->state == 'updated') {
				$action = "update";
				$page = '/' . $row->fbEventId;
			} else {
				throw new Exception("Event state is neither 'new' nor 'updated' but: '".$row->state."'");
			}
			
			if( isset($row->imageFileUrl) ) {
				$file = tempnam('tmp/images/', $row->ourEventId.'_');
				if (!$file) {
					$logger->error('Could not create file in tmp/images.');
				} else {
					$imageContent = file_get_contents($row->imageFileUrl, null, null, null, $config['maxImageFileLength']);
					if ($imageContent) {
						file_put_contents ($file, $imageContent);
						# sanity check file
						if (filesize($file) > 11)
						{
							# perform sanity check: determine image type (avoid errors from Facebook)
							$it = exif_imagetype($file);
							if ($it == 1 || $it == 2 || $it == 3) {
								$fbEventArray[basename($file)] = '@'.realpath($file);
							} else {
								$logger->warning("Error adding ".$row->imageFileUrl.": File does not seem to be a GIF, PNG or JPEG.");
							}
						} else {
							$logger->warning("Error downloading ".$row->imageFileUrl.": File too small.");
						}
					} else {
						$logger->warning("Error downloading ".$row->imageFileUrl.".");
					}
				}
			} else {
				$imageContent = false;
			}
			
                        try {
                                $response = $facebook->api($page, 'post', $fbEventArray);

                                if ($response) {
                                        if ( $action == "update") {
                                                $fbEventId = $row->fbEventId;
                                        } elseif ( $action == "create" && is_numeric($response['id']) ) {
                                                $fbEventId = $response['id'];
                                        } else {
                                                throw new Exception("Response is not valid");
                                        }
                                        $database->setEventUpdated($row->ourEventId, $fbEventId);
                                        $logger->info("Event ".$action."d on facebook. fbEventId: " . $fbEventId);
                                } else {
                                        throw new Exception("Response when trying to ".$action." event was negative.");
                                }
                        } catch (Exception $e) {
                                if ($action == "create" || $propagateExceptions) {
                                        //if failed to create event don't bother trying the rest of the subscription
                                        throw $e;
                                } else {
                                        //if only an update failed go on with the other events of the subscription
                                        $logger->warning("Could not update event on Facebook.", $e);
                                }
                        }
			
			if( isset($file) && file_exists($file) )
				unlink($file);
			
			$logger->unsetCurrentOurEventId();
			
			$thereIsAnotherRow = ( $row = $STH->fetch() );
			if ($thereIsAnotherRow)
				sleep($config['waitForNextEventPublish']);
		}
		
	}	
	
}

?>
