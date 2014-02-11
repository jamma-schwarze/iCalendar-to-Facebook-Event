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
		global $propagateExceptions;
		
		$STH = $database->selectUserIdAndAccessToken($subId);
		$row = $STH->fetch();
		$fbUserId = $row->fbUserId;
		$token = $row->fbAccessToken;
		
		
		$STH = $database->selectNewEvents($subId);
		
		$thereIsAnotherRow = ( $row = $STH->fetch() );
		while ($thereIsAnotherRow) {
			//create new events
			
			$logger->setCurrentOurEventId($row->ourEventId);
			
			$rowToken = $token;

			// use page access token for pages (cover image updating needs it!)
			if (isset($row->fbPageId) && $row->fbPageId && isset($row->fbPageAccessToken)) {
				$rowToken = $row->fbPageAccessToken;
			}
			
			$fbEventArray = array(
			    'name' => $row->fbName,
			    'description' => $row->fbDescription,
			    'start_time' => date('c',$row->fbStartTime),
			    'end_time' => date('c',$row->fbEndTime),
			    'location' => $row->fbLocation,
			    'privacy_type' => $row->fbPrivacy, //or 'privacy' ?
			    'access_token' => $rowToken,
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

			$cover_url = null;

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
							# perform sanity check: determine image type and dimension (avoid errors from Facebook)
							list($img_width, $img_height, $it, $attr) = getimagesize($file);
							if ($it == IMAGETYPE_JPEG || $it == IMAGETYPE_PNG || $it == IMAGETYPE_GIF) {
								$logger->info("Image ".$row->imageFileUrl." has dimension $img_width x $img_height");
								# check whether image is usable as cover URL
								if ($img_width > 399 && $img_height >= 150) {
									$cover_url = $row->imageFileUrl;
									# note: for now, we post the picture anyway
								}
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
			
			$fbEventId = null;

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
					$fbEventId = null;
                                        throw new Exception("Response when trying to ".$action." event was negative.");
                                }
                        } catch (Exception $e) {
                                if ($action == "create" || $propagateExceptions) {
                                        //if failed to create event don't bother trying the rest of the subscription
                                        throw $e;
                                } else {
                                        //if only an update failed go on with the other events of the subscription
					$fbEventId = null;
                                        $logger->warning("Could not update event on Facebook.", $e);
                                }
                        }
			
			# we've got a cover url -> try to update
			if ($cover_url != null) {
				try {
					$coverArr = array ("cover_url" => $cover_url,
						"access_token" => $rowToken);
					$response = $facebook->api("/$fbEventId", 'post', $coverArr);
					if ($response) {
						$logger->info("Cover image updated on Facebook. fbEventId: $fbEventId, image url: $cover_url");
					} else {
						$logger->warning("Could not update cover image $cover_url for event $fbEventId on Facebook.", $e);
					}
				} catch (Exception $e) {
					if ($propagateExceptions) {
						throw $e;
					} else {
						// warn, but do not abort
						$logger->warning("Could not update cover image $cover_url for event $fbEventId on Facebook.", $e);
					}
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
