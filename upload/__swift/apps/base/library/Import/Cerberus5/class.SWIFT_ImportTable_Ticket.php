<?php

namespace Base\Library\Import\Cerberus5;

use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Tickets\Models\Note\SWIFT_TicketNoteManager;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * Import Table: Ticket
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Ticket extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Ticket');

        if (!$this->TableExists('ticket')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadLibrary('Ticket:TicketManager', APP_TICKETS);

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
        SWIFT_Loader::LoadModel('Note:TicketNoteManager', APP_TICKETS);
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

//        if ($this->GetOffset() == 0)
//        {
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "tickets");
//        }

        $_count = 0;

        $_ticketContainer = $_ticketIDList = $_oldUserIDList = array();

        $_staffCache = $this->Cache->Get('staffcache');

        $this->DatabaseImport->QueryLimit("SELECT t.*, r.address_id AS userid FROM ticket AS t, requester AS r WHERE t.id = r.ticket_id ORDER BY t.id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_ticketContainer[$this->DatabaseImport->Record['id']] = $this->DatabaseImport->Record;
            $_ticketContainer[$this->DatabaseImport->Record['id']]['staffid'] = 0;
            $_ticketIDList[] = $this->DatabaseImport->Record['id'];

            if ($this->DatabaseImport->Record['userid'] != '0') {
                $_oldUserIDList[] = $this->DatabaseImport->Record['userid'];
            }
        }

//        GETTING THE ASSIGNED WORKER
        $this->DatabaseImport->Query("SELECT `from_context_id` AS staffid, `to_context_id` AS ticketid FROM context_link WHERE `to_context` LIKE 'cerberusweb.contexts.ticket' AND to_context_id IN (" . BuildIN($_ticketIDList) . ");");
        while ($this->DatabaseImport->NextRecord()) {
            $_ticketContainer[$this->DatabaseImport->Record['ticketid']]['staffid'] = $this->DatabaseImport->Record['staffid'];
        }

        $_ticketTypeContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "tickettypes WHERE ismaster = '1'");
        $_ticketTypeID = $_ticketTypeContainer['tickettypeid'];

        $_newUserIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_oldUserIDList);

        foreach ($_ticketContainer as $_ticketID => $_ticket) {

            if ($_ticket['is_closed'] == 1) {
                $_newTicketStatusID = 3;
            } elseif ($_ticket['is_waiting'] == 1) {
                $_newTicketStatusID = 2;
            } else {
                $_newTicketStatusID = 1;
            }

            $_newTicketPriorityID = 1;

            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $_ticket['team_id']);
            if (!$_newDepartmentID) {
                $this->GetImportManager()->AddToLog('Import for Ticket ID #: ' . (int)($_ticket['id']) . ' failed due to non existant ticket department id: ' . htmlspecialchars($_ticket['team_id']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);
                continue;
            }

            $_newEmailQueueID = 1;

            $_newUserID = 0;
            if (isset($_newUserIDList[$_ticket['userid']])) {
                $_newUserID = $_newUserIDList[$_ticket['userid']];
            }

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticket['staffid']);
            $_newOwnerStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticket['staffid']);

            $_assignStatus = false;
            if (!empty($_newOwnerStaffID)) {
                $_assignStatus = true;
            }

            $_newSLAPlanID = 1;
            $_newTicketSLAPlanID = 0;

            $_newEditedByStaffID = 0;

            $_newTemplateGroupID = 0;

            $_newEscalationRuleID = 0;

            $_creationMode = SWIFT_Ticket::CREATIONMODE_EMAIL;

            $_ticketType = SWIFT_Ticket::TYPE_DEFAULT;
