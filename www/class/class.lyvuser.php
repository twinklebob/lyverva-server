<?php
namespace Lyverva;

class lyvUser extends lyvDAL {
	public $iLyvUserId = 0;
	public $dtCreateDate = 0;
	public $iCreateUser = 0;
	public $dtModifyDate = 0;
	public $iModifyUser = 0;
	public $sFirstname = "";
	public $sSurname = "";
	public $sEmail = "";
	public $sPasswordHash = "";
	
	public function __construct($iId = 0) {
		parent::sTableName = "LyvUser";
		parent::__construct($iId);
	}
}