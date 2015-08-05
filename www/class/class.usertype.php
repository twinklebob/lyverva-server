<?php
namespace Lyverva;

class UserType extends LyvDAL {
	public $iUserTypeId = 0;
	public $sDescription = "";
	
	public function __construct($iId = 0) {
		$this->sTableName = "usertype";
		parent::__construct($iId);
	}
}
