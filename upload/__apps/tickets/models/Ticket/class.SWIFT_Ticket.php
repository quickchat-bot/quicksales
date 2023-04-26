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

use Base\Models\User\SWIFT_UserOrganizationLink;
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
use Parser\Models\Ban\SWIFT_ParserBan;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\Template\SWIFT_TemplateGroup;
use Tickets\Library\SLA\SWIFT_SLAManager;
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

/**
 * The Ticket Model Class
 *
 * @property SWIFT_SLAManager $SLAManager
 * @property SWIFT_TicketFlag $TicketFlag
 * @property SWIFT_TicketEmailDispatch $TicketEmailDispatch
 * @author Varun Shoor
 */
class SWIFT_Ticket extends SWIFT_Model
{
    use SWIFT_TicketGettersTrait;
    use SWIFT_TicketSettersTrait;
    use SWIFT_TicketCrudTrait;

    const TABLE_NAME        =    'tickets';
    const PRIMARY_KEY        =    'ticketid';

    const TABLE_STRUCTURE    =  "ticketid I PRIMARY AUTO NOTNULL,
                                ticketmaskid C(20) DEFAULT '' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                departmenttitle C(255) DEFAULT '' NOTNULL,
                                ticketstatusid I DEFAULT '0' NOTNULL,
                                ticketstatustitle C(255) DEFAULT '' NOTNULL,
                                priorityid I DEFAULT '0' NOTNULL,
                                prioritytitle C(255) DEFAULT '' NOTNULL,
                                emailqueueid I DEFAULT '0' NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                ownerstaffid I DEFAULT '0' NOTNULL,
                                ownerstaffname C(255) DEFAULT '' NOTNULL,
                                assignstatus I2 DEFAULT '0' NOTNULL,

                                fullname C(225) DEFAULT '' NOTNULL,
                                email C(150) DEFAULT '' NOTNULL,
                                lastreplier C(255) DEFAULT '' NOTNULL,
                                replyto C(150) DEFAULT '' NOTNULL,
                                subject C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                lastactivity I DEFAULT '0' NOTNULL,
                                laststaffreplytime I DEFAULT '0' NOTNULL,
                                lastuserreplytime I DEFAULT '0' NOTNULL,
                                slaplanid I DEFAULT '0' NOTNULL,
                                ticketslaplanid I DEFAULT '0' NOTNULL,
                                duetime I DEFAULT '0' NOTNULL,
                                totalreplies I DEFAULT '0' NOTNULL,
                                ipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                flagtype I2 DEFAULT '0' NOTNULL,
                                hasnotes I2 DEFAULT '0' NOTNULL,
                                hasattachments I2 DEFAULT '0' NOTNULL,
                                isemailed I2 DEFAULT '0' NOTNULL,
                                edited I2 DEFAULT '0' NOTNULL,
                                editedbystaffid I DEFAULT '0' NOTNULL,
                                editeddateline I DEFAULT '0' NOTNULL,
                                creator I2 DEFAULT '0' NOTNULL,
                                charset C(100) DEFAULT '' NOTNULL,
                                transferencoding C(50) DEFAULT '' NOTNULL,
                                timeworked I DEFAULT '0' NOTNULL,
                                timebilled I DEFAULT '0' NOTNULL,
                                dateicon I DEFAULT '0' NOTNULL,
                                lastpostid I DEFAULT '0' NOTNULL,
                                firstpostid I DEFAULT '0' NOTNULL,
                                tgroupid I DEFAULT '0' NOTNULL,
                                messageid C(17) DEFAULT '' NOTNULL,
                                escalationruleid I DEFAULT '0' NOTNULL,
                                hasdraft I2 DEFAULT '0' NOTNULL,
                                hasbilling I2 DEFAULT '0' NOTNULL,
                                isphonecall I2 DEFAULT '0' NOTNULL,
                                isescalated I2 DEFAULT '0' NOTNULL,
                                isescalatedvolatile I2 DEFAULT '0' NOTNULL,
                                phoneno C(255) DEFAULT '' NOTNULL,

                                isautoclosed I2 DEFAULT '0' NOTNULL,
                                autocloseruleid I DEFAULT '0' NOTNULL,
                                autoclosestatus I2 DEFAULT '0' NOTNULL,
                                autoclosetimeline I DEFAULT '0' NOTNULL,

                                escalatedtime I DEFAULT '0' NOTNULL,
                                followupcount I DEFAULT '0' NOTNULL,
                                hasfollowup I2 DEFAULT '0' NOTNULL,

                                hasratings I2 DEFAULT '0' NOTNULL,
                                tickethash C(50) DEFAULT '' NOTNULL,
                                islinked I2 DEFAULT '0' NOTNULL,
                                trasholddepartmentid I DEFAULT '0' NOT NULL,
                                tickettype I2 DEFAULT '0' NOTNULL,
                                tickettypeid I DEFAULT '0' NOTNULL,
                                tickettypetitle C(255) DEFAULT '' NOTNULL,
                                creationmode I2 DEFAULT '0' NOTNULL,

                                isfirstcontactresolved I2 DEFAULT '0' NOTNULL,
                                wasreopened I2 DEFAULT '0' NOTNULL,
                                reopendateline I DEFAULT '0' NOTNULL,
                                resolutiondateline I DEFAULT '0' NOTNULL,
                                escalationlevelcount I DEFAULT '0' NOTNULL,
                                resolutionseconds I DEFAULT '0' NOTNULL,
                                resolutionlevel I DEFAULT '0' NOTNULL,
                                repliestoresolution I DEFAULT '0' NOTNULL,

                                averageresponsetime I DEFAULT '0' NOTNULL,
                                averageslaresponsetime I DEFAULT '0' NOTNULL,
                                averageresponsetimehits I DEFAULT '0' NOTNULL,
                                firstresponsetime I DEFAULT '0' NOTNULL,

                                resolutionduedateline I DEFAULT '0' NOTNULL,
                                isresolved I2 DEFAULT '0' NOTNULL,
                                iswatched I2 DEFAULT '0' NOTNULL,

                                oldeditemailaddress C(255) DEFAULT '' NOTNULL,

                                recurrencefromticketid I DEFAULT '0' NOTNULL,

                                linkkbarticleid I DEFAULT '0' NOTNULL,
                                linkticketmacroid I DEFAULT '0' NOTNULL,
                                bayescategoryid I DEFAULT '0' NOTNULL";

    const INDEX_TICKETCOUNT = 'departmentid, ticketstatusid, ownerstaffid, tickettypeid, lastactivity';
    const INDEX_1           = 'userid, email, replyto, departmentid, isresolved';
    const INDEX_2           = 'slaplanid, duetime, ticketstatusid';
    const INDEX_3           = 'departmentid, ticketstatusid, lastactivity';
    const INDEX_4           = 'email';
    const INDEX_5           = 'departmentid, ticketstatusid, userid';
    const INDEX_6           = 'departmentid, ticketstatusid, duetime';
    const INDEX_7           = 'dateline';
    const INDEX_8           = 'departmentid, ticketstatusid, lastuserreplytime';
    const INDEX_9           = 'duetime, resolutionduedateline, isescalatedvolatile, isresolved';
    const INDEX_10          = 'ticketmaskid, ticketid, departmentid';
    const INDEX_11          = 'departmentid, ticketstatusid, duetime, resolutionduedateline';
    const INDEX_12          = 'isresolved, departmentid';
    const INDEX_13          = 'ticketstatusid, departmentid, priorityid, tickettypeid';
    const INDEX_14          = 'isescalatedvolatile, isresolved';
    const INDEX_15          = 'ticketid, departmentid'; // Used by Staff API Protocol
    const INDEX_16          = 'ticketid, isresolved, autoclosestatus, lastactivity'; // Used by Auto Close
    const INDEX_17          = 'autoclosestatus, autocloseruleid, autoclosetimeline'; // Used by Auto Close
    const INDEX_18          = 'lastactivity'; // Unified Search
    const INDEX_19          = 'recurrencefromticketid'; // Ticket Recurrence
    const INDEX_20          = 'tickettypeid, isresolved, departmentid'; // GetDashboardTypeProgress
    const INDEX_21          = 'priorityid, isresolved, departmentid'; // GetDashboardPriorityProgress
    const INDEX_23          = 'replyto, userid';


    const COLUMN_RENAME_HASBENCHMARKS = 'hasratings';

    protected $_dataStore = array();

    // Attachments
    static protected $_attachmentsContainer = array();
    static protected $_notificationAttachmentsContainer = array();

    // Notification Stuff
    public $Notification = false;
    public $NotificationManager = false;
    static protected $_notificationExecutionCache = array();

    // Workflow Queue Related Variables
    static protected $_isWorkflowQueueActive = false;
    static protected $_workflowQueue = array();

    // Watcher
    static protected $_watcherPendingCache = array();
    static protected $_watcherExecutedCache = array();
    protected $_watchNotificationMessage = array();
    protected $_lastPostNotificationMessage = '';
    protected $_watcherCustomName = '';

    protected $_noAlerts = false;

    /**
     * @var int This property decides whether the SLA calculation has been queued on shutdown
     */
    protected $_slaOverdueQueued = -1;

    /**
     * @var bool This property is used to prevent the execution of SLA calculation.
     */
    protected $_noSLACalculation = false;

    /**
     * @var SWIFT_User|null This property is used to cache the user object
     */
    protected $_UserObject = null;

    /**
     * @var SWIFT_UserOrganization|null This property is used to cache the user organization object
     */
    protected $_UserOrganizationObject = null;

    /**
     * @var SWIFT_UserGroup|null This property is used to cache the user group object
     */
    protected $_UserGroupObject = null;

    /**
     * @var bool This property is used to determine the cache flag for the user object
     */
    protected $_isUserObjectCached = false;

    /**
     * @var bool This property is used to determine the cache flag for the user group object
     */
    protected $_isUserGroupObjectCached = false;

    /**
     * @var bool This property is used to determine the cache flag for the user organization object
     */
    protected $_isUserOrganizationObjectCached = false;

    /**
     * @var bool Is used to determine if the status was changed in this object
     */
    protected $_hasStatusChanged = false;

    /**
     * @var array Is used to keep old Ticket Properties.
     */
    protected $_oldTicketProperties = array();

    // Core Constants
    const CREATOR_STAFF = 1;
    const CREATOR_USER = 2;
    const CREATOR_CLIENT = 2;

    const CREATIONMODE_SUPPORTCENTER = 1;
    const CREATIONMODE_STAFFCP = 2;
    const CREATIONMODE_EMAIL = 3;
    const CREATIONMODE_API = 4;
    const CREATIONMODE_SITEBADGE = 5;
    const CREATIONMODE_MOBILE = 6;
    const CREATIONMODE_STAFFAPI = 7;

    const TYPE_DEFAULT = 1;
    const TYPE_PHONE = 2;

    const MAIL_NOTIFICATION = 1;
    const MAIL_CLIENT = 2;
    const MAIL_THIRDPARTY = 3;

    const AUTOCLOSESTATUS_NONE = 0;
    const AUTOCLOSESTATUS_PENDING = 1;
    const AUTOCLOSESTATUS_CLOSED = 2;

