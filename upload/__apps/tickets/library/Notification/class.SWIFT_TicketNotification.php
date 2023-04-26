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

namespace Tickets\Library\Notification;

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Emoji;
use SWIFT_Exception;
use Base\Library\HTML\SWIFT_HTML;
use SWIFT_Interface;
use SWIFT_LanguageEngine;
use SWIFT_Library;
use SWIFT_Mail;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\Rating\SWIFT_RatingResult;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_TemplateEngine;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\Note\SWIFT_TicketNoteManager;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserOrganization;

/**
 * The Ticket Notification Management Class
 *
 * @property SWIFT_Mail $Mail
 * @property SWIFT_Emoji $Emoji
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @author Varun Shoor
 */
class SWIFT_TicketNotification extends SWIFT_Library {
    protected $Ticket = false;
    static protected $_notificationDispatchCache = array();
    protected $_updateContainer = array();

    // Core Constants
    const TYPE_USER = 1;
    const TYPE_USERORGANIZATION = 2;
    const TYPE_STAFF = 3;
    const TYPE_TEAM = 4;
    const TYPE_DEPARTMENT = 5;
    const TYPE_CUSTOM = 6;

    const CONTENT_TEXT = 1;
    const CONTENT_HTML = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception If the creation fals
     */
    public function __construct(SWIFT_Ticket $_SWIFT_TicketObject) {
        parent::__construct();
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        if (!$this->SetTicket($_SWIFT_TicketObject)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }
    }

