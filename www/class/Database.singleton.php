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

/*
 * Usage:
 * require("config.inc.php");
 * $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
 * $db = Database::obtain();
 */

/**
 * Database helper class
 */
class Database {

    /**
     * MySQL connection object
     * @var \mysqli 
     */
    private $oMySql;

    /**
     * Debug flag for showing error messages
     * @var bool
     */
    public $debug = true;

    /**
     * Stores the single instance of Database
     * @var self
     */
    private static $instance;
    /**
     * Database server
     * @var string 
     */
    private $server = "";
    /**
     * Database login name
     * @var string
     */
    private $user = "";
    /**
     * Database login password
     * @var string
     */
    private $pass = "";
    /**
     * Database name
     * @var string
     */
    private $database = "";
    
    /**
     * Detail of last error
     * @var string
     */
    private $error = "";
    
    /**
     * Last query executed
     * @var string
     */
    private $lastQuery = '';
    
    /**
     * Last parameters passed to query execution
     * @var array
     */
    private $lastParams = [];

    /**
     * mysqli_stmt object to perform actions against
     * @var \mysqli_stmt
     */
    private $stmt = null;
    #######################

    /**
     * Number of rows affected by last SQL query
     */
    public $affected_rows = 0;
    /**
     * Connection status flag
     * @var bool
     */
    public $bIsConnected = false;

    /**
     * Create new instance of a Database helper object
     * @param string $server Database server
     * @param string $user Database login name
     * @param string $pass Database password
     * @param string $database Database name
     */
    private function __construct($server = null, $user = null, $pass = null, $database = null) {
	// error catching if not passed in
	if ($server == null || $user == null || $database == null) {
	    $this->oops("Database information must be passed in when the object is first created.");
	}

	$this->server = $server;
	$this->user = $user;
	$this->pass = $pass;
	$this->database = $database;
    }

    /**
     * Singleton declaration
     * @param string $server Database server
     * @param string $user Database login name
     * @param string $pass Database password
     * @param string $database Database name
     * @return self Singleton instance
     */
    public static function obtain($server = null, $user = null, $pass = null, $database = null) {
	if (!self::$instance) {
	    self::$instance = new Database($server, $user, $pass, $database);
	}

	return self::$instance;
    }

    /**
     * Connect and select database using defined variables
     */
    public function connect() {
	$this->oMySql = new mysqli($this->server, $this->user, $this->pass, $this->database);

	if ($this->oMySql->connect_error) {
	    $this->oops("Could not connect to server: {$this->server}.");
	    //die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
	}

	// Store connected status
	$this->bIsConnected = true;

	// Unset the data so it can't be dumped
	$this->server = '';
	$this->user = '';
	$this->pass = '';
	$this->database = '';
    }

    /**
     * Close the connection
     */
    public function close() {
	// Close any currently opened statements
	if (!empty($this->stmt)) {
	    if (!$this->stmt->close()) {
		$this->oops("Unable to close statement.");
	    }
	}
	// Now close db connection
	if (!$this->oMySql->close()) {
	    $this->oops("Connection close failed.");
	}
	$this->bIsConnected = false;
    }

    /**
     * Escapes characters to be mysql ready
     * @param string $string Value to be escaped
     * @return string Escaped string
     */
    public function escape($string) {
	if (get_magic_quotes_runtime())
	    $string = stripslashes($string);
	return $this->oMySql->real_escape_string($string);
    }


