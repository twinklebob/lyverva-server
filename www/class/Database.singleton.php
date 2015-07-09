<?php
# Name: Database.singleton.php
# File Description: MySQL Singleton Class to allow easy and clean access to common mysql commands
# Author: ricocheting
# Web: http://www.ricocheting.com/
# Update: 2010-07-19
# Version: 3.1.4
# Copyright 2003 ricocheting.com


/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


//require("config.inc.php");
//$db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

//$db = Database::obtain();


###################################################################################################
###################################################################################################
###################################################################################################
class Database{
	
	private $oMySql;

	// debug flag for showing error messages
	public	$debug = true;

	// Store the single instance of Database
	private static $instance;

	private	$server   = ""; //database server
	private	$user     = ""; //database login name
	private	$pass     = ""; //database login password
	private	$database = ""; //database name

	private	$error = "";

	#######################
	//number of rows affected by SQL query
	public	$affected_rows = 0;

	private	$link_id = 0;
	private	$query_id = 0;
	
	public $bIsConnected = false;


#-#############################################
# desc: constructor
private function __construct($server=null, $user=null, $pass=null, $database=null){
	// error catching if not passed in
	if($server==null || $user==null || $database==null){
		$this->oops("Database information must be passed in when the object is first created.");
	}

	$this->server=$server;
	$this->user=$user;
	$this->pass=$pass;
	$this->database=$database;
}#-#constructor()


#-#############################################
# desc: singleton declaration
public static function obtain($server=null, $user=null, $pass=null, $database=null){
	if (!self::$instance){ 
		self::$instance = new Database($server, $user, $pass, $database); 
	} 

	return self::$instance; 
}#-#obtain()


#-#############################################
# desc: connect and select database using vars above
public function connect(){
	$this->oMySql = new mysqli($this->server,$this->user,$this->pass,$this->database);
	
	if ($this->oMySql->connect_error) {
		$this->oops("Could not connect to server: {$this->server}.");
		//die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
	}
	
	// Store connected status
	$this->bIsConnected = true;

	// unset the data so it can't be dumped
	$this->server='';
	$this->user='';
	$this->pass='';
	$this->database='';
}#-#connect()



#-#############################################
# desc: close the connection
public function close(){
	if(!$this->oMySql->close()){
		$this->oops("Connection close failed.");
	}
	$this->bIsConnected = false;
}#-#close()


#-#############################################
# Desc: escapes characters to be mysql ready
# Param: string
# returns: string
public function escape($string){
	if(get_magic_quotes_runtime()) $string = stripslashes($string);
	return $this->oMySql->real_escape_string($string);
}#-#escape()


#-#############################################
# Desc: executes SQL query to an open connection
# Param: (MySQL query) to execute
# returns: (query_id) for fetching results etc
public function query($sql, $param = null){
	// do query
	$this->query_id = $this->oMySql->query($sql);

	if (!$this->query_id){
		$this->oops("MySQL Query fail: $sql");
		return 0;
	}
	
	$this->affected_rows = $this->oMySql->affected_rows;

	return $this->query_id;
}#-#query()


#-#############################################
# desc: does a query, fetches the first row only, frees resultset
# param: (MySQL query) the query to run on server
# returns: array of fetched results
public function queryFirst($query_string, $param = null){
	$query_id = $this->query($query_string, $param);
	$out = $this->fetch($query_id);
	$this->free_result($query_id);
	return $out;
}#-#query_first()


#-#############################################
# desc: fetches and returns results one line at a time
# param: query_id for mysql run. if none specified, last used
# return: (array) fetched record(s)
public function fetch($query_id=-1){
	// retrieve row
	if ($query_id!=-1){
		$this->query_id=$query_id;
	}

	if (isset($this->query_id)){
		$record = $this->query_id->fetch_assoc();
	}else{
		$this->oops("Invalid query_id: {$this->query_id}. Records could not be fetched.");
	}

	return $record;
}#-#fetch()


#-#############################################
# desc: returns all the results (not one row)
# param: (MySQL query) the query to run on server
# returns: assoc array of ALL fetched results
public function fetch_array($sql, $param = null){
	$results = $this->query($sql, $param);
	$out = $results->fetch_all(MYSQLI_ASSOC);

	$this->free_result($results);
	return $out;
}#-#fetch_array()


#-#############################################
# desc: does an update query with an array
# param: table, assoc array with data (not escaped), where condition (optional. if none given, all records updated)
# returns: (query_id) for fetching results etc
public function update($table, $data, $where='1'){
	$q="UPDATE `$table` SET ";

	foreach($data as $key=>$val){
		if(strtolower($val)=='null') $q.= "`$key` = NULL, ";
		elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
		elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $q.= "`$key` = `$key` + $m[1], "; 
		else $q.= "`$key`='".$this->escape($val)."', ";
	}

	$q = rtrim($q, ', ') . ' WHERE '.$where.';';

	return $this->query($q);
}#-#update()


#-#############################################
# desc: does an insert query with an array
# param: table, assoc array with data (not escaped)
# returns: id of inserted record, false if error
public function insert($table, $data){
	$q="INSERT INTO `$table` ";
	$v=''; $n='';

	foreach($data as $key=>$val){
		$n.="`$key`, ";
		if(strtolower($val)=='null') $v.="NULL, ";
		elseif(strtolower($val)=='now()') $v.="NOW(), ";
		else $v.= "'".$this->escape($val)."', ";
	}

	$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";

	if($this->query($q)){
		return $this->oMySql->insert_id;
	}
	else return false;

}#-#insert()


#-#############################################
# desc: frees the resultset
# param: query_id for mysql run. if none specified, last used
private function free_result($query_id=-1){
	if ($query_id!=-1){
		$this->query_id=$query_id;
	}
	if($this->query_id!=0) {
		$this->query_id->free();
	}
}#-#free_result()


#-#############################################
# desc: throw an error message
# param: [optional] any custom error to display
private function oops($msg=''){
	$this->error = $this->oMySql->error;
	if(!$this->bIsConnected) {
		$this->error = $this->oMySql->connect_error;
		$msg = "WARNING: No link_id found. Likely not be connected to database." . PHP_EOL . $msg;
	}

	// if no debug, done here
	if(!$this->debug) {
		throw new ErrorException("Database error occurred." . $this->error);
	} else {
		$sErrorMessage = 'MySQL Error: ' . $this->error . PHP_EOL;
		if(!empty($_SERVER['REQUEST_URI'])) {
			$sErrorMessage .= 'Script: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
		}
		if(!empty($_SERVER['HTTP_REFERER'])) {
			$sErrorMessage .= 'Referer: ' . $_SERVER['HTTP_REFERER'];
		}
		throw new ErrorException($sErrorMessage);
	}
}#-#oops()


}//CLASS Database
###################################################################################################