    const MODE_DUPLICATE = 0;
    const MODE_SPLIT     = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Ticket_Exception('Failed to load Ticket Object');
        }

        $this->Load->Library('Ticket:TicketManager', [], true, false, 'tickets');

        $this->Notification = new SWIFT_TicketNotification($this);
        $this->NotificationManager = new SWIFT_NotificationManager($this);

        if (SWIFT_INTERFACE !== 'tests') {
            register_shutdown_function([$this, 'ProcessNotifications']);
        }

        if ($this->GetProperty('iswatched') == '1' && SWIFT_INTERFACE !== 'tests') {
            register_shutdown_function(array($this, 'ProcessWatchers'));
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        $intName = SWIFT::GetInstance()->Interface->GetName() ?: SWIFT_INTERFACE;
        if ($intName === 'tests') {
            return;
        }

        chdir(SWIFT_BASEPATH);

        if ($this->_slaOverdueQueued != -1) {
            $this->ProcessSLAOverdue($this->_slaOverdueQueued);
        }

        if ($this->GetProperty('iswatched') == '1') {
            $this->ProcessWatchers();
        }

        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Execute SLA If pending
     *
     * @author Varun Shoor
     * @param bool $_processResolutionDue (OPTIONAL) Whether to process the resolution due dateline
     * @param bool $_dontQueue (OPTIONAL) Dont queue the SLA execution
     * @param bool $_processReplyDue (OPTIONAL)
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ExecuteSLA($_processResolutionDue = true, $_dontQueue = false, $_processReplyDue = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessUpdatePool();

        if ($this->_slaOverdueQueued != -1 || $_dontQueue == true) {
            $this->ProcessSLAOverdue($_processResolutionDue, $_processReplyDue);

            if ($_dontQueue) {
                $this->ProcessUpdatePool();
            }
        } else {
            $this->QueueSLAOverdue($_processResolutionDue, $_processReplyDue);
        }

        return true;
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'tickets', $this->GetUpdatePool(), 'UPDATE', "ticketid = '" . ($this->GetTicketID()) .
            "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Add an entry into the update pool
     *
     * @param string $_key   The key
     * @param mixed $_value The value
     *
     * @return bool True on success, false otherwise
     */
    public function UpdatePool($_key, $_value)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Do we have a data store?
        $this->_dataStore[$_key] = $_value;

        $this->_updatePool[$_key] = $_value;

        if ($_key !== 'lastactivity' && !isset($this->_updatePool['lastactivity'])) {
            $_last = DATENOW;
            $this->_dataStore['lastactivity'] = $_last;
            $this->_updatePool['lastactivity'] = $_last;
        }

        $this->QueueUpdatePool();

        return true;
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid = '" .
                ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketid']) && !empty($_dataStore['ticketid'])) {
                $this->_dataStore = $_dataStore;
                $this->_dataStore['displayticketid'] = IIF($_SWIFT->Settings->Get('t_eticketid') == 'seq', $this->_dataStore['ticketid'], $this->_dataStore['ticketmaskid']);

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();
            $this->_dataStore['displayticketid'] = IIF($_SWIFT->Settings->Get('t_eticketid') == 'seq', $this->_dataStore['ticketid'], $this->_dataStore['ticketmaskid']);

            if (!isset($this->_dataStore['ticketid']) || empty($this->_dataStore['ticketid'])) {
                throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Check to see if its a valid creator type
     *
     * @author Varun Shoor
     * @param mixed $_creatorType The Creator Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCreatorType($_creatorType)
    {
        if ($_creatorType == self::CREATOR_STAFF || $_creatorType == self::CREATOR_USER) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid creation mode
     *
     * @author Varun Shoor
     * @param mixed $_creationMode The Creation Mode
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCreationMode($_creationMode)
    {
        if (
            $_creationMode == self::CREATIONMODE_SUPPORTCENTER || $_creationMode == self::CREATIONMODE_STAFFCP ||
            $_creationMode == self::CREATIONMODE_EMAIL || $_creationMode == self::CREATIONMODE_API ||
            $_creationMode == self::CREATIONMODE_SITEBADGE || $_creationMode == self::CREATIONMODE_MOBILE ||
            $_creationMode == self::CREATIONMODE_STAFFAPI
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid ticket type
     *
     * @author Varun Shoor
     * @param mixed $_ticketType The Ticket Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidTicketType($_ticketType)
    {
        if ($_ticketType == self::TYPE_DEFAULT || $_ticketType == self::TYPE_PHONE) {
            return true;
        }

        return false;
    }

    /**
     * Recaculates the 'hasattachment' property of the given tickets
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public static function RecalculateHasAttachmentProperty($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_finalTicketIDList = array();
        $_SWIFT->Database->Query("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTicketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        if (!count($_finalTicketIDList)) {
            return false;
        }

        // Now we calculate attachments
        $_attachmentCountContainer = array();
        $_SWIFT->Database->Query("SELECT COUNT(*) AS totalitems, ticketid FROM " . TABLE_PREFIX . "attachments GROUP BY ticketid HAVING ticketid IN (" . BuildIN($_finalTicketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_attachmentCountContainer[$_SWIFT->Database->Record['ticketid']] = ($_SWIFT->Database->Record['totalitems']);
        }

        $_attachmentTicketIDList = array();
        foreach ($_attachmentCountContainer as $_key => $_val) {
            if ($_val > 0) {
                $_attachmentTicketIDList[] = $_key;
            }
        }

        $_nonAttachmentTicketIDList = array();
        foreach ($_finalTicketIDList as $_ticketID) {
            if (!in_array($_ticketID, $_attachmentTicketIDList)) {
                $_nonAttachmentTicketIDList[] = $_ticketID;
            }
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('hasattachments' => '1'), 'UPDATE', "ticketid IN (" . BuildIN($_attachmentTicketIDList) . ")");

        if (count($_nonAttachmentTicketIDList)) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('hasattachments' => '0'), 'UPDATE', "ticketid IN (" . BuildIN($_nonAttachmentTicketIDList) . ")");
        }

        return true;
    }

    /**
     * Mark the ticket as watched
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkAsWatched()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('iswatched', '1');

        return true;
    }

    /**
     * Mark the ticket as overdue
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkAsDue()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Create Audit Log
        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            $_SWIFT->Language->Get('al_duestaffoverdue'),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('duetime'),
            '',
            DATENOW,
            '',
	        ['al_duestaffoverdue']
        );

        // Notification Update
        $_newDueDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, DATENOW);
        $this->Notification->Update($this->Language->Get('notification_due'), '', $_newDueDate);


        $this->UpdatePool('duetime', DATENOW);

        // Prevent SLA Calculation
        $this->_noSLACalculation = true;

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Clear the overdue time
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function ClearOverdue()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Prevent calculation of SLA due time on this ticket
        $this->_noSLACalculation = true;

        if ($this->GetProperty('duetime') == '0') {
            return true;
        }

        // Create Audit Log
        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            $_SWIFT->Language->Get('al_duestaffclear'),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('duetime'),
            '',
            0,
            '',
	        ['al_duestaffclear']
        );

        // Notification Update
        $_oldDueDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->GetProperty('duetime'));
        $_newDueDate = $this->Language->Get('notificationcleared');

        $this->Notification->Update($this->Language->Get('notification_due'), $_oldDueDate, $_newDueDate);

        $this->UpdatePool('duetime', '0');

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Template Group
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetTemplateGroup($_templateGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('tgroupid', $_templateGroupID);

        return true;
    }

    /**
     * Clear the resolution time
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function ClearResolutionDue()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetProperty('resolutionduedateline') == '0') {
            return true;
        }

        // Create Audit Log
        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            $_SWIFT->Language->Get('al_resduestaffclear'),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('resolutionduedateline'),
            '',
            0,
            '',
	        ['al_resduestaffclear']
        );

        // Notification Update
        $_oldDueDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->GetProperty('resolutionduedateline'));
        $_newDueDate = $this->Language->Get('notificationcleared');

        $this->Notification->Update($this->Language->Get('notification_resolutiondue'), $_oldDueDate, $_newDueDate);

        $this->UpdatePool('resolutionduedateline', '0');

        // Prevent calculation of SLA due time on this ticket
        $this->_noSLACalculation = true;

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set a flag on this ticket
     *
     * @author Varun Shoor
     * @param mixed $_flagType The Flag Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetFlag($_flagType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('Flag:TicketFlag', [], true, false, 'tickets');
        $_flagContainer = $this->TicketFlag->GetFlagContainer();

        if ($_flagType == '0') {
            // Notification Update
            $_oldFlagTitle = '';
            if (isset($_flagContainer[$this->GetProperty('flagtype')])) {
                $_oldFlagTitle = $_flagContainer[$this->GetProperty('flagtype')][0];
            }
            $_newFlagTitle = $this->Language->Get('notificationcleared');

            $this->Notification->Update($this->Language->Get('notification_flag'), $_oldFlagTitle, $_newFlagTitle);

            $this->UpdatePool('flagtype', '0');

            $this->QueueSLAOverdue();

            return true;
        }

        if (!SWIFT_TicketFlag::IsValidFlagType($_flagType)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        // If this ticket already has a flag set which matches the one specified.. then move on
        if ($this->GetProperty('flagtype') == $_flagType) {
            return true;
        }

        // Create Audit Log
        $_flagTitle = $_flagContainer[$_flagType][0];

        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATEFLAG,
            sprintf($_SWIFT->Language->Get('al_flag'), $_flagTitle),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('flagtype'),
            '',
            $_flagType,
            '',
	        ['al_flag', $_flagTitle]
        );

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_FLAGTYPE, $this->GetProperty('flagtype'), $_flagType);

        // Notification Update
        $_oldFlagTitle = '';
        if (isset($_flagContainer[$this->GetProperty('flagtype')])) {
            $_oldFlagTitle = $_flagContainer[$this->GetProperty('flagtype')][0];
        }
        $_newFlagTitle = $_flagTitle;

        $this->Notification->Update($this->Language->Get('notification_flag'), $_oldFlagTitle, $_newFlagTitle);

        $this->UpdatePool('flagtype', ($_flagType));

        $this->QueueSLAOverdue(false, false);

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Clear a flag on a ticket
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function ClearFlag()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        // If there is no flag set, then ignore.
        if ($this->GetProperty('flagtype') == '0') {
            return true;
        }

        $_SWIFT_TicketFlagObject = new SWIFT_TicketFlag();
        $_flagContainer = $_SWIFT_TicketFlagObject->GetFlagContainer();

        // Create Audit Log
        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATEFLAG,
            $_SWIFT->Language->Get('al_flagclear'),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('flagtype'),
            '',
            0,
            '',
	        ['al_flagclear']
        );

        // Notification Update
        $_oldFlagTitle = '';
        if (isset($_flagContainer[$this->GetProperty('flagtype')])) {
            $_oldFlagTitle = $_flagContainer[$this->GetProperty('flagtype')][0];
        }
        $_newFlagTitle = $this->Language->Get('notificationcleared');

        $this->Notification->Update($this->Language->Get('notification_flag'), $_oldFlagTitle, $_newFlagTitle);

        $this->UpdatePool('flagtype', 0);

        $this->QueueSLAOverdue();

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Change the department for the ticket
     *
     * @author Varun Shoor
     * @param int $_departmentID The department id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or if Invalid Data is Provided
     */
    public function SetDepartment($_departmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_departmentID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        if (!isset($_departmentCache[$_departmentID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        // If the ticket is already in this department then bail out
        if ($this->GetProperty('departmentid') == $_departmentID) {
            return true;
        }

        // Add old and new department to recount queue
        //        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
        //        SWIFT_TicketManager::Recount($_departmentID);
        SWIFT_TicketManager::Recount(false);

        // Create Audit Log
        $_oldDepartmentTitle = $_newDepartmentTitle = '';
        if (isset($_departmentCache[$this->GetProperty('departmentid')])) {
            $_oldDepartmentTitle = $_departmentCache[$this->GetProperty('departmentid')]['title'];
        }

        if ($this->GetProperty('departmentid') == '0') {
            $_oldDepartmentTitle = $this->Language->Get('trash');
        }

        $_newDepartmentTitle = $_departmentCache[$_departmentID]['title'];

        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATEDEPARTMENT,
            sprintf($_SWIFT->Language->Get('al_department'), $_oldDepartmentTitle, $_newDepartmentTitle),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('departmentid'),
            '',
            $_departmentID,
            '',
	        ['al_department', $_oldDepartmentTitle, $_newDepartmentTitle]
        );

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_DEPARTMENT, $this->GetProperty('departmentid'), $_departmentID);

        // Notification Update
        $_oldNotificationDepartmentTitle = $_newNotificationDepartmentTitle = '';
        if ($_oldDepartmentTitle == '') {
            $_oldNotificationDepartmentTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationDepartmentTitle = $_oldDepartmentTitle;
        }

        $_newNotificationDepartmentTitle = $_newDepartmentTitle;

        $_oldUserNotificationDepartmentTitle = $_oldNotificationDepartmentTitle;
        $_newUserNotificationDepartmentTitle = $_newNotificationDepartmentTitle;

        if (isset($_departmentCache[$this->GetProperty('departmentid')]) && $_departmentCache[$this->GetProperty('departmentid')]['departmenttype'] == SWIFT_PRIVATE) {
            $_oldUserNotificationDepartmentTitle = $this->Language->Get('private');
        }

        if (isset($_departmentCache[$_departmentID]) && $_departmentCache[$_departmentID]['departmenttype'] == SWIFT_PRIVATE) {
            $_newUserNotificationDepartmentTitle = $this->Language->Get('private');
        }

        $this->Notification->Update($this->Language->Get('notification_department'), $_oldNotificationDepartmentTitle, $_newNotificationDepartmentTitle, $_oldUserNotificationDepartmentTitle, $_newUserNotificationDepartmentTitle);

        $this->UpdatePool('departmentid', $_departmentID);
        $this->UpdatePool('departmenttitle', $_newDepartmentTitle);

        $this->QueueSLAOverdue(false, false);

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Ticket Type
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetType($_ticketTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketTypeID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        // Verify value of ticket type
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        if (!isset($_ticketTypeCache[$_ticketTypeID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketTypeContainer = $_ticketTypeCache[$_ticketTypeID];

        // If current ticket type is same as the new type then bail out
        if ($this->GetProperty('tickettypeid') == $_ticketTypeID) {
            return true;
        }

        // Verify the department id link of ticket type

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1298 Issue with ticket types and ticket statuses, if they are linked with Parent department
         *
         * Comments: None
         */
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_parentDepartmentID = false;
        if (isset($_departmentCache[$this->GetProperty('departmentid')])) {
            $_parentDepartmentID = $_departmentCache[$this->GetProperty('departmentid')]['parentdepartmentid'];
        }

        if ($_ticketTypeContainer['departmentid'] != '0' && $_ticketTypeContainer['departmentid'] != $this->GetProperty('departmentid') && $_ticketTypeContainer['departmentid'] != $_parentDepartmentID) {
            throw new SWIFT_Ticket_Exception('Ticket Type (' . $_ticketTypeID . ') & Department ID (' . ($this->GetProperty('departmentid')) . ') Mismatch');
        }

        // Create Audit Log
        $_oldTypeTitle = $_newTypeTitle = '';
        if (isset($_ticketTypeCache[$this->GetProperty('tickettypeid')])) {
            $_oldTypeTitle = $_ticketTypeCache[$this->GetProperty('tickettypeid')]['title'];
        }

        $_newTypeTitle = $_ticketTypeCache[$_ticketTypeID]['title'];

        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATETYPE,
            sprintf($_SWIFT->Language->Get('al_type'), $_oldTypeTitle, $_newTypeTitle),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('tickettypeid'),
            '',
            $_ticketTypeID,
            '',
	        ['al_type', $_oldTypeTitle, $_newTypeTitle]
        );

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_TICKETTYPE, $this->GetProperty('tickettypeid'), $_ticketTypeID);

        // Notification Update
        $_oldNotificationTypeTitle = $_newNotificationTypeTitle = '';
        if ($_oldTypeTitle == '') {
            $_oldNotificationTypeTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationTypeTitle = $_oldTypeTitle;
        }

        $_newNotificationTypeTitle = $_newTypeTitle;

        $_oldUserNotificationTypeTitle = $_oldNotificationTypeTitle;
        $_newUserNotificationTypeTitle = $_newNotificationTypeTitle;

        /**
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-2945:  Notice generated when Ticket Type is set from '-NA-' to an available Ticket Type
         *
         * Comments: None
         */
        if (isset($_ticketTypeCache[$this->GetProperty('tickettypeid')]) && $_ticketTypeCache[$this->GetProperty('tickettypeid')]['type'] == SWIFT_PRIVATE) {
            $_oldUserNotificationTypeTitle = $this->Language->Get('private');
        }

        if ($_ticketTypeCache[$_ticketTypeID]['type'] == SWIFT_PRIVATE) {
            $_newUserNotificationTypeTitle = $this->Language->Get('private');
        }

        $this->Notification->Update($this->Language->Get('notification_type'), $_oldNotificationTypeTitle, $_newNotificationTypeTitle, $_oldUserNotificationTypeTitle, $_newUserNotificationTypeTitle);


        $this->UpdatePool('tickettypeid', $_ticketTypeID);
        $this->UpdatePool('tickettypetitle', $_newTypeTitle);

        //        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
        SWIFT_TicketManager::Recount(false);

        $this->QueueSLAOverdue(false, false);

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Has Status Changed Property
     *
     * @author Varun Shoor
     * @param bool $_hasStatusChanged
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetHasStatusChanged($_hasStatusChanged)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_hasStatusChanged = $_hasStatusChanged;

        return true;
    }

    /**
     * Set the Ticket Status
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @param bool $_isViaAutoClose (OPTIONAL) If the status change is via auto close
     * @param bool $_suppressSurveyEmail (OPTIONAL) Whether to suppress survey email
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetStatus($_ticketStatusID, $_isViaAutoClose = false, $_suppressSurveyEmail = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_ticketStatusID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketStatusCache = $this->Cache->Get('statuscache');
        if (!isset($_ticketStatusCache[$_ticketStatusID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketStatusContainer = $_ticketStatusCache[$_ticketStatusID];

        $this->SetHasStatusChanged(true);

        // If the current ticket status id matches then bail out
        if ($this->GetProperty('ticketstatusid') == $_ticketStatusID) {
            return true;
        }

        // Verify the department id link of ticket status

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1298 Issue with ticket types and ticket statuses, if they are linked with Parent department
         *
         * Comments: None
         */
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_parentDepartmentID = false;
        if (isset($_departmentCache[$this->GetProperty('departmentid')])) {
            $_parentDepartmentID = $_departmentCache[$this->GetProperty('departmentid')]['parentdepartmentid'];
        }


        if ($_ticketStatusContainer['departmentid'] != '0' && $_ticketStatusContainer['departmentid'] != $this->GetProperty('departmentid') && $_ticketStatusContainer['departmentid'] != $_parentDepartmentID) {
            throw new SWIFT_Ticket_Exception('Ticket Status (' . $_ticketStatusID . ') & Department ID (' . ($this->GetProperty('departmentid')) . ') Mismatch');
        }

        // Create Audit Log
        $_oldStatusTitle = $_newStatusTitle = '';
        if (isset($_ticketStatusCache[$this->GetProperty('ticketstatusid')])) {
            $_oldStatusTitle = $_ticketStatusCache[$this->GetProperty('ticketstatusid')]['title'];
        }

        $_newStatusTitle = $_ticketStatusCache[$_ticketStatusID]['title'];

        $_statusLanguageKey = 'al_status';
        if ($_isViaAutoClose) {
            $_statusLanguageKey = 'al_statusautoclose';
        }

        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATESTATUS,
            sprintf($_SWIFT->Language->Get($_statusLanguageKey), $_oldStatusTitle, $_newStatusTitle),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('ticketstatusid'),
            '',
            $_ticketStatusID,
            '',
            [$_statusLanguageKey, $_oldStatusTitle, $_newStatusTitle]
        );

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_TICKETSTATUS, $this->GetProperty('ticketstatusid'), $_ticketStatusID);

        // Notification Update
        $_oldNotificationStatusTitle = $_newNotificationStatusTitle = '';
        if ($_oldStatusTitle == '') {
            $_oldNotificationStatusTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationStatusTitle = $_oldStatusTitle;
        }

        $_newNotificationStatusTitle = $_newStatusTitle;

        $_oldUserNotificationStatusTitle = $_oldNotificationStatusTitle;
        $_newUserNotificationStatusTitle = $_newNotificationStatusTitle;

        /**
         * BUG FIX - Ravi sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3202 Undefined offset: 0 (D:/xampp/htdocs/kayako/__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:2420)
         */
        if (isset($_ticketStatusCache[$this->Get('ticketstatusid')]) && isset($_ticketStatusCache[$this->Get('ticketstatusid')]['statustype']) && $_ticketStatusCache[$this->Get('ticketstatusid')]['statustype'] == SWIFT_PRIVATE) {
            $_oldUserNotificationStatusTitle = $this->Language->Get('private');
        }

        if ($_ticketStatusCache[$_ticketStatusID]['statustype'] == SWIFT_PRIVATE) {
            $_newUserNotificationStatusTitle = $this->Language->Get('private');
        }

        $this->Notification->Update($this->Language->Get('notification_status'), $_oldNotificationStatusTitle, $_newNotificationStatusTitle, $_oldUserNotificationStatusTitle, $_newUserNotificationStatusTitle);

        $this->UpdatePool('ticketstatusid', $_ticketStatusID);
        $this->UpdatePool('ticketstatustitle', $_newStatusTitle);

        //        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
        SWIFT_TicketManager::Recount(false);

        // Was reopened?
        if ($_ticketStatusContainer['markasresolved'] == '0' && $this->GetProperty('isresolved') == '1') {
            $this->UpdatePool('wasreopened', '1');
            $this->UpdatePool('reopendateline', DATENOW);
        }
        /**
         * BUG FIX : Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4424 SLA plan is not applied if clear Reply Due time option is enabled for a status.
         * SWIFT-5128 SLA timers getting reset to previous values while replying the tickets
         */
        if ($_ticketStatusContainer['resetduetime'] == '1') {
            $this->ProcessSLAOverdue();
            $this->ClearOverdue();
        }

        if ($_ticketStatusContainer['markasresolved'] == '1') {
            $this->UpdatePool('isresolved', '1');
            $this->UpdatePool('resolutiondateline', DATENOW);
            $this->UpdatePool('repliestoresolution', $this->GetProperty('totalreplies'));

            // How much time did it take to resolve this ticket?
            $this->UpdatePool('resolutionseconds', DATENOW - $this->GetProperty('dateline'));
        } else {
            $this->UpdatePool('isresolved', '0');

            // Are we changing to an unresolved status and ticket is marked as auto closed or pending closure?
            if ($this->GetProperty('autoclosestatus') != self::AUTOCLOSESTATUS_NONE) {
                $this->UpdatePool('autoclosestatus', self::AUTOCLOSESTATUS_NONE);
                $this->UpdatePool('isautoclosed', '0');
                $this->UpdatePool('autoclosetimeline', '0');
            }
        }

        // If the status is set to resolved then we reset the resolution due time
        $_processResolutionDue = false;
        if ($_ticketStatusContainer['markasresolved'] == '1') {
            $this->UpdatePool('resolutionduedateline', '0');
            $this->UpdatePool('duetime', '0');
            $this->UpdatePool('slaplanid', '0');

            $this->_noSLACalculation = true;

            // Otherwise if its not, and resolutionduedateline is 0 then we force it to recalculate the resolution due time
        } else if ($_ticketStatusContainer['markasresolved'] == '0' && $this->GetProperty('resolutionduedateline') == '0') {
            $_processResolutionDue = true;
        }

        $this->QueueSLAOverdue($_processResolutionDue, false);

        // Is First Contact Resolved?
        if ($this->GetProperty('totalreplies') == '0' && $_ticketStatusContainer['markasresolved'] == '1' && $this->GetProperty('isfirstcontactresolved') == '0') {
            $this->UpdatePool('isfirstcontactresolved', '1');
        }

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-3020 Survey Email arrives first followed by ticket reply.
         *
         * Comments: Code placement changes.
         */
        // Do we have to trigger a survey?
        if ($_ticketStatusContainer['triggersurvey'] == '1' && !$_suppressSurveyEmail) {
            $this->Load->Library('Ticket:TicketEmailDispatch', array($this), true, false, 'tickets');
            $this->TicketEmailDispatch->DispatchSurvey();
        }

        return true;
    }

    /**
     * Set the Ticket Priority
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetPriority($_ticketPriorityID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketPriorityID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        if (!isset($_ticketPriorityCache[$_ticketPriorityID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketPriorityContainer = $_ticketPriorityCache[$_ticketPriorityID];

        // If the current ticket priority id matches then bail out
        if ($this->GetProperty('priorityid') == $_ticketPriorityID) {
            return true;
        }

        // Create Audit Log
        $_oldPriorityTitle = $_newPriorityTitle = '';
        if (isset($_ticketPriorityCache[$this->GetProperty('priorityid')])) {
            $_oldPriorityTitle = $_ticketPriorityCache[$this->GetProperty('priorityid')]['title'];
        }

        $_newPriorityTitle = $_ticketPriorityCache[$_ticketPriorityID]['title'];

        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATEPRIORITY,
            sprintf($_SWIFT->Language->Get('al_priority'), $_oldPriorityTitle, $_newPriorityTitle),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('priorityid'),
            '',
            $_ticketPriorityID,
            '',
	        ['al_priority', $_oldPriorityTitle, $_newPriorityTitle]
        );

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_TICKETPRIORITY, $this->GetProperty('priorityid'), $_ticketPriorityID);

        // Notification Update
        $_oldNotificationPriorityTitle = $_newNotificationPriorityTitle = '';
        if ($_oldPriorityTitle == '') {
            $_oldNotificationPriorityTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationPriorityTitle = $_oldPriorityTitle;
        }

        $_newNotificationPriorityTitle = $_newPriorityTitle;

        $_oldUserNotificationPriorityTitle = $_oldNotificationPriorityTitle;
        $_newUserNotificationPriorityTitle = $_newNotificationPriorityTitle;

        /*
         * BUG FIX - Gurpreet Singh
         *
         * SWIFT-3416 Undefined offset error when using mass action in ticket view
         */
        if (isset($_ticketPriorityCache[$this->GetProperty('priorityid')]['type']) && $_ticketPriorityCache[$this->GetProperty('priorityid')]['type'] == SWIFT_PRIVATE) {
            $_oldUserNotificationPriorityTitle = $this->Language->Get('private');
        }

        if ($_ticketPriorityCache[$_ticketPriorityID]['type'] == SWIFT_PRIVATE) {
            $_newUserNotificationPriorityTitle = $this->Language->Get('private');
        }

        $this->Notification->Update($this->Language->Get('notification_priority'), $_oldNotificationPriorityTitle, $_newNotificationPriorityTitle, $_oldUserNotificationPriorityTitle, $_newUserNotificationPriorityTitle);

        $this->UpdatePool('priorityid', $_ticketPriorityID);
        $this->UpdatePool('prioritytitle', $_newPriorityTitle);

        //        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
        SWIFT_TicketManager::Recount(false);

        $this->QueueSLAOverdue(false, false);

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Ticket Owner
     *
     * @author Varun Shoor
     * @param int $_staffID The Owner Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetOwner($_staffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        // If the current owner matches the one provided then bail out
        if ($this->GetProperty('ownerstaffid') == $_staffID) {
            return true;
        }

        $_staffCache = $this->Cache->Get('staffcache');
        if ($_staffID != 0 && !isset($_staffCache[$_staffID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        // Create Audit Log
        $_oldOwnerTitle = $_newOwnerTitle = '';
        if (isset($_staffCache[$this->GetProperty('ownerstaffid')])) {
            $_oldOwnerTitle = $_staffCache[$this->GetProperty('ownerstaffid')]['fullname'];
        }

        if ($this->GetProperty('ownerstaffid') == '0') {
            $_oldOwnerTitle = $this->Language->Get('unassigned');
        }

        if ($_staffID == '0') {
            $_newOwnerTitle = $this->Language->Get('unassigned');
        } else {
            $_newOwnerTitle = $_staffCache[$_staffID]['fullname'];
        }

        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATEOWNER,
            sprintf($_SWIFT->Language->Get('al_owner'), $_oldOwnerTitle, $_newOwnerTitle),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('ownerstaffid'),
            '',
            $_staffID,
            '',
	        ['al_owner', $_oldOwnerTitle, $_newOwnerTitle]
        );

        // Notification Event
        $this->NotificationManager->SetEvent('ticketassigned');

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_OWNER, $this->GetProperty('ownerstaffid'), $_staffID);

        // Notification Update
        $_oldNotificationOwnerTitle = $_newNotificationOwnerTitle = '';
        if ($_oldOwnerTitle == '') {
            $_oldNotificationOwnerTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationOwnerTitle = $_oldOwnerTitle;
        }

        $_newNotificationOwnerTitle = $_newOwnerTitle;

        $this->Notification->Update($this->Language->Get('notification_staff'), $_oldNotificationOwnerTitle, $_newNotificationOwnerTitle);


        $this->UpdatePool('ownerstaffid', $_staffID);

        $_staffName = '';
        if (isset($_staffCache[$_staffID])) {
            $_staffName = $_staffCache[$_staffID]['fullname'];
        }

        $this->UpdatePool('ownerstaffname', $_staffName);

        // How many owners did it take to resolve this ticket?
        $this->UpdatePool('resolutionlevel', ($this->GetProperty('resolutionlevel')) + 1);

        //        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
        SWIFT_TicketManager::Recount(false);

        $this->QueueSLAOverdue(false, false);

        // Load the last notification message for ticket assigned alert
        $this->LoadLastPostNotificationMessage();

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Due Time
     *
     * @author Varun Shoor
     * @param string $_dueDateline The Due Dateline
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function SetDue($_dueDateline)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_dueDateline)) {
            return false;
        }

        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            sprintf($_SWIFT->Language->Get('al_due'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_dueDateline)),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('duetime'),
            '',
            $_dueDateline,
            '',
	        ['al_due',  SWIFT_TicketAuditLog::DATETIME_PARAM.$_dueDateline]
        );

        // Notification Update
        $_oldNotificationDueTitle = $_newNotificationDueTitle = '';
        if ($this->GetProperty('duetime') == '0') {
            $_oldNotificationDueTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationDueTitle = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->GetProperty('duetime'));
        }

        $_newNotificationDueTitle = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_dueDateline);

        $this->Notification->Update($this->Language->Get('notification_due'), $_oldNotificationDueTitle, $_newNotificationDueTitle);

        // Prevent calculation of SLA due time on this ticket
        $this->_noSLACalculation = true;

        $this->UpdatePool('duetime', ($_dueDateline));

        return true;
    }

    /**
     * Set the Resolution Due Time
     *
     * @author Varun Shoor
     * @param string $_dueDateline The Due Dateline
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function SetResolutionDue($_dueDateline)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_dueDateline)) {
            return false;
        }

        SWIFT_TicketAuditLog::AddToLog(
            $this,
            null,
            SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            sprintf($_SWIFT->Language->Get('al_resolutiondue'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_dueDateline)),
            SWIFT_TicketAuditLog::VALUE_NONE,
            $this->GetProperty('duetime'),
            '',
            $_dueDateline,
            '',
	        ['al_resolutiondue',  SWIFT_TicketAuditLog::DATETIME_PARAM.$_dueDateline]
        );

        // Notification Update
        $_oldNotificationDueTitle = $_newNotificationDueTitle = '';
        if ($this->GetProperty('resolutionduedateline') == '0') {
            $_oldNotificationDueTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationDueTitle = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->GetProperty('resolutionduedateline'));
        }

        $_newNotificationDueTitle = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_dueDateline);

        $this->Notification->Update($this->Language->Get('notification_resolutiondue'), $_oldNotificationDueTitle, $_newNotificationDueTitle);

        // Prevent calculation of SLA due time on this ticket
        $this->_noSLACalculation = true;

        $this->UpdatePool('resolutionduedateline', ($_dueDateline));

        return true;
    }

    /**
     * Mark ticket as linked
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkAsLinked()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('islinked', '1');

        return true;
    }

    /**
     * Mark ticket as unlinked
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkAsUnlinked()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('islinked', '0');

        return true;
    }

    /**
     * Lock a Ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Lock(SWIFT_Staff $_SWIFT_StaffObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        SWIFT_TicketLock::Create($this, $_SWIFT_StaffObject);

        return true;
    }

    /**
     * Unlock the ticket completely
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function Unlock()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_TicketLock::DeleteOnTicket(array($this->GetTicketID()));

        return true;
    }

    /**
     * Rebuild the Ticket Properties (hasnotes, hasattachments etc.)
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function RebuildProperties()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache = $this->Cache->Get('statuscache');

        /**
         * KAYAKOC-31541 : The KQL report shows incorrect "Resolved Date"(a recent date) for old tickets that was resolved/closed year/years back.
         * Description   : UpdatePool function updates the lastactivity value in the first execution, so lastactivity should be the first.
         *
         */
        if ($this->GetProperty('lastactivity') != '0') {
            $this->UpdatePool('lastactivity', $this->GetProperty('lastactivity'));
        }

        /**
         * BUG FIX - Parminder Singh
         *
         * SWIFT-1894: 'Trash' count does not clear the ticket count in case a department is deleted
         *
         */

        $_departmentCache = $this->Cache->Get('departmentcache');

        if ($this->GetProperty('trasholddepartmentid') != '0' && !isset($_departmentCache[$this->GetProperty('trasholddepartmentid')])) {
            $this->UpdatePool('trasholddepartmentid', '0');
        }

        // First Post, Last Post & Total Replies
        $_ticketPostIDContainer = $this->Database->QueryFetch("SELECT MIN(ticketpostid) AS firstpostid, MAX(ticketpostid) AS lastpostid,
            COUNT(*) AS totalreplies FROM " . TABLE_PREFIX . "ticketposts WHERE ticketid = '" . ($this->GetTicketID()) . "'");
        if (isset($_ticketPostIDContainer['firstpostid']) && !empty($_ticketPostIDContainer['firstpostid'])) {
            $this->UpdatePool('firstpostid', ($_ticketPostIDContainer['firstpostid']));
        }

        if (isset($_ticketPostIDContainer['lastpostid']) && !empty($_ticketPostIDContainer['lastpostid'])) {
            $this->UpdatePool('lastpostid', ($_ticketPostIDContainer['lastpostid']));

            $_lastTicketPostContainer = $this->Database->QueryFetch("SELECT fullname, dateline FROM " . TABLE_PREFIX . "ticketposts WHERE
                ticketpostid = '" . ($_ticketPostIDContainer['lastpostid']) . "'");

            if (isset($_lastTicketPostContainer['fullname']) && !empty($_lastTicketPostContainer['fullname'])) {
                $this->UpdatePool('lastreplier', $_lastTicketPostContainer['fullname']);
            }

            if (isset($_lastTicketPostContainer['dateline']) && $_lastTicketPostContainer['dateline'] > $this->GetProperty('lastactivity')) {
                $this->UpdatePool('lastactivity', ($_lastTicketPostContainer['dateline']));
            }
        }

        if (isset($_ticketPostIDContainer['totalreplies'])) {
            // We deduct by 1 to ignore the original ticket post as a reply
            if ($_ticketPostIDContainer['totalreplies'] > 0) {
                $_ticketPostIDContainer['totalreplies']--;
            }

            $this->UpdatePool('totalreplies', ($_ticketPostIDContainer['totalreplies']));
        }

        // Has Notes?
        $_hasNotesContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalnotes FROM " . TABLE_PREFIX . "ticketnotes WHERE
            linktypeid = '" . ($this->GetTicketID()) . "' AND linktype = '" . SWIFT_TicketNoteManager::LINKTYPE_TICKET . "'");

        $this->UpdatePool('hasnotes', IIF($_hasNotesContainer['totalnotes'] > 0, '1', '0'));


        /**
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-2576: 'hasattachments' field is not getting saved if ticket is having attachments
         *
         * Comments: Update hasattachments for ticket posts also
         */

        $_ticketPostIDList = array();

        // Has Attachments?
        $this->Database->Query("SELECT linktypeid AS ticketpostid FROM " . TABLE_PREFIX . "attachments WHERE ticketid = '" . ($this->GetTicketID()) . "'");
        while ($this->Database->NextRecord()) {
            $_ticketPostIDList[] = $this->Database->Record['ticketpostid'];
        }

        $this->UpdatePool('hasattachments', IIF(count($_ticketPostIDList) > 0, '1', '0'));

        if (_is_array($_ticketPostIDList)) {

            // First, set hasattachment to '0' for this ticket ID
            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketposts', array('hasattachments' => '0'), 'UPDATE', "ticketid = '" . ($this->GetTicketID()) . "'");
            // Now, set hasattachment to '1' based on ticket post ID list
            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketposts', array('hasattachments' => '1'), 'UPDATE', "ticketpostid IN (" . BuildIN($_ticketPostIDList) . ")");
        }

        // Has Draft?
        $_hasDraftContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketdrafts WHERE
            ticketid = '" . ($this->GetTicketID()) . "'");

        $this->UpdatePool('hasdraft', IIF($_hasDraftContainer['totalitems'] > 0, '1', '0'));

        // Has Billing Entries?
        $_hasBillingContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickettimetracks WHERE
            ticketid = '" . ($this->GetTicketID()) . "'");

        $this->UpdatePool('hasbilling', IIF($_hasBillingContainer['totalitems'] > 0, '1', '0'));

        // Has Follow-Up's?
        $_hasFollowUpContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketfollowups WHERE
            ticketid = '" . ($this->GetTicketID()) . "'");

        $this->UpdatePool('hasfollowup', IIF($_hasFollowUpContainer['totalitems'] > 0, '1', '0'));
        $this->UpdatePool('followupcount', ($_hasFollowUpContainer['totalitems']));

        // Update time worked + time billed
        $_ticketTimeWorked = $_ticketTimeBillable = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettimetracks WHERE ticketid = '" . ($this->GetTicketID()) . "'");
        while ($this->Database->NextRecord()) {
            $_ticketTimeWorked += ($this->Database->Record['timespent']);
            $_ticketTimeBillable += ($this->Database->Record['timebillable']);
        }

        $this->UpdateTimeTrack($_ticketTimeWorked, $_ticketTimeBillable);

        // Is Resolved
        if (isset($_ticketStatusCache[$this->GetProperty('ticketstatusid')])) {
            if ($_ticketStatusCache[$this->GetProperty('ticketstatusid')]['markasresolved'] == '1') {
                $this->UpdatePool('isresolved', '1');
                $this->UpdatePool('repliestoresolution', $this->GetProperty('totalreplies'));
            } else {
                $this->UpdatePool('isresolved', '0');
            }
        }

        // Rebuild the titles & names
        $this->RebuildTitles();

        // Calculate the additional properties
        $this->CalculateProperties();

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Change the ticket's hasattachments property to true
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkHasAttachments()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('hasattachments', '1');

        return true;
    }

    /**
     * Change the ticket's hasratings property to true
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkHasRatings()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('hasratings', '1');

        return true;
    }

    /**
     * Change the ticket's hasfollowup property to true
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkHasFollowUp()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('hasfollowup', '1');

        return true;
    }

    /**
     * Change the ticket's hasdraft property to true
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkHasDraft()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('hasdraft', '1');

        return true;
    }

    /**
     * Change the ticket's hasdraft property to false
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function ClearHasDraft()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('hasdraft', '0');

        return true;
    }

    /**
     * Change the ticket's hasnotes property to true
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkHasNotes()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('hasnotes', '1');

        return true;
    }

    /**
     * Change the ticket's hasbilling property to true
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function MarkHasBilling()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('hasbilling', '1');

        return true;
    }

    /**
     * Set the Message ID for this Ticket
     *
     * @author Varun Shoor
     * @param string $_messageID The Message ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetMessageID($_messageID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_messageID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('messageid', $_messageID);

        return true;
    }

    /**
     * Check to see if the given staff/user can access the ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Base $_SWIFT_BaseObject The SWIFT_Base Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CanAccess($_SWIFT_BaseObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }
        $perfLog = new SWIFT_Perf_Log();
        $startTime = time();
        if ((!$_SWIFT_BaseObject instanceof SWIFT_Staff || !$_SWIFT_BaseObject->GetIsClassLoaded()) &&
            (!$_SWIFT_BaseObject instanceof SWIFT_User || !$_SWIFT_BaseObject->GetIsClassLoaded())
        ) {
            return false;
        }

        // Staff Check
        if ($_SWIFT_BaseObject instanceof SWIFT_Staff) {
            $_assignedDepartmentIDList = $_SWIFT_BaseObject->GetAssignedDepartments(APP_TICKETS);
            $perfLog->addLog("CanAccess.1.GetAssignedDepartments", $startTime, time());
            if (in_array($this->GetProperty('departmentid'), $_assignedDepartmentIDList)) {
                return true;
            }

            if (
                $this->GetProperty('departmentid') == '0' &&
                ($this->GetProperty('trasholddepartmentid') == '0' ||
                    in_array($this->GetProperty('trasholddepartmentid'), $_assignedDepartmentIDList))
            ) {
                return true;
            }

            // User Check
        } else if ($_SWIFT_BaseObject instanceof SWIFT_User) {
            $_userID = $_SWIFT_BaseObject->GetUserID();
            $_userEmailList = $_SWIFT_BaseObject->GetEmailList();

            $_userIDList = [$_userID];

            // get organizations and for each one of type shared, get the user list
            $_links = SWIFT_UserOrganizationLink::RetrieveOnUser($_userID);
            $perfLog->addLog("CanAccess.2.RetrieveOnUser", $startTime, time());
            foreach ($_links as $link) {
                $uid = $link['userorganizationid'];
                $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::GetOnID($uid);
                if ($_SWIFT_UserOrganizationObject->GetProperty('organizationtype') == SWIFT_UserOrganization::TYPE_SHARED || $_SWIFT_BaseObject->GetProperty('userrole') == SWIFT_User::ROLE_MANAGER) {
                    $_userIDList_Organization = [];
                    $this->Database->Query("SELECT userid FROM " . TABLE_PREFIX . "userorganizationlinks WHERE userorganizationid = '" . $uid . "'");
                    while ($this->Database->NextRecord()) {
                        $_userIDList_Organization[] = $this->Database->Record['userid'];

                        $_userIDList[] = $this->Database->Record['userid'];
                    }

                    $_userEmailList = array_merge($_userEmailList, SWIFT_UserEmail::RetrieveListOnUserIDListOnSharedOrg($_userIDList_Organization));
                }
                $perfLog->addLog("CanAccess.3.EmailList", $startTime, time(), 'userID='.$_userID.', $uid='.$uid);
            }
            $perfLog->addLog("CanAccess.4.EmailList", $startTime, time(), 'userID='.$_userID);
            if ($this->GetProperty('userid') != '0' && in_array($this->GetProperty('userid'), $_userIDList)) {
                return true;
            }

            if (in_array(mb_strtolower($this->GetProperty('email')), $_userEmailList)) {
                return true;
            }

            if ($this->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF && in_array(mb_strtolower($this->GetProperty('replyto')), $_userEmailList)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recalculate the Ticket Link Property
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public static function RecalculateTicketLinkProperty($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_ticketContainer = $_ticketLinkStatus = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        // Calculate the link chains
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinkchains WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketLinkStatus[] = $_SWIFT->Database->Record['ticketid'];
        }

        foreach ($_ticketContainer as $_ticketID => $_SWIFT_TicketObject) {
            if (in_array($_ticketID, $_ticketLinkStatus)) {
                $_SWIFT_TicketObject->MarkAsLinked();
            } else {
                $_SWIFT_TicketObject->MarkAsUnlinked();
            }
        }

        return true;
    }

    /**
     * Train the given tickets
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public static function TrainBayesList($_ticketIDList, $_bayesCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        } else if (empty($_bayesCategoryID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_bayesianCategoryCache = $_SWIFT->Cache->Get('bayesiancategorycache');

        $_SWIFT_BayesianObject = new SWIFT_Bayesian();
        if (!$_SWIFT_BayesianObject instanceof SWIFT_Bayesian || !$_SWIFT_BayesianObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset($_bayesianCategoryCache[$_bayesCategoryID])) {
            throw new SWIFT_Ticket_Exception('Bayesian Category does not exist');
        }

        $_ticketObjectContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketObjectContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        foreach ($_ticketObjectContainer as $_ticketID => $_SWIFT_TicketObject) {
            $_ticketPostContainer = $_SWIFT_TicketObject->GetTicketPosts(0, false, 'ASC', SWIFT_Ticket::CREATOR_CLIENT);
            if (!_is_array($_ticketPostContainer)) {
                continue;
            }

            $_finalTicketPostText = $_SWIFT_TicketObject->GetProperty('subject');
            foreach ($_ticketPostContainer as $_ticketPostID => $_SWIFT_TicketPostObject) {
                $_finalTicketPostText .= $_SWIFT_TicketPostObject->GetProperty('contents') . SWIFT_CRLF;
            }

            $_SWIFT_BayesianObject->Train($_ticketID, $_bayesCategoryID, $_finalTicketPostText);
        }

        return true;
    }

    /**
     * Train the Bayesian
     *
     * @author Varun Shoor
     * @param int $_bayesCategoryID The Bayesian Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function TrainBayes($_bayesCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_bayesCategoryID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        self::TrainBayesList(array($this->GetTicketID()), $_bayesCategoryID);

        return true;
    }

    /**
     * Mark the given ticket ids as spam
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public static function MarkAsSpamList($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_bayesianCategoryCache = $_SWIFT->Cache->Get('bayesiancategorycache');

        $_SWIFT_BayesianObject = new SWIFT_Bayesian();
        if (!$_SWIFT_BayesianObject instanceof SWIFT_Bayesian || !$_SWIFT_BayesianObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_bayesCategoryID = false;
        foreach ($_bayesianCategoryCache as $_bayesianCategoryID => $_bayesianCategoryContainer) {
            if ($_bayesianCategoryContainer['categorytype'] == SWIFT_BayesianCategory::CATEGORY_SPAM) {
                $_bayesCategoryID = $_bayesianCategoryContainer['bayescategoryid'];

                break;
            }
        }

        if (!$_bayesCategoryID) {
            throw new SWIFT_Ticket_Exception('Spam Bayesian Category not found');
        }

        $_ticketObjectContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketObjectContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        foreach ($_ticketObjectContainer as $_ticketID => $_SWIFT_TicketObject) {
            $_ticketPostContainer = $_SWIFT_TicketObject->GetTicketPosts(0, false, 'ASC', SWIFT_Ticket::CREATOR_CLIENT);
            if (!_is_array($_ticketPostContainer)) {
                continue;
            }

            $_finalTicketPostText = $_SWIFT_TicketObject->GetProperty('subject');
            foreach ($_ticketPostContainer as $_ticketPostID => $_SWIFT_TicketPostObject) {
                $_finalTicketPostText .= $_SWIFT_TicketPostObject->GetProperty('contents') . SWIFT_CRLF;
            }

            $_SWIFT_BayesianObject->Train($_ticketID, $_bayesCategoryID, $_finalTicketPostText);
        }

        if ($_SWIFT->Settings->Get('t_spammovetotrash') == '1') {
            self::TrashList($_ticketIDList);
        }

        if ($_SWIFT->Settings->Get('t_spamban') == '1') {
            self::BanList($_ticketIDList, $_SWIFT->Staff->GetStaffID());
        }

        return true;
    }

    /**
     * Watch a ticket list
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public static function Watch($_ticketIDList, SWIFT_Staff $_SWIFT_StaffObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_ticketObjectContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketObjectContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        if (!count($_ticketObjectContainer)) {
            return false;
        }

        foreach ($_ticketObjectContainer as $_ticketID => $_SWIFT_TicketObject) {
            // Create Audit Log
            SWIFT_TicketAuditLog::AddToLog(
                $_SWIFT_TicketObject,
                null,
                SWIFT_TicketAuditLog::ACTION_WATCH,
                sprintf($_SWIFT->Language->Get('al_watch'), $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT_StaffObject->GetProperty('fullname')),
                SWIFT_TicketAuditLog::VALUE_NONE,
                0,
                '',
                0,
                '',
	            ['al_watch', $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT_StaffObject->GetProperty('fullname')]
            );

            SWIFT_TicketWatcher::Create($_SWIFT_TicketObject, $_SWIFT_StaffObject);
        }

        return true;
    }

    /**
     * Unwatch a ticket list
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public static function UnWatch($_ticketIDList, SWIFT_Staff $_SWIFT_StaffObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_ticketIDList)) {
            return false;
        }

        SWIFT_TicketWatcher::DeleteOnTicket($_ticketIDList, array($_SWIFT_StaffObject->GetStaffID()));

        return true;
    }

    /**
     * Add the object to the active workflow queue
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public static function AddToWorkflowQueue(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        } else if (isset(self::$_workflowQueue[$_SWIFT_TicketObject->GetTicketID()])) {
            return true;
        }

        self::$_workflowQueue[$_SWIFT_TicketObject->GetTicketID()] = $_SWIFT_TicketObject;

        if (self::$_isWorkflowQueueActive) {
            return true;
        }

        self::$_isWorkflowQueueActive = true;

        SWIFT::Shutdown('Tickets\Models\Ticket\SWIFT_Ticket', 'ProcessWorkflowQueue', 9, false);

        return true;
    }

    /**
     * Retrieve the history for this ticket
     *
     * @author Varun Shoor
     * @return array The History Container
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function RetrieveHistory()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_historyContainer = array();

        /** BUG FIX : Parminder Singh <parminder.singh@kayako.com>
         *
         * SWIFT-2041 : Wrong count under History tab, when ticket is created from Staff CP using 'Send Mail' option
         */
        $_extendedSQL = "email = '" . $this->Database->Escape($this->GetProperty('email')) . "'";
        if ($this->GetProperty('replyto') != '') {
            $_extendedSQL = "replyto = '" . $this->Database->Escape($this->GetProperty('replyto')) . "'";
        }

        if ($this->GetProperty('userid') != '0') {
            $_extendedSQL .= " OR userid = '" . ($this->GetProperty('userid')) . "'";
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets
                                 WHERE " . $_extendedSQL . "
                                 ORDER BY dateline DESC");
        while ($this->Database->NextRecord()) {
            $_historyContainer[$this->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($this->Database->Record));
        }

        return $_historyContainer;
    }

    /**
     * Load the Core Language Table
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function LoadLanguageTable()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Language->Load('tickets_notifications', SWIFT_LanguageEngine::TYPE_FILE);
        $_SWIFT->Language->Load('tickets_auditlogs', SWIFT_LanguageEngine::TYPE_FILE);

        return true;
    }

    /**
     * Dispatch the auto responder msg
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function DispatchAutoresponder()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        // We dispatch the auto responder to ALL registered emails of the user
        $_ccEmailList = $this->GetCCUserEmails();

        $this->Load->Library('Ticket:TicketEmailDispatch', array($this), true, false, 'tickets');
        $this->TicketEmailDispatch->DispatchAutoresponder('', $_ccEmailList);

        return true;
    }

    /**
     * Create a Note
     *
     * @author Varun Shoor
     * @param SWIFT_User|mixed $_SWIFT_UserObject (OPTIONAL) The User Object
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor The Note Color
     * @param string $_noteType The Note Type
     * @param int $_forStaffID (OPTIONAL) Restrict view to a given staff
     * @param SWIFT_Staff|bool $_SWIFT_StaffObject (OPTIONAL) The Staff Object to create note as
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CreateNote($_SWIFT_UserObject, $_noteContents, $_noteColor, $_noteType, $_forStaffID = 0, $_SWIFT_StaffObject = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_isOrganizationNote = false;
        $_SWIFT_UserOrganizationObject = $this->GetUserOrganizationObject();

        if (
            $_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded() &&
            $_noteType == 'userorganization'
        ) {
            $_isOrganizationNote = true;
        }

        // Add notes
        if (!empty($_noteContents)) {
            if ($_isOrganizationNote) {
                /**
                 * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
                 *
                 * SWIFT-2103 - Adding User Note via Ticket Followup results [User Error] : Invalid data provided
                 */
                SWIFT_UserOrganizationNote::Create($_SWIFT_UserOrganizationObject, $_noteContents, ($_noteColor), $_SWIFT_StaffObject);

                SWIFT_TicketAuditLog::AddToLog(
                    $this,
                    null,
                    SWIFT_TicketAuditLog::ACTION_NEWNOTE,
                    $_SWIFT->Language->Get('al_usernote'),
                    SWIFT_TicketAuditLog::VALUE_NONE,
                    0,
                    '',
                    0,
                    '',
	                ['al_usernote']
                );
            } else if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()  && $_noteType == 'user') {
                SWIFT_UserNote::Create($_SWIFT_UserObject, $_noteContents, ($_noteColor), $_SWIFT_StaffObject);

                SWIFT_TicketAuditLog::AddToLog(
                    $this,
                    null,
                    SWIFT_TicketAuditLog::ACTION_NEWNOTE,
                    $_SWIFT->Language->Get('al_usernote'),
                    SWIFT_TicketAuditLog::VALUE_NONE,
                    0,
                    '',
                    0,
                    '',
	                ['al_usernote']
                );
            } else {

                $_staffID = 0;
                $_staffName = $this->Language->Get('system');
                if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
                    $_staffID = $_SWIFT_StaffObject->GetStaffID();
                    $_staffName = $_SWIFT_StaffObject->GetProperty('fullname');
                } else if ($_SWIFT->Staff instanceof SWIFT_Staff && $_SWIFT->Staff->GetIsClassLoaded()) {
                    $_staffID = $_SWIFT->Staff->GetStaffID();
                    $_staffName = $_SWIFT->Staff->GetProperty('fullname');
                }

                SWIFT_TicketNote::Create($this, $_forStaffID, $_staffID, $_staffName, $_noteContents, ($_noteColor));

                SWIFT_TicketAuditLog::AddToLog($this, null,
	                SWIFT_TicketAuditLog::ACTION_NEWNOTE, $_SWIFT->Language->Get('al_ticketnote'),
	                SWIFT_TicketAuditLog::VALUE_NONE,
	                0, '', 0, '', ['al_ticketnote']);
            }
            /**
             * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
             *
             * SWIFT-4391 'Clear reply due deadline when adding a ticket note' option is not working.
             *
             */
            if ($this->Settings->Get('t_ticketnoteresetsupdatetime') == 1 && $_noteType == 'ticket') {
                $this->ClearOverdue();
                $this->UpdatePool('lastactivity', DATENOW);

                /*
                 * BUG FIX - Ravi Sharma
                 *
                 * SWIFT-2672: Resetting due time doesn't reset overdue color on tickets
                 *
                 * Comments: None
                 */
                $this->QueueSLAOverdue();
            }
        }

        return true;
    }

    /**
     * Retrieve the history for this user
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @param array $_emailList The Email List
     * @param string $_sortBy (OPTIONAL) The Custom Sort By
     * @param string $_sortOrder (OPTIONAL) The Custom Sort Order
     * @return array The History Container
     * @throws SWIFT_Exception
     */
    public static function RetrieveHistoryOnUser($_SWIFT_UserObject, $_emailList = array(), $_sortBy = null, $_sortOrder = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userID = '-1';
        $_userEmailList = $_emailList;
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_userID = $_SWIFT_UserObject->GetUserID();
            $_userEmailList = array_merge($_userEmailList, $_SWIFT_UserObject->GetEmailList());
        }

        $_historyContainer = array();

        $_ticketSortBy = 'dateline';
        $_ticketSortOrder = 'DESC';
        if (!empty($_sortBy) && !empty($_sortOrder)) {
            $_ticketSortBy = $_sortBy;
            $_ticketSortOrder = $_sortOrder;
        }
        /**
         * BUG FIX - Saloni Dhall
         *
         * SWIFT-3069: Wrong ticket count and the tickets coming after clicking on 'Tickets Tab' under 'Users Tab' in staff CP shows incorrect count and tickets.
         */
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE userid = '" . $_userID .
            "' OR email IN (" . BuildIN($_userEmailList) . ") OR replyto IN (" . BuildIN($_userEmailList) . ") ORDER BY " . $_ticketSortBy . " " . $_ticketSortOrder);
        while ($_SWIFT->Database->NextRecord()) {
            $_historyContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }
        return $_historyContainer;
    }

    /**
     * Retrieve the Support Center Tickets for this user
     *
     * @author Varun Shoor
     *
     * @param SWIFT_User $_User
     * @param bool $_sortBy (OPTIONAL) The Custom Sort By
     * @param bool $_sortOrder (OPTIONAL) The Custom Sort Order
     * @param int $_offset (OPTIONAL) The Starting Offset
     * @param bool $_limit (OPTIONAL) The Number of Results to Return
     * @param bool $_excludeResolved
     * @param int $_lastTicketID
     *
     * @return array The History Container
     * @throws SWIFT_Exception
     */
    public static function RetrieveSCTicketsOnUser(SWIFT_User $_User, $_sortBy = false, $_sortOrder = false, $_offset = 0, $_limit = false, $_excludeResolved = false, $_lastTicketID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userID        = $_User->GetID();
        $_userEmailList = $_User->GetEmailList();

        $_historyContainer = array();

        $_ticketSortBy    = 'dateline';
        $_ticketSortOrder = 'DESC';
        if (!empty($_sortBy) && !empty($_sortOrder)) {
            $_ticketSortBy    = $_sortBy;
            $_ticketSortOrder = $_sortOrder;
        }

        $_userIDList = array($_userID);

        // get organizations and for each one of type shared, get the user list
        $_links = SWIFT_UserOrganizationLink::RetrieveOnUser($_userID);
        foreach ($_links as $link) {
            $uid = $link['userorganizationid'];
            try {
                $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::GetOnID($uid);
                if ($_SWIFT_UserOrganizationObject->GetProperty('organizationtype') == SWIFT_UserOrganization::TYPE_SHARED || $_User->GetProperty('userrole') == SWIFT_User::ROLE_MANAGER) {
                    $_userIDList_Organization = [];
                    $_SWIFT->Database->Query("SELECT userid FROM " . TABLE_PREFIX . "userorganizationlinks WHERE userorganizationid = '" . $uid . "'");
                    while ($_SWIFT->Database->NextRecord()) {
                        $_userIDList_Organization[] = $_SWIFT->Database->Record['userid'];

                        $_userIDList[] = $_SWIFT->Database->Record['userid'];
                    }

                    $_userEmailList = array_merge(
                        $_userEmailList,
                        SWIFT_UserEmail::RetrieveListOnUserIDListOnSharedOrg($_userIDList_Organization)
                    );
                }
            } catch (\Exception $ex) {
                // it does not exist
            }
        }

        // Does this user have a shared organization or he is a manager in an organization?
        $_UserOrganization = $_User->GetOrganization();

        $isloaded = $_UserOrganization instanceof SWIFT_UserOrganization && $_UserOrganization->GetIsClassLoaded();
        if ($isloaded && ($_User->Get('userrole') == SWIFT_User::ROLE_MANAGER ||
            $_UserOrganization->Get('organizationtype') == SWIFT_UserOrganization::TYPE_SHARED)) {
            $_userIDList_Organization = array();
            $_SWIFT->Database->Query("SELECT userid FROM " . TABLE_PREFIX . "users
                                      WHERE userorganizationid = '" . $_UserOrganization->GetUserOrganizationID() . "'");
            while ($_SWIFT->Database->NextRecord()) {
                $_userIDList_Organization[] = $_SWIFT->Database->Record['userid'];
                $_userIDList[]              = $_SWIFT->Database->Record['userid'];
            }

            $_userEmailList = array_unique(array_merge($_userEmailList, SWIFT_UserEmail::RetrieveListOnUserIDListOnSharedOrg($_userIDList_Organization)));
        }

        $_whereExtended  = '';
        $_whereExtended .=  IIF(!empty($_excludeResolved), " AND isresolved = 0" . $_lastTicketID);
        $_whereExtended .=  IIF(!empty($_lastTicketID),     " AND ticketid < "    . $_lastTicketID);

        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets as tickets
                                       WHERE tickets.userid IN (" . BuildIN(array_unique($_userIDList), true) . ")
                                         AND tickets.departmentid <> 0 " . $_whereExtended . "
                                       ORDER BY " . $_ticketSortBy . " " . $_ticketSortOrder, $_limit, $_offset);

        while ($_SWIFT->Database->NextRecord()) {
            $_historyContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets as tickets
                                       WHERE (tickets.email IN (" . BuildIN($_userEmailList) . ") OR tickets.replyto IN (" . BuildIN($_userEmailList) . "))
                                         AND tickets.departmentid <> 0" . $_whereExtended . "
                                       ORDER BY " . $_ticketSortBy . " " . $_ticketSortOrder, $_limit, $_offset);

        while ($_SWIFT->Database->NextRecord()) {
            if (isset($_historyContainer[$_SWIFT->Database->Record['ticketid']]) || ($_SWIFT->Database->Record['creator'] == SWIFT_Ticket::CREATOR_STAFF && !in_array(mb_strtolower($_SWIFT->Database->Record['replyto']), $_userEmailList))) {
                continue;
            }

            $_historyContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        return $_historyContainer;
    }

    /**
     * Retrieve the Support Center Tickets count for this user
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @param bool $_excludeResolved
     * @param bool $_resolvedOnly
     * @return int The History Count
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveSCTicketsCountOnUser($_SWIFT_UserObject, $_excludeResolved = false, $_resolvedOnly = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userID = $_SWIFT_UserObject->GetUserID();
        $_userEmailList = $_SWIFT_UserObject->GetEmailList();

        $_userIDList = array($_userID);

        // get organizations and for each one of type shared or if it is manager, get the user list
        $_links = SWIFT_UserOrganizationLink::RetrieveOnUser($_userID);
        foreach ($_links as $link) {
            $uid = $link['userorganizationid'];
            try {
                $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::GetOnID($uid);
                if ($_SWIFT_UserOrganizationObject->GetProperty('organizationtype') == SWIFT_UserOrganization::TYPE_SHARED || $_SWIFT_UserObject->GetProperty('userrole') == SWIFT_User::ROLE_MANAGER) {
                    $_userIDList_Organization = [];
                    $_SWIFT->Database->Query("SELECT userid FROM " . TABLE_PREFIX . "userorganizationlinks WHERE userorganizationid = '" . $uid . "'");
                    while ($_SWIFT->Database->NextRecord()) {
                        $_userIDList_Organization[] = $_SWIFT->Database->Record['userid'];

                        $_userIDList[] = $_SWIFT->Database->Record['userid'];
                    }

                    $_userEmailList = array_merge(
                        $_userEmailList,
                        SWIFT_UserEmail::RetrieveListOnUserIDListOnSharedOrg($_userIDList_Organization)
                    );
                }
            } catch (\Exception $ex) {
                // organization does not exist
            }
        }

        $_whereExtended = '';
        if ($_excludeResolved) {
            $_whereExtended = ' AND isresolved = "0"';
        }

        if ($_resolvedOnly) {
            $_whereExtended = ' AND isresolved = "1"';
        }
        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4462 Wrong ticket count at client support center.
         * SWIFT-5150 Incorrect tickets count on help center for shared organization.
         *
         * Comments: Ticket those created by staff was conflicting in counter (in case of shared organization).
         */
        $_totalTickets = 0;
        $_SWIFT->Database->Query("SELECT creator, replyto FROM " . TABLE_PREFIX . "tickets
                                   WHERE (userid IN (" . BuildIN($_userIDList) . ")
                                      OR email IN (" . BuildIN($_userEmailList) . ")
                                      OR replyto IN (" . BuildIN($_userEmailList) . "))
                                      AND departmentid <> '0' " . $_whereExtended);
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['creator'] == SWIFT_Ticket::CREATOR_STAFF && !in_array(mb_strtolower($_SWIFT->Database->Record['replyto']), $_userEmailList)) {
                continue;
            }

            $_totalTickets++;
        }

        return $_totalTickets;
    }

    /**
     * Set the Watcher Notification Message
     *
     * @author Varun Shoor
     * @param string $_watcherName The Watcher Name
     * @param string $_watcherMessage The Watcher Message
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetWatcherProperties($_watcherName, $_watcherMessage)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_watcherCustomName = $_watcherName;
        $this->_watchNotificationMessage[] = $_watcherMessage;

        return true;
    }

    /**
     * Process the Notification for Ticket Watchers
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessWatchers()
    {
        chdir(SWIFT_BASEPATH);
        $_ticketID = $this->GetTicketID();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset(self::$_watcherExecutedCache[$_ticketID]) && self::$_watcherExecutedCache[$_ticketID] == 1) {
            return true;
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_emailList = array();
        $_ticketWatchContainer = SWIFT_TicketWatcher::RetrieveOnTicket($this);
        if (_is_array($_ticketWatchContainer)) {
            foreach ($_ticketWatchContainer as $_ticketWatch) {
                $_staffID = $_ticketWatch['staffid'] ?? 0;

                if (!isset($_staffCache[$_staffID])) {
                    continue;
                }

                $_emailList[] = $_staffCache[$_staffID]['email'];
            }
        }

        if (!count($_emailList)) {
            return false;
        }

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-5283: "Incorrect contents sent in the new ticket note added notification"
         **/
        $_notificationMessage = '';
        if (is_array($this->_watchNotificationMessage)) {
            foreach ($this->_watchNotificationMessage as $_value => $_Message) {
                $_notificationMessage = $_Message;
            }
        }

        $this->Notification->Dispatch(SWIFT_TicketNotification::TYPE_CUSTOM, $_emailList, $this->GetProperty('subject'), $_notificationMessage, $this->_watcherCustomName, '', true);

        self::$_watcherExecutedCache[$_ticketID] = 1;

        return true;
    }

    /**
     * Queue the Watcher Execution
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QueueWatcherExecution()
    {
        $_ticketID = $this->GetTicketID();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset(self::$_watcherPendingCache[$_ticketID]) && self::$_watcherPendingCache[$_ticketID] == '1') {
            return true;
        }

        //        SWIFT::Shutdown($this, 'ProcessWatchers', -1, false);

        self::$_watcherPendingCache[$_ticketID] = 1;

        return true;
    }

    /**
     * Process the Notification Rules
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessNotifications()
    {
        chdir(SWIFT_BASEPATH);
        $_ticketID = $this->GetTicketID();
        /**
         * BUG FIX - Saloni Dhall
         *
         * SWIFT-3412: New Client Reply rule for notifications works for the first reply only
         *
         */
        $_ticketPostID = $this->GetProperty('lastpostid');

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset(self::$_notificationExecutionCache[$_ticketID][$_ticketPostID]) && self::$_notificationExecutionCache[$_ticketID][$_ticketPostID] == '1') {
            return true;
        }

        $this->NotificationManager->Trigger();

        self::$_notificationExecutionCache[$_ticketID][$_ticketPostID] = 1;

        return true;
    }

    /**
     * Dispatch a Notification via Email on this Ticket
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @param array $_customEmailList The Custom Email List
     * @param string $_emailPrefix (OPTIONAL) The Custom Email Prefix
     * @param string $_notificationEvent (OPTIONAL) The Notification Event
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchNotification($_notificationType, $_customEmailList, $_emailPrefix = '', $_notificationEvent = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalEmailPrefix = '';
        if (!empty($_emailPrefix)) {
            $_finalEmailPrefix = $_emailPrefix . ' - ';
        }

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4086: "Incorrect contents sent in the new ticket note added notification"
         **/
        $_notificationMessage = '';
        if (is_array($this->_watchNotificationMessage)) {
            foreach ($this->_watchNotificationMessage as $_key => $_Message) {
                $_GetNotificationEvent = $this->NotificationManager->GetEvent();
                /**
                 * BUG FIX - Werner Garcia <werner.garcia@crossover.com>
                 *
                 * KAYAKOC-2475: Incorrect notification contents for New staff
                 * reply notification criteria
                 **/
                if (!is_array($_GetNotificationEvent)) {
                    $_GetNotificationEvent = [$_GetNotificationEvent];
                }
                foreach ($_GetNotificationEvent as $item) {
                    if ($item === $_notificationEvent) {
                        $_notificationMessage = $_Message;
                    }
                }
            }
        }

        if (empty($_notificationMessage)) {
            $_notificationMessage = $this->_lastPostNotificationMessage;
        }

        $this->Notification->Dispatch($_notificationType, $_customEmailList, $_finalEmailPrefix . $this->GetProperty('subject'), $_notificationMessage, $this->_watcherCustomName, '', false, $_notificationEvent);

        return true;
    }

    /**
     * Dispatch a Notification via Pool (DESKTOP APP) on this Ticket
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @param array $_customStaffIDList The Custom Staff ID List
     * @param string $_notificationEvent (OPTIONAL) The Notification Event
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchNotificationPool($_notificationType, $_customStaffIDList, $_notificationEvent = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        //        $this->Notification->DispatchPool($_notificationType, $_customStaffIDList, $this->GetProperty('subject'), $this->_watchNotificationMessage, $this->_watcherCustomName);

        return true;
    }

    /**
     * Add to Attachments
     *
     * @author Varun Shoor
     * @param string $_fileName The File Name
     * @param string $_fileType The File Type
     * @param string $_fileContents The File Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddToAttachments($_fileName, $_fileType, $_fileContents)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset(self::$_attachmentsContainer[$this->GetTicketID()])) {
            self::$_attachmentsContainer[$this->GetTicketID()] = array();
        }

        self::$_attachmentsContainer[$this->GetTicketID()][] = array('name' => $_fileName, 'type' => $_fileType, 'contents' => $_fileContents);

        return true;
    }

    /**
     * Add to Active Attachments (for notifications)
     *
     * @author Varun Shoor
     * @param string $_fileName The File Name
     * @param string $_fileType The File Type
     * @param string $_fileContents The File Contents
     * @param string $_contentID The Content ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddToNotificationAttachments($_fileName, $_fileType, $_fileContents, $_contentID = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset(self::$_notificationAttachmentsContainer[$this->GetTicketID()])) {
            self::$_notificationAttachmentsContainer[$this->GetTicketID()] = array();
        }

        self::$_notificationAttachmentsContainer[$this->GetTicketID()][] = array('name' => $_fileName, 'type' => $_fileType, 'contents' => $_fileContents, 'contentid' => $_contentID);

        return true;
    }

    /**
     * Move the tickets to trash
     *
     * @author Varun Shoor
     * @param array $_departmentIDList The Department ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function MoveToTrashBulk($_departmentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_departmentIDList)) {
            return false;
        }

        /**
         * BUG FIX - Parminder Singh
         *
         * SWIFT-1894: 'Trash' count does not clear the ticket count in case a department is deleted
         *
         */

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('departmentid' => '0', 'trasholddepartmentid' => '0'), 'UPDATE', "departmentid IN (" . BuildIN($_departmentIDList) . ") OR trasholddepartmentid IN (" . BuildIN($_departmentIDList) . ")");

        return true;
    }

    /**
     * Move the tickets status to trash
     *
     * @author Varun Shoor
     * @param array $_ticketStatusIDList The Ticket Status ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ChangeStatusToTrash($_ticketStatusIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketStatusIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('ticketstatusid' => '0'), 'UPDATE', "ticketstatusid IN (" . BuildIN($_ticketStatusIDList) . ")");

        return true;
    }

    /**
     * Load the Last Post Notification Message
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadLastPostNotificationMessage()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        try {
            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($this->GetProperty('lastpostid')));



            $this->_lastPostNotificationMessage = $this->Emoji->decode($_SWIFT_TicketPostObject->GetProperty('contents'));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            return false;
        }

        return true;
    }

    /**
     * Mark the Tickets as Pending Auto Closure
     *
     * @author Varun Shoor
     * @param array $_ticketIDList
     * @param SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function MarkAsAutoClosePending($_ticketIDList, SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_AutoCloseRuleObject instanceof SWIFT_AutoCloseRule || !$_SWIFT_AutoCloseRuleObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array(
            'isautoclosed' => '0', 'autocloseruleid' => ($_SWIFT_AutoCloseRuleObject->GetAutoCloseRuleID()),
            'autoclosestatus' => self::AUTOCLOSESTATUS_PENDING, 'autoclosetimeline' => DATENOW
        ), 'UPDATE', "ticketid IN (" . BuildIN($_ticketIDList) . ")");

        // Do we need to send the pending notification?
        if ($_SWIFT_AutoCloseRuleObject->GetProperty('sendpendingnotification') == '0') {
            return true;
        }

        $_ticketsContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets
            WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketsContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        foreach ($_ticketsContainer as $_SWIFT_TicketObject) {
            if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
                continue;
            }

            $_SWIFT_TicketEmailDispatch = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);
            $_SWIFT_TicketEmailDispatch->DispatchPendingAutoClose($_SWIFT_AutoCloseRuleObject);
        }

        return true;
    }

    /**
     * Mark the Tickets as Closed via Auto Closed
     *
     * @author Varun Shoor
     * @param array $_ticketIDList
     * @param SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function MarkAsAutoClosed($_ticketIDList, SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_AutoCloseRuleObject instanceof SWIFT_AutoCloseRule || !$_SWIFT_AutoCloseRuleObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketsContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets
            WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketsContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array(
            'isautoclosed' => '1', 'autocloseruleid' => ($_SWIFT_AutoCloseRuleObject->GetAutoCloseRuleID()),
            'autoclosestatus' => self::AUTOCLOSESTATUS_CLOSED, 'autoclosetimeline' => DATENOW
        ), 'UPDATE', "ticketid IN (" . BuildIN($_ticketIDList) . ")");

        foreach ($_ticketsContainer as $_SWIFT_TicketObject) {
            if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
                continue;
            }

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-4911 Extra parameter added in the MarkAsAutoClosed(...) function
             */
            $_SWIFT_TicketObject->SetStatus($_SWIFT_AutoCloseRuleObject->GetProperty('targetticketstatusid'), true, $_SWIFT_AutoCloseRuleObject->GetProperty('suppresssurveyemail'));

            // Do we need to send the final notification?
            if ($_SWIFT_AutoCloseRuleObject->GetProperty('sendfinalnotification') == '1') {
                $_SWIFT_TicketEmailDispatch = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);
                $_SWIFT_TicketEmailDispatch->DispatchFinalAutoClose($_SWIFT_AutoCloseRuleObject);
            }

            $_SWIFT_TicketObject->RebuildProperties();
        }

        return true;
    }

    /**
     * Retrieve the from email with proper suffix
     *
     * @author Varun Shoor
     * @param string $_fromEmailAddress
     * @param mixed $_mailType
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RetrieveFromEmailWithSuffix($_fromEmailAddress, $_mailType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Have we enabled clean subjects?
        if ($this->Settings->Get('t_cleanmailsubjects') != '1') {
            return $_fromEmailAddress;
        }

        $_finalReturnEmailAddress = '';

        $_matches = array();
        if (preg_match('/^(.*?)@(.*?)$/i', $_fromEmailAddress, $_matches)) {
            $_dispatchType = 'r';
            if ($_mailType == self::MAIL_NOTIFICATION) {
                $_dispatchType = 'a';
            } else if ($_mailType == self::MAIL_THIRDPARTY) {
                $_dispatchType = 't';
            }

            $_hashChunk = substr($this->GetProperty('tickethash'), 0, 5);

            $_finalReturnEmailAddress = $_matches[1] . '+' . $_dispatchType . '.' . $_hashChunk . '.' . $this->GetTicketID() . '@' . $_matches[2];
        } else {
            return $_fromEmailAddress;
        }


        return $_finalReturnEmailAddress;
    }

    /**
     * Reset SLA Calculations
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ResetSLA()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_noSLACalculation = false;

        return true;
    }

    /**
     * Retrieve the tickets which are generated from this ticket
     *
     * @author Parminder Singh
     * @param int $_ticketID
     * @return array
     * @throws SWIFT_Exception
     */
    public static function RetrieveRecurrenceHistory($_ticketID)
    {
        if (empty($_ticketID)) {
            return [];
        }

        $_SWIFT = SWIFT::GetInstance();

        $_ticketIDList = $_recurrenceHistoryContainer = array();

        $_SWIFT->Database->Query('SELECT ticketid FROM ' . TABLE_PREFIX . self::TABLE_NAME . "
                                  WHERE recurrencefromticketid = '" . $_ticketID . "'
                                  ORDER BY dateline DESC");

        while ($_SWIFT->Database->NextRecord()) {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        if (!count($_ticketIDList)) {
            return array();
        }

        foreach ($_ticketIDList as $__ticketID) {
            $_recurrenceHistoryContainer[$__ticketID] = new SWIFT_Ticket(new SWIFT_DataID($__ticketID));
        }

        return $_recurrenceHistoryContainer;
    }

    /**
     * Set alert rules for ticket
     *
     * @author Ruchi Kothari
     * @param bool $_noAlerts Alert rules
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetNoAlerts($_noAlerts)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_noAlerts = $_noAlerts;
    }

    /**
     * Set Old Ticket properties. This array is used to Keep previous ticket properties after execution.
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function SetOldTicketProperties()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETSTATUS] = $this->GetProperty('ticketstatusid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETPRIORITY] = $this->GetProperty('priorityid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETDEPARTMENT] = $this->GetProperty('departmentid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETOWNER] = ($this->GetProperty('ownerstaffid'));
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETEMAILQUEUE] = $this->GetProperty('emailqueueid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETFLAGTYPE] = $this->GetProperty('flagtype');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETCREATOR] = $this->GetProperty('creator');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETUSERGROUP] = $this->GetUserGroupID();

        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETFULLNAME] = $this->GetProperty('fullname');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETEMAIL] = $this->GetProperty('email');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETLASTREPLIER] = $this->GetProperty('lastreplier');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETSUBJECT] = $this->GetProperty('subject');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETCHARSET] = $this->GetProperty('charset');

        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETTEMPLATEGROUP] = $this->GetProperty('tgroupid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETISRESOLVED] = $this->GetProperty('isresolved');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETTYPE] = ($this->GetProperty('tickettypeid'));
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETWASREOPENED] = $this->GetProperty('wasreopened');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETTOTALREPLIES] = $this->GetProperty('totalreplies');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETBAYESCATEGORY] = $this->GetProperty('bayescategoryid');

        return true;
    }

    /**
     * Retrieve ticket Notes
     *
     * @author Simaranjit Singh
     *
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RetrieveNotes(): array
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketNoteContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . SWIFT_TicketNote::TABLE_NAME . "
                                WHERE linktype = " . (SWIFT_TicketNoteManager::LINKTYPE_TICKET) . "
                                  AND linktypeid = " . ($this->GetTicketID()));

        while ($this->Database->NextRecord()) {
            $_ticketNoteContainer[$this->Database->Record['ticketnoteid']] = $this->Database->Record;
        }

        return $_ticketNoteContainer;
    }
}
