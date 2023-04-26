<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-2009, QuickSupport Singapore Pte. Ltd.
 * @license    http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 * @filesource
 * ###################################
 * =======================================
 */

namespace Knowledgebase\Api;

use Controller_api;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_XML;

/**
 * The Knowledgebase Article API Controller
 *
 * @author Simaranjit Singh
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 */
class Controller_Article extends Controller_api implements SWIFT_REST_Interface
{
    /**
     * Constructor
     *
     * @author Simaranjit Singh
     * @throws \SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
        $this->Load->Library('MIME:MIMEList', false, false);
    }

    /**
     * Retrieve & Dispatch the Knowledgebase Articles
     *
     * @author Simaranjit Singh
     * @param int|bool $_knowledgebaseArticleID (OPTIONAL) The Knowledgebase Article ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessKnowledgebaseArticles($_knowledgebaseArticleID = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_articleContainer = array();

        if (!empty($_knowledgebaseArticleID)) {
            $this->Database->Query("SELECT kbarticles.*, kbarticledata.contents, kbarticledata.contentstext
                FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
                LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticles.kbarticleid = kbarticledata.kbarticleid)
                WHERE kbarticles.kbarticleid = '" . ($_knowledgebaseArticleID) . "'");
        } else {
            $this->Database->Query("SELECT kbarticles.*, kbarticledata.contents, kbarticledata.contentstext
                FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
                LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticles.kbarticleid = kbarticledata.kbarticleid)
                ORDER BY kbarticleid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_articleContainer[$this->Database->Record['kbarticleid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('kbarticles');

        foreach ($_articleContainer as $_knowledgebaseArticleID => $_article) {
            $this->XML->AddParentTag('kbarticle');
            $this->XML->AddTag('kbarticleid', $_article['kbarticleid']);
            $this->XML->AddTag('contents', $_article['contents']);
            $this->XML->AddTag('contentstext', $_article['contentstext']);

            //For article categorires
            $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseArticleLink::RetrieveLinkIDListOnArticle($_knowledgebaseArticleID, SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY);
            $this->XML->AddParentTag('categories');
            foreach ($_knowledgebaseCategoryIDList as $_knowledgebaseCategoryID) {
                $this->XML->AddTag('categoryid', $_knowledgebaseCategoryID);
            }
            $this->XML->EndParentTag('categories');

            $this->XML->AddTag('creator', $_article['creator']);
            $this->XML->AddTag('creatorid', $_article['creatorid']);
            $this->XML->AddTag('author', $_article['author']);
            $this->XML->AddTag('email', $_article['email']);
            $this->XML->AddTag('subject', $_article['subject']);
            $this->XML->AddTag('seosubject', $_article['seosubject']);
            $this->XML->AddTag('isedited', $_article['isedited']);
            $this->XML->AddTag('editeddateline', $_article['editeddateline']);
            $this->XML->AddTag('editedstaffid', $_article['editedstaffid']);
            $this->XML->AddTag('views', $_article['views']);
            $this->XML->AddTag('isfeatured', $_article['isfeatured']);
            $this->XML->AddTag('allowcomments', $_article['allowcomments']);
            $this->XML->AddTag('totalcomments', $_article['totalcomments']);
            $this->XML->AddTag('hasattachments', $_article['hasattachments']);

            $this->XML->AddParentTag('attachments');

            // Attachment Logic
            $_attachmentContainer = array();

            if ($_article['hasattachments'] == '1') {
                $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_KBARTICLE, $_knowledgebaseArticleID);

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
                    $this->XML->AddTag('link', SWIFT::Get('basename') . '/Knowledgebase/Article/GetAttachment/' . $_knowledgebaseArticleID . '/' . $_attachment['attachmentid']);

                    $this->XML->EndParentTag('attachment');
                }
            }

            $this->XML->EndParentTag('attachments');

            $this->XML->AddTag('dateline', $_article['dateline']);
            $this->XML->AddTag('articlestatus', $_article['articlestatus']);
            $this->XML->AddTag('articlerating', $_article['articlerating']);
            $this->XML->AddTag('ratinghits', $_article['ratinghits']);
            $this->XML->AddTag('ratingcount', $_article['ratingcount']);
            $this->XML->EndParentTag('kbarticle');
        }
        $this->XML->EndParentTag('kbarticles');

        return true;
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

        $this->ProcessKnowledgebaseArticles();

        $this->XML->EchoXML();

        return true;
    }

    /**
     * List all Articles
     *
     * @author Simaranjit Singh
     * @author Saloni Dhall <saloni.dhall@opencart.com.vn>
     *
     * @param int     $_knowledgebaseCategoryID
     * @param int     $_rowsPerPage              (OPTIONAL)
     * @param int     $_rowOffset                (OPTIONAL)
     * @param string  $_sortField                (OPTIONAL)
     * @param string  $_sortOrder                (OPTIONAL)
     *
     * @throws SWIFT_Exception
     * @return bool
     *
     */
    public function ListAll($_knowledgebaseCategoryID, $_rowsPerPage = -1, $_rowOffset = 0, $_sortField = null, $_sortOrder = self::SORT_ASC)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_rowsPerPage                   = IIF(!($_rowsPerPage), -1, ($_rowsPerPage));
        $_knowledgebaseArticleContainer = SWIFT_KnowledgebaseArticle::Retrieve(array($_knowledgebaseCategoryID), $_rowsPerPage, ($_rowOffset),
                                                                               $_sortField, $_sortOrder);

