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

namespace Tickets\Models\Macro;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;

/**
 * The Macro Reply Model
 *
 * @author Varun Shoor
 */
class SWIFT_MacroReply extends SWIFT_Model
{
    const TABLE_NAME        =    'macroreplies';
    const PRIMARY_KEY        =    'macroreplyid';

    const TABLE_STRUCTURE    =    "macroreplyid I PRIMARY AUTO NOTNULL,
                                macrocategoryid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                subject C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                totalhits I DEFAULT '0' NOTNULL,
                                lastusage I DEFAULT '0' NOTNULL,

                                departmentid F DEFAULT '-1' NOTNULL,
                                ownerstaffid F DEFAULT '-1' NOTNULL,
                                tickettypeid F DEFAULT '-1' NOTNULL,
                                ticketstatusid F DEFAULT '-1' NOTNULL,
                                priorityid F DEFAULT '-1' NOTNULL";

    const INDEX_1            =    'macrocategoryid';
    const INDEX_2            =    'staffid';
    const INDEX_3            =    'subject';


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

        $this->Database->AutoExecute(TABLE_PREFIX . 'macroreplies', $this->GetUpdatePool(), 'UPDATE', "macroreplyid = '" . (int) ($this->GetMacroReplyID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Macro Reply ID
     *
     * @author Varun Shoor
     * @return mixed "macroreplyid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMacroReplyID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['macroreplyid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT macroreplies.*, macroreplydata.* FROM " . TABLE_PREFIX . "macroreplies AS macroreplies
                LEFT JOIN " . TABLE_PREFIX . "macroreplydata AS macroreplydata ON (macroreplies.macroreplyid = macroreplydata.macroreplyid)
                WHERE macroreplies.macroreplyid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['macroreplyid']) && !empty($_dataStore['macroreplyid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['macroreplyid']) || empty($this->_dataStore['macroreplyid']))
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
     * Create a new Macro
     *
     * @author Varun Shoor
     * @param int $_macroCategoryID The Macro Category ID
     * @param string $_subject The Subject
     * @param string $_contents The Macro Contents
     * @param array $_tagList (OPTIONAL) The Tag List
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ownerStaffID (OPTIONAL) The Owner Staff ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketPriorityID (OPTIONAL) The Ticket Priority ID
     * @param SWIFT_Staff $_SWIFT_StaffObject (OPTIONAL) The SWIFT_Staff Object Pointer
     * @return int The Macro Reply ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_macroCategoryID, $_subject, $_contents, $_tagList = array(), $_departmentID = 0, $_ownerStaffID = 0, $_ticketTypeID = 0, $_ticketStatusID = 0,
            $_ticketPriorityID = 0, SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_subject) || empty($_contents))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffID = false;
        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded())
        {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'macroreplies', array('macrocategoryid' => $_macroCategoryID, 'staffid' => $_staffID, 'subject' => $_subject,
            'dateline' => DATENOW, 'departmentid' => $_departmentID, 'ownerstaffid' => $_ownerStaffID, 'tickettypeid' => $_ticketTypeID,
            'ticketstatusid' => $_ticketStatusID, 'priorityid' => $_ticketPriorityID), 'INSERT');
        $_macroReplyID = $_SWIFT->Database->Insert_ID();

        if (!$_macroReplyID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        SWIFT_MacroReplyData::Create($_macroReplyID, $_contents, serialize($_tagList));

        return $_macroReplyID;
    }

