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
		
		$sub->setFinalTimezone( $this->getTimezone() );
                
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
				$e['fbStartTime'] = $this->toFbTime( $e['calDTSTART']);
			
			if( isset($e['calDTEndTZID']) )
				$e['fbEndTime'] = $this->toFbTime( $e['calDTEND'], $e['calDTEndTZID'] );
			else
				$e['fbEndTime'] = $this->toFbTime( $e['calDTEND'] );
			
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
			
			if ($imageProperty) {
				$e['imageFileUrl'] = $e[ $imageProperty ];
			}
		}
	}
	
	
	private function getTimezone() {
		// sets finalTimezone to either calTZID or calXWRTIMEZONE
		
		if ( !is_null($this->sub->getCalTZID()) ) {
			$tz = $this->sub->getCalTZID();
		} elseif ( !is_null($this->sub->getCalXWRTIMEZONE()) ) {
			$tz = $this->sub->getCalXWRTIMEZONE();
		}
		
		if( !isset($tz) )
			$tz = 0;
		
		return $tz;
	}
	
	
	private function toFbTime($time, $eventTZ = null) {

		# Facebook has UTC as default
		date_default_timezone_set('UTC');
		
		# this interprets the event's time in (UTC)
		$timestamp = strtotime($time);
		
		# if event has a timezone set adjust to UTC
		if (isset($eventTZ)) {
			$calTz = new DateTimeZone($eventTZ);
			$datetime = new DateTime("@$timestamp");
			$tzOffset = timezone_offset_get($calTz, $datetime);
			$timestamp -= $tzOffset;
		}
		
		return $timestamp;
	}
}

?>