        $this->XML->AddParentTag('kbarticles');

        foreach($_knowledgebaseArticleContainer as $_knowledgebaseArticleID => $_article) {

            $this->XML->AddParentTag('kbarticle');
            $this->XML->AddTag('kbarticleid', $_article['kbarticleid']);
            $this->XML->AddTag('contents', $_article['contents']);
            $this->XML->AddTag('contentstext', $_article['contentstext']);

            //For article categorires
            $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseArticleLink::RetrieveLinkIDListOnArticle($_article['kbarticleid'], SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY);

            $this->XML->AddParentTag('categories');
            foreach ($_knowledgebaseCategoryIDList as $_knowledgebaseCategoryID) {
                $this->XML->AddTag('categoryid', $_knowledgebaseCategoryID);
            }
            $this->XML->EndParentTag('categories');

            $this->XML->AddTag('creator', $_article['creator']);
            $this->XML->AddTag('creatorid', $_article['creatorid']);
            $this->XML->AddTag('author', $_article['author']);
            $this->XML->AddTag('email', $_article['email']);
            $this->XML->AddTag('subject', $_article['subject']);
            $this->XML->AddTag('seosubject', $_article['seosubject']);
            $this->XML->AddTag('isedited', $_article['isedited']);
            $this->XML->AddTag('editeddateline', $_article['editeddateline']);
            $this->XML->AddTag('editedstaffid', $_article['editedstaffid']);
            $this->XML->AddTag('views', $_article['views']);
            $this->XML->AddTag('isfeatured', $_article['isfeatured']);
            $this->XML->AddTag('allowcomments', $_article['allowcomments']);
            $this->XML->AddTag('totalcomments', $_article['totalcomments']);
            $this->XML->AddTag('hasattachments', $_article['hasattachments']);

            $this->XML->AddParentTag('attachments');

            // Attachment Logic
            $_attachmentContainer = array();

            if ($_article['hasattachments'] == '1') {
                $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_KBARTICLE, $_article['kbarticleid']);

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
                    $this->XML->AddTag('link', SWIFT::Get('basename') . '/Knowledgebase/Article/GetAttachment/' . $_article['kbarticleid'] . '/' . $_attachment['attachmentid']);

                    $this->XML->EndParentTag('attachment');
                }
            }

            $this->XML->EndParentTag('attachments');

            $this->XML->AddTag('dateline', $_article['dateline']);
            $this->XML->AddTag('articlestatus', $_article['articlestatus']);
            $this->XML->AddTag('articlerating', $_article['articlerating']);
            $this->XML->AddTag('ratinghits', $_article['ratinghits']);
            $this->XML->AddTag('ratingcount', $_article['ratingcount']);
            $this->XML->EndParentTag('kbarticle');
        }

        $this->XML->EndParentTag('kbarticles');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve an Article
     *
     * Example Output:
     *
     * <kbarticles>
     *         <article>
     *         <kbarticleid>1</kbarticleid>
     *         <contents>These are the contents of article created from API.</contents>
     *         <contentstext>These are the contents of article created from API.</contentstext>
     *         <creator>2</creator>
     *         <creatorid>2</creatorid>
     *         <author/>
     *         <email/>
     *         <subject>apisubject</subject>
     *         <isfeatured>0</isfeatured>
     *         <allowcomments>1</allowcomments>
     *         <articlestatus>1</articlestatus>
     *         </article>
     * </kbarticles>
     *
     * @author Simaranjit Singh
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_knowledgebaseArticleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessKnowledgebaseArticles(($_knowledgebaseArticleID));

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create an article
     *
     * Required Fields:
     * creatorType
     * creatorID
     * author
     * email
     * articleStatus
     * subject
     * articleContents
     *
     * Example Output:
     *
     * <kbarticles>
     *         <article>
     *         <kbarticleid>1</kbarticleid>
     *         <contents>These are the contents of article created from API.</contents>
     *         <contentstext>These are the contents of article created from API.</contentstext>
     *         <creator>2</creator>
     *         <creatorid>2</creatorid>
     *         <author/>
     *         <email/>
     *         <subject>apisubject</subject>
     *         <isfeatured>0</isfeatured>
     *         <allowcomments>1</allowcomments>
     *         <articlestatus>1</articlestatus>
     *         </article>
     * </kbarticles>
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

        $_knowledgebaseCategoryCache = $this->Cache->Get('kbcategorycache');

        if (!isset($_POST['subject']) || trim($_POST['subject']) == '' || empty($_POST['subject'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Subject field is empty');

            return false;
        }

        if (!isset($_POST['contents']) || trim($_POST['contents']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Content field is empty');

            return false;
        }

        if (!isset($_POST['creatorid']) || trim($_POST['creatorid']) == '' || empty($_POST['creatorid'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'creatorid is empty');

            return false;
        }

        $_creatorID = ($_POST['creatorid']);

        try {
            $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_creatorID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Staff ID');

            return false;
        }

        $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PENDINGAPPROVAL;
        if (isset($_POST['articlestatus']) && !empty($_POST['articlestatus']) && ($_POST['articlestatus'] == SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED || $_POST['articlestatus'] == SWIFT_KnowledgebaseArticle::STATUS_DRAFT)) {
            if ($_SWIFT_StaffObject->GetPermission('staff_kbcaninsertpublishedarticles') != '0' && $_POST['articlestatus'] == SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED)    {

                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED;
            } else {
                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_DRAFT;
            }
        }

        $_seoSubject = '';
        if (isset($_POST['seosubject']) && !empty($_POST['seosubject'])) {
            $_seoSubject = $_POST['seosubject'];
        }

        $_isFeatured = false;
        if (isset($_POST['isfeatured']) && !empty($_POST['isfeatured'])) {
            $_isFeatured = ($_POST['isfeatured']);
        }

        $_allowComments = true;
        if (isset($_POST['allowcomments']) && !empty($_POST['allowcomments'])) {
            $_allowComments = ($_POST['allowcomments']);
        }

        $_categoryID = array('0');
        $_knowledgebaseCategoryIDList = array();
        if (isset($_POST['categoryid']) && trim($_POST['categoryid']) != '') {
            $_categoryID = explode(',', $_POST['categoryid']);
            //I need to make sure that user is not enring any invalid or non existing id
            $_knowledgebaseCategoryIDList = array_intersect(array_keys($_knowledgebaseCategoryCache), $_categoryID);
        }

        if (count($_knowledgebaseCategoryIDList) == 0) {
            $_knowledgebaseCategoryIDList = $_categoryID;
        }

        $_SWIFT_KnowledgebaseArticleObject = SWIFT_KnowledgebaseArticle::Create(SWIFT_KnowledgebaseArticle::CREATOR_STAFF, $_SWIFT_StaffObject->GetStaffID(), $_SWIFT_StaffObject->GetProperty('fullname'), $_SWIFT_StaffObject->GetProperty('email'), $_articleStatus, $_POST['subject'], $_seoSubject, $_POST['contents'], $_isFeatured, $_allowComments, $_knowledgebaseCategoryIDList);

        if (!$_SWIFT_KnowledgebaseArticleObject) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Article Creation Failed');

            return false;
            // @codeCoverageIgnoreEnd
        }

        $this->ProcessKnowledgebaseArticles($_SWIFT_KnowledgebaseArticleObject);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Update Knowledgebase Article
     *
     * Required Fields:
     * subject
     * articleContents
     * staffID
     *
     * Example Output:
     *
     * <kbarticles>
     *         <article>
     *         <kbarticleid>1</kbarticleid>
     *         <contents>These are the contents of article created from API.</contents>
     *         <contentstext>These are the contents of article created from API.</contentstext>
     *         <creator>2</creator>
     *         <creatorid>2</creatorid>
     *         <author/>
     *         <email/>
     *         <subject>apisubject</subject>
     *         <isfeatured>0</isfeatured>
     *         <allowcomments>1</allowcomments>
     *         <articlestatus>1</articlestatus>
     *         </article>
     * </kbarticles>
     *
     * @author Simaranjit Singh
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_knowledgebaseArticleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['editedstaffid']) || trim($_POST['editedstaffid']) == '' || empty($_POST['editedstaffid'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'editedstaffid is empty');

            return false;
        }

        $_knowledgebaseCategoryCache = $this->Cache->Get('kbcategorycache');

        $_SWIFT_KnowledgebaseArticleObject = false;
        try {
            $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_knowledgebaseArticleID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid article id');

            return false;
        }

        $_subject = $_SWIFT_KnowledgebaseArticleObject->GetProperty('subject');
        if (isset($_POST['subject']) && trim($_POST['subject']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'subject is empty');

            return false;
        }

        if (isset($_POST['subject'])) {
            $_subject = $_POST['subject'];
        }

        $_seoSubject = '';
        if (isset($_POST['seosubject']) && !empty($_POST['seosubject'])) {
            $_seoSubject = $_POST['seosubject'];
        }

        $_contents = $_SWIFT_KnowledgebaseArticleObject->GetProperty('contents');
        if (isset($_POST['contents']) && trim($_POST['contents']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'contents is empty');

            return false;
        }

        if (isset($_POST['contents'])) {
            $_contents = $_POST['contents'];
        }

        try {
            $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_POST['editedstaffid']));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Staff ID');

            return false;
        }

        $_isFeatured = '';
        if (isset($_POST['isfeatured']) && !empty($_POST['isfeatured'])) {
            $_isFeatured = ($_POST['isfeatured']);
        }

        $_allowComments = '';
        if (isset($_POST['allowcomments']) && !empty($_POST['allowcomments'])) {
            $_allowComments = ($_POST['allowcomments']);
        }

        $_categoryID = array('0');
        $_knowledgebaseCategoryIDList = array();
        if (isset($_POST['categoryid']) && trim($_POST['categoryid']) != '') {
            $_categoryID = explode(',', $_POST['categoryid']);
            //I need to make sure that user is not enring any invalid or non existing id
            $_knowledgebaseCategoryIDList = array_intersect(array_keys($_knowledgebaseCategoryCache), $_categoryID);
        }

        if (count($_knowledgebaseCategoryIDList) == 0) {
            $_knowledgebaseCategoryIDList = $_categoryID;
        }

        $_SWIFT_KnowledgebaseArticleObject->Update($_SWIFT_StaffObject->GetStaffID(), $_subject, $_seoSubject, $_contents, $_isFeatured, $_allowComments, $_knowledgebaseCategoryIDList);

        $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PENDINGAPPROVAL;
        if (isset($_POST['articlestatus']) && !empty($_POST['articlestatus']) && ($_POST['articlestatus'] == SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED || $_POST['articlestatus'] == SWIFT_KnowledgebaseArticle::STATUS_DRAFT)) {
            if ($_SWIFT_StaffObject->GetPermission('staff_kbcaninsertpublishedarticles') != '0' && $_POST['articlestatus'] == SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED)    {

                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED;
            } else {
                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_DRAFT;
            }
        }

        $_SWIFT_KnowledgebaseArticleObject->UpdateStatus($_articleStatus);

        $this->ProcessKnowledgebaseArticles($_knowledgebaseArticleID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Delete Knowledgebase article
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Simaranjit Singh
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_knowledgebaseArticleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_KnowledgebaseArticleObject = array();

        try {
            $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_knowledgebaseArticleID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid article id');

            return false;
        }

        SWIFT_KnowledgebaseArticle::DeleteList(array($_knowledgebaseArticleID));
        return true;
    }

    /**
     * Get Total number of articles of a category
     *
     * @author Amarjeet Kaur
     *
     * @param int $_knowledgebaseCategoryID
     *
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetArticleCount($_knowledgebaseCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (empty($_knowledgebaseCategoryID))
        {
            return false;
        }

        $_articleLinksContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalarticles FROM " . TABLE_PREFIX . SWIFT_KnowledgebaseArticleLink::TABLE_NAME . "
                                                               WHERE linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "'
                                                                  AND linktypeid = " . ($_knowledgebaseCategoryID));

        $this->XML->AddParentTag('kbarticles');

        $this->XML->AddTag('totalarticles', $_articleLinksContainer['totalarticles']);

        $this->XML->EndParentTag('kbarticles');

        $this->XML->EchoXML();

        return true;
    }
}
