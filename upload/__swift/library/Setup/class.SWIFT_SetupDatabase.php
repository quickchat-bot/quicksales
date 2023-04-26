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

use Base\Library\Language\SWIFT_LanguageManager;
use Base\Library\Template\SWIFT_TemplateManager;

/**
 * The Setup Database Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase extends SWIFT_Model
{
    private $_tableContainer = array();
    private $_indexContainer = array();
    private $_queueContainer = array();
    private $_renameTableContainer = array();
    private $_renameColumnContainer = array();
    private $_setupStatus = false;
    public $_setupStatusList = array();

    protected $_appName = '';
    protected $_appDirectory = '';

    protected $_myIsamTableList = array();

    /** @var SWIFT_TemplateManager */
    public $TemplateManager;
    /** @var SWIFT_LanguageManager */
    public $LanguageManager;
    /** @var SWIFT_SettingsManager */
    public $SettingsManager;
    /** @var SWIFT_SetupDiagnostics */
    public $SetupDiagnostics;

    // Core Constants
    const TYPE_INSERT = 1;
    const TYPE_CREATE = 2;
    const TYPE_CREATEINDEX = 3;
    const TYPE_SQL = 4;

    const EXECUTE_ALL = 0;
    const EXECUTE_INSERT = 1;
    const EXECUTE_CREATE = 2;
    const EXECUTE_CREATEINDEX = 3;
    const EXECUTE_SQL = 4;

    const TABLETYPE_MYISAM = 1;
    const TABLETYPE_INNODB = 2;


    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     */
    public function __construct($_appName)
    {
        parent::__construct();

        if (!$this->SetAppName($_appName))
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->SetStatus(true);

        $this->LoadModels();
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
     * Set the App Name
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetAppName($_appName)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_appName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
        if (empty($_appDirectory))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_appDirectory = $_appDirectory;
        $this->_appName = $_appName;

        return true;
    }

    /**
     * Retrieve the App Name
     *
     * @author Varun Shoor
     * @return string The App Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAppName()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_appName;
    }

    /**
     * Set the Setup Status
     *
     * @author Varun Shoor
     * @param bool $_setupStatus The Setup Status
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetStatus($_setupStatus = true)
    {
        if (!is_bool($_setupStatus))
        {
            return false;
        }

        $this->_setupStatus = $_setupStatus;

        return true;
    }

    /**
     * Retrieve the current setup status
     *
     * @author Varun Shoor
     * @return bool "_setupStatus" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetStatus()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_setupStatus;
    }

    /**
     * Merge the Status list text with provided data
     *
     * @author Varun Shoor
     * @param array $_statusListContainer The Status List Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MergeStatusList($_statusListContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_statusListContainer)) {
            return false;
        }

        $this->_setupStatusList = array_merge($this->_setupStatusList, $_statusListContainer);

        return true;
    }

    /**
     * Empties the Database
     *
     * @author Varun Shoor
     * @return array array(array(statustext, result, errormsg), ...)
     */
    public static function EmptyDatabase()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ADODBObject = $_SWIFT->Database->GetADODBObject();
        $_ADODBDictionaryObject = $_SWIFT->Database->GetADODBDictionaryObject();

        $_resultContainer = array();
        $_index = 0;

        $_tableList = $_ADODBObject->MetaTables('TABLES');
        foreach ($_tableList as $_key=>$_val)
        {
            $_sql = $_ADODBDictionaryObject->DropTableSQL($_val);
            $_result = $_ADODBDictionaryObject->ExecuteSQLArray($_sql);

            $_resultContainer[$_index]["statusText"] = sprintf($_SWIFT->Language->Get('setupdroptable'), $_val);
            $_resultContainer[$_index]["result"] = $_result;
            $_resultContainer[$_index]["reasonFailure"] = $_ADODBObject->ErrorMsg();
            $_index++;
        }

        return $_resultContainer;
    }

    /**
     * Adds a index into the main repository
     *
     * @author Varun Shoor
     * @param string $_tableName The Table Name (sans prefix)
     * @param SWIFT_SetupDatabaseIndex $_SWIFT_SetupDatabaseIndexObject The Setup Database Index Object Pointer...
     * @return bool "true" on Success, "false" otherwise
     */
    protected function AddIndex($_tableName, SWIFT_SetupDatabaseIndex $_SWIFT_SetupDatabaseIndexObject)
    {
        $_tableName = Clean($_tableName);

        if (!$_SWIFT_SetupDatabaseIndexObject->GetIsClassLoaded())
        {
            return false;
        }

        $this->_indexContainer[$_tableName][] = $_SWIFT_SetupDatabaseIndexObject;

        return true;
    }

    /**
     * Add Indexes for full text searching
     *
     * @author Mansi Wason <mansi.wason@opencart.com.vn>
     */
    public function AddFTIndex()
    {
        // ======= TICKETS =======
        $this->AddIndex('tickets', new SWIFT_SetupDatabaseIndex("idxft_tickets", TABLE_PREFIX . "tickets", "subject", array("FULLTEXT")));

        // ======= TICKETPOSTS =======
        $this->AddIndex('ticketposts', new SWIFT_SetupDatabaseIndex("idxft_ticketposts", TABLE_PREFIX . "ticketposts", "contents", array("FULLTEXT")));

        // ======= TICKETNOTES =======
        $this->AddIndex('ticketnotes', new SWIFT_SetupDatabaseIndex("idxft_ticketnotes", TABLE_PREFIX . "ticketnotes", "note", array("FULLTEXT")));

    }

    /**
     * Return the Index Container
     *
     * @author Varun Shoor
     * @return mixed "_indexContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetIndexContainer()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_indexContainer;
    }

    /**
     * Check to see if its a valid table type
     *
     * @author Varun Shoor
     * @param mixed $_tableType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidTableType($_tableType)
    {
        return ($_tableType == self::TABLETYPE_INNODB || $_tableType == self::TABLETYPE_MYISAM);
    }

    /**
     * Adds a table into the main repository
     *
     * @author Varun Shoor
     * @param string $_tableName The Table Name (sans prefix)
     * @param SWIFT_SetupDatabaseTable $_SWIFT_SetupDatabaseTableObject The Setup Database Table Object Pointer...
     * @param mixed $_tableType (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    protected function AddTable($_tableName, SWIFT_SetupDatabaseTable $_SWIFT_SetupDatabaseTableObject, $_tableType = self::TABLETYPE_INNODB)
    {
        $_tableName = Clean($_tableName);

        if (!$_SWIFT_SetupDatabaseTableObject->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);
        }

        $this->_tableContainer[$_tableName] = $_SWIFT_SetupDatabaseTableObject;

        if ($_tableType == self::TABLETYPE_MYISAM) {
            $this->_myIsamTableList[] = $_tableName;
        }

        return true;
    }

    /**
     * Return the Table Container
     *
     * @author Varun Shoor
     * @return mixed "_tableContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetTableContainer()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_tableContainer;
    }

    /**
     * Adds a Insert SQL Query to the Queue
     *
     * @author Varun Shoor
     * @param SWIFT_SetupDatabaseInsertSQL $_SWIFT_SetupDatabaseInsertSQLObject SWIFT_SetupDatabaseInsertSQL Pointer
     * @param bool $_executeImmediately Whether to execute the query immediately
     * @return mixed If $_executeImmediately is set to true then it returns the last insert id on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    protected function Insert(SWIFT_SetupDatabaseInsertSQL $_SWIFT_SetupDatabaseInsertSQLObject, $_executeImmediately = false)
    {
        if (!$this->GetIsClassLoaded() || !$_SWIFT_SetupDatabaseInsertSQLObject->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_executeImmediately)
        {
            $_result = $this->Execute($_SWIFT_SetupDatabaseInsertSQLObject);
            if ($_result && $_result[0])
            {
                $_insertID = $this->Database->Insert_ID();

                return $_insertID;
            }

            return false;
        } else {
            $this->_queueContainer[self::TYPE_INSERT][] = $_SWIFT_SetupDatabaseInsertSQLObject;
        }

        return true;
    }

    /**
     * Add Create Database Query to Queue
     *
     * @author Varun Shoor
     * @param SWIFT_SetupDatabaseTable $_SWIFT_SetupDatabaseTableObject The SWIFT_SetupDatabaseTable Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    protected function Create(SWIFT_SetupDatabaseTable $_SWIFT_SetupDatabaseTableObject)
    {
        if (!$this->GetIsClassLoaded() || !$_SWIFT_SetupDatabaseTableObject->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_queueContainer[self::TYPE_CREATE][] = $_SWIFT_SetupDatabaseTableObject;

        return true;
    }

    /**
     * Add Create Index Query to Queue
     *
     * @author Varun Shoor
     * @param SWIFT_SetupDatabaseIndex $_SWIFT_SetupDatabaseIndexObject The SWIFT_SetupDatabaseIndex Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    protected function CreateIndex(SWIFT_SetupDatabaseIndex $_SWIFT_SetupDatabaseIndexObject)
    {
        if (!$this->GetIsClassLoaded() || !$_SWIFT_SetupDatabaseIndexObject->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_queueContainer[self::TYPE_CREATEINDEX][] = $_SWIFT_SetupDatabaseIndexObject;

        return true;
    }

    /**
     * Add a SQL Statement to Queue
     *
     * @author Varun Shoor
     * @param SWIFT_SetupDatabaseSQL $_SWIFT_SetupDatabaseSQLObject The SWIFT_SetupDatabaseSQL Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    protected function Query(SWIFT_SetupDatabaseSQL $_SWIFT_SetupDatabaseSQLObject)
    {
        if (!$this->GetIsClassLoaded() || !$_SWIFT_SetupDatabaseSQLObject->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_queueContainer[self::TYPE_SQL][] = $_SWIFT_SetupDatabaseSQLObject;

        return true;
    }

    /**
     * Clears the Queue Container
     *
     * @author Varun Shoor
     * @param int $_executionType The Execution Type (ALL or Specific)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    private function ClearQueue($_executionType)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_executionType == self::EXECUTE_ALL)
        {
            $this->_queueContainer = array();
        } else {
            $this->_queueContainer[$_executionType] = array();
        }

        return true;
    }

    /**
     * Returns the Queue Container
     *
     * @author Varun Shoor
     * @return mixed "_queueContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetQueue()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_queueContainer;
    }

    /**
     * Creates tables in the table container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    protected function CreateTables()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableContainer = $this->GetTableContainer();
        if (_is_array($_tableContainer))
        {
            foreach ($_tableContainer as $_key => $_val)
            {
                if ($_val instanceof SWIFT_SetupDatabaseTable && $_val->GetIsClassLoaded())
                {
                    $this->Create($_val);

                    $this->ExecuteQueue();

                    if (strtolower(DB_TYPE) == 'mysql' || strtolower(DB_TYPE) == 'mysqli')
                    {
                        if (in_array($_key, $this->_myIsamTableList)) {
                            $this->Query(new SWIFT_SetupDatabaseSQL("ALTER TABLE " . $_val->GetName() . " ENGINE = MyISAM, CONVERT TO CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"));
                        } else {
                            $this->Query(new SWIFT_SetupDatabaseSQL("ALTER TABLE " . $_val->GetName() . " ENGINE = InnoDB, CONVERT TO CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"));
                        }
                    }

                    $this->ExecuteQueue();
                }
            }
        }

        return true;
    }

    /**
     * Creates indexes in the index container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    protected function CreateIndexes()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_indexContainer = $this->GetIndexContainer();
        if (_is_array($_indexContainer))
        {
            foreach ($_indexContainer as $_key => $_val)
            {
                if (_is_array($_val))
                {
                    foreach ($_val as $_subKey => $_subVal)
                    {
                        if ($_subVal instanceof SWIFT_SetupDatabaseIndex && $_subVal->GetIsClassLoaded())
                        {
                            $this->CreateIndex($_subVal);

                            $this->ExecuteQueue();
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Execute the given Queue
     *
     * @author Varun Shoor
     * @param int $_executionType The Execution Type (ALL or Specific)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function ExecuteQueue($_executionType = self::EXECUTE_ALL)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_queueContainer = $this->GetQueue();

        if ($_executionType != self::EXECUTE_ALL && (!isset($_queueContainer[$_executionType]) || !count($_queueContainer[$_executionType])))
        {
            return false;
        }

        if ($_executionType != self::EXECUTE_ALL)
        {
            $_queueContainer = $_queueContainer[$_executionType];
        }

        if (_is_array($_queueContainer))
        {
            // Type
            foreach ($_queueContainer as $_key=>$_val)
            {
                if (_is_array($_val))
                {
                    foreach ($_val as $_subKey => $_subVal)
                    {
                        $_resultStatus = $this->Execute($_subVal);

                        if (!$_resultStatus[0])
                        {
                            $this->SetStatus(false);
                        }

                        $this->DispatchControllerStatus($_subVal, $_resultStatus);
                    }
                }
            }
        }

        $this->ClearQueue($_executionType);

        return true;
    }

    /**
     * Dispatch the relevant status info to the active controller
     *
     * @author Varun Shoor
     * @param SWIFT_SetupDatabaseTable|SWIFT_SetupDatabaseIndex|SWIFT_SetupDatabaseInsertSQL|SWIFT_SetupDatabaseSQL $_SetupDatabaseObject The SWIFT Setup Database Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception if the Class is not Loaded
     */
    private function DispatchControllerStatus($_SetupDatabaseObject, $_resultStatus = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SetupDatabaseObject->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_resultStatus))
        {
            $_resultStatus = array(false, '');
        }

        if (!isset($_resultStatus[0]))
        {
            $_resultStatus[0] = false;
        }

        if (!isset($_resultStatus[1]))
        {
            $_resultStatus[1] = '';
        }

        if (isset($_SWIFT->Controller) && $_SWIFT->Controller instanceof SWIFT_Controller && $_SWIFT->Controller->GetIsClassLoaded() && get_short_class($_SWIFT->Controller) == 'Controller_Setup')
        {
            $_className = get_short_class($_SetupDatabaseObject);

            switch ($_className)
            {
                case 'SWIFT_SetupDatabaseTable':
                {
                    $this->_setupStatusList[] = array('statusText' => sprintf($this->Language->Get('setupexectable'), $_SetupDatabaseObject->GetName()), 'result' => $_resultStatus[0], 'reasonFailure' => $_resultStatus[1]);
                    break;
                }

                case 'SWIFT_SetupDatabaseIndex':
                {
                    $this->_setupStatusList[] = array('statusText' => sprintf($this->Language->Get('setupexecindex'), $_SetupDatabaseObject->GetName(), $_SetupDatabaseObject->GetTableName()), 'result' => $_resultStatus[0], 'reasonFailure' => $_resultStatus[1]);
                    break;
                }

                case 'SWIFT_SetupDatabaseInsertSQL':
                {
                    $this->_setupStatusList[] = array('statusText' => sprintf($this->Language->Get('setupexecinsert'), $_SetupDatabaseObject->GetName()), 'result' => $_resultStatus[0], 'reasonFailure' => $_resultStatus[1]);
                    break;
                }

                case 'SWIFT_SetupDatabaseSQL':
                {
                    $this->_setupStatusList[] = array('statusText' => sprintf($this->Language->Get('setupexecsql'), $_SetupDatabaseObject->GetSQL()), 'result' => $_resultStatus[0], 'reasonFailure' => $_resultStatus[1]);
                    break;
                }

                default:
                break;
            }
        }

        return true;
    }

    /**
     * Execute the Given Database Object Query and returns the result
     *
     * @author Varun Shoor
     * @param SWIFT_SetupDatabaseTable|SWIFT_SetupDatabaseIndex|SWIFT_SetupDatabaseInsertSQL|SWIFT_SetupDatabaseSQL $_SetupDatabaseObject The Database Type Object
     * @return array
     */
    private function Execute(SWIFT_Base $_SetupDatabaseObject = null)
    {
        $_ADODBObject = $this->Database->GetADODBObject();
        $_ADODBDictionaryObject = $this->Database->GetADODBDictionaryObject();

        if (!$_SetupDatabaseObject->GetIsClassLoaded())
        {
            return array(false, 'Invalid SQL Type');
        }

        if ($this->GetStatus() == false)
        {
            return array(false, '');
        }
        $_className = get_short_class($_SetupDatabaseObject);

        $_result = false;

        if($_SetupDatabaseObject instanceof SWIFT_SetupDatabaseTable) {
            $_sql = $_ADODBDictionaryObject->CreateTableSQL($_SetupDatabaseObject->GetName(), $_SetupDatabaseObject->GetFields());
            $_result = $_ADODBDictionaryObject->ExecuteSQLArray($_sql);

            if (!$_result || $_result == 1) {
                return array(false, $_sql . SWIFT_CRLF . $_ADODBObject->ErrorMsg());
            }
        } else if ($_SetupDatabaseObject instanceof SWIFT_SetupDatabaseIndex) {
            $_sql = $_ADODBDictionaryObject->CreateIndexSQL($_SetupDatabaseObject->GetName(), $_SetupDatabaseObject->GetTableName(), $_SetupDatabaseObject->GetFields(), $_SetupDatabaseObject->GetOptions());
            $_result = $_ADODBDictionaryObject->ExecuteSQLArray($_sql);

            if (!$_result || $_result == 1) {
                return array(false, $_sql . SWIFT_CRLF . $_ADODBObject->ErrorMsg());
            }
        } else if ($_SetupDatabaseObject instanceof SWIFT_SetupDatabaseInsertSQL) {
            $_result = $_ADODBObject->AutoExecute($_SetupDatabaseObject->GetName(), $_SetupDatabaseObject->GetFields(), 'INSERT');

            if (!$_result) {
                return array(false, $_SetupDatabaseObject->GetName() . SWIFT_CRLF . $_ADODBObject->ErrorMsg());
            }
        } else if ($_SetupDatabaseObject instanceof SWIFT_SetupDatabaseSQL) {
            $_result = $_ADODBObject->Execute($_SetupDatabaseObject->GetSQL());

        } else {
            return array(false, 'Invalid SQL Type');
        }

        return array(true, '');
    }

    /**
     * Loads the table into the container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadTables()
    {
        return true;
    }

    /**
     * Get the Page Count for Execution
     *
     * @author Varun Shoor
     * @return int
     */
    public function GetPageCount()
    {
        return 1;
    }

    /**
     * Function that does the heavy execution
     *
     * @author Varun Shoor
     * @param int $_pageIndex The Page Index
     * @return bool "true" on Success, "false" otherwise
     */
    public function Install($_pageIndex)
    {
        $pageIndex ?? 1;
        $_pageCount = $this->GetPageCount();

        $_appName = $this->GetAppName();

        $_SWIFT_AppObject = SWIFT_App::Get($_appName);

        if ($_pageIndex == 1)
        {
            $this->CreateTables();

            $this->CreateIndexes();

            $this->ExecuteQueue();
        }

        if ($_pageCount == $_pageIndex) {
            SWIFT_AppLog::Create($_SWIFT_AppObject, SWIFT_AppLog::TYPE_INSTALL, '');
        }

        return $this->GetStatus();
    }

    /**
     * Complete the Installation
     *
     * @author Varun Shoor
     * @return bool|array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InstallApp()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_appName = $this->GetAppName();

        $_SWIFT_AppObject = SWIFT_App::Get($_appName);

        SWIFT_Loader::AddApp(SWIFT_App::Get($_appName));

        $this->LoadTables();

        $_statusList = array();

        $_appPageNumbers = $this->GetPageCount();
        if (empty($_appPageNumbers))
        {
            $_appPageNumbers = 1;
        }

        for ($index = 1; $index <= $_appPageNumbers; $index++) {
            $_installResult = $this->Install($index);

            if (!$_installResult)
            {
                return false;
            }
        }

        $_SimpleXMLObject = SWIFT_App::RetrieveConfigXMLObject($_appName);

        $_appVersion = SWIFT_VERSION;
        if (isset($_SimpleXMLObject->version))
        {
            $_appVersion = (string)$_SimpleXMLObject->version;
        }

        $this->Settings->UpdateKey('installedapps', $_appName, '1', true);
        $this->Settings->UpdateKey('appversions', $_appName, $_appVersion, true);

        SWIFT_Settings::RebuildCache();

        $_SWIFT_TemplateManagerObject = new SWIFT_TemplateManager();
        $_templateFile = $_SWIFT_AppObject->GetDirectory() . '/' . SWIFT_CONFIG_DIRECTORY . '/templates.xml';
        if (file_exists($_templateFile)) {
            $_SWIFT_TemplateManagerObject->Merge($_templateFile);
        }

        $_SWIFT_SettingsManagerObject = new SWIFT_SettingsManager();
        $_settingsFile = $_SWIFT_AppObject->GetDirectory() . '/' . SWIFT_CONFIG_DIRECTORY . '/settings.xml';
        if (file_exists($_settingsFile)) {
            $_SWIFT_SettingsManagerObject->Import($_settingsFile, '', '', '', false);
        }

        $_SWIFT_LanguageManagerObject = new SWIFT_LanguageManager();
        $_languageFile = $_SWIFT_AppObject->GetDirectory() . '/' . SWIFT_CONFIG_DIRECTORY . '/language.xml';
        if (file_exists($_languageFile)) {
            $_SWIFT_LanguageManagerObject->UpgradeLanguages($_languageFile);
        }

        SWIFT_CacheManager::EmptyCacheDirectory();

        SWIFT_CacheManager::RebuildEntireCache();

        $_statusList = $this->_setupStatusList;

        return $_statusList;
    }

    /**
     * Run a Complete Upgrade on a App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpgradeApp()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $this->Upgrade();

        SWIFT_CacheManager::EmptyCacheDirectory();

        SWIFT_CacheManager::RebuildEntireCache();

        return true;
    }

    /**
     * Uninstalls the app
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Uninstall()
    {
        $_appName = $this->GetAppName();

        $_logData = '';

        $_SWIFT_AppObject = SWIFT_App::Get($_appName);

        $_ADODBDictionaryObject = $this->Database->GetADODBDictionaryObject();

        $this->LoadTables();

        $_tableContainer = $this->GetTableContainer();

        if (_is_array($_tableContainer))
        {
            foreach ($_tableContainer as $_key => $_val)
            {
                if ($_val instanceof SWIFT_SetupDatabaseTable && $_val->GetIsClassLoaded())
                {
                    $_dropTableSQLResult = $_ADODBDictionaryObject->DropTableSQL($_val->GetName());

                    $_logData .= implode(SWIFT_CRLF, $_dropTableSQLResult);

                    $_result = $_ADODBDictionaryObject->ExecuteSQLArray($_dropTableSQLResult);
                    if (!$_result || $_result == 1)
                    {
                        return false;
                    }
                }
            }
        }

        $this->Settings->DeleteKey('installedapps', $this->GetAppName(), true);
        $this->Settings->DeleteKey('appversions', $this->GetAppName(), true);

        $_appName = strtolower($_appName);
        $this->Load->Library('Template:TemplateManager', [], true, false, 'base');
        $this->TemplateManager->DeleteOnApp(array($_appName));

        $this->Load->Library('Language:LanguageManager', [], true, false, 'base');
        $this->LanguageManager->DeleteOnApp(array($_appName));

        $this->Load->Library('Settings:SettingsManager');
        $this->SettingsManager->DeleteOnApp(array($_appName));

        SWIFT_AppLog::Create($_SWIFT_AppObject, SWIFT_AppLog::TYPE_UNINSTALL, $_logData);

        SWIFT_Settings::RebuildCache();

        SWIFT_CacheManager::EmptyCacheDirectory();

        SWIFT_CacheManager::RebuildEntireCache();

        return true;
    }

    /**
     * Upgrades the app to the latest version
     *
     * @author Varun Shoor
     * @param bool $_isForced (OPTIONAL)
     * @param string $_forceVersion
     * @return array|bool "true" on Success, "false" otherwise
     * @throws ReflectionException
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade($_isForced = false, $_forceVersion = '')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_logData = '';

        $_statusList = array();

        $_appName = $this->GetAppName();
        $_SWIFT_AppObject = SWIFT_App::Get($_appName);

        $_SimpleXMLObject = SWIFT_App::RetrieveConfigXMLObject($_appName);

        $_appVersion = SWIFT_VERSION;
        if (isset($_SimpleXMLObject->version))
        {
            $_appVersion = (string)$_SimpleXMLObject->version;
        }

        SWIFT_Loader::AddApp($_SWIFT_AppObject);

        $_appDBVersion = SWIFT_App::GetInstalledVersion($_appName);

        $_coreAppList = SWIFT::Get('CoreApps');

        if ($_appDBVersion == false && !in_array($_appName, $_coreAppList))
        {
            return false;

        // If its a core app then use the current product version
        }

        if ($_appDBVersion == false && in_array($_appName, $_coreAppList)) {
            $_versionContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "settings WHERE section = 'core' AND `vkey` = 'version'");

            if (!isset($_versionContainer['data']) || empty($_versionContainer['data']))
            {
                return false;
            }

            $_appDBVersion = $_versionContainer['data'];
        }

        /**
         * ---------------------------------------------
         * VERSION CALLBACKS EXECUTION
         * ---------------------------------------------
         */
        $_versionTable = array();
        $_appClassName = 'SWIFT_SetupDatabase_' . $_appName;
        $_appClassName = prepend_app_namespace($_appName, $_appClassName);
        $_ReflectionClassObject = new ReflectionClass($_appClassName);
        if (!$_ReflectionClassObject instanceof ReflectionClass)
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_methodList = $_ReflectionClassObject->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($_methodList as $_ReflectionMethodObject)
        {
            $_methodName = $_ReflectionMethodObject->getName();

            // Is it a upgrade version method?
            if (substr($_methodName, 0, strlen('UpgradePre_')) == 'UpgradePre_' || substr($_methodName, 0, strlen('UpgradePost_')) == 'UpgradePost_' ||
                    substr($_methodName, 0, strlen('Upgrade_')) == 'Upgrade_')
            {
                $_versionPreParse = str_replace('_', '.', substr($_methodName, strpos($_methodName, '_')+1));
                if (!in_array($_versionPreParse, $_versionTable))
                {
                    $_versionTable[] = $_versionPreParse;
                }
            }
        }

        array_multisort($_versionTable, SORT_ASC, SORT_REGULAR);

        // Now we need to itterate through the version table and execute any PRE functions
        $_activeVersion = $_appDBVersion;
        foreach ($_versionTable as $_version)
        {
//            echo 'ACTIVE: ' . $_activeVersion . ', LOOP: ' . $_version . '<br />';
            if (version_compare($_activeVersion, $_version) == -1 &&
                    (version_compare($_version, $_appVersion) == -1 || version_compare($_version, $_appVersion) == 0))
            {
                $_methodName = 'UpgradePre_' . str_replace('.', '_', $_version);
                try
                {
                    $_ReflectionMethodObject = $_ReflectionClassObject->getMethod($_methodName);

                    if ($_ReflectionMethodObject->getName() == $_methodName)
                    {
                        $_logData .= 'Calling: ' . $_methodName . SWIFT_CRLF;

                        call_user_func_array(array($this, $_methodName), array());
                    }
                } catch (Exception $_ExceptionObject) {

                }

                $_activeVersion = $_version;
            }
        }

        /**
         * ---------------------------------------------
         * UPDATE THE DB STRUCTURE ACCORDING TO SPEC
         * ---------------------------------------------
         */
        $_ADODBObject = $this->Database->GetADODBObject();
        $_ADODBDictionaryObject = $this->Database->GetADODBDictionaryObject();

        $this->Load->Library('Setup:SetupDiagnostics');
        $_tableStructure = $this->SetupDiagnostics->GetTableStructure($_appName);

        // Process Missing Tables
        if (isset($_tableStructure[SWIFT_SetupDiagnostics::TABLE_MISSING][$_appName]))
        {
            foreach ($_tableStructure[SWIFT_SetupDiagnostics::TABLE_MISSING][$_appName] as $_createTableContainer)
            {
                $_statusList[] = array('Creating Table: ' . $_createTableContainer[0], true);
                $_logData .= 'Creating Table: ' . $_createTableContainer[0] . SWIFT_CRLF;
                $_logData .= implode(SWIFT_CRLF, $_createTableContainer[1]);

                $_result = $_ADODBDictionaryObject->ExecuteSQLArray($_createTableContainer[1]);
                if (!$_result || $_result == 1)
                {
                    print_r($_createTableContainer[1]);
                    echo '1:' . $_ADODBObject->ErrorMsg();
                }
            }
        }

        // Process Renaming of Tables
        if (isset($_tableStructure[SWIFT_SetupDiagnostics::TABLE_RENAME][$_appName]))
        {
            foreach ($_tableStructure[SWIFT_SetupDiagnostics::TABLE_RENAME][$_appName] as $_renameTableContainer)
            {
                $_statusList[] = array('Renaming Table: ' . $_renameTableContainer[0], true);
                $_logData .= 'Renaming Table: ' . $_renameTableContainer[0] . SWIFT_CRLF;
                $_logData .= implode(SWIFT_CRLF, $_renameTableContainer[1]);

                $_result = $_ADODBDictionaryObject->ExecuteSQLArray($_renameTableContainer[1]);
                if (!$_result || $_result == 1)
                {
                    print_r($_renameTableContainer[1]);
                    echo '1-2:' . $_ADODBObject->ErrorMsg();
                }
            }
        }

        // Process Columns
        $_columnStructure = $this->SetupDiagnostics->GetColumnStructure($_appName);
        foreach (array(SWIFT_SetupDiagnostics::COLUMN_RENAME, SWIFT_SetupDiagnostics::COLUMN_MISSING, SWIFT_SetupDiagnostics::COLUMN_METATYPEMISMATCH, SWIFT_SetupDiagnostics::COLUMN_LENGTHMISMATCH) as $_columnContainerType)
        {
            if (isset($_columnStructure[$_columnContainerType]))
            {
                foreach ($_columnStructure[$_columnContainerType] as $_tableName => $_queryContainer)
                {
                    $_queryStringContainer = array();
                    foreach ($_queryContainer as $_queryHolder)
                    {
                        $_statusList[] = array('Altering Columns in Table: ' . $_queryHolder[0], true);
                        $_logData .= 'Altering Columns in Table: ' . $_queryHolder[0] . SWIFT_CRLF;
                        $_logData .= $_queryHolder[1] . SWIFT_CRLF;

                        $_queryStringContainer[] = $_queryHolder[1];
                    }

                    $_result = $_ADODBDictionaryObject->ExecuteSQLArray($_queryStringContainer);
                    if (!$_result || $_result == 1)
                    {
                        print_r($_queryStringContainer);
                        echo '2:' . $_ADODBObject->ErrorMsg();
                    }
                }
            }
        }

        // Process indices
        $_indexStructure = $this->SetupDiagnostics->GetIndexStructure($_appName);
        foreach (array(SWIFT_SetupDiagnostics::INDEX_MISMATCH, SWIFT_SetupDiagnostics::INDEX_MISSING) as $_indexContainerType)
        {
            if (isset($_indexStructure[$_indexContainerType]))
            {
                foreach ($_indexStructure[$_indexContainerType] as $_tableName => $_queryContainer)
                {
                    $_queryStringContainer = array();
                    foreach ($_queryContainer as $_queryHolder)
                    {
                        $_statusList[] = array('Processing Index: ' . $_queryHolder[0], true);
                        $_logData .= 'Processing Index: ' . $_queryHolder[0] . SWIFT_CRLF;

                        foreach ($_queryHolder[1] as $_indexQuery)
                        {
                            $_queryStringContainer[] = $_indexQuery;
                            $_logData .= $_indexQuery . SWIFT_CRLF;
                        }
                    }

                    $_result = $_ADODBDictionaryObject->ExecuteSQLArray($_queryStringContainer);
                    if (!$_result || $_result == 1)
                    {
                        print_r($_queryStringContainer);
                        echo '3:' . $_ADODBObject->ErrorMsg();
                    }
                }
            }
        }

        /**
         * ---------------------------------------------
         * VERSION CALLBACK EXECUTION
         * ---------------------------------------------
         */

        // Now we need to itterate through the version table and execute any POST functions
        $_activeVersion = $_appDBVersion;
        foreach ($_versionTable as $_version)
        {
            if ($_isForced || ($_forceVersion && version_compare($_version, $_forceVersion) === 0) ||
                (version_compare($_activeVersion, $_version) == -1 &&
                    (version_compare($_version, $_appVersion) == -1 || version_compare($_version, $_appVersion) == 0)))
            {
                $_methodName = 'UpgradePost_' . str_replace('.', '_', $_version);
                try
                {
                    $_ReflectionMethodObject = $_ReflectionClassObject->getMethod($_methodName);

                    if ($_ReflectionMethodObject->getName() == $_methodName)
                    {
                        call_user_func_array(array($this, $_methodName), array());
                    }
                } catch (Exception $_ExceptionObject) {

                }

                $_methodName = 'Upgrade_' . str_replace('.', '_', $_version);
                try
                {
                    $_ReflectionMethodObject = $_ReflectionClassObject->getMethod($_methodName);

                    if ($_ReflectionMethodObject->getName() == $_methodName)
                    {
                        $_logData .= 'Calling: ' . $_methodName . SWIFT_CRLF;

                        call_user_func_array(array($this, $_methodName), array());
                    }
                } catch (Exception $_ExceptionObject) {

                }

                $_activeVersion = $_version;
            }
        }

        // Update the Version
        $this->Settings->UpdateKey('appversions', $_appName, $_appVersion, true);

        $_templateFile = $_SWIFT_AppObject->GetDirectory() . '/' . SWIFT_CONFIG_DIRECTORY . '/templates.xml';
        if (file_exists($_templateFile)) {
            $_SWIFT_TemplateManagerObject = new SWIFT_TemplateManager();
            $_SWIFT_TemplateManagerObject->Merge($_templateFile);
        }

        $_settingsFile = $_SWIFT_AppObject->GetDirectory() . '/' . SWIFT_CONFIG_DIRECTORY . '/settings.xml';
        if (file_exists($_settingsFile)) {
            $_SWIFT_SettingsManagerObject = new SWIFT_SettingsManager();
            $_SWIFT_SettingsManagerObject->Import($_settingsFile, '', '', '', true);
        }

        $_languageFile = $_SWIFT_AppObject->GetDirectory() . '/' . SWIFT_CONFIG_DIRECTORY . '/language.xml';
        if (file_exists($_languageFile)) {
            $_SWIFT_LanguageManagerObject = new SWIFT_LanguageManager();
            $_SWIFT_LanguageManagerObject->UpgradeLanguages($_languageFile);
        }

        SWIFT_AppLog::Create($_SWIFT_AppObject, SWIFT_AppLog::TYPE_UPGRADE, $_logData);

        SWIFT_Settings::RebuildCache();

        SWIFT_CacheManager::EmptyCacheDirectory();

        return $_statusList;
    }

    /**
     * Loads the table structure and indices from the models
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadModels()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_modelFileList = self::GetModelFileList($this->_appDirectory . '/' . SWIFT_MODELS_DIRECTORY);

        // For core app we search the SWIFT directory too
        if ($this->_appName == APP_CORE) {
            $_modelFileList = array_merge($_modelFileList, self::GetModelFileList('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_MODELS_DIRECTORY));
        }

        if (!count($_modelFileList)) {
            return false;
        }

        foreach ($_modelFileList as $_modelClassName => $_modelFilePath) {
            SWIFT_Loader::AddApp(SWIFT_App::Get($this->_appName));
            $_modelClassName = prepend_model_namespace($this->_appName, $_modelClassName, $_modelFilePath);

            if (!class_exists($_modelClassName)) {
                require_once($_modelFilePath);
            }

            $_modelReflectionClassObject = new ReflectionClass($_modelClassName);
            if (!$_modelReflectionClassObject instanceof ReflectionClass)
            {
                throw new SWIFT_Exception('"' . Clean($_modelClassName) . '" Reflection Class could not be initialized in SWIFT File "' . $_modelFilePath . '"');
            }

            // If its not derived from SWIFT_Model, throw exception
            if (!is_subclass_of($_modelClassName, 'SWIFT_Model') && !is_subclass_of($_modelClassName, 'SWIFT_Exception') && !interface_exists($_modelClassName)) {
                throw new SWIFT_Exception('"' . Clean($_modelClassName) . '" is not a sub class of "SWIFT_Model"');
            }

            $_parentClassContainer = array();
            $_ParentReflectionClass = $_modelReflectionClassObject;
            $_parentClassHasDefinition = false;

            while ($_ParentReflectionClass = $_ParentReflectionClass->getParentClass()) {
                if (count($_parentClassContainer) > 4) {
                    break;
                }

                $_parentClassContainer[] = $_ParentReflectionClass->getName();

                // If the parent class has a table name, we ignore the execution for this one
                if ($_ParentReflectionClass->hasConstant('TABLE_NAME') && !empty($_ParentReflectionClass->getConstant('TABLE_NAME'))) {
                    $_parentClassHasDefinition = true;
                }
            }

            if ($_parentClassHasDefinition) {
                continue;
            }

            $_constantContainer = $_modelReflectionClassObject->getConstants();

            // Check for two essential constants
            if (!isset($_constantContainer['TABLE_NAME']) || !isset($_constantContainer['PRIMARY_KEY']) || empty($_constantContainer['TABLE_NAME']) || empty($_constantContainer['PRIMARY_KEY'])) {
                continue;
            }

            $_tableName = Clean($_constantContainer['TABLE_NAME']);
            $_primaryKey = Clean($_constantContainer['PRIMARY_KEY']);

            $_tableType = self::TABLETYPE_INNODB;
            if (isset($_constantContainer['TABLE_TYPE']) && !empty($_constantContainer['TABLE_TYPE']) && self::IsValidTableType($_tableType)) {
                $_tableType = $_constantContainer['TABLE_TYPE'];
            }

            if (isset($_constantContainer['TABLE_STRUCTURE']) && !empty($_constantContainer['TABLE_STRUCTURE'])) {
                $this->AddTable($_tableName, new SWIFT_SetupDatabaseTable(TABLE_PREFIX . $_tableName, $_constantContainer['TABLE_STRUCTURE']), $_tableType);
            }

            if (isset($_constantContainer['TABLE_RENAME']) && !empty($_constantContainer['TABLE_RENAME'])) {
                $this->_renameTableContainer[TABLE_PREFIX . $_tableName] = TABLE_PREFIX . mb_strtolower($_constantContainer['TABLE_RENAME']);
            }

            // Add the indices
            foreach ($_constantContainer as $_constantName => $_constantValue) {
                if (substr(mb_strtoupper($_constantName), 0, 6) == 'INDEX_') {
                    $_indexName = substr(mb_strtolower($_constantName), 6);

                    $_finalIndexName = $_indexName;
                    if (is_numeric($_indexName)) {
                        $_finalIndexName = $_tableName . $_indexName;
                    }

                    $_indexOptions = array();

                    $_indexTypeKey = 'INDEXTYPE_' . $_indexName;
                    if (isset($_constantContainer[$_indexTypeKey]) && !empty($_constantContainer[$_indexTypeKey])) {
                        $_indexTypeValue = mb_strtoupper($_constantContainer[$_indexTypeKey]);
                        if ($_indexTypeValue == 'UNIQUE') {
                            $_indexOptions[] = 'UNIQUE';
                        }
                    }

                    $this->AddIndex($_tableName, new SWIFT_SetupDatabaseIndex($_finalIndexName, TABLE_PREFIX . $_tableName, $_constantValue, $_indexOptions));

                // Parse renamed columns
                } else if (substr(mb_strtoupper($_constantName), 0, strlen('COLUMN_RENAME_')) == 'COLUMN_RENAME_') {
                    $_finalColumnName = substr(mb_strtolower($_constantName), strlen('COLUMN_RENAME_'));

                    $this->_renameColumnContainer[TABLE_PREFIX . $_tableName][$_constantValue] = $_finalColumnName;

                }
            }
        }

        return true;
    }

    /**
     * Retrieves the renamed columns
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRenamedColumns()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return $this->_renameColumnContainer;
    }

    /**
     * Retrieves the renamed tables
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRenamedTables()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return $this->_renameTableContainer;
    }

    /**
     * Retrieves the List of Models from the Directory
     *
     * @author Varun Shoor
     * @param string $_directoryPath
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function GetModelFileList($_directoryPath)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_directoryPath = StripTrailingSlash($_directoryPath);

        if (!file_exists($_directoryPath) || !is_dir($_directoryPath)) {
            return array();
        }

        $_modelFileList = array();

        if ($_directoryHandle = opendir($_directoryPath)) {
            while (false !== ($_fileName = readdir($_directoryHandle))) {
                if ($_fileName != '.' && $_fileName != '..') {
                    $_filePath = $_directoryPath . '/' . $_fileName;

                    if (is_dir($_filePath)) {
                        $_modelFileList = array_merge($_modelFileList, self::GetModelFileList($_filePath));
                    } else {
                        $_matches = array();
                        if (preg_match('/^class\.(.*)\.php$/i', $_fileName, $_matches)) {
                            $_modelFileList[$_matches[1]] = $_filePath;
                        }
                    }
                }
            }

            closedir($_directoryHandle);
        }

        return $_modelFileList;
    }
}
?>
