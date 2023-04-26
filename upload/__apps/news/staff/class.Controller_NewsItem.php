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

namespace News\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use News\Library\Render\SWIFT_NewsRenderManager;
use News\Models\Category\SWIFT_NewsCategory;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Rules\SWIFT_Rules;
use Base\Models\SearchStore\SWIFT_SearchStore;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;

/**
 * The News Item Controller
 *
 * @author Varun Shoor
 *
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property Controller_NewsItem $Load
 * @property SWIFT_NewsRenderManager $NewsRenderManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_NewsItem $View
 */
class Controller_NewsItem extends \Controller_StaffBase
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
        $this->Language->Load('staff_newsitems');
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

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->NewsRenderManager->RenderTree());

        return true;
    }

    /**
     * Loads the Display Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayDataForViewNews()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->NewsRenderManager->RenderViewNewsTree());

        return true;
    }

    /**
     * Delete the News Items from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_newsItemIDList The News Item ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_newsItemIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_nwcandeleteitem') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_newsItemIDList)) {
            $_SWIFT->Database->Query("SELECT subject, author FROM " . TABLE_PREFIX . "newsitems WHERE newsitemid IN (" . BuildIN($_newsItemIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletenewsitem'),
                        htmlspecialchars(StripName($_SWIFT->Database->Record['subject'], 30)), htmlspecialchars($_SWIFT->Database->Record['author'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_NewsItem::DeleteList($_newsItemIDList);
        }

        return true;
    }

    /**
     * Delete the Given News Item ID
     *
     * @author Varun Shoor
     * @param int $_newsItemID The News Item ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_newsItemID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_newsItemID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the News Item Grid
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

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_nwcanmanageitems') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid($_searchStoreID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render Insert News Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertDialog() {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Check permission
        if ($_SWIFT->Staff->GetPermission('staff_nwcaninsertitem') == '0') {
            $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;

        }

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderInsertNewsDialog();
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

        if (trim($_POST['subject']) == '' || trim($_POST['newscontents_htmlcontents']) == '' || !isset($_POST['newstype']) || empty($_POST['newstype']))
        {
            $this->UserInterface->CheckFields('subject', 'newscontents', 'newstype');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_nwcaninsertitem') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_nwcanupdateitem') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a News Item
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($_POST['newstype'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('insertnews'), self::MENU_ID, 4);

        if ($_SWIFT->Staff->GetPermission('staff_nwcaninsertitem') == '0')
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

        SWIFT::Info(sprintf($this->Language->Get('titlenwitem' . $_type), htmlspecialchars($_POST['subject'])),
                sprintf($this->Language->Get('msgnwitem' . $_type), htmlspecialchars($_POST['subject'])));

        return true;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit($_isDraft = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_newsStatus = SWIFT_NewsItem::STATUS_PUBLISHED;
            if ($_isDraft == true)
            {
                $_newsStatus = SWIFT_NewsItem::STATUS_DRAFT;
            }

            $_userVisibilityCustom = $_staffVisibilityCustom = false;
            if (isset($_POST['uservisibilitycustom']))
            {
                $_userVisibilityCustom = ($_POST['uservisibilitycustom']);
            }

            if (isset($_POST['staffvisibilitycustom']))
            {
                $_staffVisibilityCustom = ($_POST['staffvisibilitycustom']);
            }

            $_expiry = GetDateFieldTimestamp('expiry');
            $_start = GetDateFieldTimestamp('start');

            if ($_start > $_expiry && $_expiry !== 0) {
                SWIFT::Alert($this->Language->Get('titlestartgtexpiry'), $this->Language->Get('msgstartgtexpiry'));
                $this->Load->Insert();

                return false;
            }

            $_newsItemID = SWIFT_NewsItem::Create($_POST['newstype'], $_newsStatus, $_SWIFT->Staff->GetProperty('fullname'), $_SWIFT->Staff->GetProperty('email'), $_POST['subject'],
                    '', $_POST['newscontents_htmlcontents'], $_SWIFT->Staff->GetStaffID(), $_expiry, $_POST['allowcomments'], $_userVisibilityCustom,
                    $this->_GetUserGroupIDList(), $_staffVisibilityCustom, $this->_GetStaffGroupIDList(), false, '', $_POST['customemailsubject'], false, $this->_GetNewsCategoryIDList(),
                    $_POST['sendemail'], $_POST['fromname'], $_POST['fromemail'], $_start);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertnewsitem'), htmlspecialchars(StripName($_POST['subject'], 25))),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_newsItemID)
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
     * Edit the News Item
     *
     * @author Varun Shoor
     * @param int $_newsItemID The News Item ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_newsItemID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_newsItemID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_NewsItemObject = new SWIFT_NewsItem($_newsItemID);
        if (!$_SWIFT_NewsItemObject instanceof SWIFT_NewsItem || !$_SWIFT_NewsItemObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_POST['newstype'] = $_SWIFT_NewsItemObject->GetProperty('newstype');

        $this->View->RenderInfoBox($_SWIFT_NewsItemObject);
        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . htmlspecialchars($_SWIFT_NewsItemObject->GetProperty('subject')), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_nwcanupdateitem') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_NewsItemObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_newsItemID The News Item ID
     * @param bool $_markAsPublished (OPTIONAL) Whether to mark the news item as published
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_newsItemID, $_markAsPublished = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_newsItemID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_NewsItemObject = new SWIFT_NewsItem($_newsItemID);
        if (!$_SWIFT_NewsItemObject instanceof SWIFT_NewsItem || !$_SWIFT_NewsItemObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {

            $_userVisibilityCustom = $_staffVisibilityCustom = false;
            if (isset($_POST['uservisibilitycustom']))
            {
                $_userVisibilityCustom = ($_POST['uservisibilitycustom']);
            }

            if (isset($_POST['staffvisibilitycustom']))
            {
                $_staffVisibilityCustom = ($_POST['staffvisibilitycustom']);
            }

            if ($_markAsPublished == true)
            {
                $_SWIFT_NewsItemObject->UpdateStatus((string)SWIFT_NewsItem::STATUS_PUBLISHED);
            }

            $_expiry = GetDateFieldTimestamp('expiry');
            $_start = GetDateFieldTimestamp('start');

            if ($_start > $_expiry && $_expiry !== 0) {
                SWIFT::Alert($this->Language->Get('titlestartgtexpiry'), $this->Language->Get('msgstartgtexpiry'));
                $this->Load->Edit($_newsItemID);

                return false;
            }

            $_updateResult = $_SWIFT_NewsItemObject->Update($_POST['subject'], '', $_POST['newscontents_htmlcontents'], $_SWIFT->Staff->GetStaffID(), $_expiry,
                    $_POST['allowcomments'], $_userVisibilityCustom, $this->_GetUserGroupIDList(), $_staffVisibilityCustom, $this->_GetStaffGroupIDList(),
                    $_POST['customemailsubject'], $this->_GetNewsCategoryIDList(), $_POST['sendemail'], $_POST['fromname'], $_POST['fromemail'], $_start);


            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatenewsitem'), htmlspecialchars(StripName($_POST['subject'], 25))),
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

        $this->Load->Edit($_newsItemID);

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
        foreach ($_POST['usergroupidlist'] as $_key => $_val)
        {
            if ($_val == '1')
            {
                $_assignedUserGroupIDList[] = ($_key);
            }
        }

        return $_assignedUserGroupIDList;
    }

    /**
     * Retrieve the News Category ID List
     *
     * @author Varun Shoor
     * @return array The News Category ID List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _GetNewsCategoryIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['newscategoryidlist']) || !_is_array($_POST['newscategoryidlist']))
        {
            return array();
        }

        return $_POST['newscategoryidlist'];
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

        $_newsItemIDList = array();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('newsitemgrid', 'newsitems.dateline', 'desc');

        switch ($_filterType)
        {
            case 'type': {
                if ($_filterValue == 'public')
                {
                    $this->Database->QueryLimit("SELECT newsitems.newsitemid FROM " . TABLE_PREFIX . "newsitems AS newsitems
                        WHERE newsitems.newstype = '" . (SWIFT_NewsItem::TYPE_PUBLIC) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                } else if ($_filterValue == 'private') {
                    $this->Database->QueryLimit("SELECT newsitems.newsitemid FROM " . TABLE_PREFIX . "newsitems AS newsitems
                        WHERE newsitems.newstype = '" . (SWIFT_NewsItem::TYPE_PRIVATE) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                } else if ($_filterValue == 'global') {
                    $this->Database->QueryLimit("SELECT newsitems.newsitemid FROM " . TABLE_PREFIX . "newsitems AS newsitems
                        WHERE newsitems.newstype = '" . (SWIFT_NewsItem::TYPE_GLOBAL) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                } else {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }
                while ($this->Database->NextRecord())
                {
                    $_newsItemIDList[] = $this->Database->Record['newsitemid'];
                }

            }
            break;

            case 'category': {
                $this->Database->QueryLimit("SELECT newscategorylinks.newsitemid FROM " . TABLE_PREFIX . "newscategorylinks AS newscategorylinks
                    LEFT JOIN " . TABLE_PREFIX . "newsitems AS newsitems ON (newscategorylinks.newsitemid = newsitems.newsitemid)
                    WHERE newscategorylinks.newscategoryid = '" . ($_filterValue) . "'
                    ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                while ($this->Database->NextRecord())
                {
                    $_newsItemIDList[] = $this->Database->Record['newsitemid'];
                }

            }
            break;


            case 'date': {
                $_extendedSQL = false;

                if ($_filterValue == 'today')
                {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newsitems.dateline', SWIFT_Rules::DATERANGE_TODAY);
                } else if ($_filterValue == 'yesterday') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newsitems.dateline', SWIFT_Rules::DATERANGE_YESTERDAY);
                } else if ($_filterValue == 'l7') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newsitems.dateline', SWIFT_Rules::DATERANGE_LAST7DAYS);
                } else if ($_filterValue == 'l30') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newsitems.dateline', SWIFT_Rules::DATERANGE_LAST30DAYS);
                } else if ($_filterValue == 'l180') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newsitems.dateline', SWIFT_Rules::DATERANGE_LAST180DAYS);
                } else if ($_filterValue == 'l365') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('newsitems.dateline', SWIFT_Rules::DATERANGE_LAST365DAYS);
                } else {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                if (!empty($_extendedSQL))
                {
                    $this->Database->QueryLimit("SELECT newsitems.newsitemid FROM " . TABLE_PREFIX . "newsitems AS newsitems
                        WHERE " . $_extendedSQL . "
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    while ($this->Database->NextRecord())
                    {
                        $_newsItemIDList[] = $this->Database->Record['newsitemid'];
                    }
                }

            }
            break;

            default:
                break;
        }

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_NEWS, $_newsItemIDList,
                $_SWIFT->Staff->GetStaffID());

        if (!_is_array($_newsItemIDList))
        {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }

    /**
     * View All News
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID (OPTIONAL) The filter by news category id
     * @param int $_offset
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ViewAll($_newsCategoryID = 0, $_offset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_newsCategoryID = ($_newsCategoryID);
        $_offset = ($_offset);

        $this->_LoadDisplayDataForViewNews();

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('viewall'), self::MENU_ID, 0);

        if ($_SWIFT->Staff->GetPermission('staff_nwcanviewitems') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderViewAll($_newsCategoryID, $_offset);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * View the News Item
     *
     * @author Varun Shoor
     * @param int $_newsItemID The News Item ID
     * @param int $_newsCategoryID (OPTIONAL) The filter by news category id
     * @param int $_offset
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ViewItem($_newsItemID, $_newsCategoryID = 0, $_offset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_newsItemID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');

        $_SWIFT_NewsItemObject = new SWIFT_NewsItem($_newsItemID);
        if (!$_SWIFT_NewsItemObject instanceof SWIFT_NewsItem || !$_SWIFT_NewsItemObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->View->RenderInfoBox($_SWIFT_NewsItemObject);
        $this->_LoadDisplayDataForViewNews();

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . htmlspecialchars($_SWIFT_NewsItemObject->GetProperty('subject')), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_nwcanviewitems') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderViewItem($_SWIFT_NewsItemObject, $_newsCategoryID, $_offset);
        }

        $this->UserInterface->Footer();

        return true;
    }
}
