<?php
class DAL {
	public $sTableName;
	public $sIdFieldName;
	public $iId;
	public $aClassExludeVars = array();
	public $oDb;
	
	public function __construct($iId = 0) {
		// TODO: Create DB Connection
		
		// If specific item requested, load it
		if($iId != 0) {
			$this->load();
		}
	}
	
	public function load() {
		$sSql = "SELECT * FROM [" . $this->sTableName . "] WHERE [" . $this->sIdFieldName . "] = ?";
		$aResults = $this->oDb->queryFirst($sSql, $this->iId);
		if($aResults) {
			$this->putClassVars($aResults);
		} else {
			throw new ErrorException(get_class($this) . ": No results found.");
		}
	}
	
	public function save() {
		$aData = $this->getClassVars();
		if($this->iId == 0) {
			// TODO: Insert
		} else {
			// TODO: Update
		}
	}
	
	private function getClassVars() {
		// TODO: Cycle through each property
		
		// TODO: Exclude required variables
		
		// TODO: Strip type
	}
	
	private function putClassVars($aData) {
		// TODO: Cycle through each property
		
		// TODO: Strip type
		
		// TODO: Update value if it is in aData
	}
}
?>