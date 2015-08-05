<?php
namespace Lyverva;

class LibraryUser extends LyvDAL {
	public $iLibraryUserId = 0;
	
	public $iLibraryId = 0;
	public $iUserId = 0;
	public $iUserType = 0;
	
	public function __construct($iId = 0) {
		$this->sTableName = "libraryuser";
		parent::__construct($iId);
	}
}