    /**
     * Set the Ticket object
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket object pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetTicket(SWIFT_Ticket $_SWIFT_TicketObject) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            return false;
        }

        $this->Ticket = $_SWIFT_TicketObject;

        return true;
    }

    /**
     * Check to see if its a valid type
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_notificationType) {
        if ($_notificationType == self::TYPE_USER || $_notificationType == self::TYPE_USERORGANIZATION || $_notificationType == self::TYPE_STAFF ||
                $_notificationType == self::TYPE_TEAM || $_notificationType == self::TYPE_DEPARTMENT || $_notificationType == self::TYPE_CUSTOM) {
            return true;
        }

        return false;
    }

    /**
     * Update a property
     *
     * @author Varun Shoor
     * @param string $_title The Property Title
     * @param string $_oldValue The Old Value
     * @param string $_newValue The New Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_title, $_oldValue, $_newValue, $_userOldValue = '', $_userNewValue = '') {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': empty title');
        }

        $this->_updateContainer[$_title] = array($_oldValue, $_newValue, $_userOldValue, $_userNewValue);

        return true;
    }

    /**
     * Get the Email List for the relevant notification type
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @param array $_customEmailList (OPTIONAL) The Custom Email List
     * @return array|bool The Email List
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid data is provided
     */
    protected function GetEmailList($_notificationType, $_customEmailList = array()) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!static::IsValidType($_notificationType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        /**
         * BUG FIX : Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4980 : Third party replies are sent to ticket creator if 'User' is selected in 'New reply from end user' notification.
         */
        $_lastPostID = $this->Ticket->GetProperty('lastpostid');
        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_lastPostID));

        $_emailList = array();

        switch ($_notificationType) {
            case self::TYPE_USER:
                {
                    $_emailList[] = $this->Ticket->GetProperty('email');

                    // Attempt to get ALL emails created by the user
                    if ($this->Ticket->GetProperty('userid') != '0' && $_SWIFT_TicketPostObject->GetProperty('isthirdparty') != '1' && $_SWIFT_TicketPostObject->GetProperty('creator') != SWIFT_TicketPost::CREATOR_THIRDPARTY) {
                        $_userEmailList = SWIFT_UserEmail::RetrieveList($this->Ticket->GetProperty('userid'));

                        foreach ($_userEmailList as $_emailAddress) {
                            if (!in_array($_emailAddress, $_emailList)) {
                                $_emailList[] = $_emailAddress;
                            }
                        }
                    }
                }
                break;

            case self::TYPE_USERORGANIZATION:
                {
                    $_SWIFT_UserOrganizationObject = $this->Ticket->GetUserOrganizationObject();
                    if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                        $_userIDList = SWIFT_User::GetUserIDListOnOrganization($_SWIFT_UserOrganizationObject->GetUserOrganizationID());

                        $_userEmailList = SWIFT_UserEmail::RetrieveListOnUserIDList($_userIDList);

                        foreach ($_userEmailList as $_emailAddress) {
                            if (!in_array($_emailAddress, $_emailList)) {
                                $_emailList[] = $_emailAddress;
                            }
                        }
                    }
                }
                break;

            case self::TYPE_STAFF:
                {
                    if ($this->Ticket->GetProperty('ownerstaffid') != '0') {

                        try {
                            $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($this->Ticket->GetProperty('ownerstaffid')));
                            if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded() && $_SWIFT_StaffObject->GetProperty('isenabled') == '1') {
                                $_emailList[] = $_SWIFT_StaffObject->GetProperty('email');
                            }
                        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                            return false;
                        }
                    }
                }
                break;

            case self::TYPE_TEAM:
                {
                    if ($this->Ticket->GetProperty('ownerstaffid') != '0') {

                        try {
                            $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($this->Ticket->GetProperty('ownerstaffid')));
                            if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
                                $_emailList = SWIFT_Staff::RetrieveEmailOnStaffGroupID($_SWIFT_StaffObject->GetProperty('staffgroupid'));
                            }
                        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                            return false;
                        }
                    }

                }
                break;

            case self::TYPE_DEPARTMENT:
                {
                    $_staffCache = $_SWIFT->Cache->Get('staffcache');

                    $_staffIDList = array();
                    if ($this->Ticket->GetProperty('departmentid') != '0') {
                        $_staffIDList = SWIFT_Department::RetrieveAssignedStaffIDList($this->Ticket->GetProperty('departmentid'));
                    }

                    if (_is_array($_staffIDList)) {
                        foreach ($_staffIDList as $_staffID) {
                            if (isset($_staffCache[$_staffID]) && !in_array($_staffCache[$_staffID]['email'], $_emailList) && $_staffCache[$_staffID]['isenabled'] == '1') {
                                $_emailList[] = $_staffCache[$_staffID]['email'];
                            }
                        }
                    }
                }
                break;

            case self::TYPE_CUSTOM:
                {
                    return $_customEmailList;
                }
                break;

            // @codeCoverageIgnoreStart
            // this code will never be executed
            default:
                break;
        }
            // @codeCoverageIgnoreEnd

        return array_merge($_emailList, $_customEmailList);
    }

    /**
     * Retrieve the base properties in Text & HTML
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @return array array(Text Content, HTML Content)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetBaseContent($_notificationType) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        $_SWIFT = SWIFT::GetInstance();
        $_templateVariableList = array();
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        $_baseContentsText = $_baseContentsHTML = '';

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-5060 Unicode characters like emojis not working in the subject
         */
        // Subject
        $_templateVariableList['_ticketnotification']['subject'] = $this->Emoji->decode($this->Ticket->Get('subject'));
        $_baseContentsText .= $this->Emoji->decode($this->Ticket->GetProperty('subject')) . SWIFT_CRLF;
        $_baseContentsText .= str_repeat('-', mb_strlen($this->Emoji->decode($this->Ticket->GetProperty('subject')))) . SWIFT_CRLF;

        $_baseContentsHTML .= $this->Emoji->decode($this->Ticket->GetProperty('subject')) . '<br />' . SWIFT_CRLF;
        $_baseContentsHTML .= str_repeat('-', mb_strlen($this->Emoji->decode($this->Ticket->GetProperty('subject')))) . '<br />' . SWIFT_CRLF;

        // Properties
        $_baseContentsText .= SWIFT_CRLF;
        $_baseContentsHTML .= '<br />' . SWIFT_CRLF;

        // Ticket ID
        $_templateVariableList['_ticketnotification']['ticketid'] = $this->Ticket->GetTicketDisplayID();
        $_baseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_ticketid')) . $this->Ticket->GetTicketDisplayID() . SWIFT_CRLF;
        $_baseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_ticketid')) . $this->Ticket->GetTicketDisplayID() . '</div>' . SWIFT_CRLF;

        // URL
        $_ticketURL = '';
        $_originalBaseName = SWIFT_BASENAME;
        if (!empty($_originalBaseName))
        {
            $_originalBaseName = '/' . $_originalBaseName;
        }
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-4630 : Sample ticket email contains Invalid Ticket Url and three language errors
         *
         * Comments : Used product_url in place of SWIFTPATH.
         */
        if ($_notificationType == self::TYPE_USER || $_notificationType == self::TYPE_USERORGANIZATION) {
            $_ticketURL = StripTrailingSlash($_SWIFT->Settings->Get('general_producturl')) . $_originalBaseName . $this->Template->GetTemplateGroupPrefix() . '/Tickets/Ticket/View/' . $this->Ticket->GetTicketDisplayID();
        } else {
            $_ticketURL = $_SWIFT->Settings->Get('general_producturl') . 'staff' . $_originalBaseName . '/Tickets/Ticket/View/' . $this->Ticket->GetTicketID();
        }

        $_templateVariableList['_ticketnotification']['ticketlink'] = $_ticketURL;
        $_baseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_url')) . $_ticketURL . SWIFT_CRLF;
        $_baseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_url')) . '<a href="' . $_ticketURL . '">' . $_ticketURL . '</a></div>' . SWIFT_CRLF;

        // General Properties
        $_templateVariableList['_ticketnotification']['fullname'] = $this->Ticket->Get('fullname');
        $_baseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_fullname')) . $this->Ticket->GetProperty('fullname') . SWIFT_CRLF;
        $_baseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_fullname')) . text_to_html_entities($this->Ticket->GetProperty('fullname')) . '</div>' . SWIFT_CRLF;

        $_templateVariableList['_ticketnotification']['email'] = $this->Ticket->Get('email');
        $_baseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_email')) . $this->Ticket->GetProperty('email') . SWIFT_CRLF;
        $_baseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_email')) . htmlspecialchars($this->Ticket->GetProperty('email')) . '</div>' . SWIFT_CRLF;

        if ($this->Ticket->GetProperty('phoneno') != '') {
            $_baseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_phone')) . $this->Ticket->GetProperty('phoneno') . SWIFT_CRLF;
            $_baseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_phone')) . htmlspecialchars($this->Ticket->GetProperty('phoneno')) . '</div>' . SWIFT_CRLF;
        }

        $_templateVariableList['_ticketnotification']['phoneno'] = IIF($this->Ticket->Get('phoneno') != '', $this->Ticket->Get('phoneno'), $this->Language->Get('nval_na'));

        $_creatorLabel = $this->Language->Get('nval_user');
        if ($this->Ticket->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF) {
            $_creatorLabel = $this->Language->Get('nval_staff');
        }

        $_templateVariableList['_ticketnotification']['creator'] = $_creatorLabel;
        $_baseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_creator')) . $_creatorLabel . SWIFT_CRLF;
        $_baseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_creator')) . $_creatorLabel . '</div>' . SWIFT_CRLF;

        // Department
        $_departmentID = $this->Ticket->GetProperty('departmentid');
        $_departmentTitle = $this->Language->Get('nval_na');
        if (isset($_departmentCache[$_departmentID])) {
            $_departmentTitle = $_departmentCache[$_departmentID]['title'];
        } else if ($_departmentID == '0') {
            $_departmentTitle = $this->Language->Get('nval_trash');
        }

        $_templateVariableList['_ticketnotification']['department'] = $_departmentTitle;
        $_baseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_department')) . $_departmentTitle . SWIFT_CRLF;
        $_baseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_department')) . text_to_html_entities($_departmentTitle) . '</div>' . SWIFT_CRLF;

        $_extendedBaseContentsText = $_extendedBaseContentsHTML = '';

        // Owner
        $_ownerStaffID = $this->Ticket->GetProperty('ownerstaffid');
        $_ownerTitle = $this->Language->Get('nval_na');
        if ($_ownerStaffID == 0) {
            $_ownerTitle = $this->Language->Get('nval_unassigned');
        } else if (isset($_staffCache[$_ownerStaffID])) {
            $_ownerTitle = $_staffCache[$_ownerStaffID]['fullname'];
        }

        $_templateVariableList['_ticketnotification']['owner'] = $_ownerTitle;
        $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_owner')) . $_ownerTitle . SWIFT_CRLF;
        $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_owner')) . htmlspecialchars($_ownerTitle) . '</div>' . SWIFT_CRLF;

        // Type
        $_ticketTypeID = $this->Ticket->GetProperty('tickettypeid');
        $_typeTitle = $this->Language->Get('nval_na');
        if (isset($_ticketTypeCache[$_ticketTypeID])) {
            $_typeTitle = $_ticketTypeCache[$_ticketTypeID]['title'];
        }

        $_templateVariableList['_ticketnotification']['type'] = $_typeTitle;
        $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_type')) . $_typeTitle . SWIFT_CRLF;
        $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_type')) . htmlspecialchars($_typeTitle) . '</div>' . SWIFT_CRLF;

        // Status
        $_ticketStatusID = $this->Ticket->GetProperty('ticketstatusid');
        $_statusTitle = $this->Language->Get('nval_na');
        if (isset($_ticketStatusCache[$_ticketStatusID])) {
            $_statusTitle = $_ticketStatusCache[$_ticketStatusID]['title'];
        }

        $_templateVariableList['_ticketnotification']['status'] = $_statusTitle;
        $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_status')) . $_statusTitle . SWIFT_CRLF;
        $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_status')) . htmlspecialchars($_statusTitle) . '</div>' . SWIFT_CRLF;

        // Priority
        $_ticketPriorityID = $this->Ticket->GetProperty('priorityid');
        $_priorityTitle = $this->Language->Get('nval_na');
        if (isset($_ticketPriorityCache[$_ticketPriorityID])) {
            $_priorityTitle = $_ticketPriorityCache[$_ticketPriorityID]['title'];
        }

        $_templateVariableList['_ticketnotification']['priority'] = $_priorityTitle;
        $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_priority')) . $_priorityTitle . SWIFT_CRLF;
        $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_priority')) . htmlspecialchars($_priorityTitle) . '</div>' . SWIFT_CRLF;

        // SLA Plan
        $_slaPlanID    = $this->Ticket->Get('slaplanid');
        $_slaPlanTitle = $this->Language->Get('nval_na');
        if (isset($_slaPlanCache[$_slaPlanID])) {
            $_slaPlanTitle = $_slaPlanCache[$_slaPlanID]['title'];

            $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_sla')) . $_slaPlanTitle . SWIFT_CRLF;
            $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_sla')) . htmlspecialchars($_slaPlanTitle) . '</div>' . SWIFT_CRLF;
        }

        $_templateVariableList['_ticketnotification']['slaplantitle'] = $_slaPlanTitle;

        // Template Group
        $_templateGroupID    = $this->Ticket->Get('tgroupid');
        $_templateGroupTitle = $this->Language->Get('nval_na');
        if (isset($_templateGroupCache[$_templateGroupID])) {
            $_templateGroupTitle = $_templateGroupCache[$_templateGroupID]['title'];

            $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_tgroup')) . $_templateGroupTitle . SWIFT_CRLF;
            $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_tgroup')) . htmlspecialchars($_templateGroupTitle) . '</div>' . SWIFT_CRLF;
        }

        $_templateVariableList['_ticketnotification']['templategrouptitle'] = $_templateGroupTitle;

        // Created
        $_createdDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Ticket->GetProperty('dateline'));

        $_templateVariableList['_ticketnotification']['created'] = $_createdDate;
        $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_created')) . $_createdDate . SWIFT_CRLF;
        $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_created')) . htmlspecialchars($_createdDate) . '</div>' . SWIFT_CRLF;

        $_updatedDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Ticket->GetProperty('lastactivity'));

        $_templateVariableList['_ticketnotification']['updated'] = $_updatedDate;
        $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_updated')) . $_updatedDate . SWIFT_CRLF;
        $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_updated')) . htmlspecialchars($_updatedDate) . '</div>' . SWIFT_CRLF;

        // Due & Resolution Due
        $_dueDateline           = $this->Ticket->Get('duetime');
        $_resolutionDueDateline = $this->Ticket->Get('resolutionduedateline');
        $_dueDate               = $_resolutionDueDate = 0;

        if ($_dueDateline != '0') {
            $_dueDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_dueDateline) . ' ' . IIF($_dueDateline > DATENOW, '(' . SWIFT_Date::ColorTime($_dueDateline - DATENOW, false, true) . ')');
            $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_due')) . $_dueDate . SWIFT_CRLF;
            $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_due')) . htmlspecialchars($_dueDate) . '</div>' . SWIFT_CRLF;
        }

        $_templateVariableList['_ticketnotification']['duedate'] = IIF($_dueDate != 0, $_dueDate, $this->Language->Get('nval_na'));

        if ($_resolutionDueDateline != '0') {
            $_resolutionDueDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_resolutionDueDateline) . ' ' . IIF($_resolutionDueDateline > DATENOW, '(' . SWIFT_Date::ColorTime($_resolutionDueDateline - DATENOW, false, true) . ')');
            $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_resolutiondue')) . $_resolutionDueDate . SWIFT_CRLF;
            $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_resolutiondue')) . htmlspecialchars($_resolutionDueDate) . '</div>' . SWIFT_CRLF;
        }

        $_templateVariableList['_ticketnotification']['resolutionduedate'] = IIF($_resolutionDueDate != 0, $_resolutionDueDate, $this->Language->Get('nval_na'));

        // User Organization
        $_userOrganization = $this->Language->Get('nval_na');
        $_UserOrganization = $this->Ticket->GetUserOrganizationObject();

        if ($_UserOrganization instanceof SWIFT_UserOrganization && $_UserOrganization->GetIsClassLoaded()) {
            $_userOrganization = $_UserOrganization->Get('organizationname');
        }

        $_templateVariableList['_ticketnotification']['userorganization'] = $_userOrganization;

        // Ratings
        if ($this->Ticket->GetProperty('hasratings') == '1')
        {
            $_ticketRatingContainer =  SWIFT_Rating::Retrieve(array(SWIFT_Rating::TYPE_TICKET), false, SWIFT_PUBLIC, $this->Ticket->GetProperty('departmentid'));

            $_ticketRatingIDList = array_keys($_ticketRatingContainer);

            $_ticketRatingResultContainer =  SWIFT_RatingResult::Retrieve($_ticketRatingIDList, array($this->Ticket->GetTicketID()));
            if (_is_array($_ticketRatingResultContainer))
            {
                $_extendedBaseContentsText .= SWIFT_CRLF;
                $_extendedBaseContentsHTML .= '<br />' . SWIFT_CRLF;
            }

            /**
             * @var int $_ratingID
             * @var array $_ratingResultContainer
             */
            foreach ($_ticketRatingResultContainer as $_ratingID => $_ratingResultContainer)
            {
                if (!isset($_ratingResultContainer[$this->Ticket->GetTicketID()]))
                {
                    continue;
                }

                $_ratingResult = $_ratingResultContainer[$this->Ticket->GetTicketID()];
                if (!isset($_ticketRatingContainer[$_ratingResult['ratingid']]))
                {
                    continue;
                }


                $_ratingContainer = $_ticketRatingContainer[$_ratingResult['ratingid']];
                $_extendedBaseContentsText .= static::GetTitle(self::CONTENT_TEXT, $_ratingContainer['ratingtitle']) . ': ' . $_ratingResult['ratingresult'] . '/' . $_ratingContainer['ratingscale'] . SWIFT_CRLF;
                $_extendedBaseContentsHTML .= static::GetTitle(self::CONTENT_HTML, $_ratingContainer['ratingtitle']) . ': ' . $_ratingResult['ratingresult'] . '/' . $_ratingContainer['ratingscale'] . '</div>' . SWIFT_CRLF;
            }
        }

        if ($_notificationType != self::TYPE_USER && $_notificationType != self::TYPE_USERORGANIZATION) {
            $_baseContentsText .= $_extendedBaseContentsText;
            $_baseContentsHTML .= $_extendedBaseContentsHTML;
        }

        return array($_baseContentsText, $_baseContentsHTML, $_templateVariableList);
    }

    /**
     * Get the Padding for a property title
     *
     * @author Varun Shoor
     * @param mixed $_contentType Content Type
     * @param string $_title The Title
     * @return string|bool Property Padding
     */
    protected static function GetTitle($_contentType, $_title) {
        $_baseLine = 22;

        $_extendLength = ($_baseLine - mb_strlen(StripName($_title, 18)));
        if ($_extendLength <= 0)
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            return $_title;
            // @codeCoverageIgnoreEnd
        }

        if ($_contentType == self::CONTENT_TEXT) {
            return str_repeat(' ', $_extendLength) . $_title;
        }

        if ($_contentType == self::CONTENT_HTML) {
            return '<div style="margin-left: 40px;">' . $_title;
        }

        return false;
    }

    /**
     * Prepare the final email content
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @param string $_notificationContents (OPTIONAL) The Notification Contents
     * @return array array(Text Contents, HTML Contents)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Prepare($_notificationType, $_notificationContents = '') {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Update the template group if needed
        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        $_ticketTemplateGroupID = $this->Ticket->GetProperty('tgroupid');
        if (!empty($_ticketTemplateGroupID) && isset($_templateGroupCache[$_ticketTemplateGroupID]))
        {
            $this->Template->SetTemplateGroupID($_ticketTemplateGroupID);
        }

        // First get the base contents
        list($_baseContentsText, $_baseContentsHTML, $_templateVariableList) = $this->GetBaseContent($_notificationType);

        $_templateVariableList['_ticketnotification']['content'] = $this->Language->Get('nval_na');

        /**
         * BUG FIX - Bishwanath Jha
         *
         * SWIFT-3695: HTML Tags are being rendered in email clients for staff replies sent to end user
         */
        // Add Notification Contents if necessary
        if (!empty($_notificationContents)) {
            $_baseContentsText .= strip_tags($_notificationContents);

            $_settingValue   = IIF(in_array($_notificationType, array(self::TYPE_USER, self::TYPE_USERORGANIZATION)), $this->Settings->Get('t_ochtml'), $this->Settings->Get('t_chtml'));
            $_isHTML         = SWIFT_HTML::DetectHTMLContent($_notificationContents);
            $_parsedContents = SWIFT_TicketPost::GetParsedContents($_notificationContents, $_settingValue, IIF($this->Settings->Get('t_chtml') == 'entities', false, $_isHTML));
            $_parsedContents = SWIFT_HTML::HTMLBreaklines($_parsedContents, $_isHTML);

            $_parsedContents  = $_SWIFT->Emoji->decode($_parsedContents);

            $_templateVariableList['_ticketnotification']['content'] = $_parsedContents;

            $_baseContentsHTML .= '<br />' . SWIFT_CRLF . '<br />' . SWIFT_CRLF . $_parsedContents;
        }

        // Now prepare the contents prefix
        $_contentPrefix = $_infoTitle = '';
        $_templateVariableList['_ticketnotification']['changedby'] = $this->Language->Get('nval_na');

        if (isset($_SWIFT->Staff) && $_SWIFT->Staff instanceof SWIFT_Staff && $_SWIFT->Staff->GetIsClassLoaded()) {
            $_infoTitle = sprintf($this->Language->Get('ninfoupdated'), $_SWIFT->Staff->GetProperty('fullname'), $this->Ticket->GetTicketDisplayID());

            $_templateVariableList['_ticketnotification']['changedby'] = $_SWIFT->Staff->Get('fullname');

        } else if (isset($_SWIFT->User) && $_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
            $_infoTitle = sprintf($this->Language->Get('ninfoupdated'), $_SWIFT->User->GetProperty('fullname'), $this->Ticket->GetTicketDisplayID());

            $_templateVariableList['_ticketnotification']['changedby'] = $_SWIFT->User->Get('fullname');
        }

        if ($_infoTitle != '') {
            $_contentPrefix .= $_infoTitle . SWIFT_CRLF;
            $_contentPrefix .= str_repeat('-', mb_strlen($_infoTitle)) . SWIFT_CRLF . SWIFT_CRLF;
        }

        $_contentPropertiesText = $_contentPropertiesHTML = '';
        foreach ($this->_updateContainer as $_updateTitle => $_updateValues) {
            $_oldValue = $_updateValues[0];
            $_newValue = $_updateValues[1];
            if ($_notificationType == self::TYPE_USER || $_notificationType == self::TYPE_USERORGANIZATION) {
                if (isset($_updateValues[2]) && !empty($_updateValues[2])) {
                    $_oldValue = $_updateValues[2];
                }
                if (isset($_updateValues[3]) && !empty($_updateValues[3])) {
                    $_newValue = $_updateValues[3];
                }
            }
            // Old Value == '' && New Value != ''
            if ($_oldValue == '' && $_newValue != '') {
                $_contentPropertiesText .= static::GetTitle(self::CONTENT_TEXT, $_updateTitle) . $_newValue . SWIFT_CRLF;
                $_contentPropertiesHTML .= static::GetTitle(self::CONTENT_HTML, $_updateTitle) . $_newValue . '</div>' . SWIFT_CRLF;

            // Old Value != '' && New Value == ''
            } else if ($_oldValue != '' && $_newValue == '') {
                $_contentPropertiesText .= static::GetTitle(self::CONTENT_TEXT, $_updateTitle) . $this->Language->Get('notificationcleared') . sprintf($this->Language->Get('notificationwas'), $_oldValue) . SWIFT_CRLF;
                $_contentPropertiesHTML .= static::GetTitle(self::CONTENT_HTML, $_updateTitle) . $this->Language->Get('notificationcleared') . sprintf($this->Language->Get('notificationwas'), $_oldValue) . '</div>' . SWIFT_CRLF;

            // Old Value != '' && New Value != ''
            } else if ($_oldValue != '' && $_newValue != '') {
                $_contentPropertiesText .= static::GetTitle(self::CONTENT_TEXT, $_updateTitle) . $_newValue . sprintf($this->Language->Get('notificationwas'), $_oldValue) . SWIFT_CRLF;
                $_contentPropertiesHTML .= static::GetTitle(self::CONTENT_HTML, $_updateTitle) . $_newValue . sprintf($this->Language->Get('notificationwas'), $_oldValue) . '</div>' . SWIFT_CRLF;
            }
        }

        if (count($this->_updateContainer)) {
            $_contentPropertiesText .= SWIFT_CRLF;
            $_contentPropertiesHTML .= '<br />' . SWIFT_CRLF;
        }

        // Prepare the final email
        $_finalEmailContentsText = $_contentPrefix . $_contentPropertiesText . $_baseContentsText;
        $_finalEmailContentsHTML = nl2br(htmlspecialchars($_contentPrefix)) . $_contentPropertiesHTML . $_baseContentsHTML;
        $_templateVariableList['_ticketnotification']['changes'] = IIF($_contentPropertiesHTML != '', $_contentPropertiesHTML, $this->Language->Get('nval_na'));

        return array($_finalEmailContentsText, $_finalEmailContentsHTML, $_templateVariableList);
    }

    /**
     * Dispatch the email
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param mixed $_notificationType The Notification Type
     * @param array $_customEmailList (OPTIONAL) The Custom Email List
     * @param string $_notificationSubject (OPTIONAL) The Notification Subject
     * @param string $_notificationContents (OPTIONAL) The Notification Contents
     * @param string $_customFromName (OPTIONAL) The Custom From Name
     * @param string $_customFromEmail (OPTIONAL) The Custom From Email
     * @param bool $_requireChanges (OPTIONAL) Whether it requires something to be changed
     * @param string $_notificationEvent (OPTIONAL) The Notification Event
     * @param string $_tokenIdentifier (OPTIONAL) An identifier to be added to cache token (i.e send email for multiple escalation notifications)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Dispatch($_notificationType, $_customEmailList = array(), $_notificationSubject = '', $_notificationContents = '', $_customFromName = '', $_customFromEmail = '',
            $_requireChanges = false, $_notificationEvent = '', $_tokenIdentifier = '') {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!static::IsValidType($_notificationType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /**
         * BUG FIX - Ruchi Kothari
         *
         * SWIFT-2240: Pre-Parser rule option "Do Not Process Ticket Alert Rules" does not work.
         *
         * Comments: Return if noalerts is true
         */
        if ($this->Ticket->GetNoAlerts() == true) {
            return true;
        }

        if ($_requireChanges == true && (empty($_notificationContents) && !count($this->_updateContainer)))
        {
            return false;
        }

        $_emailList = $this->GetEmailList($_notificationType, $_customEmailList);

        if (!is_array($_emailList) || !count($_emailList)) {
            return false;
        }

        /**
         * FEATURE - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-3186 Custom field data in autoresponders, ticket notifications.
         */
        // Custom Fields
        $_customFields = $this->CustomFieldManager->GetCustomFieldValue($this->Ticket->GetProperty('ticketid'));

        $this->Template->Assign('_customFields', $_customFields);

        // Content Container
        list($_notificationContentText, $_notificationContentHTML, $_templateVariableList) = $this->Prepare($_notificationType, $_notificationContents);

        $this->Load->Library('Mail:Mail');

        // Load the phrases from the database..
        $this->Language->Load('tickets_notifications', SWIFT_LanguageEngine::TYPE_FILE);
        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $this->Template->Assign('_notificationContentsText', $_notificationContentText);
        $this->Template->Assign('_notificationContentsHTML', $_notificationContentHTML);
        $this->Template->Assign('_ticketNotification', $_templateVariableList['_ticketnotification']);
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-4630: Sample ticket email contains Invalid Ticket Url and three language errors
         */
        $this->Template->Assign('_swiftPath', $_SWIFT->Settings->Get('general_producturl'));

        $_textEmailContents = $_htmlEmailContents = '';

        if ($_notificationType == self::TYPE_USER || $_notificationType == self::TYPE_USERORGANIZATION) {
            $_textEmailContents = $this->Template->Get('emailnotificationuser_text', SWIFT_TemplateEngine::TYPE_DB);
            $_htmlEmailContents = $this->Template->Get('emailnotificationuser_html', SWIFT_TemplateEngine::TYPE_DB);
        } else {
            $_textEmailContents = $this->Template->Get('emailnotification_text', SWIFT_TemplateEngine::TYPE_DB);
            $_htmlEmailContents = $this->Template->Get('emailnotification_html', SWIFT_TemplateEngine::TYPE_DB);
        }


        $_mailNotificationType = SWIFT_Ticket::MAIL_NOTIFICATION;
        if ($_notificationType == self::TYPE_USER || $_notificationType == self::TYPE_USERORGANIZATION) {
            $_mailNotificationType = SWIFT_Ticket::MAIL_CLIENT;
        }

        $_mailSubject = $this->Ticket->GetMailSubject($_mailNotificationType, $_notificationSubject);
        /**
         * BUG FIX - Saloni Dhall
         *
         * SWIFT-3412: New Client Reply rule for notifications works for the first reply only
         *
         */
        $_cacheToken = $this->Ticket->GetTicketID() . $this->Ticket->GetProperty('lastpostid') . $_notificationEvent . $_tokenIdentifier;

        /*
         * BUG FIX - Saloni Dhall
         *
         * SWIFT-2686 If a staff member replies to a notification email, he/she gets a copy of his own response back along with other staff members.
         *
         * Comments: Checking Last ticket post email from address and skipping.
         */
        $_staffReplyFromEmail = '';
        if (!empty($_notificationEvent) && $_notificationEvent === 'newstaffreply') {
            $_lastPostID = $this->Ticket->GetProperty('lastpostid');

            $_SWIFT_TicketPostObject = false;
            try {
                $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_lastPostID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
            if ($_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded() &&
                $_SWIFT_TicketPostObject->GetProperty('creationmode') == SWIFT_Ticket::CREATIONMODE_EMAIL && $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF) {
                $_staffReplyFromEmail = $_SWIFT_TicketPostObject->GetProperty('email');
            }
        }

        /*
        * BUG FIX - Saloni Dhall
        *
        * SWIFT-2606 Help desk should only send ticket note notifications to the staff member, whom the note is set to be visible
        *
        * Comments : None
        */
        $_ticketNoteForStaffEmail = '';
        if($this->Ticket->GetTicketID() && $this->Ticket->GetProperty('hasnotes') == '1' && $_notificationEvent == 'newticketnotes')
        {
            // Get the staff ID for the last note for whome the note specifically made or visible.
            $_TicketNote = SWIFT_TicketNoteManager::GetLastNote($this->Ticket->GetTicketID());

            if ($_TicketNote instanceof SWIFT_TicketNote && $_TicketNote->GetIsClassLoaded()) {
                $_staffCache = $_SWIFT->Cache->Get('staffcache');
                if (isset($_staffCache[$_TicketNote->GetProperty('forstaffid')])) {
                    $_ticketNoteForStaffEmail = $_staffCache[$_TicketNote->GetProperty('forstaffid')]['email'];
                }
            }
        }

        foreach ($_emailList as $_emailAddress)
        {
            if (!isset(static::$_notificationDispatchCache[$_cacheToken]))
            {
                static::$_notificationDispatchCache[$_cacheToken] = array();
            }

            /*
             * BUG FIX - Ravi Sharma
             *
             * SWIFT-704 Staff members gets notification for restricted departments.
             *
             * Comments: None
             */
            $_staffID = SWIFT_Staff::IsStaffEmail($_emailAddress);
            if (!empty($_staffID) && !in_array($this->Ticket->GetProperty('departmentid'), SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffID)))
            {
                continue;
            }

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-823 Notifications to ticket owner sent when owner make change
             *
             */
            if (in_array($_emailAddress, static::$_notificationDispatchCache[$_cacheToken]) || (isset($_SWIFT->Staff) && $_SWIFT->Staff instanceof SWIFT_Staff && $_SWIFT->Staff->GetIsClassLoaded() && $_emailAddress == $_SWIFT->Staff->GetProperty('email')) || $_emailAddress == $_staffReplyFromEmail || (!empty($_ticketNoteForStaffEmail) && $_emailAddress != $_ticketNoteForStaffEmail))
            {
                continue;
            }

            $this->Mail = new SWIFT_Mail();

            if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFFAPI) {
                $_attachmentContainer = $this->Ticket->GetAttachments();
            } else {
                $_attachmentContainer = $this->Ticket->GetNotificationAttachments();
            }
            $_htmlContents = $_htmlEmailContents;
            if (_is_array($_attachmentContainer)) {
                foreach ($_attachmentContainer as $_ticketAttachment) {
                    /**
                     * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                     *
                     * SWIFT-4191 Inline images do not render in staff notification emails, in MS Outlook client
                     *
                     * Comments: Embed the attachment in case ticket attachment contained contentid
                     */
                    if (empty($_ticketAttachment['contentid'])) {
                        $this->Mail->Attach($_ticketAttachment['contents'], $_ticketAttachment['type'], $_ticketAttachment['name']);
                    } else {
                        $_htmlContents = str_replace('cid:' . $_ticketAttachment['contentid'], $this->Mail->Embed($_ticketAttachment['contents'], $_ticketAttachment['type'], $_ticketAttachment['name']), $_htmlContents);
                    }
                }
            }

            /*
            $_debugMsg = 'To: ' . $_emailAddress . SWIFT_CRLF;
            $_debugMsg .= 'From Email: ' . IIF(empty($_customFromEmail), $this->Ticket->GetMailFromEmail(), $_customFromEmail) . SWIFT_CRLF;
            $_debugMsg .= 'From Name: ' . IIF(empty($_customFromName), SWIFT::Get('companyname'), $_customFromName) . SWIFT_CRLF;
            $_debugMsg .= 'Subject: ' . $_mailSubject . SWIFT_CRLF;
            $_debugMsg .= 'Contents: ' . SWIFT_CRLF . $_textEmailContents . SWIFT_CRLF;
            echo nl2br($_debugMsg);
            echo $_htmlEmailContents;
            */

            $this->Mail->SetToField($_emailAddress);

            $_fromEmailAddress = IIF(empty($_customFromEmail), $this->Ticket->GetMailFromEmail(), $_customFromEmail);
            $_fromEmailAddress = $this->Ticket->RetrieveFromEmailWithSuffix($_fromEmailAddress, SWIFT_Ticket::MAIL_NOTIFICATION);

            $this->Mail->SetFromField($_fromEmailAddress, IIF(empty($_customFromName), SWIFT::Get('companyname'), $_customFromName));
            $this->Mail->SetSubjectField($_mailSubject);
            $this->Mail->SetDataText($_textEmailContents, true);
            $this->Mail->SetDataHTML($_htmlContents, false, false);

            if (SWIFT_INTERFACE !== 'tests')
                $this->Mail->SendMail();

            static::$_notificationDispatchCache[$_cacheToken][] = $_emailAddress;
        }

        return true;
    }
}
