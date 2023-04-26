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

namespace Tickets\Api;

use Controller_api;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreString;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use SWIFT_XML;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The TicketAttachment API Controller
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 * @author Varun Shoor
 */
class Controller_TicketAttachment extends Controller_api implements SWIFT_REST_Interface
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

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call /Tickets/TicketAttachment/ListAll instead.');

        return true;
    }


    /**
     * Get a list of attachments for the given ticket
     *
     * Example Output:
     *
     * <attachments>
     *    <attachment>
     *        <id>1</id>
     *        <ticketid>1</ticketid>
     *        <ticketpostid>1</ticketpostid>
     *        <filename>icon_chart.gif</filename>
     *        <filesize>541</filesize>
     *        <filetype>image/gif</filetype>
     *        <dateline>1296645496</dateline>
     *    </attachment>
     * </attachments>
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param bool|int $_attachmentID (OPTIONAL) To filter result set to a single attachment id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListAll($_ticketID, $_attachmentID = false)
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
        if (!empty($_attachmentID)) {
            $_querySuffix .= " AND attachmentid = '" . (int) ($_attachmentID) . "'";
        }

        $_attachmentContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE linktype = '" . SWIFT_Attachment::LINKTYPE_TICKETPOST . "' AND ticketid = '" . ($_ticketID) . "'" . $_querySuffix);
        while ($this->Database->NextRecord()) {
            $_attachmentContainer[$this->Database->Record['attachmentid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('attachments');
            foreach ($_attachmentContainer as $_attID => $_attachment) {
                $this->XML->AddParentTag('attachment');
                    $this->XML->AddTag('id', $_attachment['attachmentid']);
                    $this->XML->AddTag('ticketid', $_attachment['ticketid']);
                    $this->XML->AddTag('ticketpostid', $_attachment['linktypeid']);
                    $this->XML->AddTag('filename', $_attachment['filename']);
                    $this->XML->AddTag('filesize', $_attachment['filesize']);
                    $this->XML->AddTag('filetype', $_attachment['filetype']);
                    $this->XML->AddTag('dateline', $_attachment['dateline']);
                $this->XML->EndParentTag('attachment');
            }
        $this->XML->EndParentTag('attachments');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Attachment
     *
     * Example Output:
     *
     * <attachment>
     *        <id>1</id>
     *        <ticketid>1</ticketid>
     *        <ticketpostid>1</ticketpostid>
     *        <filename>icon_chart.gif</filename>
     *        <filesize>541</filesize>
     *        <filetype>image/gif</filetype>
     *        <dateline>1296645496</dateline>
     *        <contents><![CDATA[iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK==]]></contents>
     * </attachment>
     *
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ticketID, $_attachmentID)
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
            $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Attachment not Found');

            return false;
        }

        if ($_SWIFT_AttachmentObject->GetProperty('ticketid') != $_ticketID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Attachment does not belong to the specified ticket');

            return false;
        }

        $this->XML->AddParentTag('attachments');
            $this->XML->AddParentTag('attachment');

                $this->XML->AddTag('id', $_SWIFT_AttachmentObject->GetProperty('attachmentid'));
                $this->XML->AddTag('ticketid', $_SWIFT_AttachmentObject->GetProperty('ticketid'));
                $this->XML->AddTag('ticketpostid', $_SWIFT_AttachmentObject->GetProperty('linktypeid'));
                $this->XML->AddTag('filename', $_SWIFT_AttachmentObject->GetProperty('filename'));
                $this->XML->AddTag('filesize', $_SWIFT_AttachmentObject->GetProperty('filesize'));
                $this->XML->AddTag('filetype', $_SWIFT_AttachmentObject->GetProperty('filetype'));
                $this->XML->AddTag('dateline', $_SWIFT_AttachmentObject->GetProperty('dateline'));

                $this->XML->AddTag('contents', base64_encode($_SWIFT_AttachmentObject->Get()));

            $this->XML->EndParentTag('attachment');
        $this->XML->EndParentTag('attachments');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a new Ticket Attachment
     *
     * Required Fields:
     * ticketid
     * ticketpostid
     *
     * Example Output:
     *
     * <attachments>
     *    <attachment>
     *        <id>1</id>
     *        <ticketid>1</ticketid>
     *        <ticketpostid>1</ticketpostid>
     *        <filename>icon_chart.gif</filename>
     *        <filesize>541</filesize>
     *        <filetype>image/gif</filetype>
     *        <dateline>1296645496</dateline>
     *    </attachment>
     * </attachments>
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['ticketid']) || empty($_POST['ticketid']) || trim($_POST['ticketid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No Ticket ID Specified');

            return false;
        }

        if (!isset($_POST['ticketpostid']) || empty($_POST['ticketpostid']) || trim($_POST['ticketpostid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No Ticket Post ID Specified');

            return false;
        }

        $_ticketID     = $_POST['ticketid'];
        $_ticketPostID = $_POST['ticketpostid'];

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $_SWIFT_TicketPostObject = false;
        try {
            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded() || $_SWIFT_TicketPostObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket Post not found');

            return false;
        }

        if (!isset($_POST['filename']) || empty($_POST['filename']) || trim($_POST['filename']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No file name specified');

            return false;
        }

        if (!isset($_POST['contents'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No contents specified');

            return false;
        }

        $_finalContents = base64_decode($_POST['contents']);

        /*
        * BUG FIX: Parminder Singh
        *
        * SWIFT-1716: invalid filetype is returned in xml response when attaching a file in a ticket post using Rest API->REST - TicketAttachment->POST /Tickets/TicketAttachment
        *
        */
        try {
            $_fileExtension     = mb_strtolower(substr($_POST['filename'], (strrpos($_POST['filename'], '.') + 1)));
            $_MIMEList          = new SWIFT_MIMEList();
            $_mimeDataContainer = $_MIMEList->Get($_fileExtension);
        } catch (SWIFT_MIME_Exception $_MIME_Exception) {
            // Do nothing
        }
        $_contentType = 'application/octet-stream';
        if (isset($_mimeDataContainer[0]) && !empty($_mimeDataContainer[0])) {
            $_contentType = $_mimeDataContainer[0];
        }

        $_SWIFT_AttachmentStoreStringObject = new SWIFT_AttachmentStoreString($_POST['filename'], $_contentType, $_finalContents);

        unset($_finalContents);

        $_SWIFT_AttachmentObject = SWIFT_Attachment::Create(SWIFT_Attachment::LINKTYPE_TICKETPOST, $_ticketPostID, $_SWIFT_AttachmentStoreStringObject, $_ticketID);
        if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Attachment Creation Failed');

            return false;
            // @codeCoverageIgnoreEnd
        }

        SWIFT_Ticket::RecalculateHasAttachmentProperty(array($_ticketID));

        $_SWIFT_TicketPostObject->UpdateHasAttachments(1); // Update TicketPost hasattachments field

        $this->ListAll($_ticketID, $_SWIFT_AttachmentObject->GetAttachmentID());

        return true;
    }

    /**
     * Delete a Ticket Attachment
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketID, $_attachmentID)
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
            $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Attachment not Found');

            return false;
        }

        if ($_SWIFT_AttachmentObject->GetProperty('ticketid') != $_ticketID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Attachment does not belong to the specified ticket');

            return false;
        }

        SWIFT_Attachment::DeleteList(array($_attachmentID));

        SWIFT_Ticket::RecalculateHasAttachmentProperty(array($_ticketID));

        return true;
    }
}
