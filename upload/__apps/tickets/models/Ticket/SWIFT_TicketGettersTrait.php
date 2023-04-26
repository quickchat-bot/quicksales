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

namespace Tickets\Models\Ticket;

use Base\Library\HTML\SWIFT_HTML;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreFile;
use SWIFT_Base;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_LanguageEngine;
use SWIFT_Loader;
use SWIFT_Model;
use SWIFT_Perf_Log;
use Base\Library\Notification\SWIFT_NotificationManager;
use Base\Models\Notification\SWIFT_NotificationRule;
use SWIFT_ParserBan;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\Template\SWIFT_TemplateGroup;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;
use Tickets\Models\Watcher\SWIFT_TicketWatcher;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserNote;
use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserOrganizationNote;
use Base\Models\User\SWIFT_UserSetting;
use Tickets\Library\Bayesian\SWIFT_Bayesian;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Library\Notification\SWIFT_TicketNotification;
use Tickets\Library\Ticket\SWIFT_TicketEmailDispatch;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\AutoClose\SWIFT_AutoCloseRule;
use Tickets\Models\Bayesian\SWIFT_BayesianCategory;
use Tickets\Models\Draft\SWIFT_TicketDraft;
use Tickets\Models\Escalation\SWIFT_EscalationPath;
use Tickets\Models\Escalation\SWIFT_EscalationRule;
use Tickets\Models\FollowUp\SWIFT_TicketFollowUp;
use Tickets\Models\Link\SWIFT_TicketLinkChain;
use Tickets\Models\Lock\SWIFT_TicketLock;
use Tickets\Models\Lock\SWIFT_TicketPostLock;
use Tickets\Models\Merge\SWIFT_TicketMergeLog;
use Tickets\Models\MessageID\SWIFT_TicketMessageID;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\Note\SWIFT_TicketNoteManager;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Models\Recurrence\SWIFT_TicketRecurrence;
use Tickets\Models\SLA\SWIFT_SLA;

