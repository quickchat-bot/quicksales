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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Notification\SWIFT_NotificationAction;
use Base\Models\Notification\SWIFT_NotificationRule;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * The Notification Controller
 *
 * @author Varun Shoor
 *
 * @property View_Notification $View
 */
class Controller_Notification extends Controller_staff
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('staff_notifications');
    }

    /**
     * Delete the Notifications from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_notificationRuleIDList The Notification Rule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_notificationRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_candeletenotification') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_notificationRuleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "notificationrules WHERE notificationruleid IN (" .
                BuildIN($_notificationRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletenotification'),
                    htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_GENERAL, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_NotificationRule::DeleteList($_notificationRuleIDList);
        }

        return true;
    }

    /**
     * Delete the Given Notification Rule ID
     *
     * @author Varun Shoor
     * @param int $_notificationRuleID The Notification Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_notificationRuleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_notificationRuleID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Enable a List of Notification Rules from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_notificationRuleIDList The Notification Rule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_notificationRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_canupdatenotification') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_notificationRuleIDList)) {
            $_finalNotificationRuleIDList = array();

            $_finalText = '';
            $_index = 1;

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "notificationrules WHERE notificationruleid IN (" . BuildIN($_notificationRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['isenabled'] == 0) {
                    $_finalNotificationRuleIDList[] = $_SWIFT->Database->Record['notificationruleid'];
                    $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . "<br />\n";

                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityenablenotification'), htmlspecialchars($_SWIFT->Database->Record['title'])),
                        SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_HOME, SWIFT_StaffActivityLog::INTERFACE_STAFF);

                    $_index++;
                }
            }

            if (!count($_finalNotificationRuleIDList)) {
                return false;
            }

            SWIFT::Info(sprintf($_SWIFT->Language->Get('titleenablenotification'), count($_finalNotificationRuleIDList)), sprintf($_SWIFT->Language->Get('msgenablenotification'), $_finalText));

            SWIFT_NotificationRule::EnableList($_finalNotificationRuleIDList);
        }

        return true;
    }

    /**
     * Disable a List of Notification Rules from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_notificationRuleIDList The Notification Rule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_notificationRuleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_canupdatenotification') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_notificationRuleIDList)) {
            $_finalNotificationRuleIDList = array();

            $_finalText = '';
            $_index = 1;

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "notificationrules WHERE notificationruleid IN (" . BuildIN($_notificationRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['isenabled'] == 1) {
                    $_finalNotificationRuleIDList[] = $_SWIFT->Database->Record['notificationruleid'];
                    $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . "<br />\n";

                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydisablenotification'), htmlspecialchars($_SWIFT->Database->Record['title'])),
                        SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_HOME, SWIFT_StaffActivityLog::INTERFACE_STAFF);

                    $_index++;
                }
            }

            if (!count($_finalNotificationRuleIDList)) {
                return false;
            }

            SWIFT::Info(sprintf($_SWIFT->Language->Get('titledisablenotification'), count($_finalNotificationRuleIDList)), sprintf($_SWIFT->Language->Get('msgdisablenotification'), $_finalText));

            SWIFT_NotificationRule::DisableList($_finalNotificationRuleIDList);
        }

        return true;
    }


    /**
     * Displays the Notification Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_reportID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('notifications') . ' > ' . $this->Language->Get('manage'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_canviewnotifications') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @param int $_ticketFileTypeID The Ticket File Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, $_ticketFileTypeID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (trim($_POST['title']) == '') {
            $this->UserInterface->CheckFields('title');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (!isset($_POST['rulecriteria']) || !_is_array($_POST['rulecriteria'])) {
            $this->UserInterface->Error($this->Language->Get('titlenocriteria'), $this->Language->Get('msgnocriteria'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_caninsertnotification') == '0') ||
            ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_canupdatenotification') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a New Notification Rule
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('notifications') . ' > ' . $this->Language->Get('insert'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_caninsertnotification') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            // $this->View->RenderDialog(SWIFT_UserInterface::MODE_INSERT); PHPStan recommendation
            $this->View->RenderDialog();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert a New Notification Rule: Step 2
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertStep2()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('notifications') . ' > ' . $this->Language->Get('insert'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_caninsertnotification') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @param SWIFT_NotificationRule $_SWIFT_NotificationRuleObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, SWIFT_NotificationRule $_SWIFT_NotificationRuleObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_type = 'insert';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        }

        $_criteriaPointer = SWIFT_NotificationRule::GetCriteriaPointer($_SWIFT_NotificationRuleObject->GetProperty('ruletype'));
        SWIFT_NotificationRule::ExtendCustomCriteria($_SWIFT_NotificationRuleObject->GetProperty('ruletype'), $_criteriaPointer);

        // Get all the criterias
        $_finalText = '';
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "notificationcriteria WHERE notificationruleid = '" .
            (int)($_SWIFT_NotificationRuleObject->GetNotificationRuleID()) . "'");
        while ($this->Database->NextRecord()) {
            $_criteriaName = 'n' . $this->Database->Record['name'];
            $_finalText .= '<img src="' . SWIFT::Get('themepathimages') . 'linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' . $this->Language->Get('if') . ' <b>"' . $this->Language->Get($_criteriaName) . '"</b> ' . SWIFT_Rules::GetOperText($this->Database->Record['ruleop']);
            if (!empty($this->Database->Record['rulematch'])) {
                $_finalText .= ' <b>"';
            }

            $_extendedName = '';
            if (isset($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) &&
                _is_array($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) &&
                $_criteriaPointer[$this->Database->Record['name']]['field'] == 'custom') {
                foreach ($_criteriaPointer[$this->Database->Record['name']]['fieldcontents'] as $_val) {
                    if ($_val['contents'] == $this->Database->Record['rulematch']) {
                        $_extendedName = $_val['title'];

                        break;
                    }
                }
            }

            if (!empty($this->Database->Record['rulematch'])) {
                $_finalText .= htmlspecialchars(IIF(!empty($_extendedName), $_extendedName, $this->Database->Record['rulematch'])) . '"</b><BR />';
            } else {
                $_finalText .= '<BR />';
            }

        }

        $_finalText .= '<BR />';


        SWIFT::Info(sprintf($this->Language->Get('titlenotification' . $_type), htmlspecialchars($_POST['title'])),
            sprintf($this->Language->Get('msgnotification' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Get the Criteria Associated with this Notification Rule
     *
     * @author Varun Shoor
     * @return array The Criteria Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetCriteria()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_criteriaContainer = array();

        foreach ($_POST['rulecriteria'] as $_criteria) {
            $_criteriaContainer[] = $_criteria;
        }

        return $_criteriaContainer;
    }

    /**
     * Get the Actions associated with this Notification Rule
     *
     * @author Varun Shoor
     * @return array The Actions Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetActions()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_actionsContainer = array();

        if (isset($_POST['emaildispatchlist']) && _is_array($_POST['emaildispatchlist'])) {
            foreach ($_POST['emaildispatchlist'] as $_val) {
                $_actionsContainer[] = array($_val, '0');
            }
        }

        if (isset($_POST['emaildispatchliststaff']) && _is_array($_POST['emaildispatchliststaff'])) {
            foreach ($_POST['emaildispatchliststaff'] as $_staffID) {
                if (!isset($_staffCache[$_staffID])) {
                    continue;
                }

                $_actionsContainer[] = array(SWIFT_NotificationAction::ACTION_EMAILSTAFFCUSTOM, $_staffID);
            }
        }

        $_emailDispatchCustom = SWIFT_UserInterface::GetMultipleInputValues('emaildispatchcustom');
        foreach ($_emailDispatchCustom as $_email) {
            if (!IsEmailValid($_email)) {
                continue;
            }

            $_actionsContainer[] = array(SWIFT_NotificationAction::ACTION_EMAILCUSTOM, $_email);
        }

        if (isset($_POST['pooldispatchlist']) && _is_array($_POST['pooldispatchlist'])) {
            foreach ($_POST['pooldispatchlist'] as $_val) {
                $_actionsContainer[] = array($_val, '0');
            }
        }

        if (isset($_POST['poolcustomdispatchlist']) && _is_array($_POST['poolcustomdispatchlist'])) {
            foreach ($_POST['poolcustomdispatchlist'] as $_customPoolDispatchStaffID) {
                $_actionsContainer[] = array(SWIFT_NotificationAction::ACTION_POOLCUSTOM, $_customPoolDispatchStaffID);
            }
        }

        return $_actionsContainer;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
//            echo nl2br(print_r($this->_GetCriteria(), true));
//            echo nl2br(print_r($this->_GetActions(), true));

            $_notificationRuleID = SWIFT_NotificationRule::Create($_POST['title'], $_POST['ruletype'], $_POST['isenabled'], $this->_GetCriteria(), $this->_GetActions(),
                $_SWIFT->Staff->GetStaffID(), $_POST['emailprefix']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertednotification'), htmlspecialchars($_POST['title'])),
                SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_HOME, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_notificationRuleID) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_NotificationRuleObject = new SWIFT_NotificationRule(new SWIFT_DataID($_notificationRuleID));

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_NotificationRuleObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Notification Rule ID
     *
     * @author Varun Shoor
     * @param int $_notificationRuleID The Notification Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_notificationRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_notificationRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_NotificationRuleObject = new SWIFT_NotificationRule(new SWIFT_DataID($_notificationRuleID));
        if (!$_SWIFT_NotificationRuleObject instanceof SWIFT_NotificationRule || !$_SWIFT_NotificationRuleObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_LoadPOSTVariables($_notificationRuleID);

        $this->UserInterface->Header($this->Language->Get('notifications') . ' > ' . $this->Language->Get('edit'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_canupdatenotification') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_NotificationRuleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_notificationRuleID The Notification Rule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_notificationRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_notificationRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_NotificationRuleObject = new SWIFT_NotificationRule(new SWIFT_DataID($_notificationRuleID));
        if (!$_SWIFT_NotificationRuleObject instanceof SWIFT_NotificationRule || !$_SWIFT_NotificationRuleObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_NotificationRuleObject->Update($_POST['title'], $_POST['isenabled'], $this->_GetCriteria(), $this->_GetActions(), $_POST['emailprefix']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatenotification'), htmlspecialchars($_POST['title'])),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_HOME, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_NotificationRuleObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_notificationRuleID);

        return false;
    }

    /**
     * Loads the Rule Criteria into $_POST
     *
     * @author Varun Shoor
     * @param int $_notificationRuleID The Notification Rule ID
     * @return bool "true" on Success, "false" otherwise
     */
    private function _LoadPOSTVariables($_notificationRuleID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['rulecriteria'])) {
            $_POST['rulecriteria'] = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "notificationcriteria WHERE notificationruleid = '" .
                $_notificationRuleID . "' ORDER BY notificationcriteriaid ASC");
            while ($this->Database->NextRecord()) {
                $_POST['rulecriteria'][] = array($this->Database->Record['name'], $this->Database->Record['ruleop'], $this->Database->Record['rulematch'], $this->Database->Record['rulematchtype']);
            }
        }

        return true;
    }
}

?>
