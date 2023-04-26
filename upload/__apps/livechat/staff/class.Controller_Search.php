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
use Base\Models\SearchStore\SWIFT_SearchStore;
use LiveChat\Library\Chat\SWIFT_ChatRenderManager;
use SWIFT;
use SWIFT_Exception;

/**
 * The Search Handling Controller
 *
 * @author Varun Shoor
 *
 * @property View_Search $View
 */
class Controller_Search extends Controller_staff
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
     * The Search Form Renderer
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Advanced()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('search'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_lscanviewchat') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Quick Search Against Messages
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _SearchMessages($_searchQuery)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        $_messageIDList = array();

        // Empty query?
        if (trim($_searchQuery) == '') {
            return array();
        }

        $_SWIFT->Database->QueryLimit("SELECT messages.messageid FROM " . TABLE_PREFIX . "messages AS messages
            LEFT JOIN " . TABLE_PREFIX . "messagedata AS messagedata ON (messages.messageid = messagedata.messageid)
            WHERE ((messages.messagemaskid LIKE '%" . $_SWIFT->Database->Escape($_searchQuery) . "%')
                OR (" . BuildSQLSearch('messages.fullname', $_searchQuery) . ")
                OR (" . BuildSQLSearch('messages.email', $_searchQuery) . ")
                OR (" . BuildSQLSearch('messages.subject', $_searchQuery) . ")
                OR (" . BuildSQLSearch('messages.staffname', $_searchQuery) . ")
                OR (" . BuildSQLSearch('messagedata.contents', $_searchQuery) . ")
            )", 100);
        while ($_SWIFT->Database->NextRecord()) {
            $_messageIDList[] = $_SWIFT->Database->Record['messageid'];
        }

        return $_messageIDList;
    }

    /**
     * Quick Search Against Data
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _SearchQuick($_searchQuery)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        $_chatObjectIDList = array();

        // Empty query?
        if (trim($_searchQuery) == '') {
            return array();
        }

        $_SWIFT->Database->QueryLimit("SELECT chatobjects.chatobjectid FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
            LEFT JOIN " . TABLE_PREFIX . "chattextdata AS chattextdata ON (chatobjects.chatobjectid = chattextdata.chatobjectid)
            WHERE ((chatobjects.chatobjectmaskid LIKE '%" . $_SWIFT->Database->Escape($_searchQuery) . "%')
                OR (" . BuildSQLSearch('chatobjects.userfullname', $_searchQuery) . ")
                OR (" . BuildSQLSearch('chatobjects.useremail', $_searchQuery) . ")
                OR (" . BuildSQLSearch('chatobjects.staffname', $_searchQuery) . ")
                OR (" . BuildSQLSearch('chatobjects.subject', $_searchQuery) . ")
                OR (" . BuildSQLSearch('chatobjects.departmenttitle', $_searchQuery) . ")
                OR (" . BuildSQLSearch('chatobjects.ipaddress', $_searchQuery) . ")
                OR (" . BuildSQLSearch('chatobjects.userfullname', $_searchQuery) . ")
                OR (" . BuildSQLSearch('chatobjects.userfullname', $_searchQuery) . ")
                OR (" . BuildSQLSearch('chatobjects.userfullname', $_searchQuery) . ")
                OR (" . BuildSQLSearch('chattextdata.contents', $_searchQuery) . ")
            )", 100);
        while ($_SWIFT->Database->NextRecord()) {
            $_chatObjectIDList[] = $_SWIFT->Database->Record['chatobjectid'];
        }

        return $_chatObjectIDList;
    }

    /**
     * Search for Chat ID
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _SearchChatID($_searchQuery)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        $_chatObjectIDList = array();

        // Empty query?
        if (trim($_searchQuery) == '') {
            return array();
        }

        // If its a numerical value, then we become specific
        if (is_numeric($_searchQuery)) {
            $_chatIDSearchContainer = $_SWIFT->Database->QueryFetch("SELECT chatobjectid FROM " . TABLE_PREFIX . "chatobjects WHERE chatobjectid = '" . (int)($_searchQuery) . "'");
            if (isset($_chatIDSearchContainer['chatobjectid']) && !empty($_chatIDSearchContainer['chatobjectid'])) {
                return array($_chatIDSearchContainer['chatobjectid']);
            }

            // Otherwise, we need to do a LIKE match
        } else {
            $_SWIFT->Database->QueryLimit("SELECT chatobjectid FROM " . TABLE_PREFIX . "chatobjects WHERE chatobjectmaskid LIKE '%" . $_SWIFT->Database->Escape($_searchQuery) . "%'", 100);
            while ($_SWIFT->Database->NextRecord()) {
                $_chatObjectIDList[] = $_SWIFT->Database->Record['chatobjectid'];
            }

        }

        return $_chatObjectIDList;
    }

    /**
     * Load the Search Data and Show the Chat List
     *
     * @author Varun Shoor
     * @param array $_chatObjectIDList The Chat Object ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadSearch(array $_chatObjectIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // If theres only one chat to load then open it up
        if (count($_chatObjectIDList) == 1) {
            /**
             * BUG FIX: Parminder Singh
             *
             * SWIFT-1444: "Controller_Ticket" Controller has no function declaration for "Index" Action in SWIFT App "tickets" staff (library/class.SWIFT.php:756)
             *
             */
            $this->Load->Controller('ChatHistory')->Load->Method('ViewChat', $_chatObjectIDList[0]);

            return true;
        }

        SWIFT_SearchStore::DeleteOnType(SWIFT_SearchStore::TYPE_CHATS, $_SWIFT->Staff->GetStaffID());

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_CHATS, $_chatObjectIDList, $_SWIFT->Staff->GetStaffID());
        if (!_is_array($_chatObjectIDList)) {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Controller('ChatHistory')->Manage($_searchStoreID);

        return true;
    }

    /**
     * Search the Chat ID
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ChatID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_chatObjectIDList = $this->_SearchChatID(trim($_POST['query']));
        $this->LoadSearch($_chatObjectIDList);

        return true;
    }

    /**
     * Search the Messages
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Messages()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_messageIDList = $this->_SearchMessages($_POST['query']);

        // If theres only one chat to load then open it up
        if (count($_messageIDList) == 1) {
            $this->Load->Controller('Message')->ViewMessage($_messageIDList[0]);

            return true;
        }

        SWIFT_SearchStore::DeleteOnType(SWIFT_SearchStore::TYPE_CHATMESSAGE, $_SWIFT->Staff->GetStaffID());

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_CHATMESSAGE, $_messageIDList, $_SWIFT->Staff->GetStaffID());
        if (!_is_array($_messageIDList)) {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Controller('Message')->Manage($_searchStoreID);


        return true;
    }

    /**
     * Quick Search on a Chat
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickSearch()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_chatObjectIDList = $this->_SearchQuick($_POST['query']);
        $this->LoadSearch($_chatObjectIDList);

        return true;
    }
}