trait SWIFT_TicketGettersTrait
{
    /**
     * Retrieves the Ticket ID
     *
     * @author Varun Shoor
     * @return mixed "ticketid" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetTicketID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketid'];
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Get or Create a User ID based on given info
     *
     * @author Varun Shoor
     * @param string $_fullName The User Full Name
     * @param string $_email the User Email
     * @param int $_userGroupID The User Group ID
     * @param int $_languageID (OPTIONAL) The Language ID
     * @param bool $_checkGeoIP (OPTIONAL) Check GeoIP for User
     * @param string $_phoneNumber
     * @return int The User ID on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GetOrCreateUserID($_fullName, $_email, $_userGroupID, $_languageID = 0, $_checkGeoIP = false, $_phoneNumber = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        // User processing.. no user specified?
        $_userIDFromEmail = SWIFT_UserEmail::RetrieveUserIDOnUserEmail($_email);

        $_userID = false;
        if (!empty($_userIDFromEmail)) {
            $_userID = $_userIDFromEmail;
        } else {
            $_SWIFT_UserObject = SWIFT_User::Create(
                $_userGroupID,
                false,
                SWIFT_User::SALUTATION_NONE,
                $_fullName,
                '',
                $_phoneNumber,
                true,
                false,
                array($_email),
                false,
                $_languageID,
                false,
                false,
                false,
                false,
                false,
                true,
                true,
                $_checkGeoIP
            );

            $_userID = $_SWIFT_UserObject->GetUserID();
        }

        return $_userID;
    }

    /**
     * Retrieve the User Group ID based on User ID
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetUserGroupID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userID = (int) ($this->GetProperty('userid'));
        if (empty($_userID)) {
            return false;
        }

        try {
            $_SWIFT_UserObject = $this->GetUserObject();

            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                return $_SWIFT_UserObject->GetProperty('usergroupid');
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        return false;
    }

    /**
     * Retrieve the SLA properties. This array is used to process the SLA rules and the value should match the criteria specified for SWIFT_SLA
     *
     * @author Varun Shoor
     * @return mixed "_slaProperties" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetSLAProperties()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_slaProperties = array();
        $_slaProperties[SWIFT_SLA::SLA_TICKETSTATUS] = $this->GetProperty('ticketstatusid');
        $_slaProperties[SWIFT_SLA::SLA_TICKETPRIORITY] = $this->GetProperty('priorityid');
        $_slaProperties[SWIFT_SLA::SLA_TICKETDEPARTMENT] = $this->GetProperty('departmentid');
        $_slaProperties[SWIFT_SLA::SLA_TICKETOWNER] = $this->GetProperty('ownerstaffid');
        $_slaProperties[SWIFT_SLA::SLA_TICKETEMAILQUEUE] = $this->GetProperty('emailqueueid');
        $_slaProperties[SWIFT_SLA::SLA_TICKETFLAGTYPE] = $this->GetProperty('flagtype');
        $_slaProperties[SWIFT_SLA::SLA_TICKETCREATOR] = $this->GetProperty('creator');
        $_slaProperties[SWIFT_SLA::SLA_TICKETUSERGROUP] = $this->GetUserGroupID();

        $_slaProperties[SWIFT_SLA::SLA_TICKETFULLNAME] = $this->GetProperty('fullname');
        $_slaProperties[SWIFT_SLA::SLA_TICKETEMAIL] = $this->GetProperty('email');
        $_slaProperties[SWIFT_SLA::SLA_TICKETLASTREPLIER] = $this->GetProperty('lastreplier');
        $_slaProperties[SWIFT_SLA::SLA_TICKETSUBJECT] = $this->GetProperty('subject');
        $_slaProperties[SWIFT_SLA::SLA_TICKETCHARSET] = $this->GetProperty('charset');

        $_slaProperties[SWIFT_SLA::SLA_TICKETTEMPLATEGROUP] = $this->GetProperty('tgroupid');
        $_slaProperties[SWIFT_SLA::SLA_TICKETISRESOLVED] = $this->GetProperty('isresolved');
        $_slaProperties[SWIFT_SLA::SLA_TICKETTYPE] = $this->GetProperty('tickettypeid');
        $_slaProperties[SWIFT_SLA::SLA_TICKETWASREOPENED] = $this->GetProperty('wasreopened');
        $_slaProperties[SWIFT_SLA::SLA_TICKETTOTALREPLIES] = $this->GetProperty('totalreplies');
        $_slaProperties[SWIFT_SLA::SLA_TICKETBAYESCATEGORY] = $this->GetProperty('bayescategoryid');

        return $_slaProperties;
    }

    /**
     * Retrieve the Has Status Changed Property
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetHasStatusChanged()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_hasStatusChanged;
    }

    /**
     * Retrieve the linked tickets in the active chain
     *
     * @author Varun Shoor
     * @return array The Linked Ticket Container
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetLinks()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetProperty('islinked') == '0') {
            return array();
        }

        // First get all the chain hashes where this ticket is part of
        $_chainHashList = array();
        $this->Database->Query("SELECT chainhash FROM " . TABLE_PREFIX . "ticketlinkchains WHERE ticketid = '" . (int) ($this->GetTicketID()) .
            "'");
        while ($this->Database->NextRecord()) {
            $_chainHashList[] = $this->Database->Record['chainhash'];
        }

        if (!count($_chainHashList)) {
            return array();
        }

        // Now get all the tickets that are part of the chain hashes
        $_ticketLinkContainer = array();
        $this->Database->Query("SELECT tickets.*, ticketlinkchains.ticketlinktypeid FROM " . TABLE_PREFIX . "ticketlinkchains AS ticketlinkchains
            LEFT JOIN " . TABLE_PREFIX . "tickets AS tickets ON (ticketlinkchains.ticketid = tickets.ticketid) WHERE
            chainhash IN (" . BuildIN($_chainHashList) . ")");
        while ($this->Database->NextRecord()) {
            if ($this->Database->Record['ticketid'] == $this->GetTicketID()) {
                continue;
            }

            $_ticketLinkContainer[$this->Database->Record['ticketlinktypeid']][$this->Database->Record['ticketid']] =
                new SWIFT_Ticket(new SWIFT_DataStore($this->Database->Record));
        }

        return $_ticketLinkContainer;
    }

    /**
     * Return the Ticket ID to be displayed according to relevant setting
     *
     * @author Varun Shoor
     * @return mixed Ticket Mask ID or Ticket ID
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetTicketDisplayID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return IIF($this->Settings->Get('t_eticketid') == 'seq', $this->GetProperty('ticketid'), $this->GetProperty('ticketmaskid'));
    }

    /**
     * Get the ticket post count
     *
     * @author Varun Shoor
     * @return int The Ticket Post Count
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetTicketPostCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_totalCount = 0;

        $_totalItemContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketposts WHERE ticketid = '" .
            (int) ($this->GetTicketID()) . "'");
        if (isset($_totalItemContainer['totalitems'])) {
            $_totalCount = (int) ($_totalItemContainer['totalitems']);
        }

        return $_totalCount;
    }

    /**
     * Retrieve the ticket posts associated with this ticket
     *
     * @author Varun Shoor
     * @param int $_offset (OPTIONAL) The Starting Offset
     * @param int $_limit (OPTIONAL) The Number of Results to Return
     * @param string $_sortOrder (OPTIONAL) ASC/DESC The Sort Order
     * @param mixed $_creator (OPTIONAL) Filter by the Creator
     * @return array The Ticket Post Object Container
     * @throws SWIFT_Exception
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetTicketPosts($_offset = 0, $_limit = null, $_sortOrder = 'ASC', $_creator = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketPostObjectContainer = array();

        $_sqlQuery = "SELECT * FROM " . TABLE_PREFIX . "ticketposts WHERE ticketid = '" . (int) ($this->GetTicketID()) . "' ORDER BY ticketpostid " .
            $_sortOrder;
        if (!empty($_limit)) {
            $this->Database->QueryLimit($_sqlQuery, $_limit, $_offset);
        } else {
            $this->Database->Query($_sqlQuery);
        }

        while ($this->Database->NextRecord()) {
            if (!empty($_creator) && $_creator != $this->Database->Record['creator']) {
                continue;
            }

            $_ticketPostObjectContainer[$this->Database->Record['ticketpostid']] = new SWIFT_TicketPost(new SWIFT_DataStore($this->Database->Record));
        }

        return $_ticketPostObjectContainer;
    }

    /**
     * Retrieve the ticket attachment container
     *
     * @author Varun Shoor
     * @return array $_ticketAttachmentContainer
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetAttachmentContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketAttachmentContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE ticketid = '" . (int) ($this->GetTicketID()) . "'");
        while ($this->Database->NextRecord()) {
            $_ticketAttachmentContainer[$this->Database->Record['linktypeid']][$this->Database->Record['attachmentid']] = $this->Database->Record;
        }

        return $_ticketAttachmentContainer;
    }

    /**
     * Retrieve the history count for this user based on his userid & email address
     *
     * @author Varun Shoor
     * @return int The History Count
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetHistoryCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userID = '-1';
        if ($this->GetProperty('userid') != '0') {
            $_userID = (int) ($this->GetProperty('userid'));
        }

        /** BUG FIX : Parminder Singh <parminder.singh@kayako.com>
         *
         * SWIFT-2041 : Wrong count under History tab, when ticket is created from Staff CP using 'Send Mail' option
         */
        $_extendedSQL = "email = '" . $this->Database->Escape($this->GetProperty('email')) . "'";
        if ($this->GetProperty('replyto') != '') {
            $_extendedSQL = "replyto = '" . $this->Database->Escape($this->GetProperty('replyto')) . "'";
        }

