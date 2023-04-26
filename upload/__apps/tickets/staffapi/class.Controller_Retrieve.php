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

namespace Tickets\Staffapi;

use Controller_staffapi;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_DataID;
use SWIFT_Exception;
use Tickets\Library\StaffAPI\SWIFT_TicketStaffAPIManager;
use Tickets\Models\Ticket\SWIFT_Ticket;
use SWIFT_XML;

/**
 * The Ticket List Retrieval Controller
 *
 * @author Varun Shoor
 */
class Controller_Retrieve extends Controller_staffapi
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

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
        $this->Language->Load('staff_ticketssearch');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * The Ticket List Retrieval Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentIDList = $_ticketStatusIDList = $_ownerStaffIDList = $_ticketIDList = '';
        $_ticketFilterID = 0;

        if (isset($_POST['departmentid'])) {
            $_departmentIDList = $_POST['departmentid'];
        }

        if (isset($_POST['statusid']) && !empty($_POST['statusid'])) {
            $_ticketStatusIDList = $_POST['statusid'];
        }

        if (isset($_POST['ownerid']) && $_POST['ownerid'] != '') {
            $_ownerStaffIDList = $_POST['ownerid'];
        }

        if (isset($_POST['ticketid']) && !empty($_POST['ticketid'])) {
            $_ticketIDList = $_POST['ticketid'];
        }

        if (isset($_POST['filterid']) && !empty($_POST['filterid'])) {
            $_ticketFilterID = $_POST['filterid'];
        }

        $_sortBy = $_sortOrder = $_start = $_limit = false;
        if (isset($_POST['sortorder']) && (strtolower($_POST['sortorder']) === 'asc' || strtolower($_POST['sortorder']) === 'desc')) {
            $_sortOrder = strtolower($_POST['sortorder']);
        }

        if (isset($_POST['start']) && is_numeric($_POST['start'])) {
            $_start = (int) ($_POST['start']);
        }

        if (isset($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 1000) {
            $_limit = (int) ($_POST['limit']);
        }

        if (isset($_POST['sortby']) && in_array(strtolower($_POST['sortby']), SWIFT_TicketStaffAPIManager::GetValidSortByValues())) {
            $_sortBy = strtolower($_POST['sortby']);
        }

        $_extendedInfo = false;
        if (isset($_POST['wantticketdata']) && $_POST['wantticketdata'] == '1') {
            $_extendedInfo = true;
        }

        SWIFT_TicketStaffAPIManager::DispatchList($_departmentIDList, $_ticketStatusIDList, $_ownerStaffIDList, $_ticketFilterID, $_ticketIDList,
                $_sortBy, $_sortOrder, $_start, $_limit, true, $_extendedInfo);

        return true;
    }

    /**
     * The Data Ticket List Retrieval Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Data()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentIDList = $_ticketStatusIDList = $_ownerStaffIDList = $_ticketIDList = '';
        $_ticketFilterID = 0;

        if (isset($_POST['ticketid']) && !empty($_POST['ticketid'])) {
            $_ticketIDList = $_POST['ticketid'];
        } else {
            $this->_DispatchError('No Ticket ID Specified');

            return false;
        }

        $_sortBy = $_sortOrder = $_start = $_limit = false;

        if (isset($_POST['start']) && is_numeric($_POST['start'])) {
            $_start = (int) ($_POST['start']);
        }

        if (isset($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 1000) {
            $_limit = (int) ($_POST['limit']);
        }

        $_ticketInfo = true;
        if (isset($_POST['wantticketdata']) && $_POST['wantticketdata'] == '0') {
            $_ticketInfo = false;
        }

        $_wantAttachmentData = false;
        if (isset($_POST['wantattachmentdata']) && $_POST['wantattachmentdata'] == '1') {
            $_wantAttachmentData = true;
        }

        $_wantPostsOnly = false;
        if (isset($_POST['wantpostsonly']) && $_POST['wantpostsonly'] == '1') {
            $_wantPostsOnly = true;
        }

        $_postSortOrder = 'asc';
        if (isset($_POST['sortorder']) && (mb_strtolower($_POST['sortorder']) === 'asc' || mb_strtolower($_POST['sortorder']) === 'desc')) {
            $_postSortOrder = mb_strtolower($_POST['sortorder']);
        }

        SWIFT_TicketStaffAPIManager::DispatchList($_departmentIDList, $_ticketStatusIDList, $_ownerStaffIDList, $_ticketFilterID, $_ticketIDList,
                $_sortBy, $_sortOrder, 0, 1000, $_ticketInfo, true, $_wantAttachmentData, $_wantPostsOnly, $_start, $_limit, $_postSortOrder);

        return true;
    }

    /**
     * Initiate a Ticket Search
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Search()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchQuery = '';
        if (isset($_POST['query']) && !empty($_POST['query'])) {
            $_searchQuery = trim($_POST['query']);
        }

        $_searchTicketID = $_searchContents = $_searchAuthor = $_searchEmail = $_searchFullName = $_searchNotes = $_searchUserGroup = $_searchUserOrganization = $_searchUser = $_searchTags = $_searchSubject = false;
        $_departmentIDList = $_ticketStatusIDList = $_ownerStaffIDList = array();
        if (isset($_POST['ticketid']) && $_POST['ticketid'] == '1') {
            $_searchTicketID = true;
        }

        if (isset($_POST['contents']) && $_POST['contents'] == '1') {
            $_searchContents = true;
        }

        if (isset($_POST['author']) && $_POST['author'] == '1') {
            $_searchAuthor = true;
        }

        if (isset($_POST['email']) && $_POST['email'] == '1') {
            $_searchEmail = true;
        }

        if (isset($_POST['fullname']) && $_POST['fullname'] == '1') {
            $_searchFullName = true;
        }

        if (isset($_POST['notes']) && $_POST['notes'] == '1') {
            $_searchNotes = true;
        }

        if (isset($_POST['usergroup']) && $_POST['usergroup'] == '1') {
            $_searchUserGroup = true;
        }

        if (isset($_POST['userorganization']) && $_POST['userorganization'] == '1') {
            $_searchUserOrganization = true;
        }

        if (isset($_POST['user']) && $_POST['user'] == '1') {
            $_searchUser = true;
        }

        if (isset($_POST['tags']) && $_POST['tags'] == '1') {
            $_searchTags = true;
        }

        if (isset($_POST['subject']) && $_POST['subject'] == '1') {
            $_searchSubject = true;
        }

        if (isset($_POST['departmentid'])) {
            $_departmentIDList = SWIFT_TicketStaffAPIManager::RetrieveIDListFromCSV($_POST['departmentid']);
        }

        if (isset($_POST['statusid']) && !empty($_POST['statusid'])) {
            $_ticketStatusIDList = SWIFT_TicketStaffAPIManager::RetrieveIDListFromCSV($_POST['statusid']);
        }

        if (isset($_POST['ownerid']) && $_POST['ownerid'] != '') {
            $_ownerStaffIDList = SWIFT_TicketStaffAPIManager::RetrieveIDListFromCSV($_POST['ownerid']);
        }

        $_start = 0;
        $_limit = 100;
        if (isset($_POST['start']) && is_numeric($_POST['start'])) {
            $_start = (int) ($_POST['start']);
        }

        if (isset($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] <= 1000) {
            $_limit = (int) ($_POST['limit']);
        }

        SWIFT_TicketStaffAPIManager::Search($_searchQuery, $_searchTicketID, $_searchContents, $_searchAuthor, $_searchEmail, $_searchFullName, $_searchNotes, $_searchUserGroup, $_searchUserOrganization,
                $_searchUser, $_searchTags, $_searchSubject, $_departmentIDList, $_ticketStatusIDList, $_ownerStaffIDList, $_start, $_limit);

        return true;
    }

    /**
     * Retrieve an Attachment
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Attachment()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())     {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['ticketid']) || empty($_POST['ticketid']) || !isset($_POST['attachmentid']) || empty($_POST['attachmentid'])) {
            $this->_DispatchError($this->Language->Get('requiredfieldempty'));

            return false;
        }

        $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_POST['ticketid']));
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
           // @codeCoverageIgnoreStart
           // this code will never be executed
            $this->_DispatchError('Invalid Ticket ID Specified');

            return false;
           // @codeCoverageIgnoreEnd
        }

        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            $this->_DispatchError('No Permission to Access Ticket');

            return false;
        }

        $_SWIFT_AttachmentObject = new SWIFT_Attachment($_POST['attachmentid']);
        if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            $this->_DispatchError('Invalid Attachment ID Specified');

            return false;
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_AttachmentObject->GetProperty('linktype') != SWIFT_Attachment::LINKTYPE_TICKETPOST
                || $_SWIFT_AttachmentObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID()) {
            $this->_DispatchError('No Permission to Access Attachment');

            return false;
        }

        $_SWIFT_XMLObject = new SWIFT_XML();

        $_SWIFT_XMLObject->AddParentTag('kayako_staffapi');
        $_SWIFT_XMLObject->AddTag('status', '1');
        $_SWIFT_XMLObject->AddTag('error', '');

        $_SWIFT_XMLObject->AddTag('attachment', base64_encode($_SWIFT_AttachmentObject->Get()), array('id' => $_SWIFT_AttachmentObject->GetAttachmentID(),
                'filename' => $_SWIFT_AttachmentObject->GetProperty('filename'), 'filetype' => $_SWIFT_AttachmentObject->GetProperty('filetype'),
                'filesize' => $_SWIFT_AttachmentObject->GetProperty('filesize')));
        $_SWIFT_XMLObject->EndParentTag('kayako_staffapi');

        $_SWIFT_XMLObject->EchoXMLStaffAPI();

        return true;
    }
}
