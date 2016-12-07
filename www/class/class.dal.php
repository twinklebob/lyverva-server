<?php
namespace Lyverva;

use \Database;

class LyvDAL {
	protected $aIgnoreSave = array("sTableName", "aIgnoreSave", "aClassExludeVars", "oDb");
	protected $sTableName;
	protected $aClassExludeVars = array();
	
	/**
     * Database object
     * @var \Database
     */
    protected $oDb;
    public $iId = 0;
    public $dtCreateDate = null;
    public $iCreateUser = 0;
    public $dtModifyDate = null;
    public $iModifyUser = 0;

    public function __construct($iId = 0) {
        // Create DB Connection
        $this->oDb = \Database::obtain();

        // If specific item requested, load it
        if ($iId != 0) {
            $this->load($iId);
        } else {
            // Default create/modify values
            $this->dtCreateDate = new \DateTime();
            $this->dtModifyDate = new \DateTime();
        }
    }

    public function load($iId) {
        // First check DB connection
        if (!isset($this->oDb) || empty($this->oDb) || !$this->oDb->bIsConnected) {
            throw new \ErrorException(get_class($this) . " Load: Unable to connect to database.");
        }
        $sSql = "SELECT * FROM `" . $this->sTableName . "` WHERE `id` = ?";
        $aResults = $this->oDb->queryFirst($sSql, $iId);
        if ($aResults) {
            $this->putClassVars($aResults);
        } else {
            throw new \ErrorException(get_class($this) . ": No results found.");
        }
    }

    public function save($iUserId = 0) {
        // Set record User IDs
        if(!empty($iUserId)) {
            if ($this->iId == 0) {
                $this->iCreateUser = $iUserId;
            }
            $this->iModifyUser = $iUserId;
        }
        // Update modified date
        $this->dtModifyDate = new \DateTime();
        
        $aData = $this->getClassVars();
        if ($this->iId == 0) {
            // Do INSERT
            $iSqlResult = $this->oDb->insert(strtolower($this->sTableName), $aData);

            if ($iSqlResult !== 0) {
                $this->iId = $iSqlResult;
                return $this->iId;
            } else {
                return false;
            }

            // DB Audit not implemented here, yet
            // $iSqlResult = $this->oDb->insert("db_audit", array(
            //   "userid" => $aData['modifyuser'],
            //   "tablename" => strtoupper($this->sTableName),
            //   "rowid" => $this->iId,
            //   "action" => "CREATE"
            // ));
        } else {
            // Do UPDATE
            return $this->oDb->update(strtolower($this->sTableName), $aData, array("id" => $this->iId));
            // DB Audit not implemented here
            // $iSqlResult = $this->oDb->insert("db_audit", array(
            //   "userid" => $aData['modifyuser'],
            //   "tablename" => strtoupper($this->sTableName),
            //   "rowid" => $this->iId,
            //   "action" => "UPDATE"
            // 	));
        }
    }
    
    /**
     * Delete this record from the database
     */
    public function delete() {
        // Currently performs a hard delete, could use a DB flag to introduce a soft delete
        if($this->iId > 0) {
            return $this->oDb->delete($this->sTableName, array('id' => $this->iId));
        } else {
            throw new \ErrorException(get_class($this) . ' Delete: Cannot delete an unsaved record');
        }
    }

    protected function getClassVars($bIgnoreId = true) {
        // Cycle through each property
        $aVars = array();
        foreach (get_object_vars($this) as $key => $value) {
            // Exclude required variables
            $aSearchArray = array_merge($this->aClassExludeVars, $this->aIgnoreSave);
            if($bIgnoreId) {
                $aSearchArray = array_merge($aSearchArray, array('iId'));
            }
            if (!in_arrayi($key, $aSearchArray)) {
                //echo "Key is: " . $key;
                // Strip type
                if (preg_match('/^(dt)(.+)$/i', $key, $result) === 1) {
                    //var_export($result);
                    $sFieldName = $result[2];
                    if(!empty($value)) {
//                        echo get_class($this) . '->' . $sFieldName . ': ';
//                        var_dump($value);
                        $aVars[$sFieldName] = $value->format('Y-m-d H:i:s');
                    } else {
                        $aVars[$sFieldName] = null;
                    }
                } else if (preg_match('/^([a|b|d|f|i|s])(.+)$/i', $key, $result) === 1) {
                    //var_export($result);
                    $sFieldName = $result[2];
                    $aVars[$sFieldName] = $value;
                } else {
                    $aVars[$key] = $value;
                }
            }
        }

        return $aVars;
    }

    /**
     * Take data from associative array and insert into object
     * @param array $aData Associative array
     */
    public function putClassVars($aData) {
        // Cycle through each property
        foreach (get_object_vars($this) as $key => $value) {
            // Strip type
            if (preg_match('/^(dt|[a|b|d|f|i|s])(.+)$/i', $key, $result) != false) {
//                echo 'Key: ' . $key . ' ';
//                var_dump($result);
                $sFieldName = strtolower($result[2]);
                // Update value if it is in aData
                if (array_key_exists($sFieldName, $aData)) {
                    if($result[1] == 'dt' && !empty($aData[$sFieldName])) {
                        $this->$key = new \DateTime($aData[$sFieldName]);
                    } else {
                        $this->$key = $aData[$sFieldName];
                    }
                }
            }
        }
    }

}

function in_arrayi($needle, $haystack) {
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}
