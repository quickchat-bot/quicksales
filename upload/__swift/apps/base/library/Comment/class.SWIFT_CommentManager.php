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

namespace Base\Library\Comment;

use Base\Library\Captcha\SWIFT_CaptchaManager;
use Base\Models\Comment\SWIFT_Comment;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use SWIFT;
use SWIFT_Akismet;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Comment Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_CommentManager extends SWIFT_Library
{
    private $isSaas;
    private $internal_ut;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->isSaas = preg_match('/.+saas.+/', strtolower(SWIFT::Get('licensepackage')));
        $this->internal_ut = $this->Settings->Get('internal_ut') ?: false;
    }

    /**
     * Load the Staff CP
     *
     * @author Varun Shoor
     * @param string $_commentApp The Comment App
     * @param mixed $_commentType The Comment Type
     * @param int $_commentItemID The Comment Item ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function LoadStaffCP($_commentApp, $_commentType, $_commentItemID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Template->Assign('_commentsApp', $_commentApp);
        $this->Template->Assign('_commentsItemID', $_commentItemID);

        /**
         * ---------------------------------------------
         * Load Existing Comments
         * ---------------------------------------------
         */
        $_commentContainer = SWIFT_Comment::Retrieve($_commentType, $_commentItemID, SWIFT_Comment::STATUS_APPROVED);
        $this->Template->Assign('_commentCount', count($_commentContainer));
        $this->Template->Assign('_commentContainer', $_commentContainer);

        $_renderHTML = $this->Template->Get('comments');

        return $_renderHTML;
    }

    /**
     * Process the POST Data for Staff
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @param mixed $_commentType The Comment Type
     * @param int $_commentItemID The Comment Item ID
     * @param string $_parentURL (OPTIONAL) The Parent URL
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessPOSTStaff($_SWIFT_StaffObject, $_commentType, $_commentItemID, $_parentURL = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['comments']) || trim($_POST['comments']) == '') {
            return false;
        }

        $_parentCommentID = 0;
        if (isset($_POST['parentcommentid'])) {
            $_parentCommentID = (int)($_POST['parentcommentid']);
        }

        SWIFT_Comment::Create($_commentType, $_commentItemID, SWIFT_Comment::STATUS_APPROVED, $_SWIFT_StaffObject->GetProperty('fullname'), $_SWIFT_StaffObject->GetProperty('email'),
            SWIFT::Get('IP'), $_POST['comments'], SWIFT_Comment::CREATOR_STAFF, $_SWIFT_StaffObject->GetStaffID(), $_parentCommentID, $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER'],
            $_parentURL);

        return true;
    }

    /**
     * Load the Support Center Logic
     *
     * @author Varun Shoor
     * @param string $_commentApp The Comment App
     * @param mixed $_commentType The Comment Type
     * @param int $_commentItemID The Comment Item ID
     * @param string $_commentExtendedURL (OPTIONAL) The Extended URL for Form Post
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function LoadSupportCenter($_commentApp, $_commentType, $_commentItemID, $_commentExtendedURL = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Template->Assign('_canCaptcha', false);
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('security_commentscaptcha') == '1') && !$_SWIFT->Session->IsLoggedIn()) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded()) {
                $_captchaHTML = $_CaptchaObject->GetHTML();
                if ($_captchaHTML) {
                    $isRecaptcha = false;
                    if(SWIFT::GetInstance()->Settings->Get('security_captchatype') == SWIFT_CaptchaManager::TYPE_RECAPTCHA) {
                        $isRecaptcha = true;
                    }
                    $this->Template->Assign('_isRecaptcha', $isRecaptcha);
                    $this->Template->Assign('_canCaptcha', true);
                    $this->Template->Assign('_captchaHTML', $_captchaHTML);
                }
            }
        }

        if (isset($_POST['fullname'])) {
            $this->Template->Assign('_commentsPostFullName', text_to_html_entities($_POST['fullname']));
        } else {
            $this->Template->Assign('_commentsPostFullName', '');
        }

        if (isset($_POST['email'])) {
            $this->Template->Assign('_commentsPostEmail', htmlspecialchars($_POST['email']));
        } else {
            $this->Template->Assign('_commentsPostEmail', '');
        }

        if (isset($_POST['comments'])) {
            $this->Template->Assign('_commentsPostData', htmlspecialchars($_POST['comments']));
        } else {
            $this->Template->Assign('_commentsPostData', '');
        }

        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
            $_userEmailList = $_SWIFT->User->GetEmailList();

            $this->Template->Assign('_commentsFullName', text_to_html_entities($_SWIFT->User->GetProperty('fullname')));
            $this->Template->Assign('_commentsEmail', htmlspecialchars($_userEmailList[0]));
        }

        $this->Template->Assign('_commentsApp', $_commentApp);
        $this->Template->Assign('_commentsItemID', $_commentItemID);

        $this->Template->Assign('_commentExtendedURL', $_commentExtendedURL);

        /**
         * ---------------------------------------------
         * Load Existing Comments
         * ---------------------------------------------
         */
        $_commentContainer = SWIFT_Comment::Retrieve($_commentType, $_commentItemID, SWIFT_Comment::STATUS_APPROVED);
        $this->Template->Assign('_commentCount', count($_commentContainer));
        $this->Template->Assign('_commentContainer', $_commentContainer);


        return true;
    }

    /**
     * Process the POST Data
     *
     * @author Varun Shoor
     * @param mixed $_commentType The Comment Type
     * @param int $_commentItemID The Comment Item ID
     * @param string $_parentURL (OPTIONAL) The Parent URL
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessPOSTUser($_commentType, $_commentItemID, $_parentURL = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['fullname']) || !isset($_POST['comments']) || trim($_POST['fullname']) == '' || trim($_POST['comments']) == '') {
            $this->UserInterface->CheckFields('fullname', 'comments');

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            return false;
        }

        if (!empty(trim($_POST['email'])) &&
            (!IsEmailValid($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))) {
            SWIFT::ErrorField('email');

            $this->UserInterface->Error(true, $this->Language->Get('invalidemail'));

            return false;
        }

        // Check for captcha
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('security_commentscaptcha') == '1') && !$_SWIFT->Session->IsLoggedIn()) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded() && !$_CaptchaObject->IsValidCaptcha()) {
                SWIFT::ErrorField('captcha');

                $this->UserInterface->Error(true, $this->Language->Get('errcaptchainvalid'));

                return false;
            }
        }

        $_parentCommentID = 0;
        if (isset($_POST['parentcommentid'])) {
            $_parentCommentID = (int)($_POST['parentcommentid']);
        }

        $_userID = 0;
        $_commentStatus = false;
        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded() && $_SWIFT->Settings->Get('security_autoapprovecomments') == '1') {
            $_userID = $_SWIFT->User->GetUserID();
            $_commentStatus = SWIFT_Comment::STATUS_APPROVED;
        }

        if ($_commentStatus == false) {
            $_commentStatus = self::CheckCommentContents($_POST['fullname'], $_POST['email'], $_POST['comments']);
        }

        $_httpReferer = '';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $_httpReferer = $_SERVER['HTTP_REFERER'];
        }
        SWIFT_Comment::Create($_commentType, $_commentItemID, $_commentStatus, $_POST['fullname'], $_POST['email'], SWIFT::Get('IP'), $_POST['comments'], SWIFT_Comment::CREATOR_USER,
            $_userID, $_parentCommentID, $_SERVER['HTTP_USER_AGENT'], $_httpReferer, $_parentURL);

        $_infoMessage = ($this->Settings->Get('security_autoapprovecomments')) ? 'commentconfirmation_approved' : 'commentconfirmation';
        SWIFT::Info($_SWIFT->Language->Get($_infoMessage), $_SWIFT->Language->Get($_infoMessage));

        return true;
    }

    /**
     * Checks the comments contents and retrieves the appropriate status
     *
     * @author Varun Shoor
     * @param string $_fullName The Full Name
     * @param string $_email The Email Address
     * @param string $_comments The Comment Data
     * @return mixed The Comment Status
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CheckCommentContents($_fullName, $_email, $_comments)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_SWIFT->Settings->Get('security_enableakismet') == '0' || $_SWIFT->Settings->Get('security_akismetkey') == '') {
            if ($_SWIFT->Settings->Get('security_autoapprovecomments') == '1') {
                return SWIFT_Comment::STATUS_APPROVED;
            }

            return SWIFT_Comment::STATUS_PENDING;
        }

        $_SWIFT_AkismetObject = new SWIFT_Akismet();
        if ($_SWIFT_AkismetObject->Check($_fullName, $_email, $_comments) == false) {
            return SWIFT_Comment::STATUS_SPAM;
        }

        if ($_SWIFT->Settings->Get('security_autoapprovecomments') == '1') {
            return SWIFT_Comment::STATUS_APPROVED;
        }

        return SWIFT_Comment::STATUS_PENDING;
    }
}

?>
