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

namespace LiveChat\Library\UnifiedSearch;

use Base\Library\UnifiedSearch\SWIFT_UnifiedSearchBase;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_UserEmail;
use SWIFT;
use LiveChat\Models\Call\SWIFT_Call;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;

/**
 * The Unified Search Library for Live Chat App
 *
 * @author Varun Shoor
 */
class SWIFT_UnifiedSearch_livechat extends SWIFT_UnifiedSearchBase
{
    protected $_departmentIDList = array();
    public $StringHighlighter;


    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_query The Search Query
     * @param mixed $_interfaceType The Interface Type
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_maxResults
     * @throws SWIFT_Exception If Object Creation Fails
     */
    public function __construct($_query, $_interfaceType, SWIFT_Staff $_SWIFT_StaffObject, $_maxResults)
    {
        parent::__construct($_query, $_interfaceType, $_SWIFT_StaffObject, $_maxResults);

        $this->Language->Load('staff_livechat');
    }

    /**
     * Run the search and return results
     *
     * @author Varun Shoor
     * @return array|false Container of Result Objects
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Search()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalSearchResults = array();

        $this->_departmentIDList = $this->GetStaff()->GetAssignedDepartments(APP_LIVECHAT);

        /**
         * ---------------------------------------------
         * ADMIN SPECIFIC
         * ---------------------------------------------
         */
        if ($this->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
            // Rules
            $_finalSearchResults[$this->Language->Get('us_livechatrules')] = $this->SearchRules();

            // Groups
            $_finalSearchResults[$this->Language->Get('us_livechatgroups')] = $this->SearchGroups();

            // Skills
            $_finalSearchResults[$this->Language->Get('us_livechatskills')] = $this->SearchSkills();


            /**
             * ---------------------------------------------
             * STAFF SPECIFIC
             * ---------------------------------------------
             */
        } else if ($this->GetInterface() == SWIFT_Interface::INTERFACE_STAFF) {
            // Chat History
            $_finalSearchResults[$this->Language->Get('us_chathistory') . '::' . $this->Language->Get('us_created')] = $this->SearchChats();

            // Messages/Surveys
            $_finalSearchResults[$this->Language->Get('us_messagessurv') . '::' . $this->Language->Get('us_created')] = $this->SearchMessages();

            // Call Logs
            $_finalSearchResults[$this->Language->Get('us_calllogs') . '::' . $this->Language->Get('us_created')] = $this->SearchCallLogs();

        }

