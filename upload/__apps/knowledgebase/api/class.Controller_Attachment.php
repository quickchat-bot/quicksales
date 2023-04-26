<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    Kayako Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-2009, Kayako Singapore Pte. Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace Knowledgebase\Api;

use Controller_api;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreString;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use SWIFT_XML;

/**
 * The Knowledgebase Attachment API Controller
 *
 * @author Simaranjit Singh
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 */
class Controller_Attachment extends Controller_api implements SWIFT_REST_Interface
{

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
     * Get a list of attachments for the given Article
     *
     * Example Output:
     *
     * <attachments>
     *     <attachment>
     *         <id>1</id>
     *         <kbarticleid>1</kbarticleid>
     *         <filename>icon_chart.gif</filename>
     *         <filesize>541</filesize>
     *         <filetype>image/gif</filetype>
     *         <dateline>1296645496</dateline>
     *     </attachment>
     * </attachments>
     *
     * @author Simaranjit Singh
     * @param int $_kbArticleID The Knowledgebase article ID
     * @param int $_attachmentID (OPTIONAL) To filter result set to a single attachment id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListAll($_kbArticleID, $_attachmentID = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_KnowledgebaseArticleObject = false;

        try {
            $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_kbArticleID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Article not found');

            return false;
        }

        $_querySuffix = '';
        if (!empty($_attachmentID)) {
            $_querySuffix .= " AND attachmentid = '" . ($_attachmentID) . "'";
        }

        $_attachmentContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments
            WHERE linktype = '" . SWIFT_Attachment::LINKTYPE_KBARTICLE . "' AND linktypeid = '" . ($_kbArticleID) . "'" . $_querySuffix);
        while ($this->Database->NextRecord()) {
            $_attachmentContainer[$this->Database->Record['attachmentid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('kbattachments');
        foreach ($_attachmentContainer as $_attachmentID => $_attachment) {
            $this->XML->AddParentTag('kbattachment');
            $this->XML->AddTag('id', $_attachment['attachmentid']);
            $this->XML->AddTag('kbarticleid', $_attachment['linktypeid']);
            $this->XML->AddTag('filename', $_attachment['filename']);
            $this->XML->AddTag('filesize', $_attachment['filesize']);
            $this->XML->AddTag('filetype', $_attachment['filetype']);
            $this->XML->AddTag('dateline', $_attachment['dateline']);

            //Attachments logic
            $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);

            $this->XML->AddTag('contents', base64_encode($_SWIFT_AttachmentObject->Get()));

            $this->XML->EndParentTag('kbattachment');
        }
        $this->XML->EndParentTag('kbattachments');

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
     *         <kbarticleid>1</kbarticleid>
     *         <filename>icon_chart.gif</filename>
     *         <filesize>541</filesize>
     *         <filetype>image/gif</filetype>
     *         <dateline>1296645496</dateline>
     *         <contents><![CDATA[iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK==]]></contents>
     * </attachment>
     *
     *
     * @author Simaranjit Singh
     * @param int $_kbArticleID The Article ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_kbArticleID, $_attachmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_KnowledgebaseArticleObject = false;

        try {
            $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_kbArticleID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Article not found');

            return false;
        }

        $_SWIFT_AttachmentObject = false;

        try {
            $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Attachment not Found');

            return false;
        }

        if ($_SWIFT_AttachmentObject->GetProperty('linktypeid') != $_kbArticleID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Attachment does not belong to the specified article');

            return false;
        }

        $this->XML->AddParentTag('kbattachments');
        $this->XML->AddParentTag('kbattachment');

        $this->XML->AddTag('id', $_SWIFT_AttachmentObject->GetProperty('attachmentid'));
        $this->XML->AddTag('kbarticleid', $_SWIFT_AttachmentObject->GetProperty('linktypeid'));
        $this->XML->AddTag('filename', $_SWIFT_AttachmentObject->GetProperty('filename'));
        $this->XML->AddTag('filesize', $_SWIFT_AttachmentObject->GetProperty('filesize'));
        $this->XML->AddTag('filetype', $_SWIFT_AttachmentObject->GetProperty('filetype'));
        $this->XML->AddTag('dateline', $_SWIFT_AttachmentObject->GetProperty('dateline'));

        $this->XML->AddTag('contents', base64_encode($_SWIFT_AttachmentObject->Get()));

        $this->XML->EndParentTag('kbattachment');
        $this->XML->EndParentTag('kbattachments');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a new Knowledgebase Attachment
     *
     * Required Fields:
     * kbarticleid
     *
     * Example Output:
     *
     * <attachments>
     *     <attachment>
     *         <id>1</id>
     *         <kbarticleid>1</kbarticleid>
     *         <filename>icon_chart.gif</filename>
     *         <filesize>541</filesize>
     *         <filetype>image/gif</filetype>
     *         <dateline>1296645496</dateline>
     *     </attachment>
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

        if (!isset($_POST['kbarticleid']) || empty($_POST['kbarticleid']) || trim($_POST['kbarticleid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No Knowledgebase Article ID Specified');

            return false;
        }

        $_SWIFT_KnowledgebaseArticleObject = false;

        try {
            $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_POST['kbarticleid']));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Article not found');

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


        $this->Load->Library('MIME:MIMEList', false, false);
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

        $_SWIFT_AttachmentStoreStringObject = new SWIFT_AttachmentStoreString($_POST['filename'], $_contentType, $_finalContents);

        unset($_finalContents);

        $_SWIFT_AttachmentObject = SWIFT_Attachment::Create(SWIFT_Attachment::LINKTYPE_KBARTICLE, $_POST['kbarticleid'], $_SWIFT_AttachmentStoreStringObject);

        if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Attachment Creation Failed');

            return false;
            // @codeCoverageIgnoreEnd
        }

        $this->ListAll($_POST['kbarticleid'], $_SWIFT_AttachmentObject->GetAttachmentID());

        return false;
    }

    /**
     * Delete a Knowledgebase Attachment
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Simaranjit Singh
     * @param int $_kbArticleID The Article ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_kbArticleID, $_attachmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_KnowledgebaseArticleObject = false;

        try {
            $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_kbArticleID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Article not found');

            return false;
        }

        $_SWIFT_AttachmentObject = false;

        try {
            $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Attachment not Found');

            return false;
        }

        if ($_SWIFT_AttachmentObject->GetProperty('linktypeid') != $_kbArticleID) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Attachment does not belong to the specified article');

            return false;
        }

        SWIFT_Attachment::DeleteList(array($_attachmentID));

        return true;
    }

}
