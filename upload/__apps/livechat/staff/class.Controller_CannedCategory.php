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
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use LiveChat\Library\Chat\SWIFT_ChatRenderManager;
use LiveChat\Models\Canned\SWIFT_CannedCategory;
use LiveChat\Models\Canned\SWIFT_CannedResponse;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * The Canned Category Controller
 *
 * @author Varun Shoor
 *
 * @property View_CannedCategory $View
 */
class Controller_CannedCategory extends Controller_staff
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
     * Delete the Canned Categories from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_cannedCategoryIDList The Canned Category ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_cannedCategoryIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_lscandeletecanned') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_cannedCategoryIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cannedcategories
                WHERE cannedcategoryid IN (" . BuildIN($_cannedCategoryIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletecannedcategory'),
                    htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_CannedCategory::DeleteList($_cannedCategoryIDList);
        }

        return true;
    }

    /**
     * Delete the Given Canned Category ID
     *
     * @author Varun Shoor
     * @param int $_cannedCategoryID The Canned Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_cannedCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($_cannedCategoryID), true);

        $this->Load->Manage(false, false);

        return true;
    }

    /**
     * Delete the Canned Resoibses from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_cannedResponseIDList The Canned Response ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteResponseList($_cannedResponseIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        SWIFT::Set('displayresponsetab', true);

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_lscandeletecanned') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_cannedResponseIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cannedresponses
                WHERE cannedresponseid IN (" . BuildIN($_cannedResponseIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletecannedresponse'),
                    htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_CannedResponse::DeleteList($_cannedResponseIDList);
        }

        return true;
    }

    /**
     * Delete the Given Canned Response ID
     *
     * @author Varun Shoor
     * @param int $_cannedResponseID The Canned Resopnse ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteResponse($_cannedResponseID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteResponseList(array($_cannedResponseID), true);

        $this->Load->Manage(false, true);

        return true;
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

            return false;
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('chathistoryfilter'), SWIFT_ChatRenderManager::RenderTree());

        return true;
    }

    /**
     * Displays the Canned Categories
     *
     * @author Varun Shoor
     * @param bool $_isResponsesTabSelected Whether the responses tab is selected by default
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_isResponsesTabSelected = false)
    {
        return $this->Load->Manage(false, $_isResponsesTabSelected);
    }

    /**
     * Displays the Canned Categories
     *
     * @author Varun Shoor
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @param int $_isResponsesTabSelected (OPTIONAL) Whether the responses tab is selected by default
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = 0, $_isResponsesTabSelected = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_numeric($_isResponsesTabSelected)) {
            $_isResponsesTabSelected = 0;
        }

        if (!is_numeric($_searchStoreID)) {
            $_searchStoreID = 0;
        }

        if (isset($_POST['itemid']) || SWIFT::Get('displayresponsetab') == true) {
            $_isResponsesTabSelected = 0;
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('canned'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lscanviewcanned') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderTabs($_isResponsesTabSelected, $_searchStoreID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @param SWIFT_CannedCategory $_SWIFT_CannedCategoryObject (OPTIONAL) The Canned Category Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, SWIFT_CannedCategory $_SWIFT_CannedCategoryObject = null)
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

            return false;
        }

        if (trim($_POST['title']) == '' || trim($_POST['parentcategoryid']) == '' || trim($_POST['categorytype']) == '') {
            $this->UserInterface->CheckFields('title', 'parentcategoryid', 'categorytype');

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

        if ($_SWIFT_CannedCategoryObject instanceof SWIFT_CannedCategory && $_SWIFT_CannedCategoryObject->GetIsClassLoaded()) {
            $_subCannedCategoryIDList = SWIFT_CannedCategory::RetrieveSubCategoryIDList(array($_SWIFT_CannedCategoryObject->GetIsClassLoaded()));

            if (($_POST['parentcategoryid'] != '0' && in_array($_POST['parentcategoryid'], $_subCannedCategoryIDList)) ||
                $_POST['parentcategoryid'] == $_SWIFT_CannedCategoryObject->GetCannedCategoryID()) {
                $this->UserInterface->Error($this->Language->Get('titleinvalidparentcat'), $this->Language->Get('msginvalidparentcat'));

                return false;
            }
        }

        return true;
    }

    /**
     * Insert a new Canned Category
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

            return false;
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

            return false;
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        if ($_POST['parentcategoryid'] == '0') {
            $_parentCategoryTitle = $this->Language->Get('parentcategoryitem');
        } else {
            $_SWIFT_CannedCategoryObject = new SWIFT_CannedCategory($_POST['parentcategoryid']);
            if (!$_SWIFT_CannedCategoryObject instanceof SWIFT_CannedCategory || !$_SWIFT_CannedCategoryObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);

                return false;
            }

            $_parentCategoryTitle = $_SWIFT_CannedCategoryObject->GetProperty('title');
        }

        $_finalText = '<b>' . $this->Language->Get('cannedcategorytitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('parentcategory') . ':</b> ' . htmlspecialchars($_parentCategoryTitle) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('categorytype') . ':</b> ' . IIF($_POST['categorytype'] == '1', $this->Language->Get('public'), $this->Language->Get('private')) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titlecannedcategory' . $_type), htmlspecialchars($_POST['title'])),
            sprintf($this->Language->Get('msgcannedcategory' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

        return true;
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

            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_cannedCategoryID = SWIFT_CannedCategory::Create((int)($_POST['categorytype']), $_POST['title'],
                (int)($_POST['parentcategoryid']), $_SWIFT->Staff->GetStaffID());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertcannedcategory'), htmlspecialchars($_POST['title'])),
                SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_cannedCategoryID) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);

                return false;
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage(false, false);

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Canned Category ID
     *
     * @author Varun Shoor
     * @param int $_cannedCategoryID The Canned Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_cannedCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_cannedCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_CannedCategoryObject = new SWIFT_CannedCategory($_cannedCategoryID);
        if (!$_SWIFT_CannedCategoryObject instanceof SWIFT_CannedCategory || !$_SWIFT_CannedCategoryObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('canned'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lscanupdatecanned') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_CannedCategoryObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_cannedCategoryID The Canned Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_cannedCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_cannedCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_CannedCategoryObject = new SWIFT_CannedCategory($_cannedCategoryID);
        if (!$_SWIFT_CannedCategoryObject instanceof SWIFT_CannedCategory || !$_SWIFT_CannedCategoryObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_CannedCategoryObject)) {
            $_updateResult = $_SWIFT_CannedCategoryObject->Update((int)($_POST['categorytype']), $_POST['title'],
                (int)($_POST['parentcategoryid']), $_SWIFT->Staff->GetStaffID());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatecannedcategory'), htmlspecialchars($_POST['title'])),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);

                return false;
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage(false, false);

            return true;
        }

        $this->Load->Edit($_cannedCategoryID);

        return false;
    }

    /**
     * Quick Filter Options
     *
     * @author Varun Shoor
     * @param string $_filterType The Filter Type
     * @param string $_filterValue The Filter Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickResponseFilter($_filterType, $_filterValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_searchStoreID = -1;

        $_cannedResponseIDList = array();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('responsegrid', 'cannedresponses.title', 'asc');

        switch ($_filterType) {
            case 'category':
                {
                    $this->Database->QueryLimit("SELECT cannedresponses.cannedresponseid FROM " . TABLE_PREFIX . "cannedresponses AS cannedresponses
                    LEFT JOIN " . TABLE_PREFIX . "cannedcategories AS cannedcategories ON (cannedresponses.cannedcategoryid = cannedcategories.cannedcategoryid)
                    WHERE cannedresponses.cannedcategoryid = '" . (int)($_filterValue) . "'
                        AND cannedcategories.categorytype = '" . SWIFT_CannedCategory::TYPE_PUBLIC . "' OR (cannedcategories.categorytype = '" . SWIFT_CannedCategory::TYPE_PRIVATE . "' AND cannedcategories.staffid = '" . $_SWIFT->Staff->GetStaffID() . "')
                    ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    while ($this->Database->NextRecord()) {
                        $_cannedResponseIDList[] = $this->Database->Record['cannedresponseid'];
                    }

                }
                break;

            default:
                break;
        }

        if (_is_array($_cannedResponseIDList)) {
            $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_CANNEDRESPONSE, $_cannedResponseIDList, $_SWIFT->Staff->GetStaffID());
        } else {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID, 1);

        return true;
    }
}
