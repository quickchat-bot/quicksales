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

namespace Base\Staff;

use Base\Admin\Controller_Staff;
use Base\Models\Comment\SWIFT_Comment;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * The Comment Controller
 *
 * @author Varun Shoor
 * @property View_Comment $View
 */
class Controller_Comment extends Controller_staff
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('staff_comments');
    }

    /**
     * Delete the Comments from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_commentIDList The Comment ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_commentIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_candeletecomments') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_commentIDList)) {
            $_SWIFT->Database->Query("SELECT fullname, email FROM " . TABLE_PREFIX . "comments
                WHERE commentid IN (" . BuildIN($_commentIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletecomment'),
                    text_to_html_entities($_SWIFT->Database->Record['fullname']), htmlspecialchars($_SWIFT->Database->Record['email'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Comment::DeleteList($_commentIDList);
        }

        return true;
    }

    /**
     * Approve the Comments from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_commentIDList The Comment ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ApproveList($_commentIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_canupdatecomments') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_commentIDList)) {
            $_SWIFT->Database->Query("SELECT fullname, email FROM " . TABLE_PREFIX . "comments
                WHERE commentid IN (" . BuildIN($_commentIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityapprovecomment'),
                    text_to_html_entities($_SWIFT->Database->Record['fullname']), htmlspecialchars($_SWIFT->Database->Record['email'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Comment::ApproveList($_commentIDList);
        }

        return true;
    }

    /**
     * Mark the Comments from Mass Action as Spam
     *
     * @author Varun Shoor
     * @param mixed $_commentIDList The Comment ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function SpamList($_commentIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_canupdatecomments') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_commentIDList)) {
            $_SWIFT->Database->Query("SELECT fullname, email FROM " . TABLE_PREFIX . "comments
                WHERE commentid IN (" . BuildIN($_commentIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityspamcomment'),
                    text_to_html_entities($_SWIFT->Database->Record['fullname']), htmlspecialchars($_SWIFT->Database->Record['email'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Comment::SpamList($_commentIDList);
        }

        return true;
    }

    /**
     * Mark the Comments from Mass Action as Not Spam
     *
     * @author Varun Shoor
     * @param mixed $_commentIDList The Comment ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function NotSpamList($_commentIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_canupdatecomments') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_commentIDList)) {
            $_SWIFT->Database->Query("SELECT fullname, email FROM " . TABLE_PREFIX . "comments
                WHERE commentid IN (" . BuildIN($_commentIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitynotspamcomment'),
                    text_to_html_entities($_SWIFT->Database->Record['fullname']), htmlspecialchars($_SWIFT->Database->Record['email'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Comment::ApproveList($_commentIDList);
        }

        return true;
    }

    /**
     * Loads the Display Data
     *
     * @author Varun Shoor
     * @param mixed $_commentStatus The Comment Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayData($_commentStatus)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->View->RenderTree($_commentStatus));

        return true;
    }

    /**
     * Displays the Comment Grid
     *
     * @author Varun Shoor
     * @param mixed $_commentStatus The Comment Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_commentStatus = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!SWIFT_Comment::IsValidStatus($_commentStatus)) {
            $_commentStatus = SWIFT_Comment::STATUS_PENDING;
        }

        if ($_SWIFT->Staff->GetPermission('staff_canviewcomments') == '0') {
            $this->_LoadDisplayData($_commentStatus);

            $this->UserInterface->Header($this->Language->Get('comments') . ' > ' . $this->Language->Get('manage'), self::MENU_ID,
                self::NAVIGATION_ID);

            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid($_commentStatus);
        }

        $this->UserInterface->Footer();

        return true;
    }
}

?>
