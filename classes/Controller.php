<?php


/**
 * With a Controller object you can invoke common functionality to bring certain subscription up to date on facebook.
 *
 * @author maurobieg
 */
class Controller {
	
	private $importer;
	private $publisher;
	
	public function checkUrl($calUrl) {
		$this->setImporter($calUrl);
	}
	
	public function updateSub($subId){
		//fetches new events and publishes them to facebook
		//regardless whether the subscription is active or not
		
		global $logger;
		
		$logger->setCurrentSubId($subId);
		
		$this->importSub($subId);
		$this->publishSub($subId);
		
		$logger->unsetCurrentSubId();
	}
	
	public function importSub($subId){
		global $database;

		$sub = $database->getSubscription($subId);
                //downloads and parses iCal-file
                $this->setImporter(NULL, $sub);
		
                $moduleNames = array();
                $STH = $database->getModules($subId);
                while ( $row = $STH->fetch() ) {
                        $moduleNames[] = $row->module;
                }
                $moduleNames[] = 'standardModule';
                
                //TODO: select right modules
		$this->importer->applyModules($moduleNames);
		$this->importer->saveToDb();
		
		$database->setSuccessfulImport($subId);
	}
	
	public function publishSub($subId) {
		global $database;
                global $logger;
		global $propagateExceptions;
                
		$this->publisher = new Publisher();
                
                try{
                        $this->publisher->publishSubscription($subId);
                        $database->setSuccessfulPublish($subId);
                } catch(Exception $e){
                        if ($propagateExceptions)
                                throw $e;
                        else
                                $logger->warning("Event could not be created on Facebook.", $e);
                }
	}
	
	// pass either calUrl or sub
	private function setImporter($calUrl, $sub = null) {
		global $config;
		global $database;
		
		if ($sub != null) {
			$imageProperty = $sub->imageProperty;
			if ($imageProperty) {
				$eventXProperties = array($imageProperty);
			} else {
				$eventXProperties = null;
			}
			$updateWindowDays = $sub->updateWindowDays;
			if ($updateWindowDays == null) {
				$updateWindowDays = $config['defaultUpdateWindow'];
			}
			$calUrl = $sub->calUrl;
			$subId = $sub->subId;
		} else {
			$updateWindowDays = $config['defaultUpdateWindow'];
			$eventXProperties = null;
			$subId = null;
		}
		
		$this->importer = new iCalcreatorImporter(); //or qCalImporter()
		
		$this->importer->downloadAndParse(
				$calUrl,
				strtotime( $config['defaultReccurWindowOpen'] ), 
				strtotime("+$updateWindowDays days"),
				$subId,
				$eventXProperties
			);
	}
}

?>
