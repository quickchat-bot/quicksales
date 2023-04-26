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

namespace Tickets\Models\Macro;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;

/**
 * The Macro Reply Data Model
 *
 * @author Varun Shoor
 */
class SWIFT_MacroReplyData extends SWIFT_Model
{
    const TABLE_NAME        =    'macroreplydata';
    const PRIMARY_KEY        =    'macroreplydataid';

    const TABLE_STRUCTURE    =    "macroreplydataid I PRIMARY AUTO NOTNULL,
                                macroreplyid I DEFAULT '0' NOTNULL,
                                contents X2 NOTNULL,
                                tagcontents X NOTNULL";

    const INDEX_1            =    'macroreplyid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Macro Reply Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'macroreplydata', $this->GetUpdatePool(), 'UPDATE', "macroreplydataid = '" . (int) ($this->GetMacroReplyDataID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Macro Reply Data ID
     *
     * @author Varun Shoor
     * @return mixed "macroreplydataid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMacroReplyDataID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['macroreplydataid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded())
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "macroreplydata WHERE macroreplydataid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['macroreplydataid']) && !empty($_dataStore['macroreplydataid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['macroreplydataid']) || empty($this->_dataStore['macroreplydataid']))
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Macro Reply Data
     *
     * @author Varun Shoor
     * @param int $_macroReplyID The Macro Reply ID
     * @param string $_contents The Macro Contents
     * @param string $_tagContents The Tag Contents
     * @return int Macro Reply Data ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_macroReplyID, $_contents, $_tagContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_macroReplyID) || empty($_contents))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'macroreplydata', array('macroreplyid' => $_macroReplyID, 'contents' => $_contents, 'tagcontents' => $_tagContents), 'INSERT');
        $_macroReplyDataID = $_SWIFT->Database->Insert_ID();

        if (!$_macroReplyDataID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_macroReplyDataID;
    }

    /**
     * Update Macro Reply Data Record
     *
     * @author Varun Shoor
     * @param string $_contents The Macro Contents
     * @param string $_tagContents The Tag Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_contents, $_tagContents)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_contents))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('contents', $_contents);
        $this->UpdatePool('tagcontents', $_tagContents);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete Macro Reply Data record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetMacroReplyDataID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Macro Reply Data's
     *
     * @author Varun Shoor
     * @param array $_macroReplyDataIDList The Macro Reply Data ID List Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_macroReplyDataIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_macroReplyDataIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "macroreplydata WHERE macroreplydataid IN (" . BuildIN($_macroReplyDataIDList) . ")");

        return true;
    }

    /**
     * Delete the macro reply data records based on macroreplyid list
     *
     * @author Varun Shoor
     * @param array $_macroReplyIDList The Macro Reply ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnReply($_macroReplyIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_macroReplyIDList))
        {
            return false;
        }

        $_macroReplyDataIDList = array();
        $_SWIFT->Database->Query("SELECT macroreplydataid FROM " . TABLE_PREFIX . "macroreplydata WHERE macroreplyid IN (" . BuildIN($_macroReplyIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_macroReplyDataIDList[] = $_SWIFT->Database->Record['macroreplydataid'];
        }

        if (!count($_macroReplyDataIDList))
        {
            return false;
        }

        self::DeleteList($_macroReplyDataIDList);

        return true;
    }

    /**
     * Retrieve the data object on the macro reply id
     *
     * @author Varun Shoor
     * @param int $_macroReplyID The Macro Reply ID
     * @return SWIFT_MacroReplyData|null "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnReply($_macroReplyID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_macroReplyID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_macroReplyDataContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "macroreplydata WHERE macroreplyid = '" . $_macroReplyID . "'");
        if (isset($_macroReplyDataContainer['macroreplydataid']) && !empty($_macroReplyDataContainer['macroreplydataid']))
        {
            $_SWIFT_MacroReplyDataObject = new SWIFT_MacroReplyData(new SWIFT_DataStore($_macroReplyDataContainer));

            return $_SWIFT_MacroReplyDataObject;
        }

        return null;
    }
}
