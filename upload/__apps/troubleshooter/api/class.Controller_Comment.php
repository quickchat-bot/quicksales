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

namespace Troubleshooter\Api;

use SWIFT;
use Base\Models\Comment\SWIFT_Comment;
use Base\Library\Comment\SWIFT_CommentManager;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_RESTServer;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_XML;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;
use Base\Models\User\SWIFT_User;

/**
 * The Troubleshooter Comments API Controller
 *
 * @author Simaranjit Singh
 */
class Controller_Comment extends \Controller_api implements \SWIFT_REST_Interface
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
     * @param int|bool $_troubleshooterCommentID (OPTIONAL) The Troubleshooter Comment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessTroubleshooterComments($_troubleshooterCommentID = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_commentsContainer = array();
        if (!empty($_troubleshooterCommentID)) {
            $this->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commenttype = '" . SWIFT_Comment::TYPE_TROUBLESHOOTER . "' AND comments.commentid = '" . $_troubleshooterCommentID . "'
            ORDER BY comments.commentid ASC");
        } else {
            $this->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commenttype = '" . SWIFT_Comment::TYPE_TROUBLESHOOTER . "'
            ORDER BY comments.commentid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_commentsContainer[$this->Database->Record['commentid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('troubleshooterstepcomments');
        foreach ($_commentsContainer as $_commentID => $_comment) {

            $this->XML->AddParentTag('troubleshooterstepcomment');

            $this->XML->AddTag('id', $_commentID);
            $this->XML->AddTag('troubleshooterstepid', $_comment['typeid']);
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

            $this->XML->EndParentTag('troubleshooterstepcomment');
        }
        $this->XML->EndParentTag('troubleshooterstepcomments');

        return true;
    }

    /**
     * Get a list of Troubleshooter Comments
     *
     * Example Output:
     *
     * <troubleshooterstepcomments>
     *     <troubleshooterstepcomment>
     *         <id><![CDATA[1]]></id>
     *         <troubleshooterstepid><![CDATA[1]]></troubleshooterstepid>
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
     *         <referrer><![CDATA[http://domain.com/index.php?/Troubleshooter/Step/View/1/0]]></referrer>
     *         <parenturl><![CDATA[http://domain.com/index.php?/Troubleshooter/Step/View/1/0]]></parenturl>
     *         <contents><![CDATA[cotnent]]></contents>
     *     </troubleshooterstepcomment>
     * </troubleshooterstepcomments>
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

        $this->ProcessTroubleshooterComments(0);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve a Comment
     *
     * Example Output:
     *
     * <troubleshooterstepcomments>
     *     <troubleshooterstepcomment>
     *         <id><![CDATA[1]]></id>
     *         <stepid><![CDATA[1]]></stepid>
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
     *         <referrer><![CDATA[http://domain.com/index.php?/Troubleshooter/Step/View/1/0]]></referrer>
     *         <parenturl><![CDATA[http://domain.com/index.php?/Troubleshooter/Step/View/1/0]]></parenturl>
     *         <contents><![CDATA[cotnent]]></contents>
     *     </troubleshooterstepcomment>
     * </troubleshooterstepcomments>
     *
     * @author Simaranjit Singh
     * @param int $_troubleshooterCommentID The Troubleshooter Comment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_troubleshooterCommentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessTroubleshooterComments($_troubleshooterCommentID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve comments
     *
     * <troubleshooterstepcomments>
     *    <troubleshooterstepcomment>
     *        <id><![CDATA[1]]></id>
     *        <troubleshooterstepid><![CDATA[1]]></troubleshooterstepid>
     *        <creatortype><![CDATA[2]]></creatortype>
     *        <creatorid><![CDATA[0]]></creatorid>
     *        <commenttype><![CDATA[1]]></commenttype>
     *        <fullname><![CDATA[fullname]]></fullname>
     *        <email><![CDATA[email@domain.com]]></email>
     *        <ipaddress><![CDATA[::1]]></ipaddress>
     *        <dateline><![CDATA[1335437726]]></dateline>
     *        <parentcommentid><![CDATA[0]]></parentcommentid>
     *        <commentstatus><![CDATA[2]]></commentstatus>
     *        <useragent><![CDATA[Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0]]></useragent>
     *        <referrer><![CDATA[http://domain.com/index.php?/Troubleshooter/Step/View/1/0]]></referrer>
     *        <parenturl><![CDATA[http://domain.com/index.php?/Troubleshooter/Step/View/1/0]]></parenturl>
     *        <contents><![CDATA[cotnent]]></contents>
     *    </troubleshooterstepcomment>
     * </troubleshooterstepcomments>
     *
     * @author Simaranjit Singh
     * @param bool $_troubleshooterStepID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListAll($_troubleshooterStepID = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_commentsContainer = array();
        if (!empty($_troubleshooterStepID)) {
            $this->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commenttype = '" . SWIFT_Comment::TYPE_TROUBLESHOOTER . "' AND comments.typeid = '" . (int) ($_troubleshooterStepID) . "'
            ORDER BY comments.commentid ASC");
        } else {
            $this->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commenttype = '" . SWIFT_Comment::TYPE_TROUBLESHOOTER . "'
            ORDER BY comments.commentid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_commentsContainer[$this->Database->Record['commentid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('troubleshooterstepcomments');
        foreach ($_commentsContainer as $_commentID => $_comment) {

            $this->XML->AddParentTag('troubleshooterstepcomment');

            $this->XML->AddTag('id', $_commentID);
            $this->XML->AddTag('troubleshooterstepid', $_comment['typeid']);
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

            $this->XML->EndParentTag('troubleshooterstepcomment');
        }
        $this->XML->EndParentTag('troubleshooterstepcomments');

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
        }

        if (!isset($_POST['contents']) || trim($_POST['contents']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Content field is empty');

            return false;
        } else if (!isset($_POST['troubleshooterstepid']) || trim($_POST['troubleshooterstepid']) == '' || empty($_POST['troubleshooterstepid'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Step ID field is empty');

            return false;
        } else if (!isset($_POST['creatortype']) || trim($_POST['creatortype']) == '' || empty($_POST['creatortype'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Creator type field is empty');

            return false;
        }

        $_SWIFT_TroubleshooterStepObject = false;

        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_POST['troubleshooterstepid']));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid troubleshooterstepid');

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

        $_creatorType = $_fullName = '';

        if ($_POST['creatortype'] == SWIFT_Comment::CREATOR_STAFF) {
            $_SWIFT_StaffObject = false;

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
                $_SWIFT_UserObject = false;

                try {
                    $_SWIFT_UserObject = new SWIFT_User($_creatorID);
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid User ID');
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

        $_troubleshooterCategoryID = $_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid');

        $_commentID = SWIFT_Comment::Create(SWIFT_Comment::TYPE_TROUBLESHOOTER, $_POST['troubleshooterstepid'], $_commentStatus, $_fullName, $_email, SWIFT::Get('IP'), $_POST['contents'], $_creatorType, $_creatorID, $_parentCommentID, $_SERVER['HTTP_USER_AGENT'], '', SWIFT::Get('swiftpath') . 'index.php?/Troubleshooter/Step/View/' . $_troubleshooterCategoryID . '/' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID());

        if (!$_commentID) {
            // @codeCoverageIgnoreStart
            // This code will never be reached
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Creation failed');

            return false;
            // @codeCoverageIgnoreEnd
        }

        $this->ProcessTroubleshooterComments((int) ($_commentID));

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Update the previous comment
     *
     * @author Simaranjit Singh
     * @param int $_troubleshooterCommentID The Troubleshooter Comment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_troubleshooterCommentID)
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
     * @param int $_troubleshooterCommentID The Troubleshooter Comment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_troubleshooterCommentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_Comment::DeleteList(array($_troubleshooterCommentID));

        return true;
    }

}