    /**
     * Update the Macro Reply Record
     *
     * @author Varun Shoor
     * @param int $_macroCategoryID The Macro Category ID
     * @param string $_subject The Subject
     * @param string $_contents The Macro Contents
     * @param array $_tagList (OPTIONAL) The Tag List
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ownerStaffID (OPTIONAL) The Owner Staff ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketPriorityID (OPTIONAL) The Ticket Priority ID
     * @param SWIFT_Staff $_SWIFT_StaffObject (OPTIONAL) The SWIFT_Staff Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_macroCategoryID, $_subject, $_contents, $_tagList = array(), $_departmentID = 0, $_ownerStaffID = 0, $_ticketTypeID = 0, $_ticketStatusID = 0,
            $_ticketPriorityID = 0, SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_subject) || empty($_contents))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffID = false;
        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded())
        {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
        }

        $this->UpdatePool('macrocategoryid', $_macroCategoryID);
        $this->UpdatePool('staffid', $_staffID);
        $this->UpdatePool('subject', $_subject);
        $this->UpdatePool('departmentid', $_departmentID);
        $this->UpdatePool('ownerstaffid', $_ownerStaffID);
        $this->UpdatePool('tickettypeid', $_ticketTypeID);
        $this->UpdatePool('ticketstatusid', $_ticketStatusID);
        $this->UpdatePool('priorityid', $_ticketPriorityID);
        $this->ProcessUpdatePool();

        $_SWIFT_MacroReplyDataObject = SWIFT_MacroReplyData::RetrieveOnReply($this->GetMacroReplyID());
        if (!($_SWIFT_MacroReplyDataObject instanceof SWIFT_MacroReplyData && $_SWIFT_MacroReplyDataObject->GetIsClassLoaded()))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_MacroReplyDataObject->Update($_contents, serialize($_tagList));

        return true;
    }

    /**
     * Update the usage of this macro reply
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateUsage()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('totalhits', ($this->GetProperty('totalhits')+1));
        $this->UpdatePool('lastusage', DATENOW);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Macro Reply record
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

        self::DeleteList(array($this->GetMacroReplyID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Macro Replies
     *
     * @author Varun Shoor
     * @param array $_macroReplyIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_macroReplyIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_macroReplyIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "macroreplies WHERE macroreplyid IN (" . BuildIN($_macroReplyIDList) . ")");

        SWIFT_MacroReplyData::DeleteOnReply($_macroReplyIDList);

        return true;
    }

    /**
     * Delete a list of macro replies based on the category id
     *
     * @author Varun Shoor
     * @param array $_macroCategoryIDList The Macro Category ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnCategory($_macroCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_macroCategoryIDList))
        {
            return false;
        }

        $_macroReplyIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "macroreplies WHERE macrocategoryid IN (" . BuildIN($_macroCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_macroReplyIDList[] = (int) ($_SWIFT->Database->Record['macroreplyid']);
        }

        if (!count($_macroReplyIDList))
        {
            return false;
        }

        self::DeleteList($_macroReplyIDList);

        return true;
    }

    /**
     * Retrieve all the macro replies
     *
     * @author Varun Shoor
     * @param bool $_loadMacroReplyData (OPTIONAL) Whether to load macro reply data, true by default.
     * @return array The Reply Container Array
     * @throws SWIFT_Exception
     */
    public static function RetrieveMacroReplies($_loadMacroReplyData = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_macroRepliesContainer = $_replyParentMap = array();

        if ($_loadMacroReplyData)
        {
            $_SWIFT->Database->Query("SELECT macroreplies.*, macroreplydata.contents AS contents FROM " . TABLE_PREFIX . "macroreplies AS macroreplies
                LEFT JOIN " . TABLE_PREFIX . "macroreplydata AS macroreplydata ON (macroreplies.macroreplyid = macroreplydata.macroreplyid)
                ORDER BY macroreplies.subject ASC");
        } else {
            $_SWIFT->Database->Query("SELECT macroreplies.* FROM " . TABLE_PREFIX . "macroreplies AS macroreplies ORDER BY macroreplies.subject ASC");
        }
        while ($_SWIFT->Database->NextRecord())
        {
            $_macroRepliesContainer[$_SWIFT->Database->Record['macroreplyid']] = $_SWIFT->Database->Record;

            if (!isset($_replyParentMap[$_SWIFT->Database->Record['macrocategoryid']]))
            {
                $_replyParentMap[$_SWIFT->Database->Record['macrocategoryid']] = array();
            }

            $_replyParentMap[$_SWIFT->Database->Record['macrocategoryid']][] = $_SWIFT->Database->Record;
        }

        return array('_macroRepliesContainer' => $_macroRepliesContainer, '_replyParentMap' => $_replyParentMap);
    }
}
?>
