<?php
class lyvDAL {
	protected $aIgnoreSave = array("sTableName", "aIgnoreSave", "aClassExludeVars", "iId", "dtCreatedate", "iCreateuser", "dtModifydate", "iModifyuser");
	protected $sTableName;
	protected $sIdFieldName;
	protected $aClassExludeVars = array();
	
	protected $oDb;
	
	public $iId;
	
	public $dtCreatedate = 0;
	public $iCreateuser = 0;
	public $dtModifydate = 0;
	public $iModifyuser = 0;
	
	public function __construct($iId = 0) {
		// TODO: Create DB Connection
		$this->oDb = Database::obtain(); 
		
		// If specific item requested, load it
		if($iId != 0) {
			$this->iId;
			$this->load();
		} else {
			// TODO: Default create/modify values
		}
	}
	
	public function load() {
		// First check DB connection
		if(!isset($this->oDb) || empty($this->oDb) || !$this->oDb->bIsConnected) {
			throw new ErrorException(get_class($this) . " Load: Unable to connect to database.");
		}
		$sSql = "SELECT * FROM [" . $this->sTableName . "] WHERE [" . $this->sIdFieldName . "] = ?";
		$aResults = $this->oDb->queryFirst($sSql, $this->iId);
		if($aResults) {
			$this->putClassVars($aResults);
		} else {
			throw new ErrorException(get_class($this) . ": No results found.");
		}
	}
	
	public function save($iUserId = 0) {
		$aData = $this->getClassVars();
		if($this->iId == 0) {
			// TODO: Insert
			// Set create/modify date and user
			$aData['createdate'] = date("Y-m-d H:i", $this->dtCreatedate);
			$aData['createuser'] = $iUserId;
			$aData['modifydate'] = date("Y-m-d H:i");
			$aData['modifyuser'] = $iUserId;
		} else {
			// TODO: Update
			// Get modifydate and modifyuser
			$aData['modifydate'] = date("Y-m-d H:i");
			$aData['modifyuser'] = $iUserId;
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
