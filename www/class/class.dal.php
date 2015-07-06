<?php
class lyvDAL {
	protected $aIgnoreSave = array("sTableName", "aIgnoreSave", "aClassExludeVars", "dtCreatedate", "iCreateuser", "dtModifydate", "iModifyuser");
	protected $sTableName;
	protected $aClassExludeVars = array();
	
	protected $oDb;
	
	public $dtCreatedate = 0;
	public $iCreateuser = 0;
	public $dtModifydate = 0;
	public $iModifyuser = 0;
	
	public function __construct($iId = 0) {
		// Create DB Connection
		$this->oDb = Database::obtain(); 
		
		// If specific item requested, load it
		if($iId != 0) {
			$this->load($iId);
		} else {
			// Default create/modify values
			$this->dtCreatedate = date("Y-m-d H:i");
			$this->dtModifydate = date("Y-m-d H:i");
		}
	}
	
	public function load($iId) {
		// First check DB connection
		if(!isset($this->oDb) || empty($this->oDb) || !$this->oDb->bIsConnected) {
			throw new ErrorException(get_class($this) . " Load: Unable to connect to database.");
		}
		$sSql = "SELECT * FROM [" . $this->sTableName . "] WHERE [" . $this->sTableName . "id] = ?";
		$aResults = $this->oDb->queryFirst($sSql, $iId);
		if($aResults) {
			$this->putClassVars($aResults);
		} else {
			throw new ErrorException(get_class($this) . ": No results found.");
		}
	}
	
	public function save($iUserId = 0) {
		$aData = $this->getClassVars();
		$sIdField = "i" . $this->sTableName . "id";
		if($this->$sIdField == 0) {
			// Set create/modify date and user
			$aData['createdate'] = date("Y-m-d H:i", $this->dtCreatedate);
			$aData['createuser'] = $iUserId;
			$aData['modifydate'] = date("Y-m-d H:i");
			$aData['modifyuser'] = $iUserId;
			
			// Do INSERT
			$iSqlResult = $this->oDb->insert(strtoupper($this->sTableName), $aData);
			
			if($iSqlResult !== 0) {
				$this->$sIdField = $iSqlResult;
			} else {
				$bRetVal = false;
			}
			
			$iSqlResult = $this->oDb->insert("DB_AUDIT", array(
			  "userid" => $aData['modifyuser'],
			  "tablename" => strtoupper($this->sTableName),
			  "rowid" => $this->$sIdField,
			  "action" => "CREATE"
			));
		} else {
			// Get modifydate and modifyuser
			$aData['modifydate'] = date("Y-m-d H:i");
			$aData['modifyuser'] = $iUserId;
			
			// Do UPDATE
			$this->oDb->update(strtoupper($this->sTableName), $aData, array("id" => $this->$sIdField));
			$iSqlResult = $this->oDb->insert("DB_AUDIT", array(
			  "userid" => $aData['modifyuser'],
			  "tablename" => strtoupper($this->sTableName),
			  "rowid" => $this->$sIdField,
			  "action" => "UPDATE"
      			));
		}
	}
	
	private function getClassVars() {
		// Cycle through each property
		$aVars = array();
		$sIdField = "i" . $this->sTableName . "id";
		foreach(get_object_vars($this) as $key => $value) {
			// Exclude required variables
			if(!array_key_exists($key, array_merge($this->aClassExludeVars, $this->aIgnoreSave, array($sIdField)))) {
				// Strip type
				if(preg_match('/^(dt|[a|b|d|f|i|s])(.+)$/i', $key, $result) !== false) {
					$aVars[$result[2]] = $value;
				} else {
					$aVars[$key] = $value;
				}
			}
		}
		
		return $aVars;
	}
	
	private function putClassVars($aData) {
		// Cycle through each property
		foreach(get_object_vars($this) as $key => $value) {
			// Strip type
			if(preg_match('/^(dt|[a|b|d|f|i|s])(.+)$/i', $key, $result) !== false) {
				$sFieldName = strtolower($result[2]);
				// Update value if it is in aData
				if(array_key_exists($sFieldName, $aData)) {
					$this->$key = $aData[$sFieldName];
				}
			}
		}
	}
}
