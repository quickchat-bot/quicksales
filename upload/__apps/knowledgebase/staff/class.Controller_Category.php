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

namespace Knowledgebase\Staff;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Controller_StaffBase;
use Knowledgebase\Library\Render\SWIFT_KnowledgebaseRenderManager;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * The Knowledgebase Category Controller
 *
 * @author Varun Shoor
 *
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_KnowledgebaseRenderManager $KnowledgebaseRenderManager
 * @property View_Category $View
 * @property Controller_Category $Load
 */
class Controller_Category extends Controller_StaffBase
{
    // Core Constants
    const MENU_ID = 4;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Load->Library('Render:KnowledgebaseRenderManager');

        $this->Language->Load('staff_knowledgebase');
    }

    /**
     * Delete the Knowledgebase Categories from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_knowledgebaseCategoryIDList The Knowledgebase Category ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_knowledgebaseCategoryIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_kbcandeletecategory') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_knowledgebaseCategoryIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "kbcategories
                WHERE kbcategoryid IN (" . BuildIN($_knowledgebaseCategoryIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletekbcategory'),
                        htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                        SWIFT_StaffActivityLog::SECTION_KNOWLEDGEBASE, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_KnowledgebaseCategory::DeleteList($_knowledgebaseCategoryIDList);
        }

        return true;
    }

    /**
     * Delete the Given Knowledgebase Category ID
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseCategoryID The Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_knowledgebaseCategoryID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_knowledgebaseCategoryID), true);

        $this->Load->Manage(false, false);

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
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_kbcanviewcategories') == '0')
        {
            return false;
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->KnowledgebaseRenderManager->RenderTree());

        return true;
    }

    /**
     * Displays the Knowledgebase Categories
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        return $this->Load->Manage();
    }

    /**
     * Displays the Knowledgebase Categories
     *
     * @author Varun Shoor
     * @param bool $_p1
     * @param bool $_p2
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_p1 = false, $_p2 = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . $this->Language->Get('categories'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('activitydeletekbcategory') == '0' || $_SWIFT->Staff->GetPermission('staff_kbcanviewcategories') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderTabs();
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

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (trim($_POST['title']) == '' || trim($_POST['parentkbcategoryid']) == '' || trim($_POST['categorytype']) == '' || empty($_POST['displayorder']))
        {
            $this->UserInterface->CheckFields('title', 'parentkbcategoryid', 'categorytype', 'displayorder');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_kbcaninsertcategory') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_kbcanupdatecategory') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Knowledgebase Category
     *
     * @author Varun Shoor
     * @param int|false $_selectedKnowledgebaseCategoryID (OPTIONAL) The Selected Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert($_selectedKnowledgebaseCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . $this->Language->Get('categories'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_kbcaninsertcategory') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, $_selectedKnowledgebaseCategoryID);
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_type = 'insert';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        }

        $_parentCategoryTitle = '';
        if ($_POST['parentkbcategoryid'] == '0')
        {
            $_parentCategoryTitle = $this->Language->Get('parentcategoryitem');
        } else {
            $_SWIFT_KnowledgebaseCategoryObject = $this->getKnowledgeBaseFromParentId($_POST['parentkbcategoryid']);
            $_parentCategoryTitle = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('title');
        }

        $_categoryType = $this->Language->Get('private');
        if ($_POST['categorytype'] == SWIFT_KnowledgebaseCategory::TYPE_GLOBAL)
        {
            $_categoryType = $this->Language->Get('global');
        } else if ($_POST['categorytype'] == SWIFT_KnowledgebaseCategory::TYPE_PUBLIC) {
            $_categoryType = $this->Language->Get('public');
        } else if ($_POST['categorytype'] == SWIFT_KnowledgebaseCategory::TYPE_INHERIT) {
            $_categoryType = $this->Language->Get('inherit');
        }

        $_finalText = '<b>' . $this->Language->Get('categorytitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('parentcategory') . ':</b> ' . htmlspecialchars($_parentCategoryTitle) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('categorytype') . ':</b> ' . $_categoryType . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titlekbcategory' . $_type), htmlspecialchars($_POST['title'])),
                sprintf($this->Language->Get('msgkbcategory' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

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

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_userVisibilityCustom = $_staffVisibilityCustom = false;

            if ($_POST['categorytype'] == SWIFT_KnowledgebaseCategory::TYPE_INHERIT && $_POST['parentkbcategoryid'] > 0) {
                $_SWIFT_KnowledgebaseCategoryObject = $this->getKnowledgeBaseFromParentId($_POST['parentkbcategoryid']);
                $_userVisibilityCustom = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('uservisibilitycustom');
                $_staffVisibilityCustom = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('staffvisibilitycustom');
                $_staffGroupIDList = $_SWIFT_KnowledgebaseCategoryObject->GetLinkedStaffGroupIDList();
                $_userGroupIdList = $_SWIFT_KnowledgebaseCategoryObject->GetLinkedUserGroupIDList();
            } else {
                if (isset($_POST['uservisibilitycustom'])) {
                   $_userVisibilityCustom = ($_POST['uservisibilitycustom']);
                }

                if (isset($_POST['staffvisibilitycustom'])) {
                   $_staffVisibilityCustom = ($_POST['staffvisibilitycustom']);
                }

                $_staffGroupIDList = $this->_GetStaffGroupIDList();
                $_userGroupIdList = $this->_GetUserGroupIDList();
            }

            $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseCategory::Create($_POST['parentkbcategoryid'], $_POST['title'], $_POST['categorytype'], $_POST['displayorder'],
                    $_POST['articlesortorder'], $_POST['allowcomments'], $_POST['allowrating'], $_POST['ispublished'], $_userVisibilityCustom, $_userGroupIdList, $_staffVisibilityCustom, $_staffGroupIDList, $_SWIFT->Staff->GetStaffID());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertkbcategory'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_KNOWLEDGEBASE, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_knowledgebaseCategoryIDList)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage(false, false);

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Knowledgebase Category ID
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseCategoryID The Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_knowledgebaseCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_knowledgebaseCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_KnowledgebaseCategoryObject = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_knowledgebaseCategoryID));
        if (!$_SWIFT_KnowledgebaseCategoryObject instanceof SWIFT_KnowledgebaseCategory || !$_SWIFT_KnowledgebaseCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . $this->Language->Get('editcategory'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_kbcanupdatecategory') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_KnowledgebaseCategoryObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseCategoryID The Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_knowledgebaseCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_knowledgebaseCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_KnowledgebaseCategoryObject = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_knowledgebaseCategoryID));
        if (!$_SWIFT_KnowledgebaseCategoryObject instanceof SWIFT_KnowledgebaseCategory || !$_SWIFT_KnowledgebaseCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_userVisibilityCustom = $_staffVisibilityCustom = false;

            if ($_POST['categorytype'] == SWIFT_KnowledgebaseCategory::TYPE_INHERIT && $_POST['parentkbcategoryid'] > 0) {
                $_SWIFT_ParentKnowledgebaseCategoryObject = $this->getKnowledgeBaseFromParentId($_POST['parentkbcategoryid']);
                $_userVisibilityCustom = $_SWIFT_ParentKnowledgebaseCategoryObject->GetProperty('uservisibilitycustom');
                $_staffVisibilityCustom = $_SWIFT_ParentKnowledgebaseCategoryObject->GetProperty('staffvisibilitycustom');
                $_staffGroupIDList = $_SWIFT_ParentKnowledgebaseCategoryObject->GetLinkedStaffGroupIDList();
                $_userGroupIdList = $_SWIFT_ParentKnowledgebaseCategoryObject->GetLinkedUserGroupIDList();
            } else {
                if (isset($_POST['uservisibilitycustom'])) {
                    $_userVisibilityCustom = ($_POST['uservisibilitycustom']);
                }

                if (isset($_POST['staffvisibilitycustom'])) {
                    $_staffVisibilityCustom = ($_POST['staffvisibilitycustom']);
                }

                $_staffGroupIDList = $this->_GetStaffGroupIDList();
                $_userGroupIdList = $this->_GetUserGroupIDList();
            }

            /*
             * BUG FIX - Bishwanath Jha
             *
             * SWIFT-3815: Private knowledgebase article getting searched on the Support Center, Category scope issue
             *
             * Comments: If any Category now is linked to - Parent Category - and had Category scope 'Inherit' then move Category scope to 'Global' by default.
             */
            $_categoryType = $_POST['categorytype'];

            if ($_POST['parentkbcategoryid'] == 0 && $_POST['categorytype'] == SWIFT_KnowledgebaseCategory::TYPE_INHERIT
                && $_SWIFT_KnowledgebaseCategoryObject->Get('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_INHERIT)
            {
                $_categoryType = SWIFT_KnowledgebaseCategory::TYPE_GLOBAL;
            }

            SWIFT_KnowledgebaseCategory::UpdateChildrenInheritedLinks($_SWIFT_KnowledgebaseCategoryObject,
                $_userVisibilityCustom ?? 0, $_staffVisibilityCustom ?? 0, $_userGroupIdList ?? [], $_staffGroupIDList ?? []);

            $_updateResult = $_SWIFT_KnowledgebaseCategoryObject->Update($_POST['parentkbcategoryid'], $_POST['title'], $_categoryType, $_POST['displayorder'], $_POST['articlesortorder'],
                    $_POST['allowcomments'], $_POST['allowrating'], $_POST['ispublished'], $_userVisibilityCustom, $_userGroupIdList, $_staffVisibilityCustom, $_staffGroupIDList, $_SWIFT->Staff->GetStaffID());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatekbcategory'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_KNOWLEDGEBASE, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage(false, false);

            return true;
        }

        $this->Load->Edit($_knowledgebaseCategoryID);

        return false;
    }

    /**
     * Retrieve the Assigned Staff Group ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedStaffGroupIDList" (ARRAY) on Success, "false" otherwise
     */
    private function _GetStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['staffgroupidlist']) || !_is_array($_POST['staffgroupidlist']))
        {
            return array();
        }

        $_assignedStaffGroupIDList = array();
        foreach ($_POST['staffgroupidlist'] as $_key => $_val)
        {
            if ($_val == '1')
            {
                $_assignedStaffGroupIDList[] = ($_key);
            }
        }

        return $_assignedStaffGroupIDList;
    }

    /**
     * Retrieve the Assigned User Group ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedUserGroupIDList" (ARRAY) on Success, "false" otherwise
     */
    private function _GetUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['usergroupidlist']) || !_is_array($_POST['usergroupidlist']))
        {
            return array();
        }

        $_assignedUserGroupIDList = array();

        /*
         * BUG FIX - Nidhi Gupta
         *
         * SWIFT-3815: Private knowledgebase article getting searched on the Support Center, Category scope issue
         *
         * Comments: If category type is not private then only prepare usergroupidlist.
         */
        if ($_POST['categorytype'] != SWIFT_KnowledgebaseCategory::TYPE_PRIVATE) {
            foreach ($_POST['usergroupidlist'] as $_key => $_val) {
                if ($_val == '1') {
                    $_assignedUserGroupIDList[] = ($_key);
                }
            }
        }

        return $_assignedUserGroupIDList;
    }

    protected function getKnowledgeBaseFromParentId($parentCategoryId)
    {
        $_SWIFT_KnowledgebaseCategoryObject = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($parentCategoryId));
        if (!$_SWIFT_KnowledgebaseCategoryObject instanceof SWIFT_KnowledgebaseCategory || !$_SWIFT_KnowledgebaseCategoryObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        return $_SWIFT_KnowledgebaseCategoryObject;
    }
}
