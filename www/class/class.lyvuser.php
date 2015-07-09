<?php
namespace Lyverva;

class lyvUser extends lyvDAL {
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