//            if ($_ticket['isphonecall'] == '1')
//            {
//                $_ticketType = SWIFT_Ticket::TYPE_PHONE;
//            }

            $_ticketmaskid = GenerateUniqueMask();


            $_fullname = $_email = $_replyto = '';
            if (!empty($_newStaffID)) {
                if (isset($_staffCache[$_newStaffID])) {
                    $_fullname = $_staffCache[$_newStaffID]['fullname'];
                    $_email = $_staffCache[$_newStaffID]['email'];
                    $_replyto = $_staffCache[$_newStaffID]['email'];
                } else {
                    $_SWIFT_StaffObject_Worker = new SWIFT_Staff(new SWIFT_DataID($_newStaffID));
                    if (!$_SWIFT_StaffObject_Worker instanceof SWIFT_Staff || !$_SWIFT_StaffObject_Worker->GetIsClassLoaded()) {
                        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                    }

                    $_fullname = $_SWIFT_StaffObject_Worker->GetProperty('fullname');
                    $_email = $_SWIFT_StaffObject_Worker->GetProperty('email');
                    $_replyto = $_SWIFT_StaffObject_Worker->GetProperty('email');
                }

                $_newCreator = SWIFT_Ticket::CREATOR_STAFF;

            } else {
                $_newStaffID = 0;
                $_newCreator = SWIFT_Ticket::CREATOR_CLIENT;

                try {
                    $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_newUserID));

                    $_fullname = $_SWIFT_UserObject->GetProperty('fullname');
                    $_emailList = $_SWIFT_UserObject->GetEmailList();

                    $_email = $_emailList[0];
                    $_replyto = $_emailList[0];
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                }
            }

            $this->GetImportManager()->AddToLog('Importing Ticket ID: ' . htmlspecialchars($_ticket['id']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'tickets',
                array('ticketmaskid' => $_ticketmaskid, 'departmentid' => $_newDepartmentID, 'ticketstatusid' => $_newTicketStatusID,
                    'priorityid' => $_newTicketPriorityID, 'emailqueueid' => $_newEmailQueueID, 'userid' => $_newUserID, 'staffid' => $_newStaffID,
                    'ownerstaffid' => $_newOwnerStaffID, 'assignstatus' => (int)($_assignStatus), 'fullname' => $_fullname, 'email' => $_email,
                    'lastreplier' => '',
                    'replyto' => $_replyto, 'subject' => $_ticket['subject'], 'dateline' => $_ticket['created_date'], 'lastactivity' => $_ticket['updated_date'],
                    'laststaffreplytime' => '0', 'lastuserreplytime' => '0', 'slaplanid' => $_newSLAPlanID, 'ticketslaplanid' => $_newTicketSLAPlanID,
                    'duetime' => '0', 'totalreplies' => '0', 'ipaddress' => '0', 'flagtype' => '0',
                    'hasnotes' => '0', 'hasattachments' => '0', 'isemailed' => '0', 'edited' => '0',
                    'editedbystaffid' => '0', 'editeddateline' => '0', 'creator' => $_newCreator,
                    'lastpostid' => $_ticket['last_message_id'], 'firstpostid' => $_ticket['first_message_id'], 'tgroupid' => $_newTemplateGroupID,
                    'escalationruleid' => $_newEscalationRuleID, 'hasdraft' => '0', 'hasbilling' => '0', 'isphonecall' => '0',
                    'isescalated' => '0', 'phoneno' => '0', 'autoclosetimeline' => '0', 'escalatedtime' => '0',
                    'followupcount' => '0', 'hasfollowup' => '0', 'hasratings' => '0', 'tickethash' => BuildHash(),
                    'islinked' => '0', 'tickettype' => $_ticketType, 'tickettypeid' => $_ticketTypeID, 'creationmode' => $_creationMode
                ), 'INSERT');

            $dwk_ticketID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('ticketid', $_ticket['id'], $dwk_ticketID);

            $_countReplies = 0;

//            NOW IMPORT THE TICKET POSTS
            $_ticketPostContainer = [];
            $_oldUserIDListPost = [];
            $this->DatabaseImport->Query("SELECT m . * , smc.data AS message FROM `message` AS m, `storage_message_content` AS smc WHERE m.storage_key = smc.id AND m.ticket_id = " . (int)($_ticket['id']) . " ORDER BY m.id ASC", 2);
            while ($this->DatabaseImport->NextRecord(2)) {
                $_countReplies++;

                $_ticketPostContainer[$this->DatabaseImport->Record2['id']] = $this->DatabaseImport->Record2;

                if ($this->DatabaseImport->Record2['address_id'] != '0') {
                    $_oldUserIDListPost[] = $this->DatabaseImport->Record2['address_id'];
                }
            }

            $_newUserIDListPost = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_oldUserIDListPost);

            $_lastreplier = '';
            $control = true;

            foreach ($_ticketPostContainer as $_ticketPostID => $_ticketPost) {

                $_newUserID = 0;
                if (isset($_newUserIDListPost[$_ticketPost['address_id']])) {
                    $_newUserID = $_newUserIDListPost[$_ticketPost['address_id']];
                }

                $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticketPost['worker_id']);

                $_newEditedByStaffID = 0;

                $_isThirdParty = false;

                //define("POST_STAFF", 1);
                //define("POST_CLIENT", 2);
                //define("POST_THIRDPARTY", 3);
                //define("POST_RECIPIENT", 4);
                //define("POST_USER", 2);
                //define("POST_FORWARD", 5);

                if ($_ticketPost['is_outgoing'] == 1) {
                    if (isset($_staffCache[$_newStaffID])) {
                        $_fullname = $_staffCache[$_newStaffID]['fullname'];
                        $_email = $_staffCache[$_newStaffID]['email'];
                        $_replyto = $_staffCache[$_newStaffID]['email'];
                    } else {
                        $_SWIFT_StaffObject_Worker = new SWIFT_Staff(new SWIFT_DataID($_newStaffID));
                        if (!$_SWIFT_StaffObject_Worker instanceof SWIFT_Staff || !$_SWIFT_StaffObject_Worker->GetIsClassLoaded()) {
                            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                        }

                        $_fullname = $_SWIFT_StaffObject_Worker->GetProperty('fullname');
                        $_email = $_SWIFT_StaffObject_Worker->GetProperty('email');
                        $_replyto = $_SWIFT_StaffObject_Worker->GetProperty('email');
                    }

                    $_newCreator = SWIFT_TicketPost::CREATOR_STAFF;

                } else {
                    $_newStaffID = 0;
                    $_newCreator = SWIFT_TicketPost::CREATOR_CLIENT;

                    try {
                        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_newUserID));

                        $_fullname = $_SWIFT_UserObject->GetProperty('fullname');
                        $_emailList = $_SWIFT_UserObject->GetEmailList();

                        $_email = $_emailList[0];
                        $_replyto = $_emailList[0];
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    }
                }

                $this->GetImportManager()->AddToLog('Importing Ticket Post ID: ' . htmlspecialchars($_ticketPostID), SWIFT_ImportManager::LOG_SUCCESS);

                $_newTicketID = $this->ImportManager->GetImportRegistry()->GetKey('ticketid', $_ticketPost['ticket_id']);

                $this->Database->AutoExecute(TABLE_PREFIX . 'ticketposts',
                    array('ticketid' => $_newTicketID, 'dateline' => $_ticketPost['created_date'], 'userid' => $_newUserID,
                        'fullname' => $_fullname, 'email' => $_email, 'emailto' => $_replyto,
                        'ipaddress' => '0', 'hasattachments' => '0', 'edited' => '0',
                        'editedbystaffid' => $_newEditedByStaffID, 'editeddateline' => '0', 'creator' => $_newCreator, 'isthirdparty' => $_isThirdParty,
                        'ishtml' => '0', 'isemailed' => '0', 'staffid' => $_newStaffID, 'contents' => $_ticketPost['message'],
                        'contenthash' => sha1($_ticketPost['message']), 'subjecthash' => sha1('')
                    ), 'INSERT');

                $dwk_ticketPostID = $this->Database->InsertID();

                $this->ImportManager->GetImportRegistry()->UpdateKey('ticketpostid', $_ticketPost['id'], $dwk_ticketPostID);


                $_lastreplier = $_fullname;
            }

            unset($_ticketPostContainer);
            unset($_newUserIDListPost);