        $_countContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets
                                                         WHERE userid = '" . (int) ($_userID) . "' OR " . $_extendedSQL);
        if (isset($_countContainer['totalitems']) && (int) ($_countContainer['totalitems']) > 0) {
            return $_countContainer['totalitems'] - 1;
        }

        return 0;
    }

    /**
     * Retrieve the relevant user object
     *
     * @author Varun Shoor
     * @return mixed "SWIFT_User" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetUserObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Have we already cached the user object?
        if ($this->_isUserObjectCached == true) {
            return $this->_UserObject;
        }

        $_SWIFT_UserObject = false;
        if ($this->GetProperty('userid') != '0') {
            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($this->GetProperty('userid')));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                return false;
            }
        }

        $this->_UserObject = $_SWIFT_UserObject;
        $this->_isUserObjectCached = true;

        return $_SWIFT_UserObject;
    }

    /**
     * Retrieve the relevant user group object
     *
     * @author Varun Shoor
     * @return mixed "SWIFT_UserGroup" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetUserGroupObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Have we already cached the user object?
        if ($this->_isUserGroupObjectCached == true) {
            return $this->_UserGroupObject;
        }

        $_SWIFT_UserObject = $this->GetUserObject();
        $_SWIFT_UserGroupObject = false;

        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            try {
                $_SWIFT_UserGroupObject = new SWIFT_UserGroup($_SWIFT_UserObject->GetProperty('usergroupid'));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                return false;
            }
        }

        $this->_UserGroupObject = $_SWIFT_UserGroupObject;
        $this->_isUserGroupObjectCached = true;

        return $_SWIFT_UserGroupObject;
    }

    /**
     * Retrieve the user organization object associated with this ticket
     *
     * @author Varun Shoor
     * @return mixed "SWIFT_UserOrganization" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetUserOrganizationObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Have we already cached the user organization object?
        if ($this->_isUserOrganizationObjectCached == true) {
            return $this->_UserOrganizationObject;
        }

        $_SWIFT_UserOrganizationObject = false;

        $_SWIFT_UserObject = $this->GetUserobject();

        if (
            $_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded() &&
            $_SWIFT_UserObject->GetProperty('userorganizationid') != '0'
        ) {
            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT_UserObject->GetProperty('userorganizationid'));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        $this->_UserOrganizationObject = $_SWIFT_UserOrganizationObject;
        $this->_isUserOrganizationObjectCached = true;

        return $_SWIFT_UserOrganizationObject;
    }

    /**
     * Get the relevant mail subject based on mail type
     *
     * @author Varun Shoor
     * @param mixed $_mailType The Mail Type
     * @param string $_customSubject (OPTIONAL) The Custom Subject
     * @return mixed "Mail Subject" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetMailSubject($_mailType, $_customSubject = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');
        $_emailQueueID = $this->GetProperty('emailqueueid');
        $_templateGroupID = $this->GetProperty('tgroupid');

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1327 From name and From email address improvements in autoresponder email, if visitor sends an offline message and help desk create the ticket
         * SWIFT-1651 From Email Address (in autoresponder email) should set according to the template group under email queue, if ticket is created from client support center in case of multi-domain installation
         *
         * Comments: None
         */

        // First try to load up the email queue based on template group & department match
        if (empty($_emailQueueID) && isset($_emailQueueCache['list']) && _is_array($_emailQueueCache['list'])) {
            foreach ($_emailQueueCache['list'] as $_emailQueueContainer) {
                if ($_emailQueueContainer['departmentid'] == $this->GetProperty('departmentid') && $_emailQueueContainer['tgroupid'] == $_templateGroupID) {
                    $_emailQueueID = $_emailQueueContainer['emailqueueid'];

                    break;
                }
            }
        }

        // No Queue Prefix set, itterate through queues looking for one for ONLY this department
        if (empty($_emailQueueID) && isset($_emailQueueCache['list']) && _is_array($_emailQueueCache['list'])) {
            foreach ($_emailQueueCache['list'] as $_emailQueueContainer) {
                if ($_emailQueueContainer['departmentid'] == $this->GetProperty('departmentid')) {
                    $_emailQueueID = $_emailQueueContainer['emailqueueid'];

                    break;
                }
            }
        }

        $_subjectPrefix = $_finalSubjectPrefix = '';
        if (isset($_emailQueueCache['list'][$_emailQueueID])) {
            $_emailQueueContainer = $_emailQueueCache['list'][$_emailQueueID];

            $_subjectPrefix = $_emailQueueContainer['prefix'];
        }

        if (!empty($_subjectPrefix)) {
            $_finalSubjectPrefix = $_subjectPrefix . ' ';
        }

        $_finalSubject = $this->GetProperty('subject');
        if (!empty($_customSubject)) {
            $_finalSubject = $_customSubject;
        }

        $_finalSubject = $this->Emoji->Decode($_finalSubject);

        if ($this->Settings->Get('t_cleanmailsubjects') == '1') {
            return $_finalSubject;
        }

        switch ($_mailType) {
            case self::MAIL_NOTIFICATION:
                return '[' . $_finalSubjectPrefix . '!' . $this->GetTicketDisplayID() . ']: ' . $_finalSubject;
                break;

            case self::MAIL_CLIENT:
                return '[' . $_finalSubjectPrefix . '#' . $this->GetTicketDisplayID() . ']: ' . $_finalSubject;
                break;

            case self::MAIL_THIRDPARTY:
                return '[' . $_finalSubjectPrefix . '~' . $this->GetTicketDisplayID() . ']: ' . $_finalSubject;
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Retrieve the default from name for the email
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject (OPTIONAL) The SWIFT_Staff Object Pointer
     * @return string The Default From Name
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetMailFromName(SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');
        $_emailQueueID = $this->GetProperty('emailqueueid');
        $_templateGroupID = $this->GetProperty('tgroupid');
        if (empty($_templateGroupID) && isset($_SWIFT->TemplateGroup) && $_SWIFT->TemplateGroup instanceof SWIFT_TemplateGroup) {
            $_templateGroupID = $_SWIFT->TemplateGroup->GetTemplateGroupID();
        }


        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1327 From name and From email address improvements in autoresponder email, if visitor sends an offline message and help desk create the ticket
         * SWIFT-1651 From Email Address (in autoresponder email) should set according to the template group under email queue, if ticket is created from client support center in case of multi-domain installation
         *
         * Comments: None
         */

        // First try to lookup based on department and template group
        if (empty($_emailQueueID) && isset($_emailQueueCache['list']) && _is_array($_emailQueueCache['list'])) {
            foreach ($_emailQueueCache['list'] as $_emailQueueContainer) {
                if ($_emailQueueContainer['departmentid'] == $this->GetProperty('departmentid') && $_emailQueueContainer['tgroupid'] == $_templateGroupID) {
                    $_emailQueueID = $_emailQueueContainer['emailqueueid'];

                    break;
                }
            }
        }

        // No Queue Prefix set, itterate through queues looking for one for ONLY this department
        if (empty($_emailQueueID) && isset($_emailQueueCache['list']) && _is_array($_emailQueueCache['list'])) {
            foreach ($_emailQueueCache['list'] as $_emailQueueContainer) {
                if ($_emailQueueContainer['departmentid'] == $this->GetProperty('departmentid')) {
                    $_emailQueueID = $_emailQueueContainer['emailqueueid'];

                    break;
                }
            }
        }

        // Name Priority: Template Group > Settings > Staff > Email Queue

        $_defaultFromName = SWIFT::Get('companyname');
        if (empty($_defaultFromName)) {
            $_defaultFromName = $this->Settings->Get('general_companyname');
        }

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_defaultFromName = $_SWIFT_StaffObject->GetProperty('fullname');
        }

        if (isset($_emailQueueCache['list'][$_emailQueueID])) {
            $_emailQueueContainer = $_emailQueueCache['list'][$_emailQueueID];

            if (!empty($_emailQueueContainer['customfromname'])) {
                $_defaultFromName = $_emailQueueContainer['customfromname'];
            }
        }

        return $_defaultFromName;
    }

    /**
     * Retrieve the default from email
     *
     * @author Varun Shoor
     * @return string The Default Return Email
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetMailFromEmail()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');
        $_emailQueueID = $this->GetProperty('emailqueueid');
        $_templateGroupID = $this->GetProperty('tgroupid');
        if (empty($_templateGroupID) && isset($_SWIFT->TemplateGroup) && $_SWIFT->TemplateGroup instanceof SWIFT_TemplateGroup) {
            $_templateGroupID = $_SWIFT->TemplateGroup->GetTemplateGroupID();
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1327 From name and From email address improvements in autoresponder email, if visitor sends an offline message and help desk create the ticket
         * SWIFT-1651 From Email Address (in autoresponder email) should set according to the template group under email queue, if ticket is created from client support center in case of multi-domain installation
         *
         * Comments: None
         */

        // First check against the template group id and department id
        if (empty($_emailQueueID) && isset($_emailQueueCache['list']) && _is_array($_emailQueueCache['list'])) {
            foreach ($_emailQueueCache['list'] as $_emailQueueContainer) {
                if ($_emailQueueContainer['isenabled'] && $_emailQueueContainer['departmentid'] == $this->GetProperty('departmentid') && $_templateGroupID == $_emailQueueContainer['tgroupid']) {
                    $_emailQueueID = $_emailQueueContainer['emailqueueid'];

                    break;
                }
            }
        }

        // No Queue Prefix set, iterate through queues looking for one for ONLY this department
        if (empty($_emailQueueID) && isset($_emailQueueCache['list']) && _is_array($_emailQueueCache['list'])) {
            foreach ($_emailQueueCache['list'] as $_emailQueueContainer) {
                if ($_emailQueueContainer['isenabled'] && $_emailQueueContainer['departmentid'] == $this->GetProperty('departmentid')) {
                    $_emailQueueID = $_emailQueueContainer['emailqueueid'];

                    break;
                }
            }
        }

        // Email Priority: Settings > Email Queue
        $_defaultFromEmail = $this->Settings->Get('general_returnemail');

        if (isset($_emailQueueCache['list'][$_emailQueueID])) {
            $_emailQueueContainer = $_emailQueueCache['list'][$_emailQueueID];
            if (!empty($_emailQueueContainer['customfromemail'])) {
                $_defaultFromEmail = $_emailQueueContainer['customfromemail'];
            } else {
                $_defaultFromEmail = $_emailQueueContainer['email'];
            }
        }

        return $_defaultFromEmail;
    }

    /**
     * Retrieve the signature for this based on associated queue or given staff/user
     *
     * @author Varun Shoor
     * @param bool $_isHTML Whether the reply is HTML
     * @param object $_SWIFT_StaffObject (OPTIONAL) The SWIFT_Staff Object
     * @return string
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetSignature($_isHTML, $_SWIFT_StaffObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');

        $_emailQueueID = $this->GetProperty('emailqueueid');

        $_signatureContents = '';

        // First priority is given to Staff signature
        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_staffSignatureContents = '';
            try {
                $_staffSignatureContents = $_SWIFT_StaffObject->GetProperty('signature');
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if ($_isHTML) {
                if (!SWIFT_HTML::DetectHTMLContent($_staffSignatureContents)) {
                    $_staffSignatureContents = nl2br($_staffSignatureContents);
                }
                $_signatureContents .= $_staffSignatureContents;
            } else {
                $_staffSignatureContents = strip_tags($_staffSignatureContents);
                $_signatureContents .= preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_staffSignatureContents);
            }
        }

        if (_is_array($_emailQueueCache) && isset($_emailQueueCache['list'][$_emailQueueID])) {
            $_emailQueueContainer = $_emailQueueCache['list'][$_emailQueueID];
            if ($_signatureContents != '') {
                $_signatureContents .= SWIFT_CRLF;
            }
            if ($_isHTML) {
                if (!SWIFT_HTML::DetectHTMLContent($_emailQueueContainer['contents'])) {
                    if ($_signatureContents != '') {
                        $_signatureContents .= '<br />';
                    }
                    $_emailQueueContainer['contents'] = nl2br($_emailQueueContainer['contents']);
                }
                $_signatureContents .= $_emailQueueContainer['contents'];
            } else {
                $_emailQueueContainer['contents'] = strip_tags($_emailQueueContainer['contents']);
                $_signatureContents .= preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_emailQueueContainer['contents']);
            }
        }

        if (empty($_signatureContents)) {
            return '';
        }

        return SWIFT_CRLF . $_signatureContents;
    }

    /**
     * Retrieves all other emails of the user excluding the one that was used to create the ticket
     *
     * @author Varun Shoor
     * @return array|bool The Email List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCCUserEmails()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_UserObject = $this->GetUserObject();
        $_ccEmailList = array();

        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            /*
             * BUG FIX - Pankaj Garg
             *
             * SWIFT-1683, In case of multiple email addresses for user account, there should be an option under User account to be enabled for sending ticket updates to all the email addresses
             *
             * Comments:We dispatch the auto responder and staff replies to ALL registered emails of the user whose option - 'Send Ticket update to all the email addresses' is set to true
             */
            $_userSettingContainer = SWIFT_UserSetting::RetrieveOnUser($_SWIFT_UserObject);

            if (isset($_userSettingContainer['sendemailtoall']) && $_userSettingContainer['sendemailtoall'] == 0) {
                return false;
            }

            $_userEmailList = $_SWIFT_UserObject->GetEmailList();
            if (_is_array($_userEmailList)) {
                foreach ($_userEmailList as $_emailAddress) {
                    if (mb_strtolower($_emailAddress) != mb_strtolower($this->GetProperty('email'))) {
                        $_ccEmailList[] = $_emailAddress;
                    }
                }
            }
        }
        return $_ccEmailList;
    }

    /**
     * Retrieve the first ticket post object
     *
     * @author Varun Shoor
     * @return SWIFT_TicketPost The SWIFT_TicketPost Object
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetFirstPostObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($this->GetProperty('firstpostid')));
        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $_SWIFT_TicketPostObject;
    }

    /**
     * Retrieve the time tracking count for the ticket
     *
     * @author Varun Shoor
     * @return int The Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTimeTrackCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_timeTrackCount = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickettimetracks WHERE ticketid = '" . (int) ($this->GetTicketID()) . "'");
        if (isset($_timeTrackCount['totalitems'])) {
            return (int) ($_timeTrackCount['totalitems']);
        }

        return 0;
    }

    /**
     * Retrieve the list of all possible ticket ids created by a given user
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object Pointer
     * @return array The Ticket ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetTicketIDListOnUser(SWIFT_User $_SWIFT_UserObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketIDList = array();

        $_SWIFT->Database->Query("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE userid = '" .  ($_SWIFT_UserObject->GetUserID()) . "' OR email IN (" . BuildIN($_SWIFT_UserObject->GetEmailList()) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketIDList[] = (int) ($_SWIFT->Database->Record['ticketid']);
        }

        return $_ticketIDList;
    }

    /**
     * Retrieve the Ticket ID from a Ticket Mask ID
     *
     * @author Varun Shoor
     * @param string $_ticketMaskID The Ticket Mask ID
     * @return int The Ticket ID
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetTicketIDFromMask($_ticketMaskID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketMaskID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketIDContainer = $_SWIFT->Database->QueryFetch("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE ticketmaskid = '" . $_SWIFT->Database->Escape($_ticketMaskID) . "'");
        if (isset($_ticketIDContainer['ticketid']) &&  !empty($_ticketIDContainer['ticketid'])) {
            return $_ticketIDContainer['ticketid'];
        }

        return 0;
    }

    /**
     * Retrieve the history count for this user based on his userid & email addresses
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @param array $_userEmailList
     * @return int The History Count
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public static function GetHistoryCountOnUser($_SWIFT_UserObject, $_userEmailList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userID = '-1';
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_userID = $_SWIFT_UserObject->GetUserID();
        }

        if (!_is_array($_userEmailList)) {
            $_userEmailList = $_SWIFT_UserObject->GetEmailList();
        }

        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets WHERE userid = '" .
            intval($_userID) . "' OR (email IN (" . BuildIN($_userEmailList) . ") OR replyto IN (" . BuildIN($_userEmailList) . "))");

        if (isset($_countContainer['totalitems']) && intval($_countContainer['totalitems']) > 0) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the Attachments Container
     *
     * @author Varun Shoor
     * @return array|bool The Attachments Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAttachments()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset(self::$_attachmentsContainer[$this->GetTicketID()])) {
            return false;
        }

        return self::$_attachmentsContainer[$this->GetTicketID()];
    }



    /**
     * Retrieve the Attachments Container
     *
     * @author Varun Shoor
     * @return array|bool The Attachments Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNotificationAttachments()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset(self::$_notificationAttachmentsContainer[$this->GetTicketID()])) {
            return false;
        }

        return self::$_notificationAttachmentsContainer[$this->GetTicketID()];
    }

    /**
     * Retrieve the last post contents
     *
     * @author Varun Shoor
     * @return string The last post contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLastPostContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        try {
            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($this->GetProperty('lastpostid')));

            if ($_SWIFT_TicketPostObject->GetProperty('ticketid') == $this->GetTicketID()) {
                return $_SWIFT_TicketPostObject->GetDisplayContents();
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {


            return '';
        }



        return '';
    }

    /**
     * Retrieve the Ticket Post IDs linked with this ticket
     *
     * @author Varun Shoor
     * @param bool $_ignoreForwardedPosts (OPTIONAL) Whether to ignore forwarded posts
     * @return array The Ticket Post ID List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketPostIDList($_ignoreForwardedPosts = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketPostIDList = array();

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2286 Staff creates a ticket with attachment. When forward, attachment does not go with the ticket.
         *
         */
        $this->Database->Query("SELECT ticketpostid, creator, emailto, isthirdparty FROM " . TABLE_PREFIX . "ticketposts WHERE ticketid = '" . (int) ($this->GetTicketID()) . "'");
        while ($this->Database->NextRecord()) {
            if ($_ignoreForwardedPosts == true && $this->Database->Record['creator'] == SWIFT_TicketPost::CREATOR_STAFF && $this->Database->Record['emailto'] != '' && $this->Database->Record['isthirdparty'] == '1') {
                continue;
            }

            $_ticketPostIDList[] = $this->Database->Record['ticketpostid'];
        }

        return $_ticketPostIDList;
    }

    /**
     * Retrieve the Ticket ID Object on the provided Ticket ID
     *
     * @author Varun Shoor
     * @param mixed $_ticketID The Numeric or Mask Ticket ID
     * @return SWIFT_Ticket|null
     */
    public static function GetObjectOnID($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();
        $perfLog = new SWIFT_Perf_Log();
        $startTime = time();
        $_SWIFT_TicketObject = false;

        if (is_numeric($_ticketID)) {
            try {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
                $perfLog->addLog("GetObjectOnID.1.SWIFT_Ticket", $startTime, time(), 'ticketID='.$_ticketID);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        } else {
            $_ticketID = SWIFT_Ticket::GetTicketIDFromMask($_ticketID);
            $perfLog->addLog("GetObjectOnID.2.GetTicketIDFromMask", $startTime, time(), 'ticketID='.$_ticketID);
            if (empty($_ticketID)) {
                return null;
            }

            try {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
                $perfLog->addLog("GetObjectOnID.3.Mask.SWIFT_Ticket", $startTime, time(), 'ticketID='.$_ticketID);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
            return $_SWIFT_TicketObject;
        }

        // By now we couldnt get the ticket object, we have to lookup the merge logs
        $_mergeTicketID = false;
        if (is_numeric($_ticketID)) {
            $_mergeTicketID = SWIFT_TicketMergeLog::GetTicketIDFromMergedTicketID($_ticketID);
        } else {
            $_mergeTicketID = SWIFT_TicketMergeLog::GetTicketIDFromMergedTicketMaskID($_ticketID);
        }
        $perfLog->addLog("GetObjectOnID.4.GetTicketIDFromMerged", $startTime, time(), 'ticketID='.$_ticketID);

        if (!empty($_mergeTicketID)) {
            try {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_mergeTicketID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
                return $_SWIFT_TicketObject;
            }
        }

        return null;
    }

    /**
     * Return the total ticket count
     *
     * @author Varun Shoor
     * @return int The Total Ticket Count
     */
    public static function GetTicketCount()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketCountContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets");
        if (isset($_ticketCountContainer['totalitems']) && !empty($_ticketCountContainer['totalitems'])) {
            return (int) ($_ticketCountContainer['totalitems']);
        }

        return 0;
    }

    /**
     * Get alert rules for ticket
     *
     * @author Ruchi Kothari
     * @return bool Alert rules status
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNoAlerts()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_noAlerts;
    }

    /**
     * Get Old ticket properties
     *
     * @author Mahesh Salaria
     * @return array of old ticket properties
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOldTicketProperties()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_oldTicketProperties;
    }
}
