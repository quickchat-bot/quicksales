<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-2009, QuickSupport Singapore Pte. Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace Troubleshooter\Api;

use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreString;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use SWIFT_RESTServer;
use SWIFT_XML;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;

/**
 * The Troubleshooter Attachment API Controller
 *
 * @author Simaranjit Singh
 */
class Controller_Attachment extends \Controller_api implements \SWIFT_REST_Interface
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
    }

    /**
     * GetList
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

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented.');

        return false;
    }

    /**
     * Get a list of attachments for the given Step
     *
     * Example Output:
     *
     * <attachments>
     *    <attachment>
     *        <id>1</id>
     *        <troubleshooterstepid>1</troubleshooterstepid>
     *        <filename>icon_chart.gif</filename>
     *        <filesize>541</filesize>
     *        <filetype>image/gif</filetype>
     *        <dateline>1296645496</dateline>
     *    </attachment>
     * </attachments>
     *
     * @author Simaranjit Singh
     * @param int $_troubleshooterStepID The Troubleshooter step ID
     * @param bool $_attachmentID (OPTIONAL) To filter result set to a single attachment id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListAll($_troubleshooterStepID, $_attachmentID = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TroubleshooterStepObject = false;

        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Troubleshooter step not found');

            return false;
        }

        $_querySuffix = '';
        if (!empty($_attachmentID)) {
            $_querySuffix .= " AND attachmentid = '" . (int) ($_attachmentID) . "'";
        }

        $_attachmentContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE linktype = '" . SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP . "' AND linktypeid = '" . $_troubleshooterStepID . "'" . $_querySuffix);
        while ($this->Database->NextRecord()) {
            $_attachmentContainer[$this->Database->Record['attachmentid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('troubleshooterattachments');
        foreach ($_attachmentContainer as $_attID => $_attachment) {
            $this->XML->AddParentTag('troubleshooterattachment');
            $this->XML->AddTag('id', $_attachment['attachmentid']);
            $this->XML->AddTag('troubleshooterstepid', $_attachment['linktypeid']);
            $this->XML->AddTag('filename', $_attachment['filename']);
            $this->XML->AddTag('filesize', $_attachment['filesize']);
            $this->XML->AddTag('filetype', $_attachment['filetype']);
            $this->XML->AddTag('dateline', $_attachment['dateline']);

            //Attachments logic
            $_SWIFT_AttachmentObject = new SWIFT_Attachment((int) $_attID);

            $this->XML->AddTag('contents', base64_encode((string) $_SWIFT_AttachmentObject->Get()));

            $this->XML->EndParentTag('troubleshooterattachment');
        }
        $this->XML->EndParentTag('troubleshooterattachments');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Attachment
     *
     * Example Output:
     *
     * <attachment>
     *         <id>1</id>
     *         <troubleshooterstepid>1</troubleshooterstepid>
     *         <filename>icon_chart.gif</filename>
     *         <filesize>541</filesize>
     *         <filetype>image/gif</filetype>
     *         <dateline>1296645496</dateline>
     *         <contents><![CDATA[iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK==]]></contents>
     * </attachment>
     *
     *
     * @author Simaranjit Singh
     * @param int $_troubleshooterStepID The Troubleshooter step ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_troubleshooterStepID, $_attachmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TroubleshooterStepObject = false;


        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Troubleshooter step not found');

            return false;
        }

        $_SWIFT_AttachmentObject = false;

        try {
            $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Attachment not Found');

            return false;
        }

        if ($_SWIFT_AttachmentObject->GetProperty('linktypeid') != $_troubleshooterStepID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Attachment does not belong to the specified step');

            return false;
        }

        $this->XML->AddParentTag('troubleshooterattachments');
        $this->XML->AddParentTag('troubleshooterattachment');

        $this->XML->AddTag('id', $_SWIFT_AttachmentObject->GetProperty('attachmentid'));
        $this->XML->AddTag('troubleshooterstepid', $_SWIFT_AttachmentObject->GetProperty('linktypeid'));
        $this->XML->AddTag('filename', $_SWIFT_AttachmentObject->GetProperty('filename'));
        $this->XML->AddTag('filesize', $_SWIFT_AttachmentObject->GetProperty('filesize'));
        $this->XML->AddTag('filetype', $_SWIFT_AttachmentObject->GetProperty('filetype'));
        $this->XML->AddTag('dateline', $_SWIFT_AttachmentObject->GetProperty('dateline'));

        $this->XML->AddTag('contents', base64_encode((string) $_SWIFT_AttachmentObject->Get()));

        $this->XML->EndParentTag('troubleshooterattachment');
        $this->XML->EndParentTag('troubleshooterattachments');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a new Troubleshooter Attachment
     *
     * Required Fields:
     * kbarticleid
     *
     * Example Output:
     *
     * <attachments>
     *    <attachment>
     *        <id>1</id>
     *        <troubleshooterstepid>1</troubleshooterstepid>
     *        <filename>icon_chart.gif</filename>
     *        <filesize>541</filesize>
     *        <filetype>image/gif</filetype>
     *        <dateline>1296645496</dateline>
     *    </attachment>
     * </attachments>
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

        if (!isset($_POST['troubleshooterstepid']) || empty($_POST['troubleshooterstepid']) || trim($_POST['troubleshooterstepid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No Troubleshooter step ID Specified');

            return false;
        }

        $_SWIFT_TroubleshooterStepObject = false;

        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_POST['troubleshooterstepid']));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Troubleshooter step not found');

            return false;
        }

        if (!isset($_POST['filename']) || empty($_POST['filename']) || trim($_POST['filename']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No file name specified');

            return false;
        } else if (!isset($_POST['contents'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No contents specified');

            return false;
        }

        $_finalContents = base64_decode($_POST['contents']);

        $this->Load->Library('MIME:MIMEList', [], false);


        try {
            $_fileExtension     = mb_strtolower(substr($_POST['filename'], (strrpos($_POST['filename'], '.') + 1)));
            $_MIMEList          = new SWIFT_MIMEList();
            $_mimeDataContainer = $_MIMEList->Get($_fileExtension);
        } catch (SWIFT_MIME_Exception $_MIME_Exception) {
            //Do Nothing
        }
        $_contentType = 'application/octet-stream';
        if (isset($_mimeDataContainer[0]) && !empty($_mimeDataContainer[0])) {
            $_contentType = $_mimeDataContainer[0];
        }

        $_SWIFT_AttachmentStoreStringObject = new SWIFT_AttachmentStoreString($_POST['filename'], $_contentType, (string) $_finalContents);

        unset($_finalContents);

        $_SWIFT_AttachmentObject = SWIFT_Attachment::Create(SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP, $_POST['troubleshooterstepid'], $_SWIFT_AttachmentStoreStringObject);
        if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // This code will never be reached
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Attachment Creation Failed');

            return false;
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_TroubleshooterStepObject->UpdatePool('hasattachments', '1');
        $_SWIFT_TroubleshooterStepObject->ProcessUpdatePool();

        $this->ListAll($_POST['troubleshooterstepid'], $_SWIFT_AttachmentObject->GetAttachmentID());

        return false;
    }

    /**
     * Delete a Troubleshooter Attachment
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Simaranjit Singh
     * @param int $_troubleshooterStepID The Troubleshooter step ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_troubleshooterStepID, $_attachmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TroubleshooterStepObject = false;

        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Troubleshooter step not found');

            return false;
        }

        $_SWIFT_AttachmentObject = false;

        try {
            $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Attachment not Found');

            return false;
        }

        if ($_SWIFT_AttachmentObject->GetProperty('linktypeid') != $_troubleshooterStepID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Attachment does not belong to the specified article');

            return false;
        }

        SWIFT_Attachment::DeleteList(array($_attachmentID));

        return true;
    }

}

?>
