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

namespace News\Api;

use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use Base\Models\Comment\SWIFT_Comment;
use Base\Library\Comment\SWIFT_CommentManager;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use Controller_api;
use SWIFT_RESTServer;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use SWIFT_XML;

/**
 * The News Comments API Controller
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 * @author Simaranjit Singh
 */
class Controller_Comment extends Controller_api implements SWIFT_REST_Interface
{

    /**
     * Constructor
     *
     * @author Simaranjit Singh
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');
        $this->Load->Model('User:User', [], false, false, 'base');
    }

    /**
     * Retrieve & Dispatch the Comments
     *
     * @author Simaranjit Singh
     * @param int $_newsCommentID (OPTIONAL) The News Comment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessNewsComments($_newsCommentID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_commentsContainer = array();

        if (!empty($_newsCommentID)) {
            $this->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commenttype = '" . SWIFT_Comment::TYPE_NEWS . "' AND comments.commentid = '" .  ($_newsCommentID) . "'
            ORDER BY comments.commentid ASC");
        } else {
            $this->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commenttype = '" . SWIFT_Comment::TYPE_NEWS . "'
            ORDER BY comments.commentid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_commentsContainer[$this->Database->Record['commentid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('newsitemcomments');

        foreach ($_commentsContainer as $_commentID => $_comment) {

            $this->XML->AddParentTag('newsitemcomment');

            $this->XML->AddTag('id', $_commentID);
            $this->XML->AddTag('newsitemid', $_comment['typeid']);
            $this->XML->AddTag('creatortype', $_comment['creatortype']);
            $this->XML->AddTag('creatorid', $_comment['creatorid']);
            $this->XML->AddTag('fullname', $_comment['fullname']);
            $this->XML->AddTag('email', $_comment['email']);
            $this->XML->AddTag('ipaddress', $_comment['ipaddress']);
            $this->XML->AddTag('dateline', $_comment['dateline']);
            $this->XML->AddTag('parentcommentid', $_comment['parentcommentid']);
            $this->XML->AddTag('commentstatus', $_comment['commentstatus']);
            $this->XML->AddTag('useragent', $_comment['useragent']);
            $this->XML->AddTag('referrer', $_comment['referrer']);
            $this->XML->AddTag('parenturl', $_comment['parenturl']);
            $this->XML->AddTag('contents', $_comment['contents']);

            $this->XML->EndParentTag('newsitemcomment');
        }

        $this->XML->EndParentTag('newsitemcomments');

        return true;
    }

    /**
     * Get a list of News Comments
     *
     * Example Output:
     *
     * <newsitemcomments>
     *     <newsitemcomment>
     *         <id><![CDATA[1]]></id>
     *         <newsitemid><![CDATA[1]]></newsitemid>
     *         <creatortype><![CDATA[2]]></creatortype>
     *         <creatorid><![CDATA[0]]></creatorid>
     *         <commenttype><![CDATA[1]]></commenttype>
     *         <fullname><![CDATA[fullname]]></fullname>
     *         <email><![CDATA[email@domain.com]]></email>
     *         <ipaddress><![CDATA[::1]]></ipaddress>
     *         <dateline><![CDATA[1335437726]]></dateline>
     *         <parentcommentid><![CDATA[0]]></parentcommentid>
     *         <commentstatus><![CDATA[2]]></commentstatus>
     *         <useragent><![CDATA[Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0]]></useragent>
     *         <referrer><![CDATA[http://domain.com/index.php?/News/NewsItem/View/1/0/assign]]></referrer>
     *         <parenturl><![CDATA[http://domain.com/index.php?/News/NewsItem/View/1]]></parenturl>
     *         <contents><![CDATA[cotnent]]></contents>
     *     </newsitemcomment>
     * </newsitemcomments>
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessNewsComments(0);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve a Comment
     *
     * Example Output:
     *
     * <newsitemcomments>
     *     <newsitemcomment>
     *         <id><![CDATA[1]]></id>
     *         <newsitemid><![CDATA[1]]></newsitemid>
     *         <creatortype><![CDATA[2]]></creatortype>
     *         <creatorid><![CDATA[0]]></creatorid>
     *         <commenttype><![CDATA[1]]></commenttype>
     *         <fullname><![CDATA[fullname]]></fullname>
     *         <email><![CDATA[email@domain.com]]></email>
     *         <ipaddress><![CDATA[::1]]></ipaddress>
     *         <dateline><![CDATA[1335437726]]></dateline>
     *         <parentcommentid><![CDATA[0]]></parentcommentid>
     *         <commentstatus><![CDATA[2]]></commentstatus>
     *         <useragent><![CDATA[Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0]]></useragent>
     *         <referrer><![CDATA[http://domain.com/index.php?/News/NewsItem/View/1/0/assign]]></referrer>
     *         <parenturl><![CDATA[http://domain.com/index.php?/News/NewsItem/View/1]]></parenturl>
     *         <contents><![CDATA[cotnent]]></contents>
     *     </newsitemcomment>
     * </newsitemcomments>
     *
     * @author Simaranjit Singh
     * @param int $_newsCommentID The News Comment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_newsCommentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessNewsComments($_newsCommentID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve comments
     *
     * <newsitemcomments>
     *     <newsitemcomment>
     *         <id><![CDATA[1]]></id>
     *         <newsitemid><![CDATA[1]]></newsitemid>
     *         <creatortype><![CDATA[2]]></creatortype>
     *         <creatorid><![CDATA[0]]></creatorid>
     *         <commenttype><![CDATA[1]]></commenttype>
     *         <fullname><![CDATA[fullname]]></fullname>
     *         <email><![CDATA[email@domain.com]]></email>
     *         <ipaddress><![CDATA[::1]]></ipaddress>
     *         <dateline><![CDATA[1335437726]]></dateline>
     *         <parentcommentid><![CDATA[0]]></parentcommentid>
     *         <commentstatus><![CDATA[2]]></commentstatus>
     *         <useragent><![CDATA[Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0]]></useragent>
     *         <referrer><![CDATA[http://domain.com/index.php?/News/NewsItem/View/1/0/assign]]></referrer>
     *         <parenturl><![CDATA[http://domain.com/index.php?/News/NewsItem/View/1]]></parenturl>
     *         <contents><![CDATA[cotnent]]></contents>
     *     </newsitemcomment>
     * </newsitemcomments>
     *
     * @author Simaranjit Singh
     * @param int $_newsItemID News Item ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListAll($_newsItemID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_commentsContainer = array();

        if (!empty($_newsItemID)) {
            $this->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commenttype = '" . SWIFT_Comment::TYPE_NEWS . "' AND comments.typeid = '" . (int) ($_newsItemID) . "'
            ORDER BY comments.commentid ASC");
        } else {
            $this->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commenttype = '" . SWIFT_Comment::TYPE_NEWS . "'
            ORDER BY comments.commentid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_commentsContainer[$this->Database->Record['commentid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('newsitemcomments');

        foreach ($_commentsContainer as $_commentID => $_comment) {

            $this->XML->AddParentTag('newsitemcomment');

            $this->XML->AddTag('id', $_commentID);
            $this->XML->AddTag('newsitemid', $_comment['typeid']);
            $this->XML->AddTag('creatortype', $_comment['creatortype']);
            $this->XML->AddTag('creatorid', $_comment['creatorid']);
            $this->XML->AddTag('fullname', $_comment['fullname']);
            $this->XML->AddTag('email', $_comment['email']);
            $this->XML->AddTag('ipaddress', $_comment['ipaddress']);
            $this->XML->AddTag('dateline', $_comment['dateline']);
            $this->XML->AddTag('parentcommentid', $_comment['parentcommentid']);
            $this->XML->AddTag('commentstatus', $_comment['commentstatus']);
            $this->XML->AddTag('useragent', $_comment['useragent']);
            $this->XML->AddTag('referrer', $_comment['referrer']);
            $this->XML->AddTag('parenturl', $_comment['parenturl']);
            $this->XML->AddTag('contents', $_comment['contents']);

            $this->XML->EndParentTag('newsitemcomment');
        }

        $this->XML->EndParentTag('newsitemcomments');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Post a new comment
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!isset($_POST['contents']) || trim($_POST['contents']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Content field is empty');

            return false;
        } else if (!isset($_POST['newsitemid']) || trim($_POST['newsitemid']) == '' || empty($_POST['newsitemid'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'News Item ID field is empty');

            return false;
        } else if (!isset($_POST['creatortype']) || trim($_POST['creatortype']) == '' || empty($_POST['creatortype'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Creator type field is empty');

            return false;
        }

        $_SWIFT_NewsItemObject = false;

        try {
            $_SWIFT_NewsItemObject = new SWIFT_NewsItem((int) ($_POST['newsitemid']));

        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid newsitemid');

            return false;
        }

        $_creatorID = 0;
        if (isset($_POST['creatorid']) && trim($_POST['creatorid']) != '' && !empty($_POST['creatorid'])) {
            $_creatorID = $_POST['creatorid'];
        }

        $_email = '';
        if (isset($_POST['email']) && trim($_POST['email']) != '') {
            $_email = $_POST['email'];
        }

        $_SWIFT_StaffObject = $_creatorType = $_fullName = $_SWIFT_UserObject = false;

        if ($_POST['creatortype'] == SWIFT_Comment::CREATOR_STAFF) {
            try {
                $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_creatorID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid staff ID');

                return false;
            }

            $_creatorType = SWIFT_Comment::CREATOR_STAFF;
            $_fullName = $_SWIFT_StaffObject->GetProperty('fullname');

        } else if ($_POST['creatortype'] == SWIFT_Comment::CREATOR_USER) {

            if ($_creatorID != 0) {
                try {
                    $_SWIFT_UserObject = new SWIFT_User($_creatorID);
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid user ID');
                    return false;
                }

                $_creatorType = SWIFT_Comment::CREATOR_USER;
                $_fullName = $_SWIFT_UserObject->GetProperty('fullname');

            } else if (isset($_POST['fullname']) && trim($_POST['fullname']) != '') {
                $_creatorType = SWIFT_Comment::CREATOR_USER;
                $_fullName = $_POST['fullname'];

            } else if (!isset($_POST['fullname']) || trim($_POST['fullname']) == '') {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Full name field is empty');

                return false;
            }
        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid creator type');

            return false;
        }

        $_parentCommentID = 0;
        if (isset($_POST['parentcommentid']) && trim($_POST['parentcommentid']) != '') {
            $_parentCommentID = $_POST['parentcommentid'];
        }

        $_commentStatus = false;
        if ($_creatorID != 0 && ($_SWIFT->Settings->Get('security_autoapprovecomments') == '1' || $_creatorType == SWIFT_Comment::CREATOR_STAFF)) {
            $_commentStatus = SWIFT_Comment::STATUS_APPROVED;
        }

        if ($_commentStatus == false) {
            $_commentStatus = SWIFT_CommentManager::CheckCommentContents($_fullName, $_email, $_POST['contents']);
        }

        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = '';
        }

        $_commentID = SWIFT_Comment::Create(SWIFT_Comment::TYPE_NEWS, $_POST['newsitemid'], $_commentStatus, $_fullName, $_email, SWIFT::Get('IP'), $_POST['contents'], $_creatorType, $_creatorID, $_parentCommentID, $_SERVER['HTTP_USER_AGENT'], '', SWIFT::Get('swiftpath') . 'index.php?/News/NewsItem/View/' . $_SWIFT_NewsItemObject->GetNewsItemID());

        if (!$_commentID) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Creation failed');

            return false;
            // @codeCoverageIgnoreEnd
        }

        $this->ProcessNewsComments((int) ($_commentID));

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Update the previous comment
     *
     * @author Simaranjit Singh
     * @param int $_newsCommentID The News Comment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_newsCommentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        echo 'Put not implemented';

        return false;
    }

    /**
     * Delete comment
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Simaranjit Singh
     * @param int $_newsCommentID The News Comment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_newsCommentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        SWIFT_Comment::DeleteList(array($_newsCommentID));

        return true;
    }

}
