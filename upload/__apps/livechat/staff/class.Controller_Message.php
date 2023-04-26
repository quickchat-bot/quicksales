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
use Base\Library\Rating\SWIFT_RatingRenderer;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Message\SWIFT_Message;
use LiveChat\Models\Message\SWIFT_MessageManager;
use LiveChat\Models\Message\SWIFT_MessageSurvey;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Session;

/**
 * The Offline Message/Survey Controller
 *
 * @author Varun Shoor
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @property Controller_Message $Load
 * @property View_Message $View
 */
class Controller_Message extends Controller_staff
{
    // Core Constants
    const MENU_ID = 3;
    const NAVIGATION_ID = 1;

    public $TagCloud;
    public $UserInterfaceGrid;
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

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->View->RenderTree());

        return true;
    }

    /**
     * Delete the Chat Message/Surveys from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_messageIDList The Message ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_messageIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_lscandeletemessages') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_messageIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "messages
                WHERE messageid IN (" . BuildIN($_messageIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletelsmessage'),
                    text_to_html_entities($_SWIFT->Database->Record['fullname']) .
                    IIF(!empty($_SWIFT->Database->Record['subject']), ' (' . htmlspecialchars($_SWIFT->Database->Record['subject']) . ')')),
                    SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            // Begin Hook: staff_message_delete
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_message_delete')) ? eval($_hookCode) : false;
            // End Hook

            SWIFT_Message::DeleteList($_messageIDList);
        }

        return true;
    }

    /**
     * Delete the Given Message ID
     *
     * @author Varun Shoor
     * @param int $_messageID The Message ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_messageID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_messageID), true);

        $this->Load->Manage(false);

        return true;
    }

    /**
     * Mark as Read Chat Message/Surveys from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_messageIDList The Message ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function MarkAsReadList($_messageIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_lscanupdatemessages') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_messageIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "messages WHERE messageid IN (" . BuildIN($_messageIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitymarkasreadlsmessage'),
                    text_to_html_entities($_SWIFT->Database->Record['fullname']) .
                    IIF(!empty($_SWIFT->Database->Record['subject']), ' (' . htmlspecialchars($_SWIFT->Database->Record['subject']) . ')')),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_Message::MarkAsReadList($_messageIDList);
        }

        return true;
    }

    /**
     * Mark as Read the Given Message ID
     *
     * @author Varun Shoor
     * @param int $_messageID The Message ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkAsRead($_messageID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::MarkAsReadList(array($_messageID), true);

        $this->Load->Manage(false);

        return true;
    }

    /**
     * Displays the Message Grid
     *
     * @author Varun Shoor
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @param int $_departmentID (OPTIONAL) Filter by Department ID
     * @param bool $_filterToSurvey (OPTIONAL) Whether to filter the results to just the surveys
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = 0, $_departmentID = 0, $_filterToSurvey = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_numeric($_departmentID)) {
            $_departmentID = 0;
        }

        SWIFT::Set('chmfilterdepartmentid', $_departmentID);

        if (!is_numeric($_searchStoreID)) {
            $_searchStoreID = 0;
        }

        if ($_SWIFT->Staff->GetPermission('staff_lscanviewmessages') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid($_searchStoreID, $_departmentID, $_filterToSurvey);
        }

        $this->Load->Library('Tag:TagCloud', array(SWIFT_TagLink::RetrieveCloudContainer(SWIFT_TagLink::TYPE_CHATMESSAGE), false,
            'window.$gridirs.RunIRS(\'chatmessagegrid\', \'tag:%s\');'), true, false, 'base');

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('messagest'), self::MENU_ID,
            self::NAVIGATION_ID, $this->TagCloud->Render());

        $this->View->UserInterfaceGrid->Display();

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
        $_filterBySurvey = false;

        $_messageIDList = array();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('chatmessagegrid', 'messages.dateline', 'desc');

        switch ($_filterType) {
            case 'department':
                {
                    $this->Load->Manage($_searchStoreID, (int)($_filterValue));

                    return true;

                }
                break;

            case 'date':
                {
                    $_extendedSQL = false;

                    if ($_filterValue == 'today') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('messages.dateline', SWIFT_Rules::DATERANGE_TODAY);
                    } else if ($_filterValue == 'yesterday') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('messages.dateline', SWIFT_Rules::DATERANGE_YESTERDAY);
                    } else if ($_filterValue == 'l7') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('messages.dateline', SWIFT_Rules::DATERANGE_LAST7DAYS);
                    } else if ($_filterValue == 'l30') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('messages.dateline', SWIFT_Rules::DATERANGE_LAST30DAYS);
                    } else if ($_filterValue == 'l180') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('messages.dateline', SWIFT_Rules::DATERANGE_LAST180DAYS);
                    } else if ($_filterValue == 'l365') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('messages.dateline', SWIFT_Rules::DATERANGE_LAST365DAYS);
                    }

                    if (!empty($_extendedSQL)) {
                        $this->Database->QueryLimit("SELECT messages.messageid FROM " . TABLE_PREFIX . "messages AS messages
                        WHERE " . $_extendedSQL . "
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                        while ($this->Database->NextRecord()) {
                            $_messageIDList[] = $this->Database->Record['messageid'];
                        }
                    }

                }
                break;

            case 'type':
                {
                    if ($_filterValue == 'new') {
                        $this->Database->QueryLimit("SELECT messages.messageid FROM " . TABLE_PREFIX . "messages AS messages
                        WHERE messages.messagetype = '" . SWIFT_Message::MESSAGE_CLIENT . "'
                            AND messages.messagestatus = '" . (SWIFT_Message::STATUS_NEW) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'replied') {
                        $this->Database->QueryLimit("SELECT messages.messageid FROM " . TABLE_PREFIX . "messages AS messages
                        WHERE messages.messagetype = '" . SWIFT_Message::MESSAGE_CLIENT . "'
                            AND (messages.messagestatus = '" . (SWIFT_Message::STATUS_READ) . "' OR messages.messagestatus = '" . (SWIFT_Message::STATUS_REPLIED) . "')
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'survey') {
                        $this->Database->QueryLimit("SELECT messages.messageid FROM " . TABLE_PREFIX . "messages AS messages
                        WHERE messages.messagetype = '" . SWIFT_Message::MESSAGE_CLIENTSURVEY . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    }
                    while ($this->Database->NextRecord()) {
                        $_messageIDList[] = $this->Database->Record['messageid'];
                    }

                }
                break;

            case 'rating':
                {
                    $_extendedSQL = false;
                    $_filterBySurvey = true;

                    if ($_filterValue == '0') {
                        $_extendedSQL = "messages.messagerating = '0' OR messages.messagerating = '0.5'";
                    } else if ($_filterValue == '1') {
                        $_extendedSQL = "messages.messagerating = '1' OR messages.messagerating = '1.5'";
                    } else if ($_filterValue == '2') {
                        $_extendedSQL = "messages.messagerating = '2' OR messages.messagerating = '2.5'";
                    } else if ($_filterValue == '3') {
                        $_extendedSQL = "messages.messagerating = '3' OR messages.messagerating = '3.5'";
                    } else if ($_filterValue == '4') {
                        $_extendedSQL = "messages.messagerating = '4' OR messages.messagerating = '4.5'";
                    } else if ($_filterValue == '5') {
                        $_extendedSQL = "messages.messagerating = '5'";
                    }

                    if (!empty($_extendedSQL)) {
                        $this->Database->QueryLimit("SELECT messages.messageid FROM " . TABLE_PREFIX . "messages AS messages
                        WHERE messages.messagetype = '" . SWIFT_MessageSurvey::MESSAGE_CLIENTSURVEY . "'
                            AND (" . $_extendedSQL . ")
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                        while ($this->Database->NextRecord()) {
                            $_messageIDList[] = $this->Database->Record['messageid'];
                        }
                    }

                }
                break;

            default:
                break;
        }

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_CHATMESSAGE, $_messageIDList, $_SWIFT->Staff->GetStaffID());

        if (!_is_array($_messageIDList)) {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID, false, $_filterBySurvey);

        return true;
    }

    /**
     * View a message
     *
     * @author Varun Shoor
     * @param int $_messageID The Message ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid data is provided
     */
    public function ViewMessage($_messageID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_messageID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_MessageObject = SWIFT_MessageManager::RetrieveMessageObject($_messageID);
        if (!$_SWIFT_MessageObject instanceof SWIFT_MessageManager || !$_SWIFT_MessageObject->GetIsClassLoaded() || !$_SWIFT_MessageObject->CanAccess($_SWIFT->Staff)) {
            $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('messagest'), self::MENU_ID, self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;
        }

        $this->View->RenderInfoBox($_SWIFT_MessageObject);

        // Ratings
        if ($_SWIFT_MessageObject->GetProperty('chatobjectid') != '0') {
            $_SWIFT_ChatObject = false;

            try {
                $_SWIFT_ChatObject = new SWIFT_Chat($_SWIFT_MessageObject->GetProperty('chatobjectid'));

                $_ratingContainer = SWIFT_Rating::Retrieve(array(SWIFT_Rating::TYPE_CHATSURVEY), $_SWIFT->Staff, false);
                SWIFT_RatingRenderer::RenderNavigationBox(array(SWIFT_Rating::TYPE_CHATSURVEY), $_SWIFT_ChatObject->GetChatObjectID(), '/LiveChat/ChatHistory/Rating/' . $_SWIFT_ChatObject->GetChatObjectID(), $_ratingContainer);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        $this->_LoadDisplayData();

        $_messageExtend = sprintf($this->Language->Get('messagetitle'), $_SWIFT_MessageObject->GetMessageMaskID(), htmlspecialchars($_SWIFT_MessageObject->GetProperty('subject')));
        if ($_SWIFT_MessageObject->GetProperty('messagetype') == SWIFT_Message::MESSAGE_CLIENTSURVEY) {
            $_messageExtend = sprintf($this->Language->Get('surveytitle'), $_SWIFT_MessageObject->GetMessageMaskID(), htmlspecialchars($_SWIFT_MessageObject->GetProperty('subject')));
        }

        $this->UserInterface->Header($_messageExtend, self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_lscanviewmessages') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderMessage($_SWIFT_MessageObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * View a message (SUBMISSION PROCESSOR)
     *
     * @author Varun Shoor
     * @param int $_messageID The Message ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid data is provided
     */
    public function ViewMessageSubmit($_messageID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_messageID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_MessageObject = SWIFT_MessageManager::RetrieveMessageObject($_messageID);
        if (!$_SWIFT_MessageObject instanceof SWIFT_MessageManager || !$_SWIFT_MessageObject->GetIsClassLoaded() || !$_SWIFT_MessageObject->CanAccess($_SWIFT->Staff)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        // Process Tags
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            SWIFT_Tag::Process(SWIFT_TagLink::TYPE_CHATMESSAGE, $_SWIFT_MessageObject->GetMessageID(),
                SWIFT_UserInterface::GetMultipleInputValues('tags'), $_SWIFT->Staff->GetStaffID());
        }

        // Begin Hook: staff_message_viewsubmit
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_message_viewsubmit')) ? eval($_hookCode) : false;
        // End Hook

        $this->ViewMessage($_messageID);

        return true;
    }

    /**
     * Reply to a message
     *
     * @author Varun Shoor
     * @param int $_messageID The Message ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid data is provided
     */
    public function Reply($_messageID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_messageID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_MessageObject = SWIFT_MessageManager::RetrieveMessageObject($_messageID);
        if (!$_SWIFT_MessageObject instanceof SWIFT_MessageManager || !$_SWIFT_MessageObject->GetIsClassLoaded() || !$_SWIFT_MessageObject->CanAccess($_SWIFT->Staff)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        // Permission Checks
        if ($_SWIFT->Staff->GetPermission('staff_lscanupdatemessages') == '0') {
            return false;
        }

        // Sanitization Checks
        if (empty($_POST['subject']) || empty($_POST['fromemail']) || empty($_POST['replycontents'])) {
            $this->UserInterface->CheckFields('subject', 'fromemail', 'replycontents');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->ViewMessage($_messageID);

            return false;
        } else if ($_POST['fromemail'] != $_SWIFT->Staff->GetProperty('email') && $_POST['fromemail'] != $this->Settings->Get('general_returnemail')) {
            SWIFT::ErrorField('fromemail');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->ViewMessage($_messageID);

            return false;
        }

        // We dont allow replying to already replied messages..
        if ($_SWIFT_MessageObject->GetProperty('messagestatus') == SWIFT_Message::STATUS_REPLIED) {
            return false;
        }

        // Begin Hook: staff_message_reply
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_message_reply')) ? eval($_hookCode) : false;
        // End Hook

        // Everythings fine.. dispatch the reply..
        $_SWIFT_MessageObject->Reply($_SWIFT->Staff->GetStaffID(), $_SWIFT->Staff->GetProperty('fullname'), $_POST['fromemail'], $_POST['subject'],
            $_POST['replycontents']);

        SWIFT::Info($this->Language->Get('titlereplydispatched'), $this->Language->Get('msgreplydispatched'));

        $this->Load->ViewMessage($_messageID);

        return true;
    }
}
