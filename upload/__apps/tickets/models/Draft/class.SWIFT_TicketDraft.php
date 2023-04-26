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

namespace Tickets\Models\Draft;

use SWIFT;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Draft Management Class
 *
 * @method int GetTicketDraftID()
 * @author Varun Shoor
 */
class SWIFT_TicketDraft extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketdrafts';
    const PRIMARY_KEY        =    'ticketdraftid';

    const TABLE_STRUCTURE    =    "ticketdraftid I PRIMARY AUTO NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,
                                isedited I2 DEFAULT '0' NOTNULL,
                                editedstaffname C(255) DEFAULT '' NOTNULL,
                                editedbystaffid I DEFAULT '0' NOTNULL,
                                editeddateline I DEFAULT '0' NOTNULL,
                                contents X2";

    const INDEX_1            =    'ticketid, staffid';

    /**
     * Retrieve the relevant ticket draft
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_Staff|null $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @return mixed SWIFT_TicketDraft or False
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_TicketDraftObject = false;

        $_ticketDraftContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketdrafts WHERE ticketid = '" .
                (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
        if (isset($_ticketDraftContainer['ticketdraftid']) && !empty($_ticketDraftContainer['ticketdraftid']))
        {
            $_SWIFT_TicketDraftObject = new SWIFT_TicketDraft(new SWIFT_DataStore($_ticketDraftContainer));
        }

        return $_SWIFT_TicketDraftObject;
    }

    /**
     * Create a new draft entry for given ticket if one doesnt exist
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @param string $_draftContents The Draft Contents
     * @return SWIFT_TicketDraft The Ticket Draft Object
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CreateIfNotExists(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_draftContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_TicketDraftObject = false;

        $_draftContents = $_SWIFT->Emoji->encode($_draftContents);

        $_ticketDraftContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketdrafts WHERE ticketid = '" .
                (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
        if (isset($_ticketDraftContainer['ticketdraftid']) && !empty($_ticketDraftContainer['ticketdraftid']))
        {
            $_SWIFT_TicketDraftObject = new SWIFT_TicketDraft(new SWIFT_DataStore($_ticketDraftContainer));
        } else {
            $_ticketDraftID = self::Create($_SWIFT_TicketObject, $_SWIFT_StaffObject->GetStaffID(), $_SWIFT_StaffObject->GetProperty('fullname'),
                    $_draftContents);
            $_SWIFT_TicketDraftObject = new SWIFT_TicketDraft(new SWIFT_DataID($_ticketDraftID));

            $_SWIFT_TicketDraftObject->Update($_SWIFT_StaffObject, $_draftContents);
        }

        if (!$_SWIFT_TicketDraftObject instanceof SWIFT_TicketDraft || !$_SWIFT_TicketDraftObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $_SWIFT_TicketDraftObject;
    }

    /**
     * Create a new Ticket Draft
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Name
     * @param string $_draftContents The Draft Contents
     * @return mixed "_ticketDraftID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, $_staffID, $_staffName, $_draftContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || empty($_staffID) || empty($_staffName))
        {
            throw new SWIFT_Draft_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketdrafts', array('ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()),
            'dateline' => DATENOW, 'staffid' =>  ($_staffID), 'staffname' => $_staffName, 'contents' => $_draftContents), 'INSERT');
        $_ticketDraftID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketDraftID)
        {
            throw new SWIFT_Draft_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketObject->MarkHasDraft();

        return $_ticketDraftID;
    }

    /**
     * Update the Ticket Draft Record
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject The Staff Object Pointer
     * @param string $_draftContents The Draft Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Draft_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update(SWIFT_Staff $_SWIFT_StaffObject, $_draftContents)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Draft_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('isedited', '1');
        $this->UpdatePool('editedstaffname', $_SWIFT_StaffObject->GetProperty('fullname'));
        $this->UpdatePool('editedbystaffid', (int) ($_SWIFT_StaffObject->GetProperty('staffid')));
        $this->UpdatePool('editeddateline', DATENOW);
        $this->UpdatePool('contents', $_draftContents);

        return true;
    }

    /**
     * Insert if Draft doesnt exist, otherwise update
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @param string $_draftContents The Draft Contents
     * @return mixed "_ticketDraftID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Replace(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_draftContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
                !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded())
        {
            throw new SWIFT_Draft_Exception(SWIFT_INVALIDDATA);
        }

        // Try to see if we can locate a draft for this ticket..
        $_ticketDraftContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketdrafts WHERE ticketid = '" .
                (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
        if (isset($_ticketDraftContainer['ticketdraftid']) && !empty($_ticketDraftContainer['ticketdraftid']))
        {
            $_SWIFT_TicketDraftObject = new self(new SWIFT_DataStore($_ticketDraftContainer));
            if ($_SWIFT_TicketDraftObject instanceof self && $_SWIFT_TicketDraftObject->GetIsClassLoaded())
            {
//                $_SWIFT_TicketDraftObject->Update($_SWIFT_StaffObject->GetStaffID(), $_SWIFT_StaffObject->GetProperty('fullname'), $_draftContents);
                $_SWIFT_TicketDraftObject->Update($_SWIFT_StaffObject, $_draftContents);

                return $_SWIFT_TicketDraftObject->GetTicketDraftID();
            }
        }

        $_ticketDraftID = SWIFT_TicketDraft::Create($_SWIFT_TicketObject, $_SWIFT_StaffObject->GetStaffID(),
                $_SWIFT_StaffObject->GetProperty('fullname'), $_draftContents);

        return $_ticketDraftID;
    }

    /**
     * Delete drafts on list of ticket ids
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_ticketDraftIDList = array();
        $_SWIFT->Database->Query("SELECT ticketdraftid FROM " . TABLE_PREFIX . "ticketdrafts WHERE ticketid IN (" .
                BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketDraftIDList[] = (int) ($_SWIFT->Database->Record['ticketdraftid']);
        }

        if (!count($_ticketDraftIDList))
        {
            return false;
        }

        self::DeleteList($_ticketDraftIDList);

        return true;
    }

    /**
     * Update the staff name. This is used to rebuild the properties and keep the names in sync.
     *
     * @author Varun Shoor
     * @param int $_ticketDraftID
     * @param string $_staffName
     * @param string $_editedStaffName
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateStaffName($_ticketDraftID, $_staffName, $_editedStaffName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketdrafts', array('staffname' => $_staffName, 'editedstaffname' => $_editedStaffName), 'UPDATE', "ticketdraftid = '" .  ($_ticketDraftID) . "'");

        return true;
    }

    /**
     * Update the global property on all ticket drafts, used to update stuff like departmentname etc.
     *
     * @author Varun Shoor
     * @param string $_updateFieldName
     * @param string $_updateFieldValue
     * @param string $_whereFieldName
     * @param string $_whereFieldValue
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateGlobalProperty($_updateFieldName, $_updateFieldValue, $_whereFieldName, $_whereFieldValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_updateFieldName = $_SWIFT->Database->Escape($_updateFieldName);
        $_whereFieldName = $_SWIFT->Database->Escape($_whereFieldName);
        $_whereFieldValue = (int) ($_whereFieldValue); // Expected to be always int

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketdrafts', array($_updateFieldName => $_updateFieldValue), 'UPDATE', $_whereFieldName . " = '" . $_SWIFT->Database->Escape($_whereFieldValue) . "'");

        return true;
    }
}
?>
