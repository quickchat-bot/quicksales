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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\Import;

use Base\Library\Import\SWIFT_Import_Exception;
use Base\Models\Import\SWIFT_ImportLog;
use Base\Models\Import\SWIFT_ImportRegistry;
use SWIFT;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Library;
use SWIFT_Loader;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;

/**
 * The Import Manager Class
 *
 * @method UpdateForm($_databaseHost, $_databaseName, $_databasePort = '', $_dbUsername = '', $_dbPassword = '', $_databaseSocket = '')

 * @author Varun Shoor
 */
class SWIFT_ImportManager extends SWIFT_Library
{
    protected $_productName = '';
    protected $_productTitle = '';

    protected $_logContainer = array();

    public $DatabaseImport = false;

    /**
     * @var SWIFT_ImportRegistry
     */
    protected $ImportRegistry;

    static protected $_productListCache = false;
    static protected $_ImportRegistry = false;

    // Core Constants
    const LOG_SUCCESS = 1;
    const LOG_FAILURE = 2;
    const LOG_WARNING = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_productName The Product Name
     * @param string $_productTitle (OPTIONAL) The Product Title
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Import_Exception If Object Creation Fails
     */
    public function __construct($_productName, $_productTitle = '')
    {
        parent::__construct();

        if (!$this->SetProduct($_productName)) {
            throw new SWIFT_Import_Exception(SWIFT_CREATEFAILED);
        }

        $_finalProductTitle = $_productTitle;
        if (empty($_productTitle)) {
            $_finalProductTitle = $_productName;
        }

        $this->SetProductTitle($_productTitle);

        if (!self::$_ImportRegistry instanceof SWIFT_ImportRegistry || !self::$_ImportRegistry->GetIsClassLoaded()) {
            self::$_ImportRegistry = new SWIFT_ImportRegistry();
        }

        $this->ImportRegistry = self::$_ImportRegistry;
    }

