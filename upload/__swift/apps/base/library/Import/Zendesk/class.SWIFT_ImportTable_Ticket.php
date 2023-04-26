<?php

namespace Base\Library\Import\Zendesk;

use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use DOMDocument;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * Import Zendesk XML: Ticlkets
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Ticket extends SWIFT_ImportTable
{
    var $dwk_zendesk_url = false;
    var $dwk_tickets_array = array();

    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager_Zendesk $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Ticket');

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);

        SWIFT_Loader::LoadLibrary('Ticket:TicketManager', APP_TICKETS);

        $this->dwk_zendesk_url = $_SWIFT_ImportManagerObject->dwk_getZendeskUrl();

        $this->dwk_tickets_array = $this->dwk_getZendeskInformation();
    }

    /**
     * Import the data based on offset in the zendesk xml
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
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketposts");
//        }

        $_count = 0;

        $dwk_control = $this->GetOffset() + $this->GetItemsPerPass();

        if ($dwk_control > $this->GetTotal()) {
            $dwk_control = $this->GetTotal();
        }

        $_ticketContainer = $_ticketIDList = $_oldUserIDList = array();

        for ($index = $this->GetOffset(); $index < $dwk_control; $index++) {
            $_count++;

            $_ticketContainer[$this->dwk_tickets_array[$index]['ticketid']] = $this->dwk_tickets_array[$index];
            $_ticketIDList[] = $this->dwk_tickets_array[$index]['ticketid'];

            if ($this->dwk_tickets_array[$index]['userid'] != '0') {
                $_oldUserIDList[] = $this->dwk_tickets_array[$index]['userid'];
            }
        }

        $_ticketTypeContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "tickettypes WHERE ismaster = '1'");
        $_ticketTypeID = $_ticketTypeContainer['tickettypeid'];

        $_newUserIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_oldUserIDList);

        foreach ($_ticketContainer as $_ticketID => $_ticket) {
//            $_newTicketStatusID = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $_ticket['ticketstatusid']);
//            if (!$_newTicketStatusID)
//            {
//                $this->GetImportManager()->AddToLog('Import for Ticket ID #: ' . (int) ($_ticket['ticketid']) . ' failed due to non existant ticket status id: ' . htmlspecialchars($_ticket['ticketstatusid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);
//                continue;
//            }

//            Zendesk Ticket Status: New = 0
//            Zendesk Ticket Status: Open = 1
//            Zendesk Ticket Status: Pending = 2
//            Zendesk Ticket Status: Solved = 3
//            Zendesk Ticket Status: Closed = 4
            $_newTicketStatusID = null;
            if ($_ticket['ticketstatusid'] == 0 || $_ticket['ticketstatusid'] == 1) {
                $_newTicketStatusID = 1;
            } elseif ($_ticket['ticketstatusid'] == 2) {
                $_newTicketStatusID = 2;
            } elseif ($_ticket['ticketstatusid'] == 3 || $_ticket['ticketstatusid'] == 4) {
                $_newTicketStatusID = 3;
            }

//            $_newTicketPriorityID = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $_ticket['priorityid']);
//            if (!$_newTicketPriorityID)
//            {
//                $this->GetImportManager()->AddToLog('Import for Ticket ID #: ' . (int) ($_ticket['ticketid']) . ' failed due to non existant ticket priority id: ' . htmlspecialchars($_ticket['priorityid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);
//                continue;
//            }

//            Zendesk Ticket Priorities: (No priority set) = 0
//            Zendesk Ticket Priorities: Low = 1
//            Zendesk Ticket Priorities: Normal = 2
//            Zendesk Ticket Priorities: High = 3
//            Zendesk Ticket Priorities: Urgent = 4
            if ($_ticket['priorityid'] == 0) {
                $_newTicketPriorityID = 1;
            } else {
                $_newTicketPriorityID = $_ticket['priorityid'];
            }

            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $_ticket['departmentid']);
            if (!$_newDepartmentID) {
                $this->GetImportManager()->AddToLog('Import for Ticket ID #: ' . (int)($_ticket['ticketid']) . ' failed due to non existant ticket department id: ' . htmlspecialchars($_ticket['departmentid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);
                continue;
            }

            $_newEmailQueueID = 0;

//            This is necsary because its not posible to know if a user or a staff in Zendesk created the ticket
            $_newUserID = 0;

            $_fullname = $_email = $_replyto = '';
            if (isset($_newUserIDList[$_ticket['userid']])) {
                $_newUserID = $_newUserIDList[$_ticket['userid']];
                $_newStaffID = 0;
                $_newCreator = SWIFT_Ticket::CREATOR_CLIENT;

                try {
                    $_SWIFT_UserObject = new SWIFT_User($_newUserID);

                    $_fullname = $_SWIFT_UserObject->GetProperty('fullname');
                    $_emailList = $_SWIFT_UserObject->GetEmailList();

                    $_email = $_emailList[0];
                    $_replyto = $_emailList[0];
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                }
            } else {
                $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticket['userid']);

                if (!$_newStaffID) {
                    $this->GetImportManager()->AddToLog('Import for Ticket ID #: ' . (int)($_ticket['ticketid']) . ' failed due to non existant creator', SWIFT_ImportManager::LOG_WARNING);
                    continue;
                }

                $_newCreator = SWIFT_Ticket::CREATOR_STAFF;

                $_SWIFT_StaffObject_Worker = new SWIFT_Staff(new SWIFT_DataID($_newStaffID));
                if (!$_SWIFT_StaffObject_Worker instanceof SWIFT_Staff || !$_SWIFT_StaffObject_Worker->GetIsClassLoaded()) {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }
                $_fullname = $_SWIFT_StaffObject_Worker->GetProperty('fullname');
                $_email = $_SWIFT_StaffObject_Worker->GetProperty('email');
                $_replyto = $_SWIFT_StaffObject_Worker->GetProperty('email');
            }

            $_newOwnerStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticket['ownerstaffid']);

            $_assignStatus = false;
            if (!empty($_newOwnerStaffID)) {
                $_assignStatus = true;
            }

            $_newSLAPlanID = 0;
            $_newTicketSLAPlanID = 0;

            $_newEditedByStaffID = 0;

            $_newTemplateGroupID = 0;

            $_newEscalationRuleID = 0;

            $_creationMode = SWIFT_Ticket::CREATIONMODE_EMAIL;

            $_ticketType = SWIFT_Ticket::TYPE_DEFAULT;

            $_ticketmaskid = GenerateUniqueMask();

            $this->GetImportManager()->AddToLog('Importing Ticket ID: ' . htmlspecialchars($_ticket['ticketid']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'tickets',
                array('ticketmaskid' => $_ticketmaskid, 'departmentid' => $_newDepartmentID, 'ticketstatusid' => $_newTicketStatusID,
                    'priorityid' => $_newTicketPriorityID, 'emailqueueid' => $_newEmailQueueID, 'userid' => $_newUserID, 'staffid' => $_newStaffID,
                    'ownerstaffid' => $_newOwnerStaffID, 'assignstatus' => (int)($_assignStatus), 'fullname' => $_fullname, 'email' => $_email,
                    'lastreplier' => $_ticket['lastreplier'],
                    'replyto' => $_replyto, 'subject' => $_ticket['subject'], 'dateline' => $_ticket['dateline'], 'lastactivity' => $_ticket['lastactivity'],
                    'laststaffreplytime' => '0', 'lastuserreplytime' => '0', 'slaplanid' => $_newSLAPlanID, 'ticketslaplanid' => $_newTicketSLAPlanID,
                    'duetime' => '0', 'totalreplies' => $_ticket['totalreplies'], 'ipaddress' => '0', 'flagtype' => '0',
                    'hasnotes' => '0', 'hasattachments' => $_ticket['hasattachments'], 'isemailed' => '0', 'edited' => '0',
                    'editedbystaffid' => $_newEditedByStaffID, 'editeddateline' => '0', 'creator' => $_newCreator,
                    'timeworked' => '0', 'timebilled' => '0', 'dateicon' => '0',
                    'lastpostid' => '0', 'firstpostid' => '0', 'tgroupid' => $_newTemplateGroupID,
                    'escalationruleid' => $_newEscalationRuleID, 'hasdraft' => '0', 'hasbilling' => '0', 'isphonecall' => '0',
                    'isescalated' => '0', 'autoclosetimeline' => '0', 'escalatedtime' => '0',
                    'followupcount' => '0', 'hasfollowup' => '0', 'hasratings' => '0', 'tickethash' => BuildHash(),
                    'islinked' => '0', 'tickettype' => $_ticketType, 'tickettypeid' => $_ticketTypeID, 'creationmode' => $_creationMode
                ), 'INSERT');
            $dwk_ticketID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('ticketid', $_ticket['ticketid'], $dwk_ticketID);

            foreach ($_ticket['posts'] as $_ticketPostID => $_ticketPost) {

//                This is necsary because its not posible to know if a user or a staff in Zendesk created the ticket
                $_newUserID = 0;
                if (isset($_newUserIDList[$_ticketPost['userid']])) {
                    $_newUserID = $_newUserIDList[$_ticketPost['userid']];
                    $_newStaffID = 0;
                    $_newCreator = SWIFT_TicketPost::CREATOR_CLIENT;

                    try {
                        $_SWIFT_UserObject = new SWIFT_User($_newUserID);

                        $_fullname = $_SWIFT_UserObject->GetProperty('fullname');
                        $_emailList = $_SWIFT_UserObject->GetEmailList();

                        $_email = $_emailList[0];
                        $_replyto = $_emailList[0];
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    }
                } else {
                    $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticketPost['userid']);

                    if (!$_newStaffID) {
                        $this->GetImportManager()->AddToLog('Import for Ticket Post ID #: ' . $_ticketPostID . ' for Ticket ID #:' . $dwk_ticketID . ' failed due to non existant creator', SWIFT_ImportManager::LOG_WARNING);
                        continue;
                    }

                    $_newCreator = SWIFT_TicketPost::CREATOR_STAFF;

                    $_SWIFT_StaffObject_Worker = new SWIFT_Staff(new SWIFT_DataID($_newStaffID));
                    if (!$_SWIFT_StaffObject_Worker instanceof SWIFT_Staff || !$_SWIFT_StaffObject_Worker->GetIsClassLoaded()) {
                        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                    }
                    $_fullname = $_SWIFT_StaffObject_Worker->GetProperty('fullname');
                    $_email = $_SWIFT_StaffObject_Worker->GetProperty('email');
                    $_replyto = $_SWIFT_StaffObject_Worker->GetProperty('email');
                }

                $_newEditedByStaffID = 0;

                $_isThirdParty = false;

                $_subjecthash = '';

                $this->GetImportManager()->AddToLog('Importing Ticket Post ID: ' . htmlspecialchars($_ticketPostID), SWIFT_ImportManager::LOG_SUCCESS);

                $this->Database->AutoExecute(TABLE_PREFIX . 'ticketposts',
                    array('ticketid' => $dwk_ticketID, 'dateline' => $_ticketPost['dateline'], 'userid' => $_newUserID,
                        'fullname' => $_fullname, 'email' => $_email, 'emailto' => $_replyto, 'ipaddress' => '0',
                        'hasattachments' => $_ticketPost['hasattachments'], 'edited' => '0',
                        'editedbystaffid' => '0', 'editeddateline' => '0', 'creator' => $_newCreator, 'isthirdparty' => $_isThirdParty,
                        'ishtml' => '0', 'isemailed' => '0', 'staffid' => $_newStaffID, 'contents' => $_ticketPost['contents'],
                        'contenthash' => sha1($_ticketPost['contents']), 'subjecthash' => sha1($_subjecthash), 'isprivate' => (int)($_ticketPost['isprivate'])
                    ), 'INSERT');
                $dwk_ticketPostID = $this->Database->InsertID();

                $this->ImportManager->GetImportRegistry()->UpdateKey('ticketid', $_ticketPostID, $dwk_ticketPostID);

                if ($_ticketPost['hasattachments'] > 0) {
                    foreach ($_ticketPost['attachment'] as $_ticketPostAttachmentID => $_ticketPostAttachment) {
                        // define("ATTACHMENT_DB", 1);
                        // define("ATTACHMENT_FILE", 2);
                        // define("ATTACHMENT_DL", 3);

                        $this->GetImportManager()->AddToLog('Importing Ticket Attachment ID: ' . htmlspecialchars($_ticketPostAttachmentID), SWIFT_ImportManager::LOG_SUCCESS);

//                        $_attachmentType = SWIFT_Attachment::TYPE_DATABASE;
                        $_attachmentType = SWIFT_Attachment::TYPE_FILE;
                        $_storeFileName = SWIFT_Attachment::GenerateRandomFileName();

                        $this->Database->AutoExecute(TABLE_PREFIX . 'attachments',
                            array('linktype' => SWIFT_Attachment::LINKTYPE_TICKETPOST, 'linktypeid' => $dwk_ticketPostID,
                                'downloaditemid' => '0', 'ticketid' => $dwk_ticketID, 'filename' => $_ticketPostAttachment['filename'],
                                'filesize' => $_ticketPostAttachment['filesize'], 'filetype' => $_ticketPostAttachment['filetype'], 'dateline' => $_ticketPostAttachment['dateline'],
                                'attachmenttype' => $_attachmentType, 'storefilename' => $_storeFileName
                            ), 'INSERT');
                        $_attachmentID = $this->Database->Insert_ID();

                        $this->ImportManager->GetImportRegistry()->UpdateKey('attachment', $_ticketPostAttachmentID, $_attachmentID, true);

                        $_file = fopen($_ticketPostAttachment['contents'], "r");
                        $dwk_content = "";

//                        Read the attachment
                        if ($_file) {
                            while (!feof($_file)) {
                                $buffer = fgets($_file, 4096);
                                $dwk_content = $dwk_content . $buffer;
                            }
                            fclose($_file);
                        }

                        $_finalFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_storeFileName;

                        $Handle = fopen($_finalFilePath, 'w');
                        fwrite($Handle, $dwk_content);
                        fclose($Handle);

                        @chmod($_finalFilePath, SWIFT_Attachment::DEFAULT_FILEPERMISSION);

//                        $_newAttachmentChunks = str_split($dwk_content, 200);
//
//                        foreach ($_newAttachmentChunks as $_chunkContents)
//                        {
//                            $_notBase64 = false;
//                            $_finalChunkContents = '';
//                            if (strtolower(DB_TYPE) == 'mysql')
//                            {
//                                $_finalChunkContents = $_chunkContents;
//                                $_notBase64 = true;
//                            } else {
//                                $_finalChunkContents = base64_encode($_chunkContents);
//                            }
//
//                            $this->Database->AutoExecute(TABLE_PREFIX . 'attachmentchunks',
//                                    array('attachmentid' => $_attachmentID, 'contents' => $_finalChunkContents, 'notbase64' => (int) ($_notBase64)
//                                    ), 'INSERT');
//                        }
                        unset($dwk_content);
                        unset($_file);
                    }
                }
            }
        }

        SWIFT_TicketManager::RebuildCache();

        return $_count;
    }

    /**
     * Retrieve the total number of records in the zendesk xml
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

        $_countContainer['totalitems'] = count($this->dwk_tickets_array);

        return $_countContainer['totalitems'];
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

        return 10;
    }

    /**
     * Load the Zendesk XML into an array
     *
     * @author Nicolás Ibarra Sabogal
     * @return array All the Zendesk information that into the XML
     */
    public function dwk_getZendeskInformation()
    {

        $dwk_temp_url = $this->dwk_zendesk_url;
        $dwk_tickets_xml = new DOMDocument();

        $dwk_tickets_xml->load($dwk_temp_url . 'tickets.xml');
        $dwk_tickets = $dwk_tickets_xml->getElementsByTagName("ticket");

        $dwk_count = 0;
        $dwk_temp_array = array();

        foreach ($dwk_tickets as $ticket) {
            $dwk_temp_array[$dwk_count]['ticketid'] = $ticket->getElementsByTagName("nice-id")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['ownerstaffid'] = $ticket->getElementsByTagName("assignee-id")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['dateline'] = $this->returnZendeskTimestamp($ticket->getElementsByTagName("created-at")->item(0)->nodeValue);
            $dwk_temp_array[$dwk_count]['departmentid'] = $ticket->getElementsByTagName("group-id")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['priorityid'] = $ticket->getElementsByTagName("priority-id")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['userid'] = $ticket->getElementsByTagName("requester-id")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['ticketstatusid'] = $ticket->getElementsByTagName("status-id")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['subject'] = $ticket->getElementsByTagName("subject")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['lastactivity'] = $this->returnZendeskTimestamp($ticket->getElementsByTagName("updated-at")->item(0)->nodeValue);
            $dwk_temp_array[$dwk_count]["totalreplies"] = 0;
            $dwk_temp_array[$dwk_count]["lastreplier"] = $ticket->getElementsByTagName("requester-id")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]["hasattachments"] = 0;

//            POSTS
            $dwk_ticket_posts = $ticket->getElementsByTagName("comment");

            $dwk_posts = 1;

            foreach ($dwk_ticket_posts as $post) {
//                This is the id of the end-user, agent or admin that send the post. I need to check this to know how was the creator
                $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['userid'] = $post->getElementsByTagName("author-id")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['dateline'] = $this->returnZendeskTimestamp($ticket->getElementsByTagName("created-at")->item(0)->nodeValue);
                $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['isprivate'] = IIF($post->getElementsByTagName("is-public")->item(0)->nodeValue == 'true', 0, 1);
                $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['contents'] = $post->getElementsByTagName("value")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['hasattachments'] = 0;

                $dwk_temp_array[$dwk_count]["totalreplies"]++;
                $dwk_temp_array[$dwk_count]["lastreplier"] = $post->getElementsByTagName("author-id")->item(0)->nodeValue;

                $dwk_posts_attachments = $post->getElementsByTagName("attachment");
                $dwk_attachments = 1;
                foreach ($dwk_posts_attachments as $attachment) {
                    $dwk_temp_array[$dwk_count]["hasattachments"] = 1;
                    $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['hasattachments'] = 1;

                    $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['attachment'][$dwk_attachments]['filetype'] = $attachment->getElementsByTagName("content-type")->item(0)->nodeValue;
                    $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['attachment'][$dwk_attachments]['dateline'] = $this->returnZendeskTimestamp($ticket->getElementsByTagName("created-at")->item(0)->nodeValue);
                    $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['attachment'][$dwk_attachments]['filename'] = $attachment->getElementsByTagName("filename")->item(0)->nodeValue;
                    $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['attachment'][$dwk_attachments]['filesize'] = $attachment->getElementsByTagName("size")->item(0)->nodeValue;
                    $dwk_temp_url = $attachment->getElementsByTagName("url")->item(0)->nodeValue;

//                        $_file = fopen($dwk_temp_url, "r");
//                        $dwk_content = "";
//
////                        Read the attachment
//                        if ($_file) {
//                            while (!feof($_file)) {
//                                $buffer = fgets($_file, 4096);
//                                $dwk_content = $dwk_content.$buffer;
//                            }
//                            fclose($_file);
//                        }
//
//                        $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['attachment'][$dwk_attachments]['contents'] = $dwk_content;
                    $dwk_temp_array[$dwk_count]['posts'][$dwk_posts]['attachment'][$dwk_attachments]['contents'] = $dwk_temp_url;

                    $dwk_attachments++;
                }

                $dwk_posts++;
            }
            $dwk_count++;
        }

        return $dwk_temp_array;
    }

    /**
     * Converts an Zendesk time to a unix-timestamp.
     */
    public function returnZendeskTimestamp($timestamp)
    {
        $dwk_temp_date = str_replace("T", " ", $timestamp);

        $dwk_array_date = explode(" ", $dwk_temp_date);

        if (stristr($dwk_array_date[1], "-")) {
            $dwk_array_time = explode("-", $dwk_array_date[1]);
        } elseif (stristr($dwk_array_date[1], "+")) {
            $dwk_array_time = explode("+", $dwk_array_date[1]);
        } else {
            $dwk_array_time = $dwk_array_date[1];
        }

        $dwk_final_date = $dwk_array_date[0] . " " . $dwk_array_time[0];

//        return strtotime($dwk_final_date)+3600;
        return strtotime($dwk_final_date);
    }
}

?>
