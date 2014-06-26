<?php
class DAL {
	public $sTableName;
	public $iId;
	public $aClassExludeVars = array();
	
	public function __construct($iId = 0) {
	}
	
	public function load() {
	}
	
	public function save() {
	}
	
	private function getClassVars() {
		// TODO: Cycle through each property
		
		// TODO: Exclude required variables
		
		// TODO: Strip type
	}
	
	private function putClassVars($aData) {
	}
}
?>