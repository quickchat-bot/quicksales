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

namespace LiveChat\Admin;

use Base\Models\Staff\SWIFT_StaffActivityLog;
use Controller_admin;
use SWIFT;
use SWIFT_Session;

/**
 * The Live Chat Online Status Controller Class
 *
 * @author Varun Shoor
 *
 * @property View_OnlineStatus $View
 */
class Controller_OnlineStatus extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 13;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('admin_livesupport');
    }

    /**
     * Disconnect the Online Sessions from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_sessionIDList The Session ID ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisconnectList($_sessionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_lrcandisconnectstaff') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_sessionIDList)) {
            $_finalSessionIDList = array();
            $_index = 1;
            $_itemText = '';

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff AS staff LEFT JOIN " . TABLE_PREFIX . "sessions AS sessions ON (staff.staffid = sessions.typeid) WHERE sessions.sessionid IN (" . BuildIN($_sessionIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_finalSessionIDList[] = $_SWIFT->Database->Record['sessionid'];
                $_itemText .= $_index . ". " . text_to_html_entities($_SWIFT->Database->Record['fullname']) . " (" . htmlspecialchars($_SWIFT->Database->Record['username']) . ")<br />";

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletedlssession'), text_to_html_entities($_SWIFT->Database->Record['fullname']), text_to_html_entities($_SWIFT->Database->Record['username'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

                $_index++;
            }

            if (!count($_finalSessionIDList)) {
                return false;
            }

            SWIFT::Info(sprintf($_SWIFT->Language->Get('titledcstaff'), count($_finalSessionIDList)), $_SWIFT->Language->Get('msgdcstaff') . '<br />' . $_itemText);

            SWIFT_Session::KillSessionList($_finalSessionIDList);
        }

        return true;
    }

    /**
     * Displays the Online Status Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('onlinestatus'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanviewonlinestaff') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }
}
