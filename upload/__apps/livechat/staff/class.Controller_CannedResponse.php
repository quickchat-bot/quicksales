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

namespace LiveChat\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use LiveChat\Library\Chat\SWIFT_ChatRenderManager;
use LiveChat\Models\Canned\SWIFT_CannedCategory;
use LiveChat\Models\Canned\SWIFT_CannedResponse;
use SWIFT;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Session;

/**
 * The Canned Response Controller
 *
 * @author Varun Shoor
 *
 * @property View_CannedResponse $View
 */
class Controller_CannedResponse extends Controller_staff
{
    // Core Constants
    const MENU_ID = 3;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->View('ChatHistory');

        $this->Language->Load('staff_livechat');
    }

    /**
     * Loads the Display Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('chathistoryfilter'), SWIFT_ChatRenderManager::RenderTree());

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

        $str = 'url_' . $_fieldName;
        if (!isset($_POST[$str])) {
            return '';
        }

        return $_POST[$str];
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

        $_urlData = $_imageData = $_responseContents = '';
        if (isset($_POST['urldata'])) {
            $_urlData = $_POST['urldata'];
        }

        if (isset($_POST['imagedata'])) {
            $_imageData = $_POST['imagedata'];
        }

        if (isset($_POST['responsecontents'])) {
            $_responseContents = $_POST['responsecontents'];
        }

        if (trim($_POST['title']) == '' || trim($_POST['cannedcategoryid']) == '') {
            $this->UserInterface->CheckFields('title', 'cannedcategoryid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (!isset($_POST['urldata']) && !isset($_POST['imagedata']) && !isset($_POST['responsecontents']) && !isset($_FILES['file_imagedata'])) {
            SWIFT::ErrorField('urldata', 'imagedata', 'responsecontents');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (trim($_urlData) == '' && trim($_imageData) == '' && trim($_responseContents) == '' && !isset($_FILES['file_imagedata'])) {
            SWIFT::ErrorField('urldata', 'imagedata', 'responsecontents');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_lscaninsertcanned') == '0') ||
            ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_lscanupdatecanned') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        // Any uploaded file? Check extensions...
        foreach (array('imagedata') as $_key => $_val) {
            $_uploadedFieldName = 'file_' . $_val;

            if (isset($_FILES[$_uploadedFieldName]) && isset($_FILES[$_uploadedFieldName]['tmp_name']) && is_uploaded_file($_FILES[$_uploadedFieldName]['tmp_name'])) {
                $_pathInfoContainer = pathinfo($_FILES[$_uploadedFieldName]['name']);
                if (!isset($_pathInfoContainer['extension']) || empty($_pathInfoContainer['extension']) || ($_pathInfoContainer['extension'] != 'gif' && $_pathInfoContainer['extension'] != 'jpeg' && $_pathInfoContainer['extension'] != 'jpg' && $_pathInfoContainer['extension'] != 'png')) {
                    SWIFT::ErrorField($_val);

                    $this->UserInterface->Error($this->Language->Get('titleinvalidfileext'), $this->Language->Get('msginvalidfileext'));

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Insert a new Canned Response
     *
     * @author Varun Shoor
     * @param int|false $_selectedCannedCategoryID (OPTIONAL) The Selected Canned Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert($_selectedCannedCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('canned'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lscaninsertcanned') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, $_selectedCannedCategoryID);
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

        if ($_POST['cannedcategoryid'] == '0') {
            $_parentCategoryTitle = $this->Language->Get('parentcategoryitem');
        } else {
            $_SWIFT_CannedCategoryObject = new SWIFT_CannedCategory($_POST['cannedcategoryid']);
            if (!$_SWIFT_CannedCategoryObject instanceof SWIFT_CannedCategory || !$_SWIFT_CannedCategoryObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_parentCategoryTitle = $_SWIFT_CannedCategoryObject->GetProperty('title');
        }

        $_finalText = '<b>' . $this->Language->Get('cannedresponsetitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('parentcategory') . ':</b> ' . htmlspecialchars($_parentCategoryTitle) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titlecannedresponse' . $_type), htmlspecialchars($_POST['title'])),
            sprintf($this->Language->Get('msgcannedresponse' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Return the processed variables
     *
     * @author Varun Shoor
     * @return array The variable container array on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetVariables()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_urlData = $_imageData = $_responseContents = '';
        if (isset($_POST['urldata'])) {
            $_urlData = $_POST['urldata'];
        }

        $_imageData = $this->_GetIcon('imagedata');

        if (isset($_POST['responsecontents'])) {
            $_responseContents = $_POST['responsecontents'];
        }

        if (!isset($_POST['responsetype'])) {
            $_responseType = SWIFT_CannedResponse::TYPE_NONE;
        } else {
            $_responseType = $_POST['responsetype'];
        }

        // Checkbox Processing
        if (isset($_POST['urldataenabled']) && $_POST['urldataenabled'] == '0') {
            $_urlData = '';
        }

        if (isset($_POST['imagedataenabled']) && $_POST['imagedataenabled'] == '0') {
            $_imageData = '';
        }

        if (isset($_POST['responsetypeenabled']) && $_POST['responsetypeenabled'] == '0') {
            $_responseType = SWIFT_CannedResponse::TYPE_NONE;
            $_responseContents = '';
        }

        return array('_urlData' => $_urlData, '_imageData' => $_imageData, '_responseType' => $_responseType, '_responseContents' => $_responseContents);
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
            $_variableContainer = $this->_GetVariables();

            $_cannedResponseID = SWIFT_CannedResponse::Create($_POST['cannedcategoryid'], $_POST['title'], $_variableContainer['_urlData'], $_variableContainer['_imageData'],
                $_variableContainer['_responseType'], $_variableContainer['_responseContents'], $_SWIFT->Staff->GetStaffID());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertcannedresponse'), htmlspecialchars($_POST['title'])),
                SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_cannedResponseID) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Controller('CannedCategory')->Manage(false, true);

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Canned Response ID
     *
     * @author Varun Shoor
     * @param int $_cannedResponseID The Canned Response ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_cannedResponseID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_cannedResponseID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_CannedResponseObject = new SWIFT_CannedResponse($_cannedResponseID);
        if (!$_SWIFT_CannedResponseObject instanceof SWIFT_CannedResponse || !$_SWIFT_CannedResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('canned'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lscanupdatecanned') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_CannedResponseObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_cannedResponseID The Canned Response ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_cannedResponseID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_cannedResponseID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_CannedResponseObject = new SWIFT_CannedResponse($_cannedResponseID);
        if (!$_SWIFT_CannedResponseObject instanceof SWIFT_CannedResponse || !$_SWIFT_CannedResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_variableContainer = $this->_GetVariables();

            $_updateResult = $_SWIFT_CannedResponseObject->Update($_POST['cannedcategoryid'], $_POST['title'], $_variableContainer['_urlData'], $_variableContainer['_imageData'],
                $_variableContainer['_responseType'], $_variableContainer['_responseContents'], $_SWIFT->Staff->GetStaffID());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatecannedresponse'), htmlspecialchars($_POST['title'])),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Controller('CannedCategory')->Manage(false, true);

            return true;
        }

        $this->Load->Edit($_cannedResponseID);

        return false;
    }
}
