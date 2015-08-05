<?php
namespace Lyverva;

class LyvUser extends LyvDAL {
	public $iLyvUserId = 0;
	
	public $sFirstname = "";
	public $sSurname = "";
	public $sEmail = "";
	public $sPasswordHash = "";
	
	public function __construct($iId = 0) {
		$this->sTableName = "LyvUser";
		parent::__construct($iId);
	}
}
