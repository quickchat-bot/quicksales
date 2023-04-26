<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    Kayako Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-Kayako Singapore Pte. Ltd.h Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace Troubleshooter\Api;

use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use SWIFT_RESTServer;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_XML;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;
use Troubleshooter\Models\Link\SWIFT_TroubleshooterLink;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;

/**
 * The Troubleshooter Step API Controller
 *
 * @author Simaranjit Singh
 */
class Controller_Step extends \Controller_api implements \SWIFT_REST_Interface
{
    /** @var SWIFT_RESTServer $RESTServer */
    public $RESTServer;

    /** @var SWIFT_XML $XML */
    public $XML;

    /**
     * Constructor
     *
     * @author Simaranjit Singh
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
        $this->Load->Library('MIME:MIMEList', [], false);

        $this->Language->Load('troubleshooter');
    }

    /**
     * Retrieve & Dispatch Troubleshooter steps
     *
     * @author Simaranjit Singh
     * @param int $_troubleshooterStepID (OPTIONAL) The Troubleshooter Setp ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessTroubleshooterSteps($_troubleshooterStepID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_troubleshooterStepContainer = array();
        if (!empty($_troubleshooterStepID)) {
            $this->Database->Query("SELECT " . TABLE_PREFIX . "troubleshootersteps.*, " . TABLE_PREFIX . "troubleshooterdata.contents FROM " . TABLE_PREFIX . "troubleshootersteps, " . TABLE_PREFIX . "troubleshooterdata WHERE " . TABLE_PREFIX . "troubleshooterdata.troubleshooterstepid = " . TABLE_PREFIX . "troubleshootersteps.troubleshooterstepid AND " . TABLE_PREFIX . "troubleshootersteps.troubleshooterstepid  = '" . (int) ($_troubleshooterStepID) . "'");
        } else {
            $this->Database->Query("SELECT " . TABLE_PREFIX . "troubleshootersteps.*, " . TABLE_PREFIX . "troubleshooterdata.contents FROM " . TABLE_PREFIX . "troubleshootersteps, " . TABLE_PREFIX . "troubleshooterdata WHERE " . TABLE_PREFIX . "troubleshooterdata.troubleshooterstepid = " . TABLE_PREFIX . "troubleshootersteps.troubleshooterstepid");
        }

        while ($this->Database->NextRecord()) {
            $_troubleshooterStepContainer[$this->Database->Record['troubleshooterstepid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('troubleshootersteps');
        foreach ($_troubleshooterStepContainer as $_stepID => $_troubleshooterStep) {
            $this->XML->AddParentTag('troubleshooterstep');

            $this->XML->AddTag('id', $_stepID);
            $this->XML->AddTag('categoryid', $_troubleshooterStep['troubleshootercategoryid']);
            $this->XML->AddTag('staffid', $_troubleshooterStep['staffid']);
            $this->XML->AddTag('staffname', $_troubleshooterStep['staffname']);
            $this->XML->AddTag('subject', $_troubleshooterStep['subject']);
            $this->XML->AddTag('edited', $_troubleshooterStep['edited']);
            $this->XML->AddTag('editedstaffid', $_troubleshooterStep['editedstaffid']);
            $this->XML->AddTag('editedstaffname', $_troubleshooterStep['editedstaffname']);
            $this->XML->AddTag('displayorder', $_troubleshooterStep['displayorder']);
            $this->XML->AddTag('views', $_troubleshooterStep['views']);
            $this->XML->AddTag('allowcomments', $_troubleshooterStep['allowcomments']);
            $this->XML->AddTag('hasattachments', $_troubleshooterStep['hasattachments']);


            $this->XML->AddParentTag('attachments');
            // Attachment Logic
            $_attachmentContainer = array();
            if ($_troubleshooterStep['hasattachments'] == '1') {
                $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP, (int) $_stepID);

                foreach ($_attachmentContainer as $_attachmentID => $_attachment) {
                    $_mimeDataContainer = array();
                    try {
                        $_fileExtension = mb_strtolower(substr($_attachment['filename'], (strrpos($_attachment['filename'], '.') + 1)));

                        $_MIMEListObject = new SWIFT_MIMEList();
                        $_mimeDataContainer = $_MIMEListObject->Get($_fileExtension);
                    } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
                        // Do nothing
                    }

                    $_attachmentIcon = 'icon_file.gif';
                    if (isset($_mimeDataContainer[1])) {
                        $_attachmentIcon = $_mimeDataContainer[1];
                    }

                    $this->XML->AddParentTag('attachment');
                    $this->XML->AddTag('id', $_attachment['attachmentid']);
                    $this->XML->AddTag('filename', htmlspecialchars($_attachment['filename']));
                    $this->XML->AddTag('filesize', FormattedSize($_attachment['filesize']));
                    $this->XML->AddTag('link', SWIFT::Get('basename') . '/Troubleshooter/Step/GetAttachment/' . $_troubleshooterStep['troubleshootercategoryid'] . '/' . $_stepID . '/' . $_attachment['attachmentid']);

                    $this->XML->EndParentTag('attachment');
                }
            }

            $this->XML->EndParentTag('attachments');

            //Troubleshooter  parentstep logic
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID((int)$_stepID));
            /** @var array $_parentTroubleshooterStepIDList */
            $_parentTroubleshooterStepIDList = SWIFT_TroubleshooterLink::RetrieveOnChild([$_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID()]);
            $this->XML->AddParentTag('parentsteps');
            foreach ($_parentTroubleshooterStepIDList as $_parentTroubleshooterStep) {
                $this->XML->AddTag('id', $_parentTroubleshooterStep);
            }