    /**
     * Set the product name
     *
     * @author Varun Shoor
     * @param string $_productName The Product Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetProduct($_productName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_productName) || !self::IsValidProduct($_productName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_productName = Clean($_productName);

        return true;
    }

    /**
     * Retrieve the currently set product name
     *
     * @author Varun Shoor
     * @return string The Product Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProduct()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_productName;
    }

    /**
     * Retrieve the Import Registry Object
     *
     * @author Varun Shoor
     * @return SWIFT_ImportRegistry
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetImportRegistry()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->ImportRegistry;
    }

    /**
     * Set the product title
     *
     * @author Varun Shoor
     * @param string $_productTitle The Product Title
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetProductTitle($_productTitle)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_productTitle)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_productTitle = $_productTitle;

        return true;
    }

    /**
     * Retrieve the currently set product title
     *
     * @author Varun Shoor
     * @return string The Product Title
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProductTitle()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_productTitle;
    }

    /**
     * Retrieve the available Product List
     *
     * @author Varun Shoor
     * @return array The Product List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetProductList()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (self::$_productListCache != false) {
            return self::$_productListCache;
        }

        $_productList = array();
        $_basePath = './' . SWIFT_BASE_DIRECTORY . '/apps/base/' . SWIFT_LIBRARYDIRECTORY . '/Import';

        $_fileList = scandir($_basePath);
        foreach ($_fileList as $_fileName) {
            if ($_fileName == '.' || $_fileName == '..' || !is_dir($_basePath . '/' . $_fileName) || !file_exists($_basePath . '/' . $_fileName . '/class.SWIFT_ImportManager_' . $_fileName . '.php')) {
                continue;
            }

            $_productList[] = $_fileName;
        }

        if (!count($_productList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        self::$_productListCache = $_productList;

        return $_productList;
    }

    /**
     * Check to see if its a valid product
     *
     * @author Varun Shoor
     * @param string $_productName The Product Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsValidProduct($_productName)
    {
        if (empty($_productName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_productList = self::GetProductList();
        if (in_array($_productName, $_productList)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Import Manager Object for given product
     *
     * @author Varun Shoor
     * @param string $_productName The Product Name
     * @return SWIFT_ImportManager The Import Manager Object
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetImportManagerObject($_productName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidProduct($_productName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_className = '\Base\Library\Import\\'. $_productName . '\SWIFT_ImportManager_' . $_productName;
        $_SWIFT_ImportManagerObject = new $_className();
        if (!$_SWIFT_ImportManagerObject instanceof SWIFT_ImportManager || !$_SWIFT_ImportManagerObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_SWIFT_ImportManagerObject;
    }

    /**
     * Return the Import Tables
     *
     * @author Varun Shoor
     * @return array The Import Tables
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetImportTables()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return array();
    }

    /**
     * Retrieve the Table Object
     *
     * @author Varun Shoor
     * @param string $_tableName The Table Name
     * @return SWIFT_ImportTable|bool The Table Object
     * @throws SWIFT_Exception If the Class is not Loaded or If Object Creation Fails
     */
    protected function GetTableObject($_tableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_tableName)) {
            return false;
        }

        $_tableName = Clean($_tableName);
        $_className = '';
        $_SWIFT_ImportTableObject = false;

        try {
            $_className = '\Base\Library\Import\\'. $this->_productName . '\SWIFT_ImportTable_' . $_tableName;
            $_SWIFT_ImportTableObject = new $_className($this);

        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            return false;
        }

        if (!$_SWIFT_ImportTableObject instanceof SWIFT_ImportTable || !$_SWIFT_ImportTableObject->GetIsClassLoaded()) {
            return false;
        }

        return $_SWIFT_ImportTableObject;
    }

    /**
     * Retrieve the currently active table object
     *
     * @author Varun Shoor
     * @return SWIFT_ImportTable The Import Table Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetActiveTable()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalActiveTable = false;

        $_sectionContainer = $this->ImportRegistry->GetSection($this->GetProduct());

        $_registryActiveTable = $this->ImportRegistry->GetKey($this->GetProduct(), 'activetable');
        if (!empty($_registryActiveTable)) {
            $_finalActiveTable = $_registryActiveTable;

            // If none set, set the first one
        } else {
            $_tableList = $this->GetImportTables();

            foreach ($_tableList as $_tableName) {
                if (isset($_sectionContainer['completed' . $_tableName]) && $_sectionContainer['completed' . $_tableName] == '1') {
                    continue;
                }

                $_finalActiveTable = $_tableName;
                break;
            }

        }

        return $this->GetTableObject($_finalActiveTable);
    }

    /**
     * Render the Form
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceTab $_TabObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderForm(SWIFT_UserInterfaceTab $_TabObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Parse the form POST data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessForm()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Start the import progress, reset counters and settings
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function StartImport($_processTotalRecordCount = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_processTotalRecordCount) {
            $this->ProcessTotalRecordCount();
        }

        $this->ImportRegistry->UpdateKey($this->GetProduct(), 'starttime', DATENOW);

        return true;
    }

    /**
     * Retrieve the Start Time
     *
     * @author Varun Shoor
     * @return int The Start Time
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetStartTime()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int)($this->ImportRegistry->GetKey($this->GetProduct(), 'starttime'));
    }

    /**
     * Reset the Processed Record Count
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ResetProcessedRecordCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ImportRegistry->UpdateKey($this->GetProduct(), 'processedcount', '0');

        return true;
    }

    /**
     * Calculate the Total Record Count
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function ProcessTotalRecordCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_totalRecordCount = 0;

        $_sectionContainer = $this->ImportRegistry->GetSection($this->GetProduct());

        $_tableList = $this->GetImportTables();
        foreach ($_tableList as $_tableName) {
            $_SWIFT_ImportTableObject = $this->GetTableObject($_tableName);
            if (!$_SWIFT_ImportTableObject instanceof SWIFT_ImportTable || !$_SWIFT_ImportTableObject->GetIsClassLoaded()) {
                continue;
            }

            if (isset($_sectionContainer['completed' . $_tableName]) && $_sectionContainer['completed' . $_tableName] == '1') {
                continue;
            }

            $_totalRecordCount += $_SWIFT_ImportTableObject->GetTotalRecordCount();
        }

        $this->ImportRegistry->UpdateKey($this->GetProduct(), 'totalcount', $_totalRecordCount);

        return true;
    }

    /**
     * Retrieve the Total Record Count
     *
     * @author Varun Shoor
     * @return int The Total Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTotalRecordCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int)($this->ImportRegistry->GetKey($this->GetProduct(), 'totalcount'));
    }

    /**
     * Increment the Processed Record Count
     *
     * @author Varun Shoor
     * @param int $_incrementValue The Increment Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IncrementProcessedRecordCount($_incrementValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalProcessed = $this->GetProcessedRecordCount() + $_incrementValue;
        $this->ImportRegistry->UpdateKey($this->GetProduct(), 'processedcount', $_finalProcessed);

        return true;
    }

    /**
     * Retrieve the Processed Record Count
     *
     * @author Varun Shoor
     * @return int The Processed Records
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProcessedRecordCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int)($this->ImportRegistry->GetKey($this->GetProduct(), 'processedcount'));
    }

    /**
     * Add to Log
     *
     * @author Varun Shoor
     * @param string $_logMessage The Log Message
     * @param mixed $_logType The Log Type
     * @param string $_errorMessage (OPTIONAL) The Error Message
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddToLog($_logMessage, $_logType, $_errorMessage = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_logContainer[] = array($_logType, $_logMessage, $_errorMessage);

        if ($_logType == self::LOG_SUCCESS) {
            return true;
        }

        $_finalMessage = $_logMessage;
        if (!empty($_errorMessage)) {
            $_finalMessage .= SWIFT_CRLF . $_errorMessage;
        }

        SWIFT_ImportLog::Create($_finalMessage, $_logType, $_SWIFT->Staff);

        return true;
    }

    /**
     * Retrieve the Log Container
     *
     * @author Varun Shoor
     * @return array The Log Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLog()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_logContainer;
    }

    /**
     * Retrieve the Processed Percentage
     *
     * @author Varun Shoor
     * @return int|string The Processed Percentage
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProcessedPercent()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_totalRecord = $this->GetTotalRecordCount();
        $_processedRecordCount = $this->GetProcessedRecordCount();

        if ($_totalRecord == 0) {
            return 0;
        }

        $_percentage = number_format(($_processedRecordCount / $_totalRecord) * 100, 2);

        return $_percentage;
    }

    /**
     * Execute the code before importing starts
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImportPre()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        return true;
    }

    /**
     * Import the data
     *
     * @author Varun Shoor
     * @return SWIFT_ImportTable|bool The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_ImportTable = $this->GetActiveTable();
        if (!$_SWIFT_ImportTable instanceof SWIFT_ImportTable || !$_SWIFT_ImportTable->GetIsClassLoaded()) {
            return false;
        }

        if ($_SWIFT_ImportTable->GetByPass()) {
            $_SWIFT_ImportTable->MarkTableAsCompleted();

            return $_SWIFT_ImportTable;
        }

        $_processedRecords = $_SWIFT_ImportTable->Import();
        $_totalRecords = $_SWIFT_ImportTable->GetTotalRecordCount();
        $_finalProcessedRecords = $_SWIFT_ImportTable->IncrementProcessedRecordCount($_processedRecords);

        $_SWIFT_ImportTable->UpdateOffset((string)$_finalProcessedRecords);

        $this->IncrementProcessedRecordCount($_processedRecords);

        /*        echo 'Table Name: ' . $_SWIFT_ImportTable->GetTableName() . '<br/>';
                echo 'Total Records: ' . $_totalRecords . '<br/>';
                echo 'Processed: ' . $_processedRecords . '<br/>';
                echo 'Final Processed: ' . $_finalProcessedRecords . '<br/>';*/

        if ($_finalProcessedRecords >= $_totalRecords) {
            $_SWIFT_ImportTable->MarkTableAsCompleted();
        }

        return $_SWIFT_ImportTable;
    }
}

?>
