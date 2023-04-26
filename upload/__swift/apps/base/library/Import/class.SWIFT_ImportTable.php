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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\Import;

use Base\Library\Import\SWIFT_ImportManager;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Table Importer Class
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable extends SWIFT_Library
{
    protected $_tableName;
    public $DatabaseImport = false;

    public $_byPass = false;

    static protected $_tableCache = false;

    /**
     * @var SWIFT_ImportManager
     */
    protected $ImportManager;

    protected $_itemsPerPass = 50;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @param string $_tableName The Table Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject, $_tableName)
    {
        parent::__construct();

        if (!$this->SetTableName($_tableName) || !$this->SetImportManager($_SWIFT_ImportManagerObject)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->DatabaseImport = $_SWIFT_ImportManagerObject->DatabaseImport;
    }

    /**
     * Set the Import Manager Object
     *
     * @author Varun Shoor
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetImportManager(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!$_SWIFT_ImportManagerObject instanceof SWIFT_ImportManager || !$_SWIFT_ImportManagerObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->ImportManager = $_SWIFT_ImportManagerObject;

        return true;
    }

    /**
     * Retrieve the Import Manager Object
     *
     * @author Varun Shoor
     * @return SWIFT_ImportManager The Import Manager Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetImportManager()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->ImportManager;
    }

    /**
     * Retrieve the table name
     *
     * @author Varun Shoor
     * @return string The Table Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTableName()
    {
        return $this->_tableName;
    }

    /**
     * Set the Table Name
     *
     * @author Varun Shoor
     * @param string $_tableName The Table Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetTableName($_tableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_tableName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_tableName = $_tableName;

        return true;
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Varun Shoor
     * @return int|bool The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Import all records in one go
     *
     * @author Varun Shoor
     * @return int|bool The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImportAll()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Varun Shoor
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 0;
    }

    /**
     * Retrieve the bypass status
     *
     * @author Varun Shoor
     * @return bool The Bypass Status
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetByPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_byPass;
    }

    /**
     * Set the Bypass Status
     *
     * @author Varun Shoor
     * @param bool $_byPassStatus Set the bypass status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetByPass($_byPassStatus)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_byPass = $_byPassStatus;

        return true;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Varun Shoor
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_itemsPerPass;
    }

    /**
     * Set the Items per Pass
     *
     * @author Varun Shoor
     * @param int $_itemsPerPass The Number of Items per Pass
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetItemsPerPass($_itemsPerPass)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_itemsPerPass)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_itemsPerPass = $_itemsPerPass;

        return true;
    }

    /**
     * Update the Offset for
     *
     * @author Varun Shoor
     * @param string $_offset The New Offset
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateOffset($_offset)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_offset = (int)($_offset);

        $this->ImportManager->GetImportRegistry()->UpdateKey($this->ImportManager->GetProduct(), 'offset' . ':' . $this->GetTableName(), $_offset);

        return true;
    }

    /**
     * Retrieve the currently set offset
     *
     * @author Varun Shoor
     * @return int The Offset
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetOffset()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_offset = $this->ImportManager->GetImportRegistry()->GetKey($this->ImportManager->GetProduct(), 'offset' . ':' . $this->GetTableName());
        $_offset = (int)($_offset);

        return $_offset;
    }

    /**
     * Reset the offset value for this table
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ResetOffset()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ImportManager->GetImportRegistry()->UpdateKey($this->ImportManager->GetProduct(), 'offset' . ':' . $this->GetTableName(), '0');

        return true;
    }

    /**
     * Mark the table as completed
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkTableAsCompleted()
    {
        $this->ImportManager->GetImportRegistry()->UpdateKey($this->ImportManager->GetProduct(), 'completed' . $this->GetTableName(), '1');

        return true;
    }

    /**
     * Retrieve the total record count
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

        $_recordCount = $this->ImportManager->GetImportRegistry()->GetKey($this->ImportManager->GetProduct(), 'recordcount' . $this->GetTableName());
        if ($_recordCount === false) {
            $_recordCount = $this->GetTotal();
        } else {
            return $_recordCount;
        }

        $this->ImportManager->GetImportRegistry()->UpdateKey($this->ImportManager->GetProduct(), 'recordcount' . $this->GetTableName(), $_recordCount);

        return $_recordCount;
    }

    /**
     * Increment the Processed Record Count
     *
     * @author Varun Shoor
     * @param int $_incrementValue The Increment Value
     * @return int
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IncrementProcessedRecordCount($_incrementValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalProcessed = $this->GetProcessedRecordCount() + $_incrementValue;
        $this->ImportManager->GetImportRegistry()->UpdateKey($this->ImportManager->GetProduct(), 'processedcount' . $this->GetTableName(), $_finalProcessed);

        return $_finalProcessed;
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

        return (int)($this->ImportManager->GetImportRegistry()->GetKey($this->ImportManager->GetProduct(), 'processedcount' . $this->GetTableName()));
    }

    /**
     * Check to see if table exists
     *
     * @author Varun Shoor
     * @param string $_tableName The Table Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function TableExists($_tableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName = mb_strtolower($_tableName);

        if (_is_array(self::$_tableCache)) {
            if (in_array($_tableName, self::$_tableCache)) {
                return true;
            }

            return false;
        }

        $_tableList = array();

        $this->DatabaseImport->Query("SHOW TABLES");
        while ($this->DatabaseImport->NextRecord()) {
            foreach ($this->DatabaseImport->Record as $_tableNameList) {
                $_tableList[] = mb_strtolower($_tableNameList);
            }
        }

        self::$_tableCache = $_tableList;

        if (in_array($_tableName, $_tableList)) {
            return true;
        }

        return false;
    }


}

?>
