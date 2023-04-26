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

namespace Tickets\Api;

use Controller_api;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreString;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Library\HTML\SWIFT_HTML;
use SWIFT_MIMEList;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use SWIFT_XML;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The TicketPost API Controller
 *
 * @property SWIFT_RESTServer $RESTServer
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class Controller_TicketPost extends Controller_api implements SWIFT_REST_Interface
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
        $this->Language->Load('staff_ticketssearch');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * GetList
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call /Tickets/TicketPost/ListAll instead.');

        return true;
    }


    /**
     * Get a list of Ticket Posts for the given ticket
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param bool|int $_ticketPostID (OPTIONAL) To filter result set to a single ticket post id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListAll($_ticketID, $_ticketPostID = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $_querySuffix = '';
        if (!empty($_ticketPostID)) {
            $_querySuffix .= " AND ticketpostid = '" . (int) ($_ticketPostID) . "'";
        }

        $_ticketPostOrder = 'ASC';
        if ($this->Settings->Get('t_postorder') === 'desc') {
            $_ticketPostOrder = 'DESC';
        }

        $_ticketPostContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketposts
            WHERE ticketid = '" . ($_ticketID) . "'" . $_querySuffix . " ORDER BY ticketpostid " . $_ticketPostOrder);
        while ($this->Database->NextRecord()) {
            $_ticketPostContainer[$this->Database->Record['ticketpostid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('posts');
        foreach ($_ticketPostContainer as $_tID => $_ticketPost) {
            /*
             * BUG FIX - Simaranjit Singh
             *
             * SWIFT-1949 Issue with non-printable characters in REST API
             *
             */
            $_ticketPost['contents'] = preg_replace('/(?![\x{000d}\x{000a}\x{0009}])\p{C}/u', '', $_ticketPost['contents']);

            $this->XML->AddParentTag('post');

                $this->XML->AddTag('id', $_tID);
                $this->XML->AddTag('ticketpostid', $_tID);
                $this->XML->AddTag('ticketid', $_ticketPost['ticketid']);
                $this->XML->AddTag('dateline', $_ticketPost['dateline']);
                $this->XML->AddTag('userid', $_ticketPost['userid']);
                $this->XML->AddTag('fullname', $_ticketPost['fullname']);
                $this->XML->AddTag('email', $_ticketPost['email']);
                $this->XML->AddTag('emailto', $_ticketPost['emailto']);
                $this->XML->AddTag('ipaddress', $_ticketPost['ipaddress']);
                $this->XML->AddTag('hasattachments', $_ticketPost['hasattachments']);
                $this->XML->AddTag('creator', $_ticketPost['creator']);
                $this->XML->AddTag('isthirdparty', $_ticketPost['isthirdparty']);
                $this->XML->AddTag('ishtml', $_ticketPost['ishtml']);
                $this->XML->AddTag('isemailed', $_ticketPost['isemailed']);
                $this->XML->AddTag('staffid', $_ticketPost['staffid']);
                $this->XML->AddTag('issurveycomment', $_ticketPost['issurveycomment']);
                $this->XML->AddTag('contents', $_ticketPost['contents']);
                $this->XML->AddTag('isprivate', $_ticketPost['isprivate']);
            /**
             * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
             *
             * SWIFT-2632 There should be an option of retrieving attachments for the specific ticket post via REST API
             *
             * Comments: Check if hasattachments is 1 in ticketposts then displays attachments for that particular post
             */
            $_attachmentContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE linktype = '" . SWIFT_Attachment::LINKTYPE_TICKETPOST . "' AND ticketid = '" . ($_ticketID) . "' and linktypeid = '" . $_tID . "'");

            while ($this->Database->NextRecord()) {
                $_attachmentContainer[$this->Database->Record['attachmentid']] = $this->Database->Record;
            }

            if ($_ticketPost['hasattachments'] != '0')
            {
                $this->XML->AddParentTag('attachments');

                foreach ($_attachmentContainer as $_attachmentID => $_attachment) {
                    try {
                        $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
                    }
                    catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Attachment not Found');

                        return false;
                    }

                    $this->XML->AddParentTag('attachment');
                    $this->XML->AddTag('id', $_attachment['attachmentid']);
                    $this->XML->AddTag('filename', $_attachment['filename']);
                    $this->XML->AddTag('filesize', $_attachment['filesize']);
                    $this->XML->AddTag('filetype', $_attachment['filetype']);
                    $this->XML->AddTag('dateline', $_attachment['dateline']);
                    $this->XML->AddTag('contents', base64_encode($_SWIFT_AttachmentObject->Get()));
                    $this->XML->EndParentTag('attachment');
                }
                $this->XML->EndParentTag('attachments');

            }

            $this->XML->EndParentTag('post');
        }
        $this->XML->EndParentTag('posts');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Ticket Post
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketPostID The Ticket Post ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ticketID, $_ticketPostID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        try {
            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket Post not Found');

            return false;
        }

        if ($_SWIFT_TicketPostObject->GetProperty('ticketid') != $_ticketID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Post does not belong to the specified ticket');

            return false;
        }

        $this->ListAll($_ticketID, $_ticketPostID);

        return true;
    }

    /**
     * Create a new Ticket Post
     *
     * Example Output: http://wiki.opencart.com.vn/display/DEV/REST+-+Ticket
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');
        $_ticketTypeCache = $_SWIFT->Cache->Get('tickettypecache');
        $_ticketPriorityCache = $_SWIFT->Cache->Get('prioritycache');
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        $_ticketFilterCache = $_SWIFT->Cache->Get('ticketfiltercache');
        $_ticketWorkflowCache = $_SWIFT->Cache->Get('ticketworkflowcache');

        $_ticketID = 0;
        if (!isset($_POST['ticketid']) || empty($_POST['ticketid']) || trim($_POST['ticketid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No Ticket ID Specified');

            return false;
        }

        if (!isset($_POST['contents']) || empty($_POST['contents']) || trim($_POST['contents']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No Contents Specified');

            return false;
        }

        if ((!isset($_POST['userid']) || empty($_POST['userid']) || trim($_POST['userid']) == '')
            && (!isset($_POST['staffid']) || empty($_POST['staffid']) || trim($_POST['staffid']) == '')) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff ID and User ID is empty');

            return false;
        }


        $_ticketID = $_POST['ticketid'];

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        /*
         * BUG FIX - Pankaj Garg <pankaj.garg@opencart.com.vn>
         *
         * SWIFT-3104 There should be an option to send attachments with staff's update (using 'Send Mail' option) when ticket is updated via REST API
         *
         * Comments: Processing Attachments With Ticket Posts
         */
        $_attachmentStoreStringContainer = array();
        foreach ($_POST as $_parameter => $_parameterValue) {
            if ((preg_match('/^filename([0-9]{0,1})$/', $_parameter, $_filename))) {
                $_contentParameter = isset($_filename[1]) ? 'filecontent' . $_filename[1] : null;

                if (isset($_POST[$_contentParameter])) {
                    $_finalContents     = base64_decode($_POST[$_contentParameter]);
                    $_fileExtension     = mb_strtolower(substr($_POST[$_filename[0]], (strrpos($_POST[$_filename[0]], '.') + 1)));
                    $_MIMEListObject    = new SWIFT_MIMEList();
                    $_mimeDataContainer = $_MIMEListObject->Get($_fileExtension);
                    $_contentType       = 'application/octet-stream';

                    if (isset($_mimeDataContainer[0]) && !empty($_mimeDataContainer[0])) {
                        $_contentType = $_mimeDataContainer[0];
                    }

                    $_attachmentStoreStringContainer[] = new SWIFT_AttachmentStoreString($_POST[$_filename[0]], $_contentType, $_finalContents);

                    $_SWIFT_TicketObject->AddToAttachments($_POST[$_filename[0]], $_contentType, $_finalContents);
                    unset($_finalContents);
                }
            }
        }

        if (isset($_POST['userid']) && !empty($_POST['userid'])) {
            $_SWIFT_UserObject = false;

            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_POST['userid']));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User ID is Invalid');

                return false;
            }

            $_ticketPostID = SWIFT_TicketPost::CreateClient($_SWIFT_TicketObject, $_SWIFT_UserObject, SWIFT_Ticket::CREATIONMODE_API,
                                                            $_POST['contents'], $_SWIFT_TicketObject->GetProperty('subject'), SWIFT_TicketPost::CREATOR_USER, null, '', array(), DATENOW, $_attachmentStoreStringContainer);

            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        } else {
            $_SWIFT_StaffObject = false;

            try {
                $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_POST['staffid']));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff ID is Invalid');

                return false;
            }

            /**
            * BUG FIX - Saloni Dhall
            *
            * SWIFT-3027: Error generated while adding a new ticket post as staff using POST /Tickets/TicketPost method
            *
            */
            $_SWIFT_TicketPostObject = SWIFT_TicketPost::CreateStaff($_SWIFT_TicketObject, $_SWIFT_StaffObject, SWIFT_Ticket::CREATIONMODE_API,
                                                                     $_POST['contents'], $_SWIFT_TicketObject->GetProperty('subject'), SWIFT_HTML::DetectHTMLContent($_POST['contents']), false, '', $_POST['isprivate'], DATENOW, $_attachmentStoreStringContainer);


        }

        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
           // @codeCoverageIgnoreStart
           // this code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Ticket Post Creation Failed');

            return false;
           // @codeCoverageIgnoreEnd
        }

        $this->ListAll($_ticketID, $_SWIFT_TicketPostObject->GetTicketPostID());

        return true;
    }

    /**
     * Delete a Ticket Post
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketPostID The Ticket Post ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketID, $_ticketPostID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        try {
            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket Post not Found');

            return false;
        }

        if ($_SWIFT_TicketPostObject->GetProperty('ticketid') != $_ticketID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Post does not belong to the specified ticket');

            return false;
        }

        SWIFT_TicketPost::DeleteList(array($_ticketPostID));

        SWIFT_TicketManager::RebuildCache(array($_SWIFT_TicketObject->GetProperty('departmentid')));

        return true;
    }
}
