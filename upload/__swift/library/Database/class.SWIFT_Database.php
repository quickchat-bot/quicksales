<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The Database Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Database extends SWIFT_Base
{
    /** @var ADOConnection|mixed */
    private $_ADODBObject;
    private $_ADODBDictionaryObject;
    private $_PDOStatement;
    protected $_PDOObject;
    private $FirePHP = false;

    protected $_transactionActive = false;

    protected $_dbLayer = false;

    protected $_adodbLoaded = false;
    protected $_pdoLoaded = false;

    protected $_debugData = array();
    protected $_debugIndex = 0;
    public $enableDebug = false;

    // Record Containers
    public $Record = array();
    public $Record1 = array();
    public $Record2 = array();
    public $Record3 = array();
    public $Record4 = array();
    public $Record5 = array();

    // Link IDs
    private $_queryResult1 = 0;
    private $_queryResult2 = 0;
    private $_queryResult3 = 0;
    private $_queryResult4 = 0;
    private $_queryResult5 = 0;

    // Core Constants
    const LAYER_ADODB = 'adodb';
    const LAYER_PDO = 'pdo';

    const BATCH_INSERT_SIZE = 100;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct($_isCustom = false, $_dbHost = '', $_dbPort = 3306, $_dbName = '', $_dbUsername = '', $_dbPassword = '', $_dbSocket = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        global $ADODB_FORCE_TYPE;
        $ADODB_FORCE_TYPE = ADODB_FORCE_EMPTY;

        // Use countrecs option so mysqli_multi_query is used to avoid out of sync errors
        global $ADODB_COUNTRECS;
        $ADODB_COUNTRECS = true;

        $this->FirePHP = $_SWIFT->FirePHP;

        $_dbLayer = self::LAYER_PDO;
        if (!$_isCustom) {
            if (strtolower(DB_LAYER) == 'adodb') {
                $_dbLayer = self::LAYER_ADODB;
            }

            // We force ADODB during Setup
            if (SWIFT_INTERFACE == 'setup') {
                $_dbLayer = self::LAYER_ADODB;
            }
        }

        $this->_dbLayer = $_dbLayer;

        if (!$_isCustom && $this->GetDBLayer() == self::LAYER_ADODB && !$this->LoadADODB()) {
            return;
        } elseif (!$_isCustom && $this->GetDBLayer() == self::LAYER_PDO && !$this->LoadPDO()) {
            return;
        } elseif ($_isCustom && $this->GetDBLayer() == self::LAYER_PDO && !$this->LoadPDOCustom($_dbHost, $_dbPort, $_dbName, $_dbUsername, $_dbPassword, $_dbSocket)) {
            return;
        }

        parent::__construct();

        if (defined('DB_SET_NON_STRICT') && DB_SET_NON_STRICT) {
            $this->Execute("SET SESSION sql_mode=''");
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Return the active DB Layer
     *
     * @author Varun Shoor
     * @return mixed
     */
    protected function GetDBLayer()
    {
        return $this->_dbLayer;
    }

    /**
     * Reconnect the Database
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Reconnect()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($this->GetDBLayer() == self::LAYER_ADODB && !$this->LoadADODB()) {
            return false;
        } elseif ($this->GetDBLayer() == self::LAYER_PDO && !$this->LoadPDO()) {
            return false;
        }

        return true;
    }

    /**
     * Add to Debug
     *
     * @author Varun Shoor
     * @param string $_message The Message
     * @param float $_startTime The Start Micro Time
     * @param float $_endTime The End Micro Time
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function AddDebug($_message, $_startTime, $_endTime)
    {
        if (!$this->enableDebug) {
            return true;
        }

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_debugData[] = array('endtime' => $_endTime, 'starttime' => $_startTime, 'message' => $_message);

        return true;
    }

    /**
     * Print the Debug Log
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function PrintDebug()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        foreach ($this->_debugData as $_val) {
            $_endTime = $_val['endtime'];
            $_startTime = $_val['starttime'];
            $_message = $_val['message'];

            $_timeTaken = number_format($_endTime - $_startTime, 5) . ' seconds';

            echo '######################################' . SWIFT_CRLF;
            echo $_message . SWIFT_CRLF;
            echo '-------------------------------------' . SWIFT_CRLF;
            echo 'Time Taken: ' . $_timeTaken;
            echo '######################################' . SWIFT_CRLF . SWIFT_CRLF . SWIFT_CRLF;
        }


        return true;
    }

    /**
     * Retrieve the ADODB Object
     *
     * @author Varun Shoor
     * @return mixed "ADODB" (Object) on Success, "false" otherwise
     */
    public function GetADODBObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->LoadADODB();

        return $this->_ADODBObject;
    }

    /**
     * Retrieve the ADODB Dictionary Object
     *
     * @author Varun Shoor
     * @return mixed "ADODBDictionary" (OBJECT) on Success, "false" otherwise
     */
    public function GetADODBDictionaryObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->LoadADODB();

        return $this->_ADODBDictionaryObject;
    }

    /**
     * Disconnect from DB
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Disconnect()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_ADODBObject = false;
        $this->_PDOObject = false;

        return true;
    }

    /**
     * Loads the ADODB Lib into the Private name space
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    private function LoadADODB()
    {
        if ($this->_adodbLoaded == true) {
            return true;
        }

        $this->_ADODBObject = ADONewConnection(DB_DSN);
        if (!$this->_ADODBObject instanceof ADOConnection || !$this->_ADODBObject instanceof ADOConnection) {
            return false;
        }

        $_func = 'SetFetchMode';
        $this->_ADODBObject->$_func(ADODB_FETCH_ASSOC);

        /**
         * Madhur Tandon
         *
         * SWIFT-3298 : After upgrade some entries show blank if contain accented or special characters.
         *
         * Comments : Due to the charset problem getting this error.
         */
        if (strtolower(DB_TYPE) == 'mysql' || strtolower(DB_TYPE) == 'mysqli') {
            $_func = 'Execute';
            $this->_ADODBObject->$_func("SET NAMES " . DB_CHARSET);
        }

        $this->_ADODBDictionaryObject = NewDataDictionary($this->_ADODBObject, 'mysql');

        $this->_adodbLoaded = true;

        return true;
    }

    /**
     * Load the PDO Connection
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadPDOCustom($_dbHost, $_dbPort, $_dbName, $_dbUsername, $_dbPassword, $_dbSocket)
    {
        if ($this->_pdoLoaded == true) {
            return true;
        }

        $_PDOObject = false;

        try {
            $_pdoExtended = array(PDO::ATTR_PERSISTENT => DB_PDOPERSISTENT);
            if (DB_TYPE == 'mysql' || strtolower(DB_TYPE) == 'mysqli') {
                $arrIndex = 1002;

                if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                    $arrIndex = PDO::MYSQL_ATTR_INIT_COMMAND;
                } else {
                    $arrIndex = 1002;
                }

                $_pdoExtended[$arrIndex] = 'SET NAMES ' . DB_CHARSET . " COLLATE 'utf8_general_ci'";
            }

//            $_PDOObject = new PDO('mysql:host=' . $_dbHost . ';port=' . $_dbPort . ';dbname=' . $_dbName . ';charset=' . DB_CHARSET . ';', $_dbUsername, $_dbPassword, $_pdoExtended);
            if ($_dbSocket != '') {
                $_PDOObject = new PDO('mysql:unix_socket=' . $_dbSocket . ';dbname=' . $_dbName . ';', $_dbUsername, $_dbPassword, $_pdoExtended);
            } else {
                $_PDOObject = new PDO('mysql:host=' . $_dbHost . ';port=' . $_dbPort . ';dbname=' . $_dbName . ';', $_dbUsername, $_dbPassword, $_pdoExtended);
            }
            $_PDOObject->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        } catch (PDOException $_PDOExceptionObject) {
            return false;
        }

        if (!$_PDOObject instanceof PDO) {
            return false;
        }

        $this->_PDOObject = $_PDOObject;
        $this->_pdoLoaded = true;

        return true;
    }

    /**
     * Load the PDO Connection
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadPDO()
    {
        if ($this->_pdoLoaded == true) {
            return true;
        }

        $_PDOObject = false;

        try {
            $_pdoExtended = array(PDO::ATTR_PERSISTENT => DB_PDOPERSISTENT);
            if (DB_TYPE == 'mysql' || strtolower(DB_TYPE) == 'mysqli') {
                $arrIndex = 1002;

                if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                    $arrIndex = PDO::MYSQL_ATTR_INIT_COMMAND;
                } else {
                    $arrIndex = 1002;
                }

                $_pdoExtended[$arrIndex] = 'SET NAMES ' . DB_CHARSET . " COLLATE 'utf8_general_ci'";
            }

            if (defined('DB_MYSQL_SOCK') && DB_MYSQL_SOCK != '') {
                $_PDOObject = new PDO('mysql:unix_socket=' . DB_MYSQL_SOCK . ';dbname=' . DB_NAME . ';', DB_USERNAME, DB_PASSWORD, $_pdoExtended);
            } else {
                $_PDOObject = new PDO('mysql:host=' . DB_HOSTNAME . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';', DB_USERNAME, DB_PASSWORD, $_pdoExtended);
            }

            $_PDOObject->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        } catch (PDOException $_PDOExceptionObject) {
            return false;
        }

        if (!$_PDOObject instanceof PDO) {
            return false;
        }

        $this->_PDOObject = $_PDOObject;
        $this->_pdoLoaded = true;

        return true;
    }

    /**
     * Retrieve the PDO Object
     *
     * @author Varun Shoor
     * @return mixed "PDO" (Object) on Success, "false" otherwise
     */
    public function GetPDOObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->LoadPDO();

        return $this->_PDOObject;
    }

    /**
     * Check to see if the database object is connected
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function IsConnected()
    {
        if ($this->GetDBLayer() == self::LAYER_ADODB && (!$this->_ADODBObject instanceof ADOConnection || $this->_adodbLoaded == false || !$this->_ADODBObject->IsConnected())) {
            return false;
        } elseif ($this->GetDBLayer() == self::LAYER_PDO && (!$this->_PDOObject instanceof PDO || $this->_pdoLoaded == false)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves the last error
     *
     * @author Varun Shoor
     * @return mixed "ErrorMsg()" (STRING) or "ErrorNo()" (INT) on Success, "false" otherwise
     */
    public function FetchLastError()
    {
        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            if ($this->IsConnected()) {
                return $this->_ADODBObject->ErrorMsg();
            }

            return $this->_ADODBObject->ErrorNo();
        } elseif ($this->GetDBLayer() == self::LAYER_PDO && $this->_pdoLoaded == true) {
            $_pdoErrorContainer = $this->_PDOStatement->errorInfo();

            if (isset($_pdoErrorContainer[1], $_pdoErrorContainer[2])) {
                return $_pdoErrorContainer[1] . ':' . $_pdoErrorContainer[2];
            }
        }

        return false;
    }

    /**
     * Logs the SQL Error
     *
     * @author Varun Shoor
     * @param string $_errorString The Error String
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LogError($_errorString)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_DATABASE, $_errorString);

        return true;
    }

    /**
     * Similar to PEAR DB's autoExecute(), except that $mode can be 'INSERT' or 'UPDATE' or DB_AUTOQUERY_INSERT or DB_AUTOQUERY_UPDATE If $mode == 'UPDATE', then $where is compulsory as a safety measure.
     *
     * @author Varun Shoor
     * @param string $_tableName The Table Name
     * @param array $_fieldValues The Fields & Values
     * @param string $_mode The Mode INSERT/UPDATE
     * @param string $_where The WHERE Clause
     * @return mixed RecordSet (OBJECT) or false
     * @throws SWIFT_Exception When the Query Fails
     */
    protected function PDOAutoExecute($_tableName, $_fieldValues, $_mode = 'INSERT', $_where = null)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } elseif (!count($_fieldValues)) {
            return false;
        }

        $_updateFieldContainer = $_insertColumnContainer = $_finalInsertValueContainer = array();
        foreach ($_fieldValues as $_column => $_value) {
            $_updateFieldContainer[]      = $_column . ' = ' . "'" . $this->Escape($_value) . "'";
            $_insertColumnContainer[]     = $_column;
            $_finalInsertValueContainer[] = "'" . $this->Escape($_value) . "'";
        }

        $_queryResult = false;
        if ($_mode == 'UPDATE') {
            $_query = "UPDATE " . $_tableName . " SET " . implode(', ', $_updateFieldContainer) . IIF(!empty($_where), ' WHERE ' . $_where);

            $_queryResult = IIF($this->_PDOObject->exec($_query) === false, 0, 1);
        } elseif ($_mode == 'INSERT') {
            $_query = 'INSERT INTO ' . $_tableName . '(' . implode(',', $_insertColumnContainer) . ') VALUES (' . implode(',', $_finalInsertValueContainer) . ')';

            $_queryResult = IIF($this->_PDOObject->exec($_query) === false, 0, 1);
        }

        return $_queryResult;
    }

    /**
     * Similar to PEAR DB's autoExecute(), except that $mode can be 'INSERT' or 'UPDATE' or DB_AUTOQUERY_INSERT or DB_AUTOQUERY_UPDATE If $mode == 'UPDATE', then $where is compulsory as a safety measure.
     *
     * @author Varun Shoor
     * @param string $_tableName The Table Name
     * @param array $_fieldValues The Fields & Values
     * @param string $_mode The Mode INSERT/UPDATE
     * @param mixed $_where The WHERE Clause
     * @param bool $_forceUpdate Means that even if the data has not changed, perform update.
     * @param bool $_isSilent Whether to not display any error
     * @return mixed RecordSet (OBJECT) or false
     * @throws SWIFT_Exception When the Query Fails
     */
    public function AutoExecute($_tableName, $_fieldValues, $_mode = 'INSERT', $_where = false, $_forceUpdate = true, $_isSilent = false)
    {
        if (!$this->GetIsClassLoaded() || !$this->IsConnected()) {
            return false;
        }

        $_startTime = GetMicroTime();

        $_queryResult = false;
        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            if ($_where === false && ($_mode == 'UPDATE' || $_mode == 2 /* DB_AUTOQUERY_UPDATE */)) {
                throw new Exception('AutoExecute: Illegal mode=UPDATE with empty WHERE clause', 'AutoExecute');
            }

            $sql = "SELECT * FROM $_tableName";
            $rs = $this->_ADODBObject
                ->SelectLimit($sql, 1);
            if (!$rs) {
                return false; // table does not exist
            }
            while ($rs->fetchRow()); // mysqli needs the buffer to be flushed

            $rs->tableName = $_tableName;
            if ($_where !== false) {
                $sql .= " WHERE $_where";
            }
            $rs->sql = $sql;

            switch ($_mode) {
                case 'UPDATE':
                case DB_AUTOQUERY_UPDATE:
                    $sql = $this->_ADODBObject
                        ->GetUpdateSQL($rs, $_fieldValues, $_forceUpdate);
                    break;
                case 'INSERT':
                case DB_AUTOQUERY_INSERT:
                    $sql = $this->_ADODBObject
                        ->GetInsertSQL($rs, $_fieldValues);
                    break;
                default:
                    throw new Exception('AutoExecute: Unknown mode');
            }
            $_queryResult = $sql && $this->_ADODBObject->Execute($sql);
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            $_queryResult = $this->PDOAutoExecute($_tableName, $_fieldValues, $_mode, $_where);
        }
        $_endTime = GetMicroTime();

        $_firePHPString = '';
        if (count($_fieldValues) < 30) {
            $_firePHPString = 'AutoExecute Query: ' . $_mode . ' ' . $_tableName . ' (' . var_export($_fieldValues, true) . ')' . IIF($_where, ' WHERE ' . $_where);
        }

        $this->AddDebug($_firePHPString, $_startTime, $_endTime);

        if ($_queryResult) {
            $this->FirePHP->Info($_firePHPString);
        } else {
            $_lastError = $this->FetchLastError();
            $this->FirePHP->Error('Error ' . $_firePHPString);

            if (!$_isSilent) {
                $this->LogError($_firePHPString . $_lastError);

                throw new SWIFT_Exception('AutoExecute Query: ' . $_firePHPString . $_lastError);
            }
        }

        return $_queryResult;
    }

    /**
     * Used for batch/bulk insert, i.e will insert multiple rows in using one query
     *
     * @author Bishwanath Jha <bishwanath.jha@opencart.com.vn>
     * @author Nidhi Gupta <nidhi.gupta@opencart.com.vn>
     *
     * @param string $_tableName
     * @param array  $_fieldValueContainer
     * @param int    $_insertLimit
     * @param bool   $_useTransaction
     * @param bool   $_isSilent
     *
     * @return mixed RecordSet (OBJECT) or false
     * @throws SWIFT_Exception
     */
    public function AutoExecuteBatch($_tableName, $_fieldValueContainer, $_insertLimit = self::BATCH_INSERT_SIZE, $_useTransaction = false, $_isSilent = false)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } elseif (count($_fieldValueContainer) <= 0) {
            return false;
        }

        //Determining if total rows are more than BATCH_INSERT_SIZE, need iteration or not
        if (count($_fieldValueContainer) > $_insertLimit) {
            $_fieldValueContainerChunk = array_chunk($_fieldValueContainer, $_insertLimit);
        } else {
            $_fieldValueContainerChunk[0] = $_fieldValueContainer;
        }

        $_query = '';

        //Looping through, bulk section one by one depending on limit
        foreach ($_fieldValueContainerChunk as $_fieldValueList) {
            $_startTime                 = GetMicroTime();
            $_queryResult               = false;
            $_namedInsertValueContainer = array();
            $_bulkValuesSet             = '';
            $_bulkInsertSet             = '';

            $_endTime = '';
            try {
                if ($_useTransaction === true) {
                    $this->StartTrans();
                }

                $_columnCount = count(array_keys($_fieldValueList[0]));
                $_iterationNumber = 0;

                foreach ($_fieldValueList as $_columnValues) {
                    $_columnValueCount = count($_columnValues);

                    if ($_columnCount !== $_columnValueCount) {
                        throw new SWIFT_Exception('Column value count mismatch. Column count: ' . $_columnCount . ' Value count: ' . $_columnValueCount .
                            ' for iteration number :' . $_iterationNumber);
                    }

                    if ($this->GetDBLayer() == self::LAYER_ADODB) {
                        $_bulkValuesSet .= '(\'' . implode("','", $this->_ADODBObject->escape($_columnValues)) . '\'),';
                    }

                    $_insertSet = '';

                    foreach ($_columnValues as $_column => $_value) {
                        $_namedInsertValueContainer[':' . $_column . $_iterationNumber] = $_value;
                        $_insertSet .= ':' . $_column . $_iterationNumber . ',';
                    }

                    $_bulkInsertSet .= '(' . substr($_insertSet, 0, -1) . '),';
                    $_iterationNumber++;
                }

                if ($this->GetDBLayer() == self::LAYER_ADODB) {
                    $_query = 'INSERT INTO ' . $_tableName . '(' . implode(',', array_keys($_fieldValueList[0])) . ') VALUES ' . substr($_bulkValuesSet, 0, -1);

                    $_queryResult = $this->_ADODBObject->Execute($_query);
                } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
                    $_query              = 'INSERT INTO ' . $_tableName . '(' . implode(',', array_keys($_fieldValueList[0])) . ') VALUES ' . substr($_bulkInsertSet, 0, -1);
                    $this->_PDOStatement = $this->_PDOObject->prepare($_query);

                    if (!$this->_PDOStatement) {
                        echo 'PDO Prepare Failed: ' . $_query;
                        $_pdoErrorContainer = $this->_PDOObject->errorInfo();
                        if (isset($_pdoErrorContainer[1], $_pdoErrorContainer[2])) {
                            echo '<br>' . $_pdoErrorContainer[1] . ':' . $_pdoErrorContainer[2];
                        }
                    }

                    $_queryResult = $this->_PDOStatement->Execute($_namedInsertValueContainer);
                }

                $_endTime = GetMicroTime();

                if ($_useTransaction === true) {
                    $this->CompleteTrans();
                }
            } catch (SWIFT_Database_Exception $_PDOExceptionObject) {
                $this->Rollback();
            }
            // firePHP Logging here for each Bulk insertion
            $_firePHPString = '';
            $_firePHPString = 'AutoExecuteBatch Query: INSERT' . ' ' . $_tableName . ' (' . var_export($_fieldValueList, true) . ') InsertLimit : ' . $_insertLimit;
            $this->AddDebug($_firePHPString, $_startTime, $_endTime);
            if ($_queryResult) {
                $this->FirePHP->Info($_firePHPString);
            } else {
                $_lastError = $this->FetchLastError();
                $this->FirePHP->Error('Error ' . $_firePHPString);
                if (!$_isSilent) {
                    $this->LogError($_firePHPString . $_lastError);
                    throw new SWIFT_Exception('AutoExecuteBatch Query: ' . $_firePHPString . $_lastError);
                }
            }
        }

        return $_query;
    }

    /**
     * Execute the SQL
     *
     * @author Varun Shoor
     * @param string $_sql SQL statement to execute, or possibly an array holding prepared statement ($sql[0] will hold sql text)
     * @param array|bool $_inputArray holds the input data to bind to. Null elements will be set to null.
     * @return mixed RecordSet (OBJECT) or false
     * @throws SWIFT_Exception When the Query Fails
     */
    public function Execute($_sql, $_inputArray = false)
    {
        if (!$this->GetIsClassLoaded() || !$this->IsConnected()) {
            return false;
        }

        $_startTime = GetMicroTime();

        $_queryResult = false;
        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            $_queryResult = $this->_ADODBObject->Execute($_sql, $_inputArray);
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            try {
                $this->_PDOStatement = $this->_PDOObject->prepare($_sql);
                if (!$this->_PDOStatement) {
                    echo 'PDO Prepare Failed: ' . $_sql;
                    $_pdoErrorContainer = $this->_PDOObject->errorInfo();

                    if (isset($_pdoErrorContainer[1], $_pdoErrorContainer[2])) {
                        echo '<br>' . $_pdoErrorContainer[1] . ':' . $_pdoErrorContainer[2];
                    }
                }

                if (_is_array($_inputArray)) {
                    foreach ($_inputArray as $_val) {
                        $_queryResult = $this->_PDOStatement->execute($_val);
                        if (!$_queryResult) {
                            break;
                        }
                    }
                } else {
                    $_queryResult = $this->_PDOStatement->execute();
                }
            } catch (SWIFT_Database_Exception $_PDOExceptionObject) {
                $this->Rollback();
            }
        }

        $_endTime = GetMicroTime();

        $_firePHPString = 'Execute Query: ' . $_sql . ' ('. var_export($_inputArray, true) .')';
        $this->AddDebug($_firePHPString, $_startTime, $_endTime);

        if ($_queryResult) {
            $this->FirePHP->Info($_firePHPString);
        } else {
            $_lastError = $this->FetchLastError();
            $this->LogError($_firePHPString . $_lastError);

            $this->FirePHP->Error('Error ' . $_firePHPString);

            throw new SWIFT_Exception('Execute Query: ' . $_lastError);
        }

        return $_queryResult;
    }

    /**
     * Execute the given query
     *
     * @author Varun Shoor
     * @param string $_query The Query to Execute
     * @param int $_id The Numerical ID
     * @param bool $_isSilent Whether to suppress the error or not
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception When the Query Fails
     */
    public function Query($_query, $_id = 1, $_isSilent = false, $inputArray = false)
    {
        if (!$this->GetIsClassLoaded() || !$this->IsConnected()) {
            return false;
        }

        // Execute
        $_startTime = GetMicroTime();

        $_queryResult = false;
        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            $_queryResult = $this->_ADODBObject->Execute($_query, $inputArray);
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            try {
                $this->_PDOStatement = $this->_PDOObject->prepare($_query);

                if (!$this->_PDOStatement) {
                    echo 'PDO Prepare Failed: ' . $_query;
                    $_pdoErrorContainer = $this->_PDOObject->errorInfo();

                    if (isset($_pdoErrorContainer[1], $_pdoErrorContainer[2])) {
                        echo '<br>' . $_pdoErrorContainer[1] . ':' . $_pdoErrorContainer[2];
                    }
                }

                if (false !== $inputArray && is_array($inputArray)) {
                    foreach ($inputArray as $key => $value) {
                        $this->_PDOStatement->bindValue(is_numeric($key) ? $key + 1 : $key, $value);
	                }
                }

                $this->_PDOStatement->execute();
                $_queryResult = $this->_PDOStatement;
            } catch (SWIFT_Database_Exception $_PDOExceptionObject) {
                $this->Rollback();
            }
        }

        $_endTime = GetMicroTime();

        $_queryResultPointer = '_queryResult' . $_id;
        $this->AddDebug('Query: ' . $_query, $_startTime, $_endTime);

        $this->$_queryResultPointer = $_queryResult;

        if (!$_queryResult) {
            $_lastError = $this->FetchLastError();
            $this->LogError($_query . $_lastError);

            $this->FirePHP->Error('Error Query: ' . $_query);

            if (!$_isSilent) {
                throw new SWIFT_Exception('Invalid SQL: '. $_query . ' (' . $_lastError . ')');
            }

            return false;
        } else {
            $this->FirePHP->Info('Query: ' . $_query);

            return true;
        }

        return false;
    }

    /**
     * Query a table and LIMIT the records to N starting by Y offset
     *
     * @author Varun Shoor
     * @param string $_query The Query to Execute
     * @param int $_numberOfRows The Row Record limit
     * @param int $_rowOffset The Row Offset Count
     * @param int $_id The Numerical ID
     * @param bool $_isSilent Whether to suppress the error or not
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception When the Query Fails
     */
    public function QueryLimit($_query, $_numberOfRows = -1, $_rowOffset = 0, $_id = 1, $_isSilent = false, $inputArray = false)
    {
        if (!$this->GetIsClassLoaded() || !$this->IsConnected()) {
            return false;
        }

        $_numberOfRows = $_numberOfRows;

        if (empty($_numberOfRows)) {
            $_numberOfRows = -1;
        }


        // Execute
        $_startTime = GetMicroTime();
        $_queryResult = false;
        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            if (empty($_rowOffset)) {
                $_rowOffset = -1;
            }

            $_query .= ' ';

            $_queryResult = false;
            if (!empty($_numberOfRows) && $_numberOfRows != -1 && $_numberOfRows > 0) {
                $_queryResult = $this->_ADODBObject->SelectLimit($_query, $_numberOfRows, $_rowOffset, $inputArray);
            } else {
                $_queryResult = $this->_ADODBObject->Execute($_query, $inputArray);
            }
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            $_limitExtension = '';
            if (!empty($_numberOfRows) && $_numberOfRows != -1 && $_numberOfRows > 0) {
                $_limitExtension = ' LIMIT ' . $_rowOffset . ',' . $_numberOfRows;
            }

            try {
                $this->_PDOStatement = $this->_PDOObject->prepare($_query . $_limitExtension);
                if (!$this->_PDOStatement) {
                    echo 'PDO Prepare Failed: ' . $_query.$_limitExtension;
                    $_pdoErrorContainer = $this->_PDOObject->errorInfo();

                    if (isset($_pdoErrorContainer[1], $_pdoErrorContainer[2])) {
                        echo '<br>' . $_pdoErrorContainer[1] . ':' . $_pdoErrorContainer[2];
                    }
                }

                if (false !== $inputArray && is_array($inputArray)) {
                    foreach ($inputArray as $key => $value) {
                        $this->_PDOStatement->bindValue(is_numeric($key) ? $key + 1 : $key, $value);
                    }
                }

                $this->_PDOStatement->execute();
                $_queryResult = $this->_PDOStatement;
            } catch (SWIFT_Database_Exception $_PDOExceptionObject) {
                $this->Rollback();
            }
        }
        $_endTime = GetMicroTime();

        $_queryResultPointer = '_queryResult' . $_id;
        $this->AddDebug('Query: ' . $_query . ' LIMIT(#' . $_numberOfRows . ', >' . $_rowOffset . ')', $_startTime, $_endTime);

        $this->$_queryResultPointer = $_queryResult;

        if (!$_queryResult) {
            $_lastError = $this->FetchLastError();
            $this->LogError($_query . $_lastError);

            $this->FirePHP->Error('Error Query: ' . $_query . ' LIMIT(#' . $_numberOfRows . ', >' . $_rowOffset . ')');

            if (!$_isSilent) {
                throw new SWIFT_Exception('Invalid SQL: '. $_query . ' (' . $_lastError . ')');
            }

            return false;
        } else {
            $this->FirePHP->Info('Query: ' . $_query . ' LIMIT(#' . $_numberOfRows . ', >' . $_rowOffset . ')');

            return true;
        }

        return false;
    }

    /**
     * Executes query and returns the first record
     *
     * @author Varun Shoor
     * @param string $_query The Query to Execute
     * @param int $_id The Query Numerical ID
     * @return array|bool
     */
    public function QueryFetch($_query, $_id = 3, $inputArray = false)
    {
        if (!$this->GetIsClassLoaded() || !$this->IsConnected()) {
            return false;
        }

        $_queryResult = $this->QueryLimit($_query, 1, 0, $_id, false, $inputArray);
        if (!$_queryResult) {
            return false;
        }

        return $this->NextRecord($_id);
    }

    /**
     * Executes query and returns all result records in an array.  If there are
         * no results, an empty array is returned instead.
     *
     * @author     John Haugeland
     * @param      string        $_query      The Query to Execute
     * @param      int           $_id         The Query Numerical ID (optional)
     * @return     array|false                An array of rows on success, an empty array on no results or false on failure
     * @todo                                  Find out why the default id on queryfetch is 3, to find out whether it should be here too (guessing no, using 4 for now)
     */
    public function QueryFetchAll($_query, $_id = 4)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }
        if (!$this->IsConnected()) {
            return false;
        }

        $_queryResult = $this->Query($_query, $_id);

        if (!$_queryResult) {
            return false;
        }

        $Work = array();
        $item = $this->NextRecord($_id);

        while ($item !== false) {
            $Work[] = $item;
            $item   = $this->NextRecord($_id);
        }

        return $Work;
    }

    /**
     * Retrieve the last unique primary id for a query
     *
     * @author Varun Shoor
     * @return mixed "Insert_ID" (INT) on Success, "false" otherwise
     */
    public function Insert_ID()
    {
        if (!$this->GetIsClassLoaded() || !$this->IsConnected()) {
            return false;
        }

        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            return $this->_ADODBObject->Insert_ID();
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            return $this->_PDOObject->lastInsertID();
        }

        return false;
    }

    /**
     * Retrieve the last unique primary id for a query
     *
     * @author Varun Shoor
     * @return mixed "Insert_ID" (INT) on Success, "false" otherwise
     */
    public function InsertID()
    {
        if (!$this->GetIsClassLoaded() || !$this->IsConnected()) {
            return false;
        }

        return $this->Insert_ID();
    }

    /**
     * Fetches the next available row
     *
     * @author Varun Shoor
     * @param int $_id The Query Numerical ID
     * @return mixed "Record" (ARRAY) on Success, "false" otherwise
     */
    public function NextRecord($_id = 1)
    {
        if (!$this->GetIsClassLoaded() || !$this->IsConnected()) {
            return false;
        }

        // Check whether or not the corresponding query executed without a problem
        $_queryResultPointer = '_queryResult' . $_id;

        $_queryResult = $this->$_queryResultPointer;
        if (!$_queryResult) {
            return false;
        }

        // Fetch the record and log the time taken
        $_recordPointer = 'Record'. $_id;
        $_Record = &$this->$_recordPointer;
        if ($_id == 1) {
            $this->Record = &$this->$_recordPointer;
        }

        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            $_Record = $_queryResult->FetchRow();
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            $_Record = $_queryResult->fetch(PDO::FETCH_ASSOC);
        }

        if (is_array($_Record)) {
            return $_Record;
        }

        return false;
    }

    /**
     * Escape quotes and other characters in a string
     *
     * @author Varun Shoor
     * @param string $_escapeString The String to Escape
     * @return mixed "_escapeString" (STRING) on Success, "false" otherwise
     */
    public function Escape($_escapeString)
    {
        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            return $this->_ADODBObject->escape($_escapeString);
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            return str_replace(array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a"), array("\\\\", "\\0", "\n", "\\r", "\'", '\"', "\\Z"), $_escapeString);
        }
    }

    /**
     * Replace the given fields
     *
     * @author Varun Shoor
    * @param string $_tableName The Table Name
    * @param array $_fieldArray associative array of data (you must quote strings yourself).
    * @param array $_keyCol the primary key field name or if compound key, array of field names
    * @param bool $_autoQuote set to true to use a hueristic to quote strings. Works with nulls and numbers but does not work with dates nor SQL functions.
    * @param bool $_hasAutoIncrement the primary key is an auto-inc field, so skip in insert.
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception When the Query Fails
     */
    public function Replace($_tableName, $_fieldArray, $_keyCol, $_autoQuote=false, $_hasAutoIncrement=false)
    {
        if (!is_array($_fieldArray)) {
            $_fieldArray = array();
        }

        $_queryResult = false;
        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            $_queryResult = $this->_ADODBObject->Replace($_tableName, $_fieldArray, $_keyCol, true, $_hasAutoIncrement);
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            $_updateColumnContainer = $_updateFieldContainer = array();
            foreach ($_fieldArray as $_column => $_value) {
                $_updateColumnContainer[] = $_column;
                $_updateFieldContainer[]  = "'" . $this->Escape($_value) . "'";
            }

            $_query = "REPLACE INTO " . $_tableName . " (" . implode(',', $_updateColumnContainer) . ") VALUES(" . implode(",", $_updateFieldContainer) . ")";

            $_queryResult = IIF($this->_PDOObject->exec($_query) === false, 0, 1);
        }

        $_firePHPString = 'Replace Query: ' . $_tableName . ' ('. var_export($_fieldArray, true) .') KEY('. var_export($_keyCol, true) .')';

        if ($_queryResult) {
            $this->FirePHP->Info($_firePHPString);
        } else {
            $this->FirePHP->Error('Error ' . $_firePHPString);

            throw new SWIFT_Exception('Replace Query: ' . $this->FetchLastError());
        }

        return $_queryResult;
    }

    /**
     * Start the Transaction
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function StartTrans()
    {
        $_queryResult = false;

        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            $_queryResult = $this->_ADODBObject->StartTrans();
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            try {
                $_queryResult = $this->_PDOObject->beginTransaction();
            } catch (SWIFT_Database_Exception $e) {
                print "Transaction is running (because trying another one failed)\n";
            }
        }

        if ($_queryResult) {
            $this->_transactionActive = true;
        }

        return $_queryResult;
    }

    /**
     * Complete the Transaction
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function CompleteTrans()
    {
        if ($this->_transactionActive == false) {
            return true;
        }

        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            return $this->_ADODBObject->CompleteTrans();
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            return $this->_PDOObject->commit();
        }

        return false;
    }

    /**
     * Rollback the transaction
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Rollback()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($this->_transactionActive == false) {
            return true;
        }

        if ($this->GetDBLayer() == self::LAYER_PDO) {
            return $this->_PDOObject->rollback();
        }

        return false;
    }

    /**
     * Return the Parameter Placeholder
     *
     * @author Varun Shoor
     * @param int|string $_paramIndex The Paramater Index
     * @return mixed The Parameter Placeholder from ADODB
     */
    public function Param($_paramIndex)
    {
        if ($this->GetDBLayer() == self::LAYER_ADODB) {
            return $this->_ADODBObject->Param($_paramIndex);
        } elseif ($this->GetDBLayer() == self::LAYER_PDO) {
            return '?';
        }

        return false;
    }

    /**
     * Defines the Global DSN
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DefineDSN()
    {
        /**
         * OVERRIDE ADODB DSN - MAKE SURE YOU USE $_DB["adodbtype"] for the DSN Type below.
         * MYSQL: mysql://user:pwd@localhost/mydb
         * POSTGRES: postgres://user:pwd@localhost/mydb
         * ORACLE: oci8://user:pwd@tnsname
         */

        if (defined('DB_DSN')) {
            return true;
        }

        if (defined('DB_MYSQL_SOCK')) {
            define('DB_DSN', DB_TYPE . '://' . urlencode(DB_USERNAME) . ':' . urlencode(DB_PASSWORD) . '@' . urlencode(DB_HOSTNAME . ':' . DB_MYSQL_SOCK). '/' . urlencode(DB_NAME));
        } elseif (defined('DB_PORT')) {
            define('DB_DSN', DB_TYPE . '://' . urlencode(DB_USERNAME) . ':' . urlencode(DB_PASSWORD) . '@' . DB_HOSTNAME . ':' . (int) (DB_PORT) . '/' . urlencode(DB_NAME));
        } else {
            define('DB_DSN', DB_TYPE . '://' . urlencode(DB_USERNAME) . ':' . urlencode(DB_PASSWORD) . '@' . DB_HOSTNAME . '/' . urlencode(DB_NAME));
        }

        return true;
    }
}