    /**
     * Executes SQL query to an open connection
     * @param string $sql MySQL query to execute
     * @param mixed $param Optional parameters
     * @return \mysqli_stmt Prepared statement
     */
    public function query($sql, $param = null) {
	// Close any currently opened statements
	if (!empty($this->stmt)) {
	    if (!$this->stmt->close()) {
		$this->oops("Unable to close previous statement.");
	    }
	}

        $this->lastQuery = $sql;
        $this->lastParams = $param;
        
	// Prepare statement
	$this->stmt = $this->oMySql->prepare($sql);
	if(!$this->stmt) {
	    $this->oops('Could not prepare statement');
	}
        if(!empty($param)) {
            $aStmtParams = $this->getParamArray($param);
            $this->lastParams = $aStmtParams;

            try {
                call_user_func_array(array($this->stmt, 'bind_param'), refValues($aStmtParams));
            } catch (\Exception $e) {
                $this->oops($e->getMessage());
            }
        }

	if (!$this->stmt->execute()) {
	    $this->oops("MySQL Query fail: $sql");
	    return null;
	}

	$this->affected_rows = $this->stmt->affected_rows;

	return $this->stmt;
    }


    /**
     * Transforms array of parameters into MySQLi statement params
     * @param mixed $aParam Array of parameters (or single parameter) to transform
     * @return array MySQLi compatible parameter array
     */
    public function getParamArray($aParam = null) {
	if (empty($aParam)) {
	    return array();
	}
	if (!is_array($aParam)) {
	    $aParam = array($aParam);
	}
	$sTypes = '';
	foreach ($aParam as $value) {
	    if (is_int($value)) {
		$sTypes .= 'i';
	    } else if (is_float($value)) {
		$sTypes .= 'd';
	    } else {
		$sTypes .= 's';
	    } // TODO: Currently ignores blob types
	}
	array_unshift($aParam, $sTypes);
	return $aParam;
    }
    

    /**
     * Does a query, fetches the first row only, frees resultset
     * @param string $query_string The query to run on server
     * @param mixed $param Optional parameters
     * @return array Fetched results as associative array
     */
    public function queryFirst($query_string, $param = null) {
	$stmt = $this->query($query_string, $param);
//        echo $this->lastQuery;
//        var_dump($this->lastParams);
	$out = $this->fetch($stmt);
	$this->free_result($stmt);
	return $out;
    }


    /**
     * Fetches and returns results one line at a time
     * @param \mysqli_stmt $stmt Prepared statement
     * @return array Fetched results as associative array
     */
    public function fetch($stmt = false) {
	$results = array();
	
	//$stmt->store_result();

	if ($stmt) {
	    $fields = $stmt->result_metadata()->fetch_fields();
	    $args = array();
	    foreach ($fields AS $field) {
		$key = str_replace(' ', '_', $field->name); // space may be valid SQL, but not PHP
		$args[$key] = &$field->name; // this way the array key is also preserved
	    }
	    call_user_func_array(array($stmt, "bind_result"), $args);
	    if ($stmt->fetch()) {
		$results = array_map("copy_value", $args);
	    } else if($stmt->errno != 0) {
		    $this->oops($stmt->error);
	    } else {
		return null;
	    }
	} else {
	    $this->oops("Invalid statement. Records could not be fetched.");
	}

	return $results;
    }


    /**
     * Returns all results (not one row)
     * @param string $sql The query to run on server
     * @param mixed $param Optional parameters
     * @return array Fetched results as an array of associative arrays
     */
    public function fetch_array($sql, $param = null) {
	$stmt = $this->query($sql, $param);

	$out = [];
	
	$result = $this->fetch($stmt);
//	echo '<br/>Result:<br/>' . PHP_EOL;
//	var_dump($result);
	while (!empty($result)) {
	    $out[] = $result;
	    $result = $this->fetch($stmt);
	}

	$this->free_result($stmt);
	return $out;
    }


