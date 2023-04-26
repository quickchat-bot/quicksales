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

namespace News\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterfaceClient;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Models\SearchStore\SWIFT_SearchStore;
use News\Library\Render\SWIFT_NewsRenderManager;
use News\Models\Category\SWIFT_NewsCategory;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Category Controller
 *
 * @author Varun Shoor
 *
 * @method Library($_libraryName)
 * @property Controller_Category $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_NewsRenderManager $NewsRenderManager
 * @property  View_Category $View
 */
class Controller_Category extends \Controller_StaffBase
{
    // Core Constants
    const MENU_ID = 7;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Load->Library('Render:NewsRenderManager');

        $this->Language->Load('staff_news');
        $this->Language->Load('staff_newscategories');
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->NewsRenderManager->RenderCategoryTree());

        return true;
    }

    /**
     * Delete the News Categories from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_newsCategoryIDList The News Category ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_newsCategoryIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_nwcandeletecategory') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_newsCategoryIDList)) {
            $_SWIFT->Database->Query("SELECT categorytitle FROM " . TABLE_PREFIX . "newscategories WHERE newscategoryid IN (" . BuildIN($_newsCategoryIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletenewscategory'),
                        htmlspecialchars($_SWIFT->Database->Record['categorytitle'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                        SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_NewsCategory::DeleteList($_newsCategoryIDList);
        }

        return true;
    }

    /**
     * Delete the Given News Category ID
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID The News Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_newsCategoryID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_newsCategoryID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the News Category Grid
     *
     * @author Varun Shoor
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('categories'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_nwcanviewcategories') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid($_searchStoreID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @param int $_newsCategoryID The News Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, $_newsCategoryID = 0)
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

        if (trim($_POST['categorytitle']) == '')
        {
            $this->UserInterface->CheckFields('categorytitle');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_nwcaninsertcategory') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_nwcanupdatecategory') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $_newsCategoryContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "newscategories WHERE LCASE(categorytitle) = '" . $this->Database->Escape(mb_strtolower($_POST['categorytitle'])) . "'");
        if (isset($_newsCategoryContainer['newscategoryid']) && $_newsCategoryContainer['newscategoryid'] != $_newsCategoryID)
        {
            $this->UserInterface->Error(
                    sprintf($this->Language->Get('titlenwcatmismatch'), htmlspecialchars($_newsCategoryContainer['categorytitle'])),
                    sprintf($this->Language->Get('msgnwcatmismatch'), htmlspecialchars($_newsCategoryContainer['categorytitle'])));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Category
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('insertcategory'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_nwcaninsertcategory') == '0')
        {
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_type = 'insert';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        }

        SWIFT::Info(sprintf($this->Language->Get('titlenwcategory' . $_type), htmlspecialchars($_POST['categorytitle'])),
                sprintf($this->Language->Get('msgnwcategory' . $_type), htmlspecialchars($_POST['categorytitle'])));

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
            $_newsCategoryID = SWIFT_NewsCategory::Create($_POST['categorytitle'], IIF($_POST['visibilitytype'] == '1', SWIFT_PUBLIC, SWIFT_PRIVATE));

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertnewscategory'), htmlspecialchars($_POST['categorytitle'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_newsCategoryID)
            {
                // @codeCoverageIgnoreStart
                // This code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the News Category
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID The News Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_newsCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_newsCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_NewsCategoryObject = new SWIFT_NewsCategory($_newsCategoryID);
        if (!$_SWIFT_NewsCategoryObject instanceof SWIFT_NewsCategory || !$_SWIFT_NewsCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('editcategory'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_nwcanupdatecategory') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_NewsCategoryObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID The News Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_newsCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_newsCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_NewsCategoryObject = new SWIFT_NewsCategory($_newsCategoryID);
        if (!$_SWIFT_NewsCategoryObject instanceof SWIFT_NewsCategory || !$_SWIFT_NewsCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_newsCategoryID))
        {
            $_updateResult = $_SWIFT_NewsCategoryObject->Update($_POST['categorytitle'], IIF($_POST['visibilitytype'] == '1', SWIFT_PUBLIC, SWIFT_PRIVATE));

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatenewscategory'), htmlspecialchars($_POST['categorytitle'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // This code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_newsCategoryID);

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
    public function QuickFilter($_filterType, $_filterValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchStoreID = -1;

        $_categoryIDList = array();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('newscategorygrid', 'newscategories.dateline', 'desc');

        switch ($_filterType)
        {
            case 'visibility': {
                if ($_filterValue == 'public')
                {
                    $this->Database->QueryLimit("SELECT newscategories.newscategoryid FROM " . TABLE_PREFIX . "newscategories AS newscategories
                        WHERE newscategories.visibilitytype = '" . SWIFT_PUBLIC . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                } else if ($_filterValue == 'private') {
                    $this->Database->QueryLimit("SELECT newscategories.newscategoryid FROM " . TABLE_PREFIX . "newscategories AS newscategories
                        WHERE newscategories.visibilitytype = '" . (SWIFT_PRIVATE) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                } else {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }
                while ($this->Database->NextRecord())
                {
                    $_categoryIDList[] = $this->Database->Record['newscategoryid'];
                }

            }
                break;

            case 'date': {
                $_extendedSQL = false;

                if ($_filterValue == 'today')
                {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newscategories.dateline', SWIFT_Rules::DATERANGE_TODAY);
                } else if ($_filterValue == 'yesterday') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newscategories.dateline', SWIFT_Rules::DATERANGE_YESTERDAY);
                } else if ($_filterValue == 'l7') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newscategories.dateline', SWIFT_Rules::DATERANGE_LAST7DAYS);
                } else if ($_filterValue == 'l30') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newscategories.dateline', SWIFT_Rules::DATERANGE_LAST30DAYS);
                } else if ($_filterValue == 'l180') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newscategories.dateline', SWIFT_Rules::DATERANGE_LAST180DAYS);
                } else if ($_filterValue == 'l365') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newscategories.dateline', SWIFT_Rules::DATERANGE_LAST365DAYS);
                } else {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                if (!empty($_extendedSQL))
                {
                    $this->Database->QueryLimit("SELECT newscategories.newscategoryid FROM " . TABLE_PREFIX . "newscategories AS newscategories
                        WHERE " . $_extendedSQL . "
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    while ($this->Database->NextRecord())
                    {
                        $_categoryIDList[] = $this->Database->Record['newscategoryid'];
                    }
                }

            }
                break;

            default:
                break;
        }

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_NEWSCATEGORIES, $_categoryIDList,
            $_SWIFT->Staff->GetStaffID());

        if (!_is_array($_categoryIDList))
        {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }
}