            $this->XML->EndParentTag('parentsteps');

            //Troubleshooter child step logic
            $_childTroubleshooterStepContainer = SWIFT_TroubleshooterStep::RetrieveSubSteps($_troubleshooterStep['troubleshootercategoryid'], (int) $_stepID);
            $this->XML->AddParentTag('childsteps');
            foreach ($_childTroubleshooterStepContainer as $_childTroubleshooterStepID => $_childTroubleshooterStep) {
                $this->XML->AddTag('id', $_childTroubleshooterStepID);
            }

            $this->XML->EndParentTag('childsteps');

            $this->XML->AddTag('redirecttickets', $_troubleshooterStep['redirecttickets']);
            $this->XML->AddTag('ticketsubject', $_troubleshooterStep['ticketsubject']);
            $this->XML->AddTag('redirectdepartmentid', $_troubleshooterStep['redirectdepartmentid']);
            $this->XML->AddTag('tickettypeid', $_troubleshooterStep['tickettypeid']);
            $this->XML->AddTag('priorityid', $_troubleshooterStep['priorityid']);
            $this->XML->AddTag('contents', $_troubleshooterStep['contents']);

            $this->XML->EndParentTag('troubleshooterstep');
        }
        $this->XML->EndParentTag('troubleshootersteps');

        return true;
    }

    /**
     * Get a list of Troubleshooter steps
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessTroubleshooterSteps();

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Get a Troubleshooter Step
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_troubleshooterStepID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessTroubleshooterSteps($_troubleshooterStepID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Insert a Troubleshooter Step
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        if (!isset($_POST['categoryid']) || trim($_POST['categoryid']) == '' || empty($_POST['categoryid'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Category id is empty');

            return false;
        } else if (!isset($_POST['subject']) || trim($_POST['subject']) == '' || empty($_POST['subject'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Subject is empty');

            return false;
        } else if (!isset($_POST['contents']) || trim($_POST['contents']) == '' || empty($_POST['contents'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Contents is empty');

            return false;
        } else if (!isset($_POST['staffid']) || trim($_POST['staffid']) == '' || empty($_POST['staffid'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staffid is empty');

            return false;
        }

        $_displayOrder = 0;
        if (isset($_POST['displayorder']) && !empty($_POST['displayorder'])) {
            $_displayOrder = (int) ($_POST['displayorder']);
        }

        $_allowComments = 0;
        if (isset($_POST['allowcomments']) && !empty($_POST['allowcomments'])) {
            $_allowComments = (int) ($_POST['allowcomments']);
        }

        $_enableTicketRedirection = 0;
        if (isset($_POST['enableticketredirection']) && !empty($_POST['enableticketredirection'])) {
            $_enableTicketRedirection = (int) ($_POST['enableticketredirection']);
        }

        $_redirectDepartmentID = 0;
        if (isset($_POST['redirectdepartmentid']) && !empty($_POST['redirectdepartmentid'])) {
            $_redirectDepartmentID = (int) ($_POST['redirectdepartmentid']);
        }

        $_ticketTypeID = 0;
        if (isset($_POST['tickettypeid']) && !empty($_POST['tickettypeid'])) {
            $_ticketTypeID = (int) ($_POST['tickettypeid']);
        }

        $_ticketPriorityID = 0;
        if (isset($_POST['ticketpriorityid']) && !empty($_POST['ticketpriorityid'])) {
            $_ticketPriorityID = (int) ($_POST['ticketpriorityid']);
        }

        $_customTicketSubject = '';
        if (isset($_POST['ticketsubject']) && !empty($_POST['ticketsubject'])) {
            $_customTicketSubject = ($_POST['ticketsubject']);
        }

        $_isDraft = true;
        if (isset($_POST['stepstatus']) && $_POST['stepstatus'] == SWIFT_TroubleshooterStep::STATUS_PUBLISHED) {
            $_isDraft = false;
        }

        //I need to load cache of stps in this category to make sure that no any invalid entry goes
        $_troubleshooterStepCache = SWIFT_TroubleshooterStep::RetrieveSteps($_POST['categoryid']);

        $_parentTroubleshooterStepIDList = array('0');
        if (isset($_POST['parentstepidlist']) && !empty($_POST['parentstepidlist'])) {
            $_newParentTroubleshooterStepIDList = explode(',', $_POST['parentstepidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_parentTroubleshooterStepIDList = array_intersect(array_keys($_troubleshooterStepCache), $_newParentTroubleshooterStepIDList);
        }

        try {
            $_SWIFT_StaffObject_Creator = new SWIFT_Staff(new SWIFT_DataID($_POST['staffid']));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Staff ID');

            return false;
        }

        $_stepStatus = SWIFT_TroubleshooterStep::STATUS_DRAFT;
        if ($_SWIFT_StaffObject_Creator->GetPermission('staff_trcaninsertpublishedsteps') != '0' && $_isDraft == false) {
            $_stepStatus = SWIFT_TroubleshooterStep::STATUS_PUBLISHED;
        }

        $_troubleshooterStepID = SWIFT_TroubleshooterStep::Create($_POST['categoryid'], $_stepStatus, $_POST['subject'], $_POST['contents'], $_displayOrder, (bool) $_allowComments, (bool) $_enableTicketRedirection, $_customTicketSubject, $_redirectDepartmentID, $_ticketTypeID, $_ticketPriorityID, $_parentTroubleshooterStepIDList, $_SWIFT_StaffObject_Creator);

        $this->ProcessTroubleshooterSteps($_troubleshooterStepID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Update Troubleshooter Step
     *
     * @author Simaranjit Singh
     * @param int $_troubleshooterStepID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_troubleshooterStepID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TroubleshooterStepObject = false;

        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Step ID not found');

            return false;
        }

        $_categoryID = $_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid');

        if (!isset($_POST['editedstaffid']) || trim($_POST['editedstaffid']) == '' || empty($_POST['editedstaffid'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'editedstaffid is empty');

            return false;
        }

        $_subject = $_SWIFT_TroubleshooterStepObject->GetProperty('subject');
        if (isset($_POST['subject']) && trim($_POST['subject']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'subject is empty');

            return false;
        } else if (isset($_POST['subject'])) {
            $_subject = $_POST['subject'];
        }

        $_contents = $_SWIFT_TroubleshooterStepObject->GetProperty('contents');
        if (isset($_POST['contents']) && trim($_POST['contents']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Contents is empty');

            return false;
        } else if (isset($_POST['contents'])) {
            $_contents = $_POST['contents'];
        }

        $_displayOrder = $_SWIFT_TroubleshooterStepObject->GetProperty('displayorder');
        if (isset($_POST['displayorder']) && !empty($_POST['displayorder'])) {
            $_displayOrder = (int) ($_POST['displayorder']);
        }

        $_allowComments = $_SWIFT_TroubleshooterStepObject->GetProperty('allowcomments');
        if (isset($_POST['allowcomments']) && !empty($_POST['allowcomments'])) {
            $_allowComments = (int) ($_POST['allowcomments']);
        }

        $_enableTicketRedirection = $_SWIFT_TroubleshooterStepObject->GetProperty('redirecttickets');
        if (isset($_POST['enableticketredirection']) && !empty($_POST['enableticketredirection'])) {
            $_enableTicketRedirection = (int) ($_POST['enableticketredirection']);
        }

        $_redirectDepartmentID = $_SWIFT_TroubleshooterStepObject->GetProperty('redirectdepartmentid');
        if (isset($_POST['redirectdepartmentid']) && !empty($_POST['redirectdepartmentid'])) {
            $_redirectDepartmentID = (int) ($_POST['redirectdepartmentid']);
        }

        $_ticketTypeID = $_SWIFT_TroubleshooterStepObject->GetProperty('tickettypeid');
        if (isset($_POST['tickettypeid']) && !empty($_POST['tickettypeid'])) {
            $_ticketTypeID = (int) ($_POST['tickettypeid']);
        }

        $_ticketPriorityID = $_SWIFT_TroubleshooterStepObject->GetProperty('priorityid');
        if (isset($_POST['ticketpriorityid']) && !empty($_POST['ticketpriorityid'])) {
            $_ticketPriorityID = (int) ($_POST['ticketpriorityid']);
        }

        $_customTicketSubject = $_SWIFT_TroubleshooterStepObject->GetProperty('ticketsubject');
        if (isset($_POST['ticketsubject'])) {
            $_customTicketSubject = (int) ($_POST['ticketsubject']);
        }

        $_isDraft = true;
        if (isset($_POST['stepstatus']) && $_POST['stepstatus'] == SWIFT_TroubleshooterStep::STATUS_PUBLISHED) {
            $_isDraft = false;
        }

        //I need to load cache of stps in this category to make sure that no any invalid entry goes
        $_troubleshooterStepCache = SWIFT_TroubleshooterStep::RetrieveSteps($_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid'));

        $_parentTroubleshooterStepIDList = (array) SWIFT_TroubleshooterLink::RetrieveOnChild(array($_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID()));
        if (isset($_POST['parentstepidlist']) && !empty($_POST['parentstepidlist'])) {
            $_newParentTroubleshooterStepIDList = explode(',', $_POST['parentstepidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_parentTroubleshooterStepIDList = array_intersect(array_keys($_troubleshooterStepCache), $_newParentTroubleshooterStepIDList);
        }

        try {
            $_SWIFT_StaffObject_Creator = new SWIFT_Staff(new SWIFT_DataID($_POST['editedstaffid']));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Staff ID');

            return false;
        }

        $_stepStatus = SWIFT_TroubleshooterStep::STATUS_DRAFT;
        if ($_SWIFT_StaffObject_Creator->GetPermission('staff_trcaninsertpublishedsteps') != '0' && $_isDraft == false) {
            $_stepStatus = SWIFT_TroubleshooterStep::STATUS_PUBLISHED;
        }

        $_updateResult = $_SWIFT_TroubleshooterStepObject->Update($_subject, $_contents, $_displayOrder, (bool) $_allowComments, (bool) $_enableTicketRedirection, $_customTicketSubject, $_redirectDepartmentID, $_ticketTypeID, $_ticketPriorityID, $_parentTroubleshooterStepIDList, $_SWIFT_StaffObject_Creator);

        $this->ProcessTroubleshooterSteps($_troubleshooterStepID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     *     Delete Troubleshooter Step
     *
     * @author Simaranjit Singh
     * @param string $_troubleshooterStepID troubleshooter ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_troubleshooterStepID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TroubleshooterStepObject = false;

        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID((int) $_troubleshooterStepID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Step ID not found');

            return false;
        }

        SWIFT_TroubleshooterStep::DeleteList(array($_troubleshooterStepID));

        return true;
    }

}
