<?php

/**
 * This standard Module is usually applied first to the events. It fills in the basic parameters for fb.
 *
 * @author maurobieg
 */
class standardModule extends Module{
	
	public function apply() {
		global $config;
		global $database;
		
		$sub = $this->sub;
		
		$caltz = null;
		# try using X-WR-TIMEZONE if no timezone was set for the calendar
		# (iCalcreator already adjusted for the TZID)
		if (is_null($sub->getCalTZID())
		   && !is_null($sub->getCalXWRTIMEZONE()) ) {
			$caltz = $sub->getCalXWRTIMEZONE();
		}
                
                //delete events that are too old
                foreach ($sub->eventArray as $key => $e) {
                        if ( strtotime($e['calDTSTART']) < strtotime($config['defaultWindowOpen']) ) {
                                unset($sub->eventArray[$key]);
                        }
                }
                
		$imageProperty = $database->getImageProperty( $sub->getSubId() );

		foreach ($sub->eventArray as &$e) {
                        
			$e['fbName'] = mb_substr($e['calSUMMARY'], 0, $config['maxFbTitleLength']);
			
			$e['fbDescription'] = $e['calDESCRIPTION'];
			
			$e['fbLocation'] = $e['calLOCATION'];
			
			if( isset($e['calDTStartTZID']) )
				$e['fbStartTime'] = $this->toFbTime( $e['calDTSTART'], $e['calDTStartTZID'] );
			else
				$e['fbStartTime'] = $this->toFbTime( $e['calDTSTART'], $caltz);
			
			if( isset($e['calDTEndTZID']) )
				$e['fbEndTime'] = $this->toFbTime( $e['calDTEND'], $e['calDTEndTZID'] );
			else
				$e['fbEndTime'] = $this->toFbTime( $e['calDTEND'], $caltz );
			
                        if ($e['fbStartTime'] == $e['fbEndTime']) {
                                //event must have a duration
                                $e['fbEndTime']++;
                        }
                        
                        
			if ( isset($e['calCLASS']) &&  $e['calCLASS'] == 'PRIVATE')
				$e['fbPrivacy'] = "CLOSED";
			elseif ( isset($e['calCLASS']) &&  $e['calCLASS'] == 'CONFIDENTIAL')
				$e['fbPrivacy'] = "SECRET";
			else
				$e['fbPrivacy'] = "OPEN";
			
			if ($imageProperty && isset ($e[$imageProperty])) {
				$e['imageFileUrl'] = $e[ $imageProperty ];
			}
		}
	}
	
	private function toFbTime($time, $eventTZ = null) {

		# Facebook has UTC as default
		date_default_timezone_set('UTC');
		
		# this interprets the event's time in (UTC)
		$timestamp = strtotime($time);
		
		# if event has a timezone set adjust to UTC
		if (!is_null($eventTZ)) {
			$calTz = new DateTimeZone($eventTZ);
			$datetime = new DateTime("@$timestamp");
			$tzOffset = timezone_offset_get($calTz, $datetime);
			$timestamp -= $tzOffset;
		}
		
		return $timestamp;
	}
}

?>
