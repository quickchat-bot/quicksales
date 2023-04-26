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

use Controller_StaffBase;
use Knowledgebase\Library\Render\SWIFT_KnowledgebaseRenderManager;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_App;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Loader;
use Base\Library\Rules\SWIFT_Rules;
use Base\Models\SearchStore\SWIFT_SearchStore;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;

/**
 * The Knowledgebase Article Controller
 *
 * @author Varun Shoor
 *
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @method Controller($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_KnowledgebaseRenderManager $KnowledgebaseRenderManager
 * @property View_Article $View
 * @property Controller_Article $Load
 */
class Controller_Article extends Controller_StaffBase
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

        if (SWIFT_App::IsInstalled(APP_TICKETS))
        {
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
            SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
        }

        $this->Language->Load('staff_knowledgebase');
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

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->KnowledgebaseRenderManager->RenderTree());

        return true;
    }

    /**
     * Delete the Knowledgebase Articles from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_knowledgebaseArticleIDList The Knowledgebase Article ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_knowledgebaseArticleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_kbcandeletearticle') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_knowledgebaseArticleIDList)) {
            $_SWIFT->Database->Query("SELECT subject, author FROM " . TABLE_PREFIX . "kbarticles WHERE kbarticleid IN (" . BuildIN($_knowledgebaseArticleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletekbarticle'),
                        htmlspecialchars(StripName($_SWIFT->Database->Record['subject'], 30)), htmlspecialchars($_SWIFT->Database->Record['author'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_KNOWLEDGEBASE, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_KnowledgebaseArticle::DeleteList($_knowledgebaseArticleIDList);
        }

        return true;
    }

    /**
     * Delete the Given Knowledgebase Article ID
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_knowledgebaseArticleID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_knowledgebaseArticleID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Knowledgebase Article Grid
     *
     * @author Varun Shoor
     * @param int|false $_searchStoreID (OPTIONAL) The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_kbcanmanagearticles') == '0')
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

        if (trim($_POST['subject']) == '' || trim($_POST['articlecontents_htmlcontents']) == '' || !isset($_POST['kbcategoryidlist']) || !_is_array($_POST['kbcategoryidlist']))
        {
            $this->UserInterface->CheckFields('subject', 'articlecontents', 'kbcategoryidlist');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_kbcaninsertarticle') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_kbcanupdatearticle') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a Knowledgebase Article
     *
     * @author Varun Shoor
     * @param int|false $_knowledgebaseCategoryID (OPTIONAL) The Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Insert($_knowledgebaseCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . $this->Language->Get('insertarticle'), self::MENU_ID, 4);

        if ($_SWIFT->Staff->GetPermission('staff_kbcaninsertarticle') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, $_knowledgebaseCategoryID);
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

        SWIFT::Info(sprintf($this->Language->Get('titlekbarticle' . $_type), htmlspecialchars($_POST['subject'])),
                sprintf($this->Language->Get('msgkbarticle' . $_type), htmlspecialchars($_POST['subject'])));

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
            $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PENDINGAPPROVAL;
            if ($_SWIFT->Staff->GetPermission('staff_kbcaninsertpublishedarticles') != '0' && $_isDraft == false)
            {
                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED;
            } else if ($_isDraft == true) {
                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_DRAFT;
            }

            $_knowledgebaseArticleID = SWIFT_KnowledgebaseArticle::Create(SWIFT_KnowledgebaseArticle::CREATOR_STAFF, $_SWIFT->Staff->GetStaffID(), $_SWIFT->Staff->GetProperty('fullname'),
                $_SWIFT->Staff->GetProperty('email'), $_articleStatus, $_POST['subject'], $_POST['seosubject'], $_POST['articlecontents_htmlcontents'], $_POST['isfeatured'], $_POST['allowcomments'],
                $this->_GetKnowledgebaseCategoryIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertkbarticle'), htmlspecialchars(StripName($_POST['subject'], 25))),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_KNOWLEDGEBASE, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_knowledgebaseArticleID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_knowledgebaseArticleID));
            $_SWIFT_KnowledgebaseArticleObject->ProcessPostAttachments();

            if ($_POST['tredir_ticketid'] != '0')
            {
                if ($_POST['tredir_listtype'] == 'viewticket') {
                    $this->Load->Controller('Ticket', APP_TICKETS)->View($_POST['tredir_ticketid'], $_POST['tredir_listtype'], $_POST['tredir_departmentid'], $_POST['tredir_ticketstatusid'],
                            $_POST['tredir_tickettypeid']);
                } else {
                    $this->Load->Controller('Manage', APP_TICKETS)->Redirect($_POST['tredir_listtype'], $_POST['tredir_departmentid'], $_POST['tredir_ticketstatusid'], $_POST['tredir_tickettypeid']);
                }

                return true;
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Knowledgebase Article
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_knowledgebaseArticleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_knowledgebaseArticleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_knowledgebaseArticleID));
        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->View->RenderInfoBox($_SWIFT_KnowledgebaseArticleObject);
        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . htmlspecialchars($_SWIFT_KnowledgebaseArticleObject->GetProperty('subject')), self::MENU_ID, self::NAVIGATION_ID);

        /**
         * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-1326 "Knowledgebase category restrictions to staff teams do not take effect".
         *
         * Comment - Fixed for the KB article accessed by URL. Checked the logged in staff permission with the KB category team permission.
         */
        $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseArticleLink::RetrieveLinkIDListOnArticle($_knowledgebaseArticleID, SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY);

        $_staffGroupLinkMap = SWIFT_StaffGroupLink::RetrieveMap(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_knowledgebaseCategoryIDList);

        $_staffSessionID = $_SWIFT->Cookie->Get('sessionid' . SWIFT_Interface::INTERFACE_STAFF);

        $_SWIFT->Database->QueryLimit("SELECT staffid FROM " . TABLE_PREFIX . "staffloginlog WHERE sessionid = '" . $_staffSessionID . "'");

        $_staffID = 0;
        while ($_SWIFT->Database->NextRecord()) {
            $_staffID = $_SWIFT->Database->Record['staffid'];
        }

        $_SWIFT->Database->QueryLimit("SELECT staffvisibilitycustom FROM " . TABLE_PREFIX . "kbcategories WHERE kbcategoryid = '" . $_knowledgebaseCategoryIDList[0] . "'");

        $_staffvisibilitycustomID = 0;
        while ($_SWIFT->Database->NextRecord()) {
            $_staffvisibilitycustomID = $_SWIFT->Database->Record['staffvisibilitycustom'];
        }
        $_staffGroupID = SWIFT_Staff::RetrieveStaffGroupOnStaffID($_staffID);
        if($_knowledgebaseCategoryIDList[0] == 0 || $_staffvisibilitycustomID == 0) {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_KnowledgebaseArticleObject);
        } elseif ($_SWIFT->Staff->GetPermission('staff_kbcanupdatearticle') == '0' || !in_array($_staffGroupID, $_staffGroupLinkMap[$_knowledgebaseCategoryIDList[0]])) {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_KnowledgebaseArticleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @param bool $_markAsPublished (OPTIONAL) Whether to mark the knowledgebase item as published
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_knowledgebaseArticleID, $_markAsPublished = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_knowledgebaseArticleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_knowledgebaseArticleID));
        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            if ($_markAsPublished == '1')
            {
                if ($_SWIFT->Staff->GetPermission('staff_kbcaninsertpublishedarticles') == '0') {
                    $_SWIFT_KnowledgebaseArticleObject->UpdateStatus(SWIFT_KnowledgebaseArticle::STATUS_PENDINGAPPROVAL);
                } else {
                    $_SWIFT_KnowledgebaseArticleObject->UpdateStatus(SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED);
                }
            } else if ($_SWIFT_KnowledgebaseArticleObject->GetProperty('articlestatus') == SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED && $_markAsPublished == '-1') {
                $_SWIFT_KnowledgebaseArticleObject->UpdateStatus(SWIFT_KnowledgebaseArticle::STATUS_DRAFT);
            }

            $_updateResult = $_SWIFT_KnowledgebaseArticleObject->Update($_SWIFT->Staff->GetStaffID(), $_POST['subject'], $_POST['seosubject'], $_POST['articlecontents_htmlcontents'], $_POST['isfeatured'],
                $_POST['allowcomments'], $this->_GetKnowledgebaseCategoryIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatekbarticle'), htmlspecialchars(StripName($_POST['subject'], 25))),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_KNOWLEDGEBASE, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $_SWIFT_KnowledgebaseArticleObject->ProcessPostAttachments();

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_knowledgebaseArticleID);

        return false;
    }

    /**
     * Retrieve the Knowledgebase Category ID List
     *
     * @author Varun Shoor
     * @return array The Knowledgebase Category ID List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _GetKnowledgebaseCategoryIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['kbcategoryidlist']) || !_is_array($_POST['kbcategoryidlist']))
        {
            return array();
        }

        return $_POST['kbcategoryidlist'];
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

        $_knowledgebaseArticleIDList = array();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('kbarticlegrid', 'kbarticles.dateline', 'desc');

        $_finalKnowledgebaseCategoryIDList = SWIFT_KnowledgebaseCategory::RetrieveSubCategoryIDList(array(($_filterValue)));
        $_finalKnowledgebaseCategoryIDList[] = ($_filterValue);
        switch ($_filterType)
        {
            case 'category': {
                $this->Database->QueryLimit("SELECT kbarticlelinks.kbarticleid FROM " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks
                    LEFT JOIN " . TABLE_PREFIX . "kbarticles AS kbarticles ON (kbarticlelinks.kbarticleid = kbarticles.kbarticleid)
                    WHERE kbarticlelinks.linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "' AND kbarticlelinks.linktypeid IN (" . BuildIN($_finalKnowledgebaseCategoryIDList) . ")
                    ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                while ($this->Database->NextRecord())
                {
                    $_knowledgebaseArticleIDList[] = $this->Database->Record['kbarticleid'];
                }

            }
            break;


            case 'date': {
                $_extendedSQL = false;

                if ($_filterValue == 'today')
                {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('kbarticles.dateline', SWIFT_Rules::DATERANGE_TODAY);
                } else if ($_filterValue == 'yesterday') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('kbarticles.dateline', SWIFT_Rules::DATERANGE_YESTERDAY);
                } else if ($_filterValue == 'l7') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('kbarticles.dateline', SWIFT_Rules::DATERANGE_LAST7DAYS);
                } else if ($_filterValue == 'l30') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('kbarticles.dateline', SWIFT_Rules::DATERANGE_LAST30DAYS);
                } else if ($_filterValue == 'l180') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('kbarticles.dateline', SWIFT_Rules::DATERANGE_LAST180DAYS);
                } else if ($_filterValue == 'l365') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('kbarticles.dateline', SWIFT_Rules::DATERANGE_LAST365DAYS);
                } else {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                if (!empty($_extendedSQL))
                {
                    /**
                     * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
                     *
                     * SWIFT-1326 "Knowledgebase category restrictions to staff teams do not take effect".
                     *
                     * Comment - Adjusting at the Quick Filter.
                     */
                    $this->Database->QueryLimit("SELECT kbarticlelinks.kbarticleid FROM " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks
                    LEFT JOIN " . TABLE_PREFIX . "kbarticles AS kbarticles ON (kbarticlelinks.kbarticleid = kbarticles.kbarticleid)
                    WHERE kbarticlelinks.linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "' AND kbarticlelinks.linktypeid IN (" . BuildIN($_finalKnowledgebaseCategoryIDList) . ")
                        AND " . $_extendedSQL . "
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    while ($this->Database->NextRecord())
                    {
                        $_knowledgebaseArticleIDList[] = $this->Database->Record['kbarticleid'];
                    }
                }

            }
            break;

            default:
                break;
        }

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_KBARTICLE, $_knowledgebaseArticleIDList,
                $_SWIFT->Staff->GetStaffID());

        if (!_is_array($_knowledgebaseArticleIDList))
        {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }

    /**
     * Insert a new Macro Reply but from tickets
     *
     * @author Varun Shoor
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The SWIFT_TicketPost Object
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertTicket(SWIFT_TicketPost $_SWIFT_TicketPostObject, $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_App::IsInstalled(APP_TICKETS))
        {
            return false;
        }


        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . $this->Language->Get('insertarticle'), self::MENU_ID, 4);

        if ($_SWIFT->Staff->GetPermission('staff_kbcaninsertarticle') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, false, $_SWIFT_TicketPostObject, $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        $this->UserInterface->Footer();

        return true;
    }
}
