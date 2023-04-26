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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The Widget Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Widget $View
 * @author Varun Shoor
 */
class Controller_Widget extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 31;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('admin_widgets');
    }

    /**
     * Resort the widgets
     *
     * @author Varun Shoor
     * @param mixed $_widgetIDSortList The Widget ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function SortList($_widgetIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canupdatewidget') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_Widget::UpdateDisplayOrderList($_widgetIDSortList);

        return true;
    }

    /**
     * Delete the Widgets from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_widgetIDList The Widget ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_widgetIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_candeletewidgets') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_widgetIDList)) {
            $_finalText = $_failFinalText = '';
            $_index = $_failIndex = 1;

            $_finalWidgetIDList = array();

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "widgets WHERE widgetid IN (" . BuildIN($_widgetIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['ismaster'] == '1') {
                    $_failFinalText .= $_failIndex . '. ' . SWIFT_Widget::GetLabel($_SWIFT->Database->Record['defaulttitle']) . '<BR />';
                    $_failIndex++;
                } else {
                    $_finalWidgetIDList[] = $_SWIFT->Database->Record['widgetid'];

                    $_finalText .= $_index . '. ' . SWIFT_Widget::GetLabel($_SWIFT->Database->Record['defaulttitle']) . '<BR />';
                    $_index++;

                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletewidget'), htmlspecialchars(SWIFT_Widget::GetLabel($_SWIFT->Database->Record['defaulttitle']))), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_WIDGETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
                }
            }

            if (!empty($_finalText)) {
                SWIFT::Info(sprintf($_SWIFT->Language->Get('titledeletedwidget'), ($_index - 1)), $_SWIFT->Language->Get('msgdeletedwidget') . '<BR />' . $_finalText);
            }

            if (!empty($_failFinalText)) {
                SWIFT::Alert(sprintf($_SWIFT->Language->Get('titledeletedwidgetfail'), ($_failIndex - 1)), $_SWIFT->Language->Get('msgdeletedwidgetfail') . '<BR />' . $_failFinalText);
            }

            SWIFT_Widget::DeleteList($_finalWidgetIDList);
        }

        return true;
    }

    /**
     * Delete the Given Widget ID
     *
     * @author Varun Shoor
     * @param int $_widgetID The Widget ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_widgetID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_widgetID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Widget Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('widgets') . ' > ' . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewwidgets') == '0') {
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
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode)
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

        if (trim($_POST['defaulttitle']) == '' || trim($_POST['widgetlink']) == '') {
            $this->UserInterface->CheckFields('defaulttitle', 'widgetlink');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_caninsertwidget') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_canupdatewidget') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        // Any uploaded file? Check extensions...
        foreach (array('defaulticon', 'defaultsmallicon') as $_key => $_val) {
            $_uploadedFieldName = 'file_' . $_val;

            if (isset($_FILES[$_uploadedFieldName]) && isset($_FILES[$_uploadedFieldName]['tmp_name']) && is_uploaded_file($_FILES[$_uploadedFieldName]['tmp_name'])) {
                $_pathInfoContainer = pathinfo($_FILES[$_uploadedFieldName]['name']);
                $_fileExtension = mb_strtolower($_pathInfoContainer['extension']);
                if (!isset($_pathInfoContainer['extension']) || empty($_pathInfoContainer['extension']) || ($_fileExtension != 'gif' && $_fileExtension != 'jpeg' && $_fileExtension != 'jpg' && $_fileExtension != 'png')) {
                    SWIFT::ErrorField($_val);

                    $this->UserInterface->Error($this->Language->Get('titleinvalidfileext'), $this->Language->Get('msginvalidfileext'));

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Insert a new Widget
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

        $this->UserInterface->Header($this->Language->Get('widgets') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caninsertwidget') == '0') {
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
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_finalText = '<b>' . $this->Language->Get('widgettitle') . ':</b> ' . htmlspecialchars($_POST['defaulttitle']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('widgetlink') . ':</b> ' . htmlspecialchars($_POST['widgetlink']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('displayorder') . ':</b> ' . (int)($_POST['displayorder']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('isenabled') . ':</b> ' . IIF($_POST['isenabled'] == '1', $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('displayinnavbar') . ':</b> ' . IIF($_POST['displayinnavbar'] == '1', $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('displayinindex') . ':</b> ' . IIF($_POST['displayinindex'] == '1', $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titlewidget' . $_type), htmlspecialchars($_POST['defaulttitle'])), sprintf($this->Language->Get('msgwidget' . $_type), htmlspecialchars($_POST['defaulttitle'])) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Retrieve the icon, if a new one is uploaded.. pass it through file manager and return the relevant new URL to it
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetIcon($_fieldName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // We always give priority to the uploaded file..
        $_uploadedFieldName = 'file_' . $_fieldName;
        if (isset($_FILES[$_uploadedFieldName]) && isset($_FILES[$_uploadedFieldName]['tmp_name']) && is_uploaded_file($_FILES[$_uploadedFieldName]['tmp_name'])) {
            $_fileID = SWIFT_FileManager::Create($_FILES[$_uploadedFieldName]['tmp_name'], $_FILES[$_uploadedFieldName]['name']);
            if (!empty($_fileID)) {
                $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fileID);
                if ($_SWIFT_FileManagerObject->GetIsClassLoaded()) {
                    return $_SWIFT_FileManagerObject->GetURL();
                }
            }
        }

        if (!isset($_POST['url_' . $_fieldName])) {
            return '';
        }

        return $_POST['url_' . $_fieldName];
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
            $_widgetID = SWIFT_Widget::Create($_POST['defaulttitle'], '', APP_BASE, $_POST['widgetlink'], $this->Input->SanitizeForXSS($this->_GetIcon('defaulticon')), $this->_GetIcon('defaultsmallicon'), $_POST['displayorder'], $_POST['displayinnavbar'], $_POST['displayinindex'], false, $_POST['isenabled'], $_POST['widgetvisibility'], $_SWIFT->Staff->GetStaffID(), $_POST['uservisibilitycustom'], $this->_GetUserGroupIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertwidget'), htmlspecialchars($_POST['defaulttitle'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_WIDGETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_widgetID) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Widget ID
     *
     * @author Varun Shoor
     * @param int $_widgetID The Widget ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_widgetID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_widgetID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_WidgetObject = new SWIFT_Widget($_widgetID);
        if (!$_SWIFT_WidgetObject instanceof SWIFT_Widget || !$_SWIFT_WidgetObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('widgets') . ' > ' . $this->Language->Get('edit'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatewidget') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_WidgetObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_widgetID The Widget ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_widgetID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_widgetID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_WidgetObject = new SWIFT_Widget($_widgetID);
        if (!$_SWIFT_WidgetObject instanceof SWIFT_Widget || !$_SWIFT_WidgetObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_WidgetObject->Update($_POST['defaulttitle'], $_POST['widgetlink'], $this->Input->SanitizeForXSS($this->_GetIcon('defaulticon')), $this->_GetIcon('defaultsmallicon'), $_POST['displayorder'], $_POST['displayinnavbar'], $_POST['displayinindex'], $_POST['isenabled'], $_POST['widgetvisibility'], $_SWIFT->Staff->GetStaffID(), $_POST['uservisibilitycustom'], $this->_GetUserGroupIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatewidget'), htmlspecialchars($_POST['defaulttitle'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_WIDGETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_widgetID);

        return false;
    }

    /**
     * Retrieve the relevant user group id list
     *
     * @author Varun Shoor
     * @return mixed "_userGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userGroupIDList = array();
        if (_is_array($_POST['usergroupidlist'])) {
            foreach ($_POST['usergroupidlist'] as $key => $val) {
                if ($val == '1') {
                    $_userGroupIDList[] = $key;
                }
            }
        }

        return $_userGroupIDList;
    }
}

?>
