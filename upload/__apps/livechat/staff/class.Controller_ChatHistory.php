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

namespace LiveChat\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\Rating\SWIFT_RatingRenderer;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\Rating\SWIFT_RatingResult;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use LiveChat\Library\Chat\SWIFT_ChatRenderManager;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatSearch;
use LiveChat\Models\Note\SWIFT_ChatNote;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Session;

/**
 * The Chat History Controller
 *
 * @author Varun Shoor
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @property Controller_ChatHistory $Load
 * @property View_ChatHistory $View
 */
class Controller_ChatHistory extends Controller_staff
{
    // Core Constants
    const MENU_ID = 3;
    const NAVIGATION_ID = 1;

    public $TagCloud;
    public $CustomFieldManager;
    public $CustomFieldRendererStaff;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('CustomField:CustomFieldRendererStaff', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('staff_livechat');
    }

    /**
     * Delete the Chat Objects from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_chatObjectIDList The Chat Object ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_chatObjectIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_lscandeletechat') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_chatObjectIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatobjects WHERE chatobjectid IN (" . BuildIN($_chatObjectIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletechatobject'),
                    text_to_html_entities($_SWIFT->Database->Record['userfullname']) .
                    IIF(!empty($_SWIFT->Database->Record['subject']), ' (' . htmlspecialchars($_SWIFT->Database->Record['subject']) . ')')),
                    SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            }

            // Begin Hook: staff_chat_delete
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_chat_delete')) ? eval($_hookCode) : false;
            // End Hook

            SWIFT_Chat::DeleteList($_chatObjectIDList);
        }

        return true;
    }

    /**
     * Delete the Given Chat Object ID
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_chatObjectID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_chatObjectID), true);

        $this->Load->Manage(false);

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
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), SWIFT_ChatRenderManager::RenderTree());

        return true;
    }

    /**
     * Displays the Chat History Grid
     *
     * @author Varun Shoor
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @param int $_departmentID (OPTIONAL) Filter by Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = 0, $_departmentID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_numeric($_departmentID)) {
            $_departmentID = 0;
        }

        SWIFT::Set('chfilterdepartmentid', $_departmentID);

        $this->_LoadDisplayData();

        $this->Load->Library('Tag:TagCloud', array(SWIFT_TagLink::RetrieveCloudContainer(SWIFT_TagLink::TYPE_CHAT), false,
            'window.$gridirs.RunIRS(\'chatgrid\', \'tag:%s\');'), true, false, 'base');

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('chathistory'), self::MENU_ID,
            self::NAVIGATION_ID, $this->TagCloud->Render());

        if ($_SWIFT->Staff->GetPermission('staff_lscanviewchat') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid($_searchStoreID, $_departmentID);
        }

        $this->UserInterface->Footer();

        return true;
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

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchStoreID = -1;

        $_chatObjectIDList = array();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('chatgrid', 'chatobjects.dateline', 'desc');

        switch ($_filterType) {
            case 'department':
                {
                    $this->Load->Manage($_searchStoreID, $_filterValue);

                    return true;

                }
                break;

            case 'type':
                {
                    if ($_filterValue == 'public') {
                        $this->Database->QueryLimit("SELECT chatobjects.chatobjectid FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
                        WHERE chatobjects.chattype = '" . (SWIFT_Chat::CHATTYPE_CLIENT) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'private') {
                        $this->Database->QueryLimit("SELECT chatobjects.chatobjectid FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
                        WHERE chatobjects.chattype = '" . (SWIFT_Chat::CHATTYPE_STAFF) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'unanswered') {
                        $this->Database->QueryLimit("SELECT chatobjects.chatobjectid FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
                        WHERE chatobjects.chatstatus = '" . (SWIFT_Chat::CHAT_NOANSWER) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'timedout') {
                        $this->Database->QueryLimit("SELECT chatobjects.chatobjectid FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
                        WHERE chatobjects.chatstatus = '" . (SWIFT_Chat::CHAT_TIMEOUT) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    }
                    while ($this->Database->NextRecord()) {
                        $_chatObjectIDList[] = $this->Database->Record['chatobjectid'];
                    }

                }
                break;

            case 'date':
                {
                    $_extendedSQL = false;

                    if ($_filterValue == 'today') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('chatobjects.dateline', SWIFT_Rules::DATERANGE_TODAY);
                    } else if ($_filterValue == 'yesterday') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('chatobjects.dateline', SWIFT_Rules::DATERANGE_YESTERDAY);
                    } else if ($_filterValue == 'l7') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('chatobjects.dateline', SWIFT_Rules::DATERANGE_LAST7DAYS);
                    } else if ($_filterValue == 'l30') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('chatobjects.dateline', SWIFT_Rules::DATERANGE_LAST30DAYS);
                    } else if ($_filterValue == 'l180') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('chatobjects.dateline', SWIFT_Rules::DATERANGE_LAST180DAYS);
                    } else if ($_filterValue == 'l365') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('chatobjects.dateline', SWIFT_Rules::DATERANGE_LAST365DAYS);
                    }

                    if (!empty($_extendedSQL)) {
                        $this->Database->QueryLimit("SELECT chatobjects.chatobjectid FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
                        WHERE " . $_extendedSQL . "
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                        while ($this->Database->NextRecord()) {
                            $_chatObjectIDList[] = $this->Database->Record['chatobjectid'];
                        }
                    }

                }
                break;

            default:
                break;
        }

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_CHATS, $_chatObjectIDList,
            $_SWIFT->Staff->GetStaffID());

        if (!_is_array($_chatObjectIDList)) {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }

    /**
     * Search processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SearchSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('chatgrid', 'chatobjects.dateline', 'asc');

        $_fieldPointer = SWIFT_ChatSearch::GetFieldPointer();
        $_sqlContainer = array();

        $_SWIFT_SearchEngineObject = new SWIFT_SearchEngine();
        $_searchQuery = [];

        if (isset($_POST['rulecriteria']) && _is_array($_POST['rulecriteria'])) {
            foreach ($_POST['rulecriteria'] as $_key => $_val) {
                if (!isset($_fieldPointer[$_val[0]])) {
                    if ($_val[0] == SWIFT_ChatSearch::CHATSEARCH_CONVERSATIONSQL) {
                        $_searchQuery[] = "SELECT chatobjects.chatobjectid FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
                            LEFT JOIN " . TABLE_PREFIX . "chattextdata AS chattextdata ON (chatobjects.chatobjectid = chattextdata.chatobjectid)
                            WHERE (" . BuildSQLSearch('chattextdata.contents', $_val[2]) . ')';
                    } else if ($_val[0] == SWIFT_ChatSearch::CHATSEARCH_CONVERSATIONNGRAM) {
                        $_searchQuery[] = $_SWIFT_SearchEngineObject->GetFindQuery($_val[2], SWIFT_SearchEngine::TYPE_CHAT, [], true, false);
                    }

                    continue;
                }

                // Is it date type?
                if ($_val[0] == SWIFT_ChatSearch::CHATSEARCH_DATE || $_val[0] == SWIFT_ChatSearch::CHATSEARCH_TRANSFERDATE) {
                    if (empty($_val[2])) {
                        $_val[2] = DATENOW;
                    } else {
                        $_val[2] = GetCalendarDateline($_val[2]);
                    }
                }

                // Make sure its not a date range..
                if ($_val[0] == SWIFT_ChatSearch::CHATSEARCH_DATERANGE || $_val[0] == SWIFT_ChatSearch::CHATSEARCH_TRANSFERDATERANGE) {
                    $_sqlContainer[] = SWIFT_Rules::BuildSQLDateRange($_fieldPointer[$_val[0]], $_val[2]);
                } else {
                    $_sqlContainer[] = SWIFT_Rules::BuildSQL($_fieldPointer[$_val[0]], $_val[1], $_val[2]);
                }

            }
        }
        foreach ($_searchQuery as $q) {
            $_sqlContainer[] = "chatobjectid IN ({$q})";
        }

        $_chatObjectIDList = array();
        $_filterJoiner = ($_POST['criteriaoptions'] == SWIFT_Rules::RULE_MATCHALL) ? ' AND ' : ' OR ';

        if (count($_sqlContainer)) {
            $this->Database->QueryLimit("SELECT chatobjects.chatobjectid FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
                WHERE (" . implode($_filterJoiner, $_sqlContainer) . ')' . "
                ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);

            while ($this->Database->NextRecord()) {
                $_chatObjectIDList[] = $this->Database->Record['chatobjectid'];
            }
        }

        // Search using Conversations?

        SWIFT_SearchStore::DeleteOnType(SWIFT_SearchStore::TYPE_CHATS, $_SWIFT->Staff->GetStaffID());

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_CHATS, $_chatObjectIDList, $_SWIFT->Staff->GetStaffID());
        if (!_is_array($_chatObjectIDList)) {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }

    /**
     * View the provided Chat Object
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param int $_filterDepartmentID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ViewChat($_chatObjectID, $_filterDepartmentID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!is_numeric($_filterDepartmentID)) {
            $_filterDepartmentID = 0;
        }


        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_canAccess = false;
        if ($_SWIFT->Staff->GetPermission('staff_lscanviewchat') != '0' && $_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            $_canAccess = true;
        }

        if ($_canAccess) {
            $this->View->RenderInfoBox($_SWIFT_ChatObject);

            // Ratings
            $_ratingContainer = SWIFT_Rating::Retrieve(array(SWIFT_Rating::TYPE_CHATHISTORY, SWIFT_Rating::TYPE_CHATSURVEY), $_SWIFT->Staff, false);
            SWIFT_RatingRenderer::RenderNavigationBox(array(SWIFT_Rating::TYPE_CHATHISTORY, SWIFT_Rating::TYPE_CHATSURVEY), $_SWIFT_ChatObject->GetChatObjectID(), '/LiveChat/ChatHistory/Rating/' . $_SWIFT_ChatObject->GetChatObjectID(), $_ratingContainer);

            // Tree
            $this->_LoadDisplayData();
        }

        $_extendedTitle = $_SWIFT_ChatObject->GetProperty('userfullname');
        if ($_SWIFT_ChatObject->GetProperty('subject') != '') {
            $_extendedTitle = $_SWIFT_ChatObject->GetProperty('subject');
        }
        $this->UserInterface->Header(sprintf($this->Language->Get('chattitle'), $_SWIFT_ChatObject->GetProcessedChatID(), htmlspecialchars($_extendedTitle)), self::MENU_ID,
            self::NAVIGATION_ID);

        if (!$_canAccess) {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderChatHistory($_SWIFT_ChatObject, $_filterDepartmentID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Chat Rating Handler
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Rating($_chatObjectID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!isset($_POST['ratingid']) || empty($_POST['ratingid']) || !isset($_POST['ratingvalue'])) {
            return false;

        } else if ($_SWIFT->Staff->GetPermission('staff_canviewratings') == '0' || $_SWIFT->Staff->GetPermission('staff_canupdateratings') == '0') {
            return false;
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_SWIFT_RatingObject = new SWIFT_Rating((int)($_POST['ratingid']));
        if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        SWIFT_RatingResult::CreateOrUpdateIfExists($_SWIFT_RatingObject, $_SWIFT_ChatObject->GetChatObjectID(), $_POST['ratingvalue'], SWIFT_RatingResult::CREATOR_STAFF, $_SWIFT->Staff->GetStaffID());

        return true;
    }

    /**
     * Chat Object Submission
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ViewChatSubmit($_chatObjectID, $_filterDepartmentID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (is_numeric($_filterDepartmentID)) {
            $_filterDepartmentID = (int)($_filterDepartmentID);
        } else {
            $_filterDepartmentID = false;
        }


        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_canAccess = false;
        if ($_SWIFT->Staff->GetPermission('staff_lscanviewchat') != '0' && $_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            $_canAccess = true;
        }

        if (!$_canAccess) {
            $this->Load->ViewChat($_chatObjectID, $_filterDepartmentID);
        }


        // Process Tags
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            SWIFT_Tag::Process(SWIFT_TagLink::TYPE_CHAT, $_SWIFT_ChatObject->GetChatObjectID(),
                SWIFT_UserInterface::GetMultipleInputValues('tags'), $_SWIFT->Staff->GetStaffID());
        }

        /**
         * BUG Fix: Nidhi Gupta <nidhi.gupta@kayako.com>
         * BUG FIX: Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3290 'Live Chat - Pre Chat' type custom fields cannot be edited by staff
         * SWIFT-4857 Custom fields linked to Live chat (after chat) group are not updating [Customer Reported]
         *
         * Comments: Build the properties for Custom fields value update
         */
        $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT,
            array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE, SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST), SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_ChatObject->GetChatObjectID());


        // Begin Hook: staff_chat_submit
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_chat_submit')) ? eval($_hookCode) : false;
        // End Hook

        $this->Load->ViewChat($_chatObjectID, $_filterDepartmentID);

        return true;
    }

    /**
     * Print the conversation
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Class is not loaded or If Invalid Data is Provided
     */
    public function PrintChat($_chatObjectID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Permission Check
        if ($_SWIFT->Staff->GetPermission('staff_lscanviewchat') == '0' || !$_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            return false;
        }

        $_chatDataArray = $_SWIFT_ChatObject->GetConversationArray();

        $this->Template->Assign('_chatDepartment', text_to_html_entities($_SWIFT_ChatObject->GetProperty('departmenttitle')));
        $this->Template->Assign('_chatFullName', text_to_html_entities($_SWIFT_ChatObject->GetProperty('userfullname')));
        $this->Template->Assign('_chatEmail', htmlspecialchars($_SWIFT_ChatObject->GetProperty('useremail')));
        $this->Template->Assign('_chatSubject', htmlspecialchars($_SWIFT_ChatObject->GetProperty('subject')));
        $this->Template->Assign('_chatStaff', htmlspecialchars($_SWIFT_ChatObject->GetProperty('staffname')));
        $this->Template->Assign('_chatID', htmlspecialchars($_SWIFT_ChatObject->GetProperty('chatobjectmaskid')));
        $this->Template->Assign('_chatConversation', $_chatDataArray);

        $this->Template->Render('printchat');

        return true;
    }

    /**
     * Render the Email Chat Dialog
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Class is not loaded or If Invalid Data is Provided
     */
    public function Email($_chatObjectID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }


        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('chathistory'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_lscanviewchat') == '0' || !$_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderEmail($_SWIFT_ChatObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Dispatch the chat conversation to given email address
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Class is not loaded or If Invalid Data is Provided
     */
    public function EmailSubmit($_chatObjectID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }


        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (trim($_POST['email']) == '') {
            $this->UserInterface->CheckFields('email');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Email($_chatObjectID);

            return false;
        } else if (!IsEmailValid($_POST['email'])) {
            SWIFT::ErrorField('email');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Email($_chatObjectID);

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->Email($_chatObjectID);

            return false;
        } else if ($_SWIFT->Staff->GetPermission('staff_lscanviewchat') == '0' || !$_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Email($_chatObjectID);

            return false;
        }

        $_SWIFT_ChatObject->Email(array($_POST['email']), '', $_POST['emailnotes']);

        $this->Load->ViewChat($_chatObjectID);

        return true;
    }

    /**
     * Add a note
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNote($_chatObjectID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('chathistory'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lscaninsertchatnote') == '0' || !$_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderNoteForm(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_ChatObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Add a Note Submit Processer
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNoteSubmit($_chatObjectID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('admin_lscaninsertchatnote') == '0' || !$_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            return false;
        }

        // Add notes
        if (trim($_POST['chatnotes']) != '') {
            SWIFT_ChatNote::Create($_SWIFT_ChatObject, $_POST['chatnotes'], (int)($_POST['notecolor_chatnotes']));
        }

        echo $this->View->RenderChatNotes($_SWIFT_ChatObject);

        return true;
    }

    /**
     * Edit a note
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param int $_visitorNoteID The Visitor Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditNote($_chatObjectID, $_visitorNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if ($_SWIFT->Staff->GetPermission('staff_lscanupdatechatnote') == '0') {
            return false;
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!$_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            return false;
        }

        $_SWIFT_ChatNoteObject = new SWIFT_ChatNote($_visitorNoteID);
        if (!$_SWIFT_ChatNoteObject instanceof SWIFT_ChatNote || !$_SWIFT_ChatNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('chathistory'), self::MENU_ID,
            self::NAVIGATION_ID);
        $this->View->RenderNoteForm(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_ChatObject, $_SWIFT_ChatNoteObject);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit a note processor
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param int $_visitorNoteID The Visitor Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditNoteSubmit($_chatObjectID, $_visitorNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if ($_SWIFT->Staff->GetPermission('staff_lscanupdatechatnote') == '0') {
            return false;
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!$_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            return false;
        }

        $_SWIFT_ChatNoteObject = new SWIFT_ChatNote($_visitorNoteID);
        if (!$_SWIFT_ChatNoteObject instanceof SWIFT_ChatNote || !$_SWIFT_ChatNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Add notes
        if (trim($_POST['chatnotes']) != '') {
            $_SWIFT_ChatNoteObject->Update($_SWIFT->Staff->GetStaffID(), $_SWIFT->Staff->GetProperty('fullname'),
                $_POST['chatnotes'], (int)($_POST['notecolor_chatnotes']));
        }

        echo $this->View->RenderChatNotes($_SWIFT_ChatObject);

        return true;
    }

    /**
     * Delete Note Processer
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param int $_visitorNoteID The Visitor Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteNote($_chatObjectID, $_visitorNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if ($_SWIFT->Staff->GetPermission('staff_lscandeletechatnote') == '0') {
            return false;
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!$_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
            return false;
        }

        $_SWIFT_ChatNoteObject = new SWIFT_ChatNote($_visitorNoteID);
        if (!$_SWIFT_ChatNoteObject instanceof SWIFT_ChatNote || !$_SWIFT_ChatNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ChatNoteObject->Delete();

        echo $this->View->RenderChatNotes($_SWIFT_ChatObject);

        return true;
    }

    /**
     * View Chat History
     *
     * @author Varun Shoor
     * @param string $_queryString The Query String
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function History($_queryString)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userDetails = array();

        parse_str(base64_decode($_queryString), $_userDetails);

        if (!isset($_userDetails['userid']) && !isset($_userDetails['email'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userID = 0;
        if (isset($_userDetails['userid'])) {
            $_userID = $_userDetails['userid'];
        }

        $_userEmail = '';
        if (isset($_userDetails['email'])) {
            $_userEmail = $_userDetails['email'];
        }

        $_historyContainer = SWIFT_Chat::RetrieveHistoryExtended($_userID, $_userEmail);

        $this->View->RenderChatHistoryGrid($_historyContainer);

        return true;
    }
}