    /**
     * Perform an update using an associative array
     * @param string $sTable Table name
     * @param array $aData assoc array with data
     * @param array $aWhere Associative array @see self::paramWhere()
     * @return bool TRUE if successful
     */
    public function update($sTable, $aData, $aWhere) {
	$sQuery = "UPDATE `" . $sTable . "` SET ";
	$aParams = array();
	$sWhere = "";

	// Split Data into "SET" commands and parameters
	foreach ($aData as $key => $val) {
	    if ($key != "id") {
		// Add $key to SET list
		$sQuery .= "`$key` = (?), ";

		if (strtolower($val) === 'null' || $val === '') {
		    $aParams[] = NULL;
		}
		//reverse any dates if needed
		elseif (substr_count($val, '/') == 2 && strlen($val) == 10 && strpos($val, '/') == 2) {
		    //the string contains 3 / and is the correct length and the first / appear at pos 2 - reverse the date
		    $reversedDate = substr($val, 6, 4) . "-" . substr($val, 3, 2) . "-" . substr($val, 0, 2);
		    $aParams[] = $reversedDate;
		} elseif (strtolower($val) == 'now()') {
		    $aParams[] = "NOW()";
		}
		// Controversial-- elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $sQuery.= "[$key] = '$key' + $m[1], "; 
		else
		    $aParams[] = $this->escape($val);
	    }
	}

	$sQuery = rtrim($sQuery, ', ');

	// Split where using paramWhere
	if (!empty($aWhere)) {
	    $this->paramWhere($aWhere, $sWhere, $aParams);
	    $sQuery .= " WHERE " . $sWhere;
	}

	$sQuery .= ";";

	//$this->log_me($sQuery);
	if($this->query($sQuery, $aParams)) {
	    $this->free_result($this->stmt);
	    //$this->stmt->close();
	    return true;
	} else {
	    return false;
	}
    }


    /**
     * Perform an insert using an associative array
     * @param string $sTable Name of table to insert to
     * @param array $aData Associative array of data
     * @return int|boolean ID of inserted record, false if error
     */
    public function insert($sTable, $aData) {
	$sQuery = "INSERT INTO `" . $sTable . "` ";
	$aParams = array();
	$sFields = "";
	$sVals = "";

	// Split Data into "SET" commands and parameters
	foreach ($aData as $key => $val) {
	    if ($key != "id") {
		// Add $key to SET list
		$sFields .= "`$key`, ";
                
		if (is_string($val) && strtolower($val) === 'null') {
		    $aParams[] = NULL;
		}
		//reverse any dates if needed
		elseif (is_string($val) && substr_count($val, '/') == 2 && strlen($val) == 10 && strpos($val, '/') == 2) {
		    //the string contains 3 / and is the correct length and the first / appear at pos 2 - reverse the date
		    $reversedDate = substr($val, 6, 4) . "-" . substr($val, 3, 2) . "-" . substr($val, 0, 2);
		    $aParams[] = $reversedDate;
		} elseif (is_string($val) && strtolower($val) == 'now()') {
		    $aParams[] = "NOW()";
		} else {
		    $aParams[] = $val;
		}
	    }
	    $sVals .= "?, ";
	}

	$sQuery .= "(" . rtrim($sFields, ', ') . ") VALUES (" . rtrim($sVals, ', ') . ");";

	if ($this->query($sQuery, $aParams)) {
	    $id = $this->stmt->insert_id;
	    $this->free_result($this->stmt);
	    //$this->stmt->close();
	    return $id;
	} else {
	    return false;
	}
    }
    
    /**
     * Deletes rows from a table
     * @param string $sTable Table name
     * @param array $aWhere Associative array @see self::paramWhere()
     * @return boolean TRUE if successful
     */
    public function delete($sTable, $aWhere) {
        $sQuery = "DELETE FROM `" . $sTable . "`";
	$aParams = array();
	$sWhere = "";
        
        // Split where using paramWhere
	if (!empty($aWhere)) {
	    $this->paramWhere($aWhere, $sWhere, $aParams);
	    $sQuery .= " WHERE " . $sWhere;
	} else {
            $this->oops('Cannot perform delete without WHERE clause');
        }
        
        if ($this->query($sQuery, $aParams)) {
	    return true;
	} else {
	    return false;
	}
    }


    /**
     * Frees the resultset
     * @param \mysqli_stmt $stmt Prepared statement. If none specified, last used
     */
    private function free_result($stmt = null) {
	if ($stmt) {
	    $stmt->free_result();
	} else if ($this->stmt) {
	    $this->stmt->free_result();
	}
    }


