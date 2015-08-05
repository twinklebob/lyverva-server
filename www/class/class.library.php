<?php
namespace Lyverva;

class Library extends LyvDAL {
	public $iLibraryId = 0;
	
	public $sLibGuid = "";
	public $sName = "";
	public $sLocation = "";
	
	public function __construct($iId = 0) {
		$this->sTableName = "library";
		parent::__construct($iId);
	}
}
