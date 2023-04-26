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

namespace LiveChat\Library\Chat;

use LiveChat\Library\Chat\SWIFT_Chat_Exception;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Class that handles the transfer management of a chat
 *
 * @author Varun Shoor
 */
class SWIFT_ChatTransferManager extends SWIFT_Library
{
    private $Chat = false;

    // Core Constants
    const TYPE_STAFF = 'staff';
    const TYPE_STAFFGROUP = 'staffgroup';
    const TYPE_DEPARTMENT = 'department';
    const TYPE_SKILL = 'skill';
    const TYPE_REJECT = 'reject';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @throws SWIFT_Chat_Exception If the Chat Object Data could not be set
     */
    public function __construct(SWIFT_Chat $_SWIFT_ChatObject)
    {
        parent::__construct();

        if (!$this->SetChatObject($_SWIFT_ChatObject)) {
            throw new SWIFT_Chat_Exception(SWIFT_CREATEFAILED);
        }
    }

    /**
     * Set the Chat Object
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetChatObject(SWIFT_Chat $_SWIFT_ChatObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        $this->Chat = $_SWIFT_ChatObject;

        return true;
    }

    /**
     * Retrieve the currently set chat object
     *
     * @author Varun Shoor
     * @return mixed "Chat" (SWIFT_Chat Object) on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetChatObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->Chat;
    }

    /**
     * Reject the Transfer
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID Rejecting the transfer
     * @param int $_rejectionReason The Rejection Reason
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RejectTransfer($_staffID, $_rejectionReason)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_ChatObject = $this->GetChatObject();

        $this->Chat->UpdateTransfer($_SWIFT_ChatObject->GetProperty('transferfromid'), $_SWIFT_ChatObject->GetProperty('transfertoid'), SWIFT_Chat::TRANSFER_REJECTED, DATENOW);

        return true;
    }

    /**
     * Transfers the chat to a given staff member
     *
     * @author Varun Shoor
     * @param int $_fromStaffID The Staff ID that initiated the transfer
     * @param int $_toStaffID The Staff ID to transfer the chat to
     * @return mixed "_toStaffID" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function TransferToStaff($_fromStaffID, $_toStaffID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        } else if ($_fromStaffID == $_toStaffID || empty($_fromStaffID) || empty($_toStaffID)) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        $this->Chat->UpdateTransfer($_fromStaffID, $_toStaffID, SWIFT_Chat::TRANSFER_PENDING, DATENOW);

        return $_toStaffID;
    }

    /**
     * Transfer a chat to a staff group.. find out the available staff in a group and transfer it to him
     *
     * @author Varun Shoor
     * @param int $_fromStaffID The Staff ID that initiated the transfer
     * @param int $_staffGroupID The Group to transfer to
     * @return int "_roundRobinStaffID" The Staff to which chat has been transferred on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function TransferToStaffGroup($_fromStaffID, $_staffGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_fromStaffID) || empty($_staffGroupID)) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        $_staffCache = $this->Cache->Get('staffcache');

        // Retrieve all the staff ids under this group
        $_staffIDList = array();
        foreach ($_staffCache as $_key => $_val) {
            if ($_val['staffgroupid'] == $_staffGroupID) {
                $_staffIDList[] = (int)($_val['staffid']);
            }
        }

        // No staff found under this group?
        if (!count($_staffIDList)) {
            return 0;
        }

        $_roundRobinStaffID = SWIFT_Chat::GetRoundRobinStaff(false, array($_fromStaffID), $_staffIDList);

        if (!$_roundRobinStaffID || !isset($_staffCache[$_roundRobinStaffID])) {
            return 0;
        }

        $this->Chat->UpdateTransfer($_fromStaffID, $_roundRobinStaffID, SWIFT_Chat::TRANSFER_PENDING, DATENOW);

        return $_roundRobinStaffID;
    }

    /**
     * Transfer a chat to a department - find and transfer a chat to an operator assigned to a department
     *
     * @author Varun Shoor
     * @param int $_fromStaffID The Staff ID that initiated the transfer
     * @param int $_departmentID The Department ID to transfer to
     * @return int "_roundRobinStaffID" The Staff to which chat has been transferred on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function TransferToDepartment($_fromStaffID, $_departmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_fromStaffID) || empty($_departmentID)) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_roundRobinStaffID = SWIFT_Chat::GetRoundRobinStaff($_departmentID, array($_fromStaffID));

        if (!$_roundRobinStaffID || !isset($_staffCache[$_roundRobinStaffID])) {
            return 0;
        }

        $this->Chat->UpdateTransfer($_fromStaffID, $_roundRobinStaffID, SWIFT_Chat::TRANSFER_PENDING, DATENOW);

        return $_roundRobinStaffID;
    }

    /**
     * Transfer a chat to a skill.. find out a staff online with the available skill and transfer the chat to him
     *
     * @author Varun Shoor
     * @param int $_fromStaffID The Staff ID that initiated the transfer
     * @param int $_chatSkillID The Skill to transfer to
     * @return int "_roundRobinStaffID" The Staff to which chat has been transferred on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function TransferToSkill($_fromStaffID, $_chatSkillID)
    {
        $_staffCache = $this->Cache->Get('staffcache');

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_fromStaffID) || empty($_chatSkillID)) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        // Retrieve all the links for this skill
        $_staffIDList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskilllinks WHERE chatskillid = '" . ($_chatSkillID) . "'");
        while ($this->Database->NextRecord()) {
            if (!in_array($this->Database->Record['staffid'], $_staffIDList)) {
                $_staffIDList[] = $this->Database->Record['staffid'];
            }
        }

        // No staff found for this skill?
        if (!count($_staffIDList)) {
            return 0;
        }

        $_roundRobinStaffID = SWIFT_Chat::GetRoundRobinStaff(false, array($_fromStaffID), $_staffIDList);

        if (!$_roundRobinStaffID || !isset($_staffCache[$_roundRobinStaffID])) {
            return 0;
        }

        $this->Chat->UpdateTransfer($_fromStaffID, $_roundRobinStaffID, SWIFT_Chat::TRANSFER_PENDING, DATENOW);

        $this->Chat->UpdateChatSkill($_chatSkillID);

        return $_roundRobinStaffID;
    }
}