    /**
     * Parameterises a "where" array
     * $aIn in format: array("type"=>"operator", "operator"=>"and", array("id"=>"1"), array("type"=>"operator", "operator"=>"or", array("fred"=>"jim"), array("bob"=>"12")))
     * @param array $aIn Input array
     * @param string $sOut Output string with parameterised values
     * @param array $aOut Output array of parameters (by ref)
     */
    function paramWhere($aIn, &$sOut, &$aOut) {
	$sType = "";
	if (array_key_exists("type", $aIn))
	    $sType = $aIn["type"];

	//echo "Type is: " . $sType;
	//basic key=>value pairs
	if ($sType == "" || $sType == "basic") {
	    //echo "Basic type.";
	    foreach ($aIn as $key => $value) {
		if ($key !== "type") {
		    $sOut .= $key . " = (?) ";
		    $aOut[] = $value;
		    //echo "<br/>sOut = " . $sOut . "<br/>";
		}
	    }
	}
	//logical operator types
	elseif ($sType == "operator") {
	    $sOperator = strtoupper($aIn['operator']);

	    $sOut .= "(";

	    foreach ($aIn as $key => $value) {
		//echo "Key is: " . $key . "<br/>";
		if (($key !== "type" && $key !== 'operator') || $key === 0) {
		    //echo "Calling paramWhere with value: ";
		    //print_r($value);
		    //echo "<br/>";
		    $this->paramWhere($value, $sOut, $aOut);
		    $sOut .= " " . $sOperator . " ";
		}
	    }

	    // Strip out last "operator"
	    $iPos = strrpos($sOut, " " . $sOperator . " ");
	    if ($iPos !== false) {
		$sOut = substr($sOut, 0, $iPos - 1);
	    }

	    $sOut .= ")";
	}
	//special types
	elseif ($sType == "like") {
	    foreach ($aIn as $key => $value) {
		if ($key !== "type") {
		    $sOut .= $key . " like (?) ";
		    $aOut[] = $value;
		}
	    }
	} elseif ($sType == "special") {
	    foreach ($aIn as $key => $value) {
		if ($key !== "type") {
		    $sOut .= $key . " ";
		}
	    }
	}
    }
    

    /**
     * Throw an exception message
     * @param string $msg Any custom error to display
     * @throws ErrorException
     */
    private function oops($msg = '') {
	$this->error = $this->oMySql->error;
	if (!$this->bIsConnected) {
	    $this->error = $this->oMySql->connect_error;
	    $msg = "WARNING: No link_id found. Likely not be connected to database." . PHP_EOL . $msg;
	}
	if($this->stmt && $this->stmt->errno) {
	    $this->error = $this->stmt->error;
	}

	// if no debug, done here
	if (!$this->debug) {
	    throw new ErrorException("Database error occurred." . $this->error);
	} else {
	    $sErrorMessage = 'MySQL Error: ' . $this->error . PHP_EOL;
            if(!empty($this->lastQuery)) {
                $sErrorMessage .= 'Last Query: ' . $this->lastQuery . PHP_EOL;
            }
            if(!empty($this->lastParams)) {
                $sErrorMessage .= 'Last Params: ' . var_export($this->lastParams, true) . PHP_EOL;
            }
	    if (!empty($_SERVER['REQUEST_URI'])) {
		$sErrorMessage .= 'Script: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
	    }
	    if (!empty($_SERVER['HTTP_REFERER'])) {
		$sErrorMessage .= 'Referer: ' . $_SERVER['HTTP_REFERER'];
	    }
	    throw new \ErrorException($sErrorMessage);
	}
    }
}
//CLASS Database ends
###################################################################################################

/**
 * Copy value as value
 * @param mixed $v Input value
 * @return mixed Value returned by val
 */
function copy_value($v) {
    return $v;
}

function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}