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
use Base\Models\CustomField\SWIFT_CustomField;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Custom Field Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_LanguagePhraseLinked $LanguagePhraseLinked
 * @property View_CustomField $View
 * @author Varun Shoor
 */
class Controller_CustomField extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Language:LanguagePhraseLinked', [], true, false, 'base');

        $this->Language->Load('customfields');
    }

    /**
     * Resort the Custom Fields
     *
     * @author Varun Shoor
     * @param mixed $_customFieldIDSortList The Custom Field ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function SortList($_customFieldIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canupdatecustomfield') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_CustomField::UpdateDisplayOrderList($_customFieldIDSortList);

        return true;
    }

    /**
     * Delete the Custom Field from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_customFieldIDList The Custom Field ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_customFieldIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_candeletecustomfield') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_customFieldIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "customfields WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletecustomfield'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_CUSTOMFIELDS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_CustomField::DeleteList($_customFieldIDList);
        }

        return true;
    }

    /**
     * Delete the Given Custom Field ID
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom Field ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_customFieldID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_customFieldID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Custom Field Grid
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

        $this->UserInterface->Header($this->Language->Get('customfields') . ' > ' . $this->Language->Get('managefields'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewcfields') == '0') {
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

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        if (trim($_POST['title']) == '' || trim($_POST['fieldname']) == '') {
            $this->UserInterface->CheckFields('title', 'fieldname');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_caninsertcustomfield') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_canupdatecustomfield') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Custom Field (Display the Field Choices)
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

        $this->UserInterface->Header($this->Language->Get('customfields') . ' > ' . $this->Language->Get('insertfield'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caninsertcustomfield') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderInsert();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert a new Custom Field (Step 2)
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertStep2()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!isset($_POST['customfieldgroupid']) || empty($_POST['customfieldgroupid']) || !isset($_POST['fieldtype']) || !SWIFT_CustomField::IsValidType($_POST['fieldtype'])) {
            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Insert();

            return false;
        }

        $this->UserInterface->Header($this->Language->Get('customfields') . ' > ' . $this->Language->Get('insertfield'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @param int $_customFieldID The Custom Field ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, $_customFieldID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_customFieldContainer = $this->Database->QueryFetch('SELECT customfields.*, customfields.title AS fieldtitle, customfields.displayorder AS fielddisplayorder, customfieldgroups.title AS grouptitle FROM ' . TABLE_PREFIX . 'customfields AS customfields LEFT JOIN ' . TABLE_PREFIX . 'customfieldgroups AS customfieldgroups ON (customfields.customfieldgroupid = customfieldgroups.customfieldgroupid) WHERE customfields.customfieldid = \'' . $_customFieldID . '\'');
        if (!isset($_customFieldContainer['customfieldid']) || empty($_customFieldContainer['customfieldid'])) {
            return false;
        }

        $_fieldText = '<b>' . $this->Language->Get('fieldtitle') . '</b>: ' . htmlspecialchars($_customFieldContainer['fieldtitle']) . '<BR />';

        if (!empty($_customFieldContainer['defaultvalue'])) {
            if ($_customFieldContainer['fieldtype'] == SWIFT_CustomField::TYPE_DATE) {
                $_defaultValue = SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, $_customFieldContainer['defaultvalue'], false, true);
            } else {
                $_defaultValue = htmlspecialchars($_customFieldContainer['defaultvalue']);
            }

            $_fieldText .= '<b>' . $this->Language->Get('defaultvalue') . '</b>: ' . $_defaultValue . '<BR />';
        }

        if (!empty($_customFieldContainer['description'])) {
            $_fieldText .= '<b>' . $this->Language->Get('description') . '</b>: ' . htmlspecialchars($_customFieldContainer['description']) . '<BR />';
        }

        $_fieldText .= '<b>' . $this->Language->Get('displayorder') . '</b>: ' . (int)($_customFieldContainer['displayorder']) . '<BR />';

        if ($_customFieldContainer['fieldtype'] != SWIFT_CustomField::TYPE_TEXT && $_customFieldContainer['fieldtype'] != SWIFT_CustomField::TYPE_PASSWORD && $_customFieldContainer['fieldtype'] != SWIFT_CustomField::TYPE_TEXTAREA && $_customFieldContainer['fieldtype'] != SWIFT_CustomField::TYPE_CUSTOM && $_customFieldContainer['fieldtype'] != SWIFT_CustomField::TYPE_DATE && $_customFieldContainer['fieldtype'] != SWIFT_CustomField::TYPE_FILE) {
            $_customFieldOptionsContainer = $_parentCustomFieldOptionIDList = array();

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions WHERE customfieldid = '" . (int)($_customFieldContainer['customfieldid']) . "' AND parentcustomfieldoptionid = '0' ORDER BY displayorder ASC");
            while ($this->Database->NextRecord()) {
                $_customFieldOptionsContainer[$this->Database->Record['customfieldoptionid']] = $this->Database->Record;
                $_customFieldOptionsContainer[$this->Database->Record['customfieldoptionid']]['suboptions'] = array();
                $_parentCustomFieldOptionIDList[] = $this->Database->Record['customfieldoptionid'];
            }

            if (count($_parentCustomFieldOptionIDList)) {
                $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions WHERE parentcustomfieldoptionid IN (" . BuildIN($_parentCustomFieldOptionIDList) . ") ORDER BY displayorder ASC");
                while ($this->Database->NextRecord()) {
                    $_customFieldOptionsContainer[$this->Database->Record['parentcustomfieldoptionid']]['suboptions'][] = $this->Database->Record;
                }
            }

            if (count($_customFieldOptionsContainer)) {
                $_index = 1;

                foreach ($_customFieldOptionsContainer as $_key => $_val) {
                    $_fieldText .= '<b>' . $_index . '.</b> ' . htmlspecialchars($_val['optionvalue']) . '<BR />';
                    if (count($_val['suboptions'])) {
                        $_subIndex = 1;

                        foreach ($_val['suboptions'] as $_subKey => $_subVal) {
                            $_fieldText .= '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> <b>' . $_subIndex . '.</b> ' . htmlspecialchars($_subVal['optionvalue']) . '<BR />';

                            $_subIndex++;
                        }
                    }

                    $_index++;
                }
            }
        }

        SWIFT::Info(sprintf($this->Language->Get($_type . 'cfieldtitle'), htmlspecialchars($_customFieldContainer['fieldtitle'])), sprintf($this->Language->Get($_type . 'cfieldmsg'), htmlspecialchars($_customFieldContainer['fieldtitle'])) . $_fieldText);

        return true;
    }

    /**
     * Retrieve the Field Options Container from $_POST
     *
     * @author Varun Shoor
     * @return mixed "_fieldOptionsContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetFieldOptionsContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldOptionsContainer = array();

        $_index = 0;
        $_fieldIsSelected = false;

        if (isset($_POST['fieldlist']) && _is_array($_POST['fieldlist'])) {
            foreach ($_POST['fieldlist'] as $_key => $_val) {
                $_index = $_key;
                if (!isset($_val[0]) || !isset($_val[1]) || empty($_val[0]) || empty($_val[1])) {
                    continue;
                }

                $_fieldOptionsContainer[$_index] = $_val;
                if (isset($_POST['selectedfield']) && $_POST['selectedfield'] == $_key) {
                    $_fieldOptionsContainer[$_index][2] = true;
                    $_fieldIsSelected = true;
                }

                if (!isset($_fieldOptionsContainer[$_index][2])) {
                    $_fieldOptionsContainer[$_index][2] = false;
                }

                if ($_fieldOptionsContainer[$_index][2] == true) {
                    $_fieldIsSelected = true;
                }

                $_fieldOptionsContainer[$_index][4] = array();

                if (isset($_POST['subfieldlist'][$_key]) && _is_array($_POST['subfieldlist'][$_key])) {
                    foreach ($_POST['subfieldlist'][$_key] as $_subKey => $_subVal) {
                        if (!isset($_subVal[0]) || !isset($_subVal[1]) || empty($_subVal[0]) || empty($_subVal[1])) {
                            continue;
                        }

                        $_fieldOptionsContainer[$_index][4][$_subKey] = $_subVal;

                        if (isset($_POST['subfieldsellist']) && isset($_POST['subfieldsellist'][$_key]) && $_POST['subfieldsellist'][$_key] == $_subKey) {
                            $_fieldOptionsContainer[$_index][4][$_subKey][2] = true;
                        }

                        if (!isset($_fieldOptionsContainer[$_index][4][$_subKey][2])) {
                            $_fieldOptionsContainer[$_index][4][$_subKey][2] = false;
                        }
                    }
                }

                $_index++;
            }
        }

        return $_fieldOptionsContainer;
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
            $_SWIFT_CustomFieldObject = SWIFT_CustomField::Create($_POST['customfieldgroupid'], $_POST['fieldtype'], $_POST['title'], $_POST['description'], $_POST['fieldname'], $_POST['defaultvalue'], $_POST['displayorder'], $_POST['isrequired'], $_POST['usereditable'], $_POST['staffeditable'], $_POST['regexpvalidate'], $this->_GetFieldOptionsContainer(), $_POST['encryptindb']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertcustomfield'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_CUSTOMFIELDS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_SWIFT_CustomFieldObject instanceof SWIFT_CustomField || !$_SWIFT_CustomFieldObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_CUSTOMFIELD, $_SWIFT_CustomFieldObject->GetCustomFieldID(), $_POST['languages']);

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_CustomFieldObject->GetCustomFieldID());

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Custom Field ID
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom Field ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_customFieldID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_customFieldID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_CustomFieldObject = new SWIFT_CustomField($_customFieldID);
        if (!$_SWIFT_CustomFieldObject instanceof SWIFT_CustomField || !$_SWIFT_CustomFieldObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('customfields') . ' > ' . $this->Language->Get('edit'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatecustomfield') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_CustomFieldObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom FIeld ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_customFieldID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_customFieldID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_CustomFieldObject = new SWIFT_CustomField($_customFieldID);
        if (!$_SWIFT_CustomFieldObject instanceof SWIFT_CustomField || !$_SWIFT_CustomFieldObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_updateResult = $_SWIFT_CustomFieldObject->Update($_POST['title'], $_POST['description'], $_POST['fieldname'], $_POST['defaultvalue'], $_POST['displayorder'], $_POST['isrequired'], $_POST['usereditable'], $_POST['staffeditable'], $_POST['regexpvalidate'], $this->_GetFieldOptionsContainer(), $_POST['encryptindb']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatecustomfield'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_CUSTOMFIELDS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->LanguagePhraseLinked->UpdateList(SWIFT_LanguagePhraseLinked::TYPE_CUSTOMFIELD, $_SWIFT_CustomFieldObject->GetCustomFieldID(), $_POST['languages']);

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_customFieldID);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_customFieldID);

        return false;
    }
}

?>