//            NOW IMPORT THE TICKET NOTES
            $_tickethasnotes = 0;
            $this->DatabaseImport->Query("SELECT * FROM comment WHERE `context` LIKE 'cerberusweb.contexts.ticket' AND context_id = " . (int)($_ticket['id']) . " ORDER BY id ASC", 3);
            while ($this->DatabaseImport->NextRecord(3)) {

                $this->GetImportManager()->AddToLog('Importing Ticket Note ID: ' . htmlspecialchars($this->DatabaseImport->Record3['id']), SWIFT_ImportManager::LOG_SUCCESS);

                $_newByStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record3['address_id']);
                $_staffFullName = $this->Language->Get('na');
                if (isset($_staffCache[$_newByStaffID])) {
                    $_staffFullName = $_staffCache[$_newByStaffID]['fullname'];
                }

                $_newForStaffID = 0;

                $_newUserID = 0;

                $_newTicketID = $this->ImportManager->GetImportRegistry()->GetKey('ticketid', $this->DatabaseImport->Record3['context_id']);

                $this->Database->AutoExecute(TABLE_PREFIX . 'ticketnotes',
                    array('linktypeid' => $_newTicketID, 'linktype' => SWIFT_TicketNoteManager::LINKTYPE_TICKET, 'forstaffid' => $_newForStaffID,
                        'staffid' => $_newByStaffID, 'dateline' => $this->DatabaseImport->Record3['created'], 'isedited' => '0', 'staffname' => $_staffFullName, 'editedstaffid' => '0',
                        'editedstaffname' => '0', 'editedtimeline' => '0', 'notecolor' => '1', 'note' => $this->DatabaseImport->Record3['comment']), 'INSERT');

                $_tickethasnotes = 1;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('totalreplies' => $_countReplies, 'lastreplier' => $_lastreplier, 'hasnotes' => $_tickethasnotes), 'UPDATE', "ticketid = '" . $dwk_ticketID . "'");
        }

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM ticket");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 1000;
    }
}

?>