        return $_finalSearchResults;
    }

    /**
     * Search the Rules
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchRules()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_lrcanviewrules') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "visitorrules
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/LiveChat/Rule/Edit/' . $this->Database->Record['visitorruleid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Groups
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchGroups()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_lrcanviewvisitorgroups') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "visitorgroups
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/LiveChat/Group/Edit/' . $this->Database->Record['visitorgroupid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Skills
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchSkills()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_lrcanviewskills') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "chatskills
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/LiveChat/Skill/Edit/' . $this->Database->Record['chatskillid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Chats
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchChats()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_lscanviewchat') == '0') {
            return array();
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');

        $_finalChatObjectIDList = array();

        $_userIDList = $_userEmailList = array();

        $this->Database->QueryLimit("SELECT chatobjects.* FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
            LEFT JOIN " . TABLE_PREFIX . "chattextdata AS chattextdata ON (chatobjects.chatobjectid = chattextdata.chatobjectid)
            WHERE ((" . BuildSQLSearch('chatobjects.subject', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('chatobjects.userfullname', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('chatobjects.useremail', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('chatobjects.phonenumber', $this->GetQuery(), false, false) . ")
                OR (" . BuildSQLSearch('chattextdata.contents', $this->GetQuery(), false, false) . "))
                AND chatobjects.departmentid IN (" . BuildIN($this->_departmentIDList) . ")
            ORDER BY chatobjects.dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_finalChatObjectIDList[] = $this->Database->Record['chatobjectid'];

            if ($this->Database->Record['userid'] != '0') {
                $_userIDList[] = $this->Database->Record['userid'];

                if (!isset($_userEmailList[$this->Database->Record['userid']])) {
                    $_userEmailList[$this->Database->Record['userid']] = array();
                }

                if ($this->Database->Record['useremail'] != '' && !in_array($this->Database->Record['useremail'], $_userEmailList[$this->Database->Record['userid']])) {
                    $_userEmailList[$this->Database->Record['userid']][] = $this->Database->Record['useremail'];
                }
            }
        }

        $this->Database->QueryLimit("SELECT chatobjects.* FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
            WHERE ((" . BuildSQLSearch('chatobjects.chatobjectmaskid', $this->GetQuery(), false, false) . "))
                AND chatobjects.departmentid IN (" . BuildIN($this->_departmentIDList) . ")
            ORDER BY chatobjects.dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            if (!in_array($this->Database->Record['chatobjectid'], $_finalChatObjectIDList)) {
                $_finalChatObjectIDList[] = $this->Database->Record['chatobjectid'];
            }

            if ($this->Database->Record['userid'] != '0') {
                $_userIDList[] = $this->Database->Record['userid'];

                if (!isset($_userEmailList[$this->Database->Record['userid']])) {
                    $_userEmailList[$this->Database->Record['userid']] = array();
                }

                if ($this->Database->Record['useremail'] != '' && !in_array($this->Database->Record['useremail'], $_userEmailList[$this->Database->Record['userid']])) {
                    $_userEmailList[$this->Database->Record['userid']][] = $this->Database->Record['useremail'];
                }
            }
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails
            WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")");
        while ($this->Database->NextRecord()) {
            if (isset($_userEmailList[$this->Database->Record['linktypeid']]) && !in_array($this->Database->Record['email'], $_userEmailList[$this->Database->Record['linktypeid']])) {
                $_userEmailList[$this->Database->Record['linktypeid']][] = $this->Database->Record['email'];
            }
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT chatobjects.*, chattextdata.contents AS contents FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
            LEFT JOIN " . TABLE_PREFIX . "chattextdata AS chattextdata ON (chatobjects.chatobjectid = chattextdata.chatobjectid)
            WHERE chatobjects.chatobjectid IN (" . BuildIN($_finalChatObjectIDList) . ")
            ORDER BY chatobjects.dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_chatSubject = htmlspecialchars($this->Database->Record['subject']);
            if (trim($_chatSubject) == '') {
                $_chatSubject = $this->Language->Get('nosubject');
            }

            $_extendedInfo = '';
            if (isset($_userEmailList[$this->Database->Record['userid']])) {
                $_extendedInfo .= implode(', ', $_userEmailList[$this->Database->Record['userid']]) . '<br />';
            }

            $_departmentTitle = '';
            if (isset($_departmentCache[$this->Database->Record['departmentid']])) {
                $_departmentTitle = $_departmentCache[$this->Database->Record['departmentid']]['title'];
            } else {
                $_departmentTitle = $this->Database->Record['departmenttitle'];
            }

            if (!empty($_departmentTitle)) {
                $_extendedInfo .= sprintf($this->Language->Get('usi_department'), $_departmentTitle) . '<br />';
            }

            $_staffName = '';
            if (isset($_staffCache[$this->Database->Record['staffid']])) {
                $_staffName = $_staffCache[$this->Database->Record['staffid']]['fullname'];
            } else {
                $_staffName = $this->Database->Record['staffname'];
            }

            if (!empty($_staffName)) {
                $_extendedInfo .= sprintf($this->Language->Get('usi_staff'), $_staffName) . '<br />';
            }

            if (self::HasQuery($this->Database->Record['contents'], $this->GetQuery())) {
                $_highlightResult = implode(' ... ', $this->StringHighlighter->GetHighlightedRange(strip_tags($this->Database->Record['contents']), $this->GetQuery(), 20));
                if (trim($_highlightResult) != '') {
                    $_extendedInfo .= '... ' . $_highlightResult . ' ...<br />';
                }
            }

            $_searchResults[] = array(text_to_html_entities($this->Database->Record['userfullname']) . ': ' . $_chatSubject, SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewChat/' . $this->Database->Record['chatobjectid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']), $_extendedInfo);
        }

        return $_searchResults;
    }

    /**
     * Search the Messages & Surveys
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchMessages()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_lscanviewmessages') == '0') {
            return array();
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');

        $_finalMessageIDList = array();
        $this->Database->QueryLimit("SELECT messages.messageid FROM " . TABLE_PREFIX . "messages AS messages
            LEFT JOIN " . TABLE_PREFIX . "messagedata AS messagedata ON (messages.messageid = messagedata.messageid)
            WHERE ((" . BuildSQLSearch('messages.subject', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('messages.fullname', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('messages.email', $this->GetQuery(), false, false) . ")
                OR (" . BuildSQLSearch('messagedata.contents', $this->GetQuery(), false, false) . "))
                AND messages.departmentid IN (" . BuildIN($this->_departmentIDList) . ")
            ORDER BY messages.dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_finalMessageIDList[] = $this->Database->Record['messageid'];
        }

        $this->Database->QueryLimit("SELECT messages.messageid FROM " . TABLE_PREFIX . "messages AS messages
            LEFT JOIN " . TABLE_PREFIX . "messagedata AS messagedata ON (messages.messageid = messagedata.messageid)
            WHERE ((" . BuildSQLSearch('messages.messagemaskid', $this->GetQuery(), false, false) . "))
                AND messages.departmentid IN (" . BuildIN($this->_departmentIDList) . ")
            ORDER BY messages.dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            if (!in_array($this->Database->Record['messageid'], $_finalMessageIDList)) {
                $_finalMessageIDList[] = $this->Database->Record['messageid'];
            }
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT messages.*, messagedata.contents AS contents FROM " . TABLE_PREFIX . "messages AS messages
            LEFT JOIN " . TABLE_PREFIX . "messagedata AS messagedata ON (messages.messageid = messagedata.messageid)
            WHERE messages.messageid IN (" . BuildIN($_finalMessageIDList) . ")
            ORDER BY messages.dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_messageSubject = htmlspecialchars($this->Database->Record['subject']);
            if (trim($_messageSubject) == '') {
                $_messageSubject = $this->Language->Get('nosubject');
            }


            $_extendedInfo = '';
            if (trim($this->Database->Record['email'] != '')) {
                $_extendedInfo .= $this->Database->Record['email'] . '<br />';
            }

            $_departmentTitle = '';
            if (isset($_departmentCache[$this->Database->Record['departmentid']])) {
                $_departmentTitle = $_departmentCache[$this->Database->Record['departmentid']]['title'];
            }

            if (!empty($_departmentTitle)) {
                $_extendedInfo .= sprintf($this->Language->Get('usi_department'), $_departmentTitle) . '<br />';
            }

            $_staffName = '';
            if (isset($_staffCache[$this->Database->Record['staffid']])) {
                $_staffName = $_staffCache[$this->Database->Record['staffid']]['fullname'];
            } else {
                $_staffName = $this->Database->Record['staffname'];
            }

            if (!empty($_staffName)) {
                $_extendedInfo .= sprintf($this->Language->Get('usi_staff'), $_staffName) . '<br />';
            }

            if (self::HasQuery($this->Database->Record['contents'], $this->GetQuery())) {
                $_highlightResult = implode(' ... ', $this->StringHighlighter->GetHighlightedRange(strip_tags($this->Database->Record['contents']), $this->GetQuery(), 20));
                if (trim($_highlightResult) != '') {
                    $_extendedInfo .= '... ' . $_highlightResult . ' ...<br />';
                }
            }

            $_searchResults[] = array(text_to_html_entities($this->Database->Record['fullname']) . ': ' . $_messageSubject, SWIFT::Get('basename') . '/LiveChat/Message/ViewMessage/' . $this->Database->Record['messageid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']), $_extendedInfo);
        }

        return $_searchResults;
    }

    /**
     * Search the Call Logs
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchCallLogs()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_lscanviewcalls') == '0') {
            return array();
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');

        $_searchResults = $_userIDList = $_userEmailList = $_callIDList = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "calls
            WHERE ((" . BuildSQLSearch('phonenumber', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('userfullname', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('useremail', $this->GetQuery(), false, false) . "))
                AND departmentid IN (" . BuildIN($this->_departmentIDList) . ")
            ORDER BY dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_callIDList[] = $this->Database->Record['callid'];

            if ($this->Database->Record['userid'] != '0') {
                $_userIDList[] = $this->Database->Record['userid'];

                if (!isset($_userEmailList[$this->Database->Record['userid']])) {
                    $_userEmailList[$this->Database->Record['userid']] = array();
                }

                if (trim($this->Database->Record['useremail']) != '') {
                    $_userEmailList[$this->Database->Record['userid']][] = $this->Database->Record['useremail'];
                }
            }
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails
            WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")");
        while ($this->Database->NextRecord()) {
            if (isset($_userEmailList[$this->Database->Record['linktypeid']]) && !in_array($this->Database->Record['email'], $_userEmailList[$this->Database->Record['linktypeid']])) {
                $_userEmailList[$this->Database->Record['linktypeid']][] = $this->Database->Record['email'];
            }
        }

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "calls
            WHERE callid IN (" . BuildIN($_callIDList) . ")
            ORDER BY dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {

            $_extendedInfo = '';
            if (isset($_userEmailList[$this->Database->Record['userid']])) {
                $_extendedInfo .= implode(', ', $_userEmailList[$this->Database->Record['userid']]) . '<br />';
            }

            $_departmentTitle = '';
            if (isset($_departmentCache[$this->Database->Record['departmentid']])) {
                $_departmentTitle = $_departmentCache[$this->Database->Record['departmentid']]['title'];
            }

            if (!empty($_departmentTitle)) {
                $_extendedInfo .= sprintf($this->Language->Get('usi_department'), $_departmentTitle) . '<br />';
            }

            $_staffName = '';
            if (isset($_staffCache[$this->Database->Record['staffid']])) {
                $_staffName = $_staffCache[$this->Database->Record['staffid']]['fullname'];
            } else {
                $_staffName = $this->Database->Record['stafffullname'];
            }

            if (!empty($_staffName)) {
                $_extendedInfo .= sprintf($this->Language->Get('usi_staff'), $_staffName) . '<br />';
            }
            $_extendedInfo .= sprintf($this->Language->Get('usi_status'), SWIFT_Call::GetStatusLabel($this->Database->Record['callstatus'])) . '<br />';
            $_extendedInfo .= sprintf($this->Language->Get('usi_type'), SWIFT_Call::GetTypeLabel($this->Database->Record['calltype'])) . '<br />';
            $_extendedInfo .= sprintf($this->Language->Get('usi_duration'), SWIFT_Date::ColorTime($this->Database->Record['duration'], false, true)) . '<br />';

            $_searchResults[] = array($this->Database->Record['phonenumber'] . IIF(trim($this->Database->Record['userfullname']) != '', ' - ' . text_to_html_entities($this->Database->Record['userfullname'])), SWIFT::Get('basename') . '/LiveChat/Call/ViewCall/' . $this->Database->Record['callid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']), $_extendedInfo);
        }

        return $_searchResults;
    }
}
