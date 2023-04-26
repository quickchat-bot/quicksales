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

namespace Tickets;

use SWIFT;
use Tickets\Models\AutoClose\SWIFT_AutoCloseRule;
use Tickets\Models\Bayesian\SWIFT_BayesianCategory;
use SWIFT_Cron;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_DataID;
use Base\Models\Department\SWIFT_Department;
use Tickets\Models\Escalation\SWIFT_EscalationRule;
use SWIFT_Exception;
use SWIFT_Loader;
use Tickets\Models\Macro\SWIFT_MacroCategory;
use Tickets\Models\Macro\SWIFT_MacroReply;
use Base\Models\Notification\SWIFT_NotificationAction;
use Base\Models\Notification\SWIFT_NotificationRule;
use Base\Models\Rating\SWIFT_Rating;
use Base\Library\Rules\SWIFT_Rules;
use SWIFT_Setup_Exception;
use SWIFT_SetupDatabase;
use SWIFT_SetupDatabaseIndex;
use SWIFT_SetupDatabaseSQL;
use SWIFT_SetupDatabaseTable;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Library\SLA\SWIFT_SLAHolidayManager;
use Tickets\Models\SLA\SWIFT_SLASchedule;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffAssign;
use Base\Models\Staff\SWIFT_StaffGroupAssign;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\FileType\SWIFT_TicketFileType;
use Tickets\Models\Filter\SWIFT_TicketFilter;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Link\SWIFT_TicketLinkType;
use Tickets\Models\Priority\SWIFT_TicketPriority;
use Tickets\Models\Status\SWIFT_TicketStatus;
use Tickets\Models\Type\SWIFT_TicketType;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Models\View\SWIFT_TicketViewField;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;
use Base\Models\Widget\SWIFT_Widget;

use SWIFT_Console;

/**
 * The Main Installer
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase_tickets extends SWIFT_SetupDatabase
{
    // Core Constants
    const PAGE_COUNT = 1;

    const STATUS_OPEN = 1;
    const STATUS_ONHOLD = 2;
    const STATUS_CLOSED = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(APP_TICKETS);

        SWIFT_Loader::LoadLibrary('Flag:TicketFlag', APP_TICKETS, false);
        SWIFT_Loader::LoadLibrary('SLA:SLAHolidayManager', APP_TICKETS, false);

        // Ticket Models
        SWIFT_Loader::LoadModel('SLA:SLA', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('SLA:SLASchedule', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('SLA:SLAHoliday', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('Status:TicketStatus', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('Priority:TicketPriority', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('FileType:TicketFileType', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('Link:TicketLinkType', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('Type:TicketType', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('Bayesian:BayesianCategory', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('View:TicketView', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('View:TicketViewField', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('View:TicketViewLink', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('Workflow:TicketWorkflow', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('Escalation:EscalationRule', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('Filter:TicketFilter', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('Filter:TicketFilterField', APP_TICKETS, false);
        SWIFT_Loader::LoadModel('AutoClose:AutoCloseRule', APP_TICKETS, false);
    }

    /**
     * Loads the table into the container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception
     */
    public function LoadTables()
    {
        // ======= BAYESWORDS =======
        $this->AddTable('bayeswords', new SWIFT_SetupDatabaseTable(TABLE_PREFIX ."bayeswords", "bayeswordid I PRIMARY AUTO NOTNULL,
                                                                        word C(1000) DEFAULT '' NOTNULL"));
        $this->AddIndex('bayeswords', new SWIFT_SetupDatabaseIndex("bayeswords1", TABLE_PREFIX ."bayeswords", "word", array("UNIQUE")));

        // ======= BAYESWORDSFREQS =======
        $this->AddTable('bayeswordsfreqs', new SWIFT_SetupDatabaseTable(TABLE_PREFIX ."bayeswordsfreqs", "bayeswordid I DEFAULT '0' NOTNULL,
                                                                        bayescategoryid I DEFAULT '0' NOTNULL,
                                                                        wordcount I8 DEFAULT '0' NOTNULL"));
        $this->AddIndex('bayeswordsfreqs', new SWIFT_SetupDatabaseIndex("bayeswordsfreqs1", TABLE_PREFIX ."bayeswordsfreqs", "bayeswordid, bayescategoryid", array("UNIQUE")));

        // ======= SLAHOLIDAYLINKS =======
        $this->AddTable('slaholidaylinks', new SWIFT_SetupDatabaseTable(TABLE_PREFIX ."slaholidaylinks", "slaholidaylinkid I PRIMARY AUTO NOTNULL,
                                                                        slaplanid I DEFAULT '0' NOTNULL,
                                                                        slaholidayid I DEFAULT '0' NOTNULL"));
        $this->AddIndex('slaholidaylinks', new SWIFT_SetupDatabaseIndex("slaholidaylinks1", TABLE_PREFIX ."slaholidaylinks", "slaplanid, slaholidayid"));
        $this->AddIndex('slaholidaylinks', new SWIFT_SetupDatabaseIndex("slaholidaylinks2", TABLE_PREFIX ."slaholidaylinks", "slaholidayid"));

        // ======= SLASCHEDULETABLE =======
        $this->AddTable('slascheduletable', new SWIFT_SetupDatabaseTable(TABLE_PREFIX ."slascheduletable", "slascheduletableid I PRIMARY AUTO NOTNULL,
                                                                        slascheduleid I DEFAULT '0' NOTNULL,
                                                                        sladay C(100) DEFAULT '' NOTNULL,
                                                                        opentimeline C(6) DEFAULT '00:00' NOTNULL,
                                                                        closetimeline C(6) DEFAULT '00:00' NOTNULL"));

        // ======= SLARULECRITERIA =======
        $this->AddTable('slarulecriteria', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "slarulecriteria", "slarulecriteriaid I PRIMARY AUTO NOTNULL,
                                                                slaplanid I DEFAULT '0' NOTNULL,
                                                                name C(100) DEFAULT '' NOTNULL,
                                                                ruleop I2 DEFAULT '0' NOTNULL,
                                                                rulematch C(255) DEFAULT '' NOTNULL,
                                                                rulematchtype I2 DEFAULT '0' NOTNULL"));
        $this->AddIndex('slarulecriteria', new SWIFT_SetupDatabaseIndex("slarulecriteria1", TABLE_PREFIX . "slarulecriteria", "slaplanid"));

        // ======= TICKETWORKFLOWCRITERIA =======
        $this->AddTable('ticketworkflowcriteria', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "ticketworkflowcriteria", "ticketworkflowcriteriaid I PRIMARY AUTO NOTNULL,
                                                                ticketworkflowid I DEFAULT '0' NOTNULL,
                                                                name C(100) DEFAULT '' NOTNULL,
                                                                ruleop I2 DEFAULT '0' NOTNULL,
                                                                rulematch C(255) DEFAULT '' NOTNULL,
                                                                rulematchtype I2 DEFAULT '0' NOTNULL"));
        $this->AddIndex('ticketworkflowcriteria', new SWIFT_SetupDatabaseIndex("ticketworkflowcriteria1", TABLE_PREFIX . "ticketworkflowcriteria", "ticketworkflowid"));

        // ======= TICKETWORKFLOWACTIONS =======
        $this->AddTable('ticketworkflowactions', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "ticketworkflowactions", "ticketworkflowactionid I PRIMARY AUTO NOTNULL,
                                                                ticketworkflowid I DEFAULT '0' NOTNULL,
                                                                name C(100) DEFAULT '' NOTNULL,
                                                                typeid I DEFAULT '0' NOTNULL,
                                                                typedata X2,
                                                                typechar C(255) DEFAULT '' NOTNULL"));
        $this->AddIndex('ticketworkflowactions', new SWIFT_SetupDatabaseIndex("ticketworkflowactions1", TABLE_PREFIX . "ticketworkflowactions", "ticketworkflowid"));

        // ======= AUTOCLOSECRITERIA =======
        $this->AddTable('autoclosecriteria', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "autoclosecriteria", "autoclosecriteriaid I PRIMARY AUTO NOTNULL,
                                                                autocloseruleid I DEFAULT '0' NOTNULL,
                                                                name C(100) DEFAULT '' NOTNULL,
                                                                ruleop I2 DEFAULT '0' NOTNULL,
                                                                rulematch C(255) DEFAULT '' NOTNULL,
                                                                rulematchtype I2 DEFAULT '0' NOTNULL"));
        $this->AddIndex('autoclosecriteria', new SWIFT_SetupDatabaseIndex("autoclosecriteria1", TABLE_PREFIX . "autoclosecriteria", "autocloseruleid"));

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT- 4951 New fulltext indexes are not being added on a new database installation.
         *
         */
        $this->AddFTIndex();

        return true;
    }

    /**
     * Get the Page Count for Execution
     *
     * @author Varun Shoor
     * @return int
     */
    public function GetPageCount()
    {
        return self::PAGE_COUNT;
    }

    /**
     * Function that does the heavy execution
     *
     * @author Varun Shoor
     * @param int $_pageIndex The Page Index
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception
     * @throws SWIFT_Exception
     */
    public function Install($_pageIndex = 1)
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::Install($_pageIndex);

        if (strtolower(DB_TYPE) === 'mysql' || strtolower(DB_TYPE) === 'mysqli')
        {
            $this->Query(new SWIFT_SetupDatabaseSQL("ALTER TABLE ". TABLE_PREFIX ."ticketlocks TYPE = HEAP"));
        }

        // ======= TICKETVIEWS =======
        $_staffIDContainer = $_SWIFT->Database->QueryFetch("SELECT staffid FROM " . TABLE_PREFIX . "staff");
        $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffIDContainer['staffid']));

        $_ticketViewFieldContainer = array(SWIFT_TicketViewField::FIELD_TICKETTYPEICON,
            SWIFT_TicketViewField::FIELD_SUBJECT, SWIFT_TicketViewField::FIELD_TICKETID, SWIFT_TicketViewField::FIELD_LASTREPLIER,
            SWIFT_TicketViewFIeld::FIELD_REPLIES, SWIFT_TicketViewField::FIELD_PRIORITY, SWIFT_TicketViewField::FIELD_LASTACTIVITY,
            SWIFT_TicketViewField::FIELD_DUEDATE, SWIFT_TicketViewField::FIELD_FLAG);

        $_ticketViewLinkContainer = array();

        $_ticketViewID_Default = SWIFT_TicketView::Create($this->Language->Get('defaultview'), SWIFT_TicketView::VIEWSCOPE_GLOBAL,
                $_SWIFT_StaffObject, false, true, true, SWIFT_TicketViewField::FIELD_LASTACTIVITY, SWIFT_TicketView::SORT_DESC,
                '20', '0', true, false, SWIFT_TicketView::AFTERREPLY_ACTIVETICKETLIST,
                $_ticketViewFieldContainer, $_ticketViewLinkContainer);

        $_ticketViewID_AllTickets = SWIFT_TicketView::Create($this->Language->Get('alltickets'), SWIFT_TicketView::VIEWSCOPE_GLOBAL,
                $_SWIFT_StaffObject, true, false, false, SWIFT_TicketViewField::FIELD_LASTACTIVITY, SWIFT_TicketView::SORT_DESC,
                '20', '0', true, false, SWIFT_TicketView::AFTERREPLY_ACTIVETICKETLIST,
                $_ticketViewFieldContainer, $_ticketViewLinkContainer, true);

        // ======= DEPARTMENTS =======
        $_DepartmentObject = SWIFT_Department::Insert($this->Language->Get('coregeneral'), APP_TICKETS, SWIFT_PUBLIC, 0, 0, false, array());
        $this->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('departmentid' => $_DepartmentObject->GetDepartmentID()),
                'UPDATE', "1 = 1");

        $_staffIDList = $_staffGroupIDList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff");
        while ($this->Database->NextRecord())
        {
            $_staffIDList[] = $this->Database->Record['staffid'];
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup");
        while ($this->Database->NextRecord())
        {
            $_staffGroupIDList[] = $this->Database->Record['staffgroupid'];
        }

        SWIFT_StaffAssign::AssignDepartmentList($_DepartmentObject, $_staffIDList);
        SWIFT_StaffGroupAssign::AssignDepartmentList($_DepartmentObject, $_staffGroupIDList);

        // ======= TICKETTYPES =======
        $_ticketTypeID_Issues = SWIFT_TicketType::Create($this->Language->Get('tickettypeissue'), '{$themepath}icon_typeissue.svg', '1', SWIFT_PUBLIC, '0', false, array(), true);
        SWIFT_TicketType::Create($this->Language->Get('tickettypetask'), '{$themepath}icon_typetask.svg', '2', SWIFT_PRIVATE, '0', false, array(), false);
        SWIFT_TicketType::Create($this->Language->Get('tickettypebug'), '{$themepath}icon_typebug.svg', '3', SWIFT_PUBLIC, '0', false, array(), false);
        SWIFT_TicketType::Create($this->Language->Get('tickettypelead'), '{$themepath}icon_typelead.svg', '4', SWIFT_PRIVATE, '0', false, array(), false);
        SWIFT_TicketType::Create($this->Language->Get('tickettypefeedback'), '{$themepath}icon_lightbulb.svg', '5', SWIFT_PUBLIC, '0', false, array(), false);
        $this->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('tickettypeid' => $_ticketTypeID_Issues),
                'UPDATE', "1 = 1");

        // ======= TICKETSTATUS =======
        $_ticketStatusID_Open = SWIFT_TicketStatus::Create($this->Language->Get('statusopen'), '1', false, true, '#4eafcb', '#4eafcb', 0, SWIFT_PUBLIC, false, '{$themepath}icon_ticketstatusopen.png', false, false, false, array(), true);
        $_ticketStatusID_InProgress = SWIFT_TicketStatus::Create($this->Language->Get('statusinprogress'), '2', false, true, '#e0af2a', '#e0af2a', 0, SWIFT_PUBLIC, false, '{$themepath}icon_ticketstatusinprogress.png', false, false, false);
        $_ticketStatusID_Closed = SWIFT_TicketStatus::Create($this->Language->Get('statusclosed'), '3', true, false, '#36a148', '#36a148', 0, SWIFT_PUBLIC, false, '{$themepath}icon_ticketstatusclosed.png', false, true, false);
        $this->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('ticketstatusid' => $_ticketStatusID_Open),
                'UPDATE', "1 = 1");

        $this->Database->AutoExecute(TABLE_PREFIX . 'settings', array('data' => $_ticketStatusID_Open), 'UPDATE', "vkey = 't_cstatusupd'");
        SWIFT_TicketStatus::RebuildCache();

        // ======= TICKETPRIORITIES =======
        SWIFT_TicketPriority::Create($this->Language->Get('prlow'), '1', SWIFT_PUBLIC, '#5b5b5b', '', false);
        $_ticketPriorityID_Normal = SWIFT_TicketPriority::Create($this->Language->Get('prnormal'), '2', SWIFT_PUBLIC, '#000000', '', false, array(), true);
        $_ticketPriorityID_High = SWIFT_TicketPriority::Create($this->Language->Get('prhigh'), '3', SWIFT_PUBLIC, '#d6000e', '', false);
        $_ticketPriorityID_Urgent = SWIFT_TicketPriority::Create($this->Language->Get('prurgent'), '4', SWIFT_PUBLIC, '#ffffff', '#d6000e', false);
        $this->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('tickettypeid' => $_ticketPriorityID_Normal),
                'UPDATE', "1 = 1");

        SWIFT_TicketPriority::RebuildCache();

        // ======= CRON =======
        SWIFT_Cron::Create('tickets', 'Tickets', 'TicketsMinute', 'GeneralTasks', '0', '10', '0', true);
        SWIFT_Cron::Create('ticketfollowup', 'Tickets', 'TicketsMinute', 'FollowUp', '0', '10', '0', true);
        SWIFT_Cron::Create('ticketautoclose', 'Tickets', 'TicketsMinute', 'AutoClose', '0', '15', '0', true);

        // ======= BAYESIAN =======
        SWIFT_BayesianCategory::Create($this->Language->Get('spam'), 1, true, SWIFT_BayesianCategory::CATEGORY_SPAM);
        SWIFT_BayesianCategory::Create($this->Language->Get('nospam'), 1, true, SWIFT_BayesianCategory::CATEGORY_NOTSPAM);

        // ======= ticketfiletypes =======
        SWIFT_TicketFileType::Create('swf', 0, true, true, false);
        SWIFT_TicketFileType::Create('wav', 0, true, true, false);
        SWIFT_TicketFileType::Create('mov', 0, true, true, false);
        SWIFT_TicketFileType::Create('php', 0, true, true, false);
        SWIFT_TicketFileType::Create('mpeg', 0, true, true, false);
        SWIFT_TicketFileType::Create('mpg', 0, true, true, false);
        SWIFT_TicketFileType::Create('mp3', 0, true, true, false);
        SWIFT_TicketFileType::Create('png', 0, true, true, false);
        SWIFT_TicketFileType::Create('jpg', 0, true, true, false);
        SWIFT_TicketFileType::Create('gif', 0, true, true, false);
        SWIFT_TicketFileType::Create('jpeg', 0, true, true, false);
        SWIFT_TicketFileType::Create('ico', 0, true, true, false);
        SWIFT_TicketFileType::Create('htm', 0, true, true, false);
        SWIFT_TicketFileType::Create('html', 0, true, true, false);
        SWIFT_TicketFileType::Create('txt', 0, true, true, false);
        SWIFT_TicketFileType::Create('pdf', 0, true, true, false);
        SWIFT_TicketFileType::Create('rtf', 0, true, true, false);
        SWIFT_TicketFileType::Create('zip', 0, true, true, false);
        SWIFT_TicketFileType::Create('gz', 0, true, true, false);
        SWIFT_TicketFileType::Create('tar', 0, true, true, false);
        SWIFT_TicketFileType::Create('rar', 0, true, true, false);
        SWIFT_TicketFileType::Create('bz', 0, true, true, false);
        SWIFT_TicketFileType::Create('bz2', 0, true, true, false);
        SWIFT_TicketFileType::Create('doc', 0, true, true, false);
        SWIFT_TicketFileType::Create('docx', 0, true, true, false);
        SWIFT_TicketFileType::Create('xls', 0, true, true, false);
        SWIFT_TicketFileType::Create('xlsx', 0, true, true, false);
        SWIFT_TicketFileType::Create('ppt', 0, true, true, false);
        SWIFT_TicketFileType::Create('pptx', 0, true, true, false);

        SWIFT_TicketFileType::RebuildCache();

        // ======= TICKETLINKTYPES =======
        SWIFT_TicketLinkType::Create($this->Language->Get('tcrelatesto'), '1', false);
        SWIFT_TicketLinkType::Create($this->Language->Get('tcisrelatedto'), '2', false);
        SWIFT_TicketLinkType::Create($this->Language->Get('tcduplicates'), '3', false);
        SWIFT_TicketLinkType::Create($this->Language->Get('tcisduplicatedby'), '4', false);
        SWIFT_TicketLinkType::Create($this->Language->Get('tcwasclonedas'), '5', false);
        SWIFT_TicketLinkType::Create($this->Language->Get('tcisderivedfrom'), '6', false);

        SWIFT_TicketLinkType::RebuildCache();

        // ======= WIDGET =======
        SWIFT_Widget::Create('PHRASE:widgetsubmitticket', 'submitticket', APP_TICKETS, '/Tickets/Submit', '{$themepath}icon_widget_submitticket.svg', '{$themepath}icon_widget_submitticket_small.png', 3, true, true, true, true, SWIFT_Widget::VISIBLE_ALL, false);
        SWIFT_Widget::Create('PHRASE:widgetviewtickets', 'viewtickets', APP_TICKETS, '/Tickets/ViewList', '{$themepath}icon_widget_viewticket.svg', '{$themepath}icon_widget_viewticket_small.png', 2, true, true, true, true, SWIFT_Widget::VISIBLE_LOGGEDIN, false);

        $this->ExecuteQueue();

        // ======= IMPORT HOLIDAYS =======
        $_SWIFT_SLAHolidayManagerObject = new SWIFT_SLAHolidayManager();
        $_SWIFT_SLAHolidayManagerObject->Import('./' . SWIFT_APPS_DIRECTORY . '/tickets/' . SWIFT_CONFIG_DIRECTORY . '/holidays_us_2012.xml');

        // ======= SLA =======
        $_daysContainer = array();
        $_daysContainer['sunday'] = array('type' => SWIFT_SLASchedule::SCHEDULE_DAYCLOSED, 'hours' => array());
        $_daysContainer['monday'] = array('type' => SWIFT_SLASchedule::SCHEDULE_DAYOPEN24, 'hours' => array());
        $_daysContainer['tuesday'] = array('type' => SWIFT_SLASchedule::SCHEDULE_DAYOPEN24, 'hours' => array());
        $_daysContainer['wednesday'] = array('type' => SWIFT_SLASchedule::SCHEDULE_DAYOPEN24, 'hours' => array());
        $_daysContainer['thursday'] = array('type' => SWIFT_SLASchedule::SCHEDULE_DAYOPEN24, 'hours' => array());
        $_daysContainer['friday'] = array('type' => SWIFT_SLASchedule::SCHEDULE_DAYOPEN24, 'hours' => array());
        $_daysContainer['saturday'] = array('type' => SWIFT_SLASchedule::SCHEDULE_DAYCLOSED, 'hours' => array());
        $_slaScheduleID = SWIFT_SLASchedule::Create($this->Language->Get('sladefaultschedule'), $_daysContainer);

        $_SWIFT_SLAObject = SWIFT_SLA::Create($this->Language->Get('sladefaultplan'), '24', '72', $_slaScheduleID, true, 1, SWIFT_SLA::RULE_MATCHEXTENDED,
                array(1 => array('isresolved', '1', '0', SWIFT_Rules::RULE_MATCHALL)), array());

        // ======= ESCALATIONS =======
        SWIFT_EscalationRule::Create($this->Language->Get('defaultescalationrule'), $_SWIFT_SLAObject->GetSLAPlanID(), -1, SWIFT_EscalationRule::TYPE_RESOLUTIONDUE, -1, $_ticketPriorityID_High, -1, -1, SWIFT_TicketFlag::FLAG_RED, 0, array(), array('escalated'), array());


        // ======= TICKET WORKFLOW =======
        $_criteriaContainer = array(array('ticketstatus', '1', $_ticketStatusID_Open, SWIFT_Rules::RULE_MATCHALL));
        $_actionContainer = array(array('name' => 'status', 'typeid' => $_ticketStatusID_Closed));
        SWIFT_TicketWorkflow::Create($this->Language->Get('workflowcloseticket'), true, 1, SWIFT_TicketWorkflow::RULE_MATCHEXTENDED, $_criteriaContainer, $_actionContainer, array(), false, array());

        // ======= RATINGS =======
        SWIFT_Rating::Create($this->Language->Get('overallsatisfaction'), 1, SWIFT_Rating::TYPE_TICKET, 0, true, true, 5, SWIFT_PUBLIC, false, false, array(), array());


        // ======= TICKET FILTERS =======
        $_ticketFilterFieldContainerOpen = array(array('owner', '1', '-1'), array('status', '1', $_ticketStatusID_Open));
        $_ticketFilterFieldContainerPending = array(array('owner', '1', '-1'), array('status', '1', $_ticketStatusID_InProgress));

        SWIFT_TicketFilter::Create($this->Language->Get('myopentickets'), SWIFT_TicketFilter::TYPE_PUBLIC, 0, SWIFT_Rules::RULE_MATCHALL, $_staffIDList[0], $_ticketFilterFieldContainerOpen);
        SWIFT_TicketFilter::Create($this->Language->Get('mypendingtickets'), SWIFT_TicketFilter::TYPE_PUBLIC, 0, SWIFT_Rules::RULE_MATCHALL, $_staffIDList[0], $_ticketFilterFieldContainerPending);

        // ======= NOTIFICATION RULE =======
        $_notificationCriteria = array(array('ticketevent', '1', 'ticketassigned', '1'));
        $_notificationActions = array(array(SWIFT_NotificationAction::ACTION_EMAILSTAFF, '0'), array(SWIFT_NotificationAction::ACTION_POOLSTAFF, '0'));
        SWIFT_NotificationRule::Create($this->Language->Get('sticketassigned'), SWIFT_NotificationRule::TYPE_TICKET, 1, $_notificationCriteria, $_notificationActions, $_staffIDContainer['staffid']);

        // ======= AUTOCLOSE RULE =======
        $_autoCloseCriteria = array(array('ticketstatusid', '1', $_ticketStatusID_Open, '1'));
        SWIFT_AutoCloseRule::Create($this->Language->Get('sautocloserule'), $_ticketStatusID_Closed, 72, 125, true, true, false, 1, $_autoCloseCriteria);

        $this->InstallSampleData($_staffIDContainer, $_DepartmentObject, $_SWIFT_StaffObject, $_staffIDList, $_staffGroupIDList, $_ticketStatusID_Open, $_ticketPriorityID_Normal, $_ticketTypeID_Issues);

        $this->ExecuteQueue();

        return true;
    }

    /**
     * @author Saloni Dhall <saloni.dhall@kayako.com>
     * @author Utsav Handa <utsav.handa@kayako.com>
     *
     * @param array $_staffIDContainer
     * @param SWIFT_Department $_Department
     * @param SWIFT_Staff $_Staff
     * @param array $_staffIDList
     * @param array $_staffGroupIDList
     * @param int $_ticketStatusID_Open
     * @param int $_ticketPriorityID_Normal
     * @param int $_ticketTypeID_Issues
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function InstallSampleData($_staffIDContainer, $_Department, $_Staff, $_staffIDList, $_staffGroupIDList, $_ticketStatusID_Open, $_ticketPriorityID_Normal, $_ticketTypeID_Issues)
    {
        if (!defined('INSTALL_SAMPLE_DATA') || INSTALL_SAMPLE_DATA != true) {
            return false;
        }

        // Create a department
        $_DepartmentSupport = SWIFT_Department::Insert($this->Language->Get('sample_departmentname'), APP_TICKETS, SWIFT_PUBLIC, 0, 0, false, array());

        SWIFT_StaffAssign::AssignDepartmentList($_DepartmentSupport, $_staffIDList);

        SWIFT_StaffGroupAssign::AssignDepartmentList($_DepartmentSupport, $_staffGroupIDList);

        // Create a macro category
        $_macroCategoryID = SWIFT_MacroCategory::Create($this->Language->Get('sample_macrocategoryname'), SWIFT_MacroCategory::TYPE_PUBLIC, 0, 0, $_Staff);

        // Create macro(s)
        SWIFT_MacroReply::Create($_macroCategoryID, $this->Language->Get('sample_macrotitle'), $this->Language->Get('sample_macroreplycontents'), array(), $_Department->GetDepartmentID(), false, false,
                                 false, false, $_Staff);

        // Create a custom field group
        $_staffGroupPermission = array();
        foreach ($_staffGroupIDList as $_val) {
            $_staffGroupPermission[$_val] = '1';
        }

        $_customFieldGroupID = SWIFT_CustomFieldGroup::Create($this->Language->Get('sample_customfieldgrouptitle'), SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET, 1, array($_DepartmentSupport->GetDepartmentID()),
                                                              $_staffGroupPermission, false);

        // Create a custom field - select
        SWIFT_CustomField::Create($_customFieldGroupID, SWIFT_CustomField::TYPE_SELECT, $this->Language->Get('sample_customfieldselecttitle'), $this->Language->Get('sample_customfieldselectdescription'),
                                  substr(BuildHash(), 1, 12), false, 2, 1, 1, 1, false, array("1" => array($this->Language->Get('sample_customfieldselectoption1'), "1"), "2" => array($this->Language->Get('sample_customfieldselectoption2'),"2"), "3" => array($this->Language->Get('sample_customfieldselectoption3'), "3")));

        // Create a custom field - text
        SWIFT_CustomField::Create($_customFieldGroupID, SWIFT_CustomField::TYPE_TEXT, $this->Language->Get('sample_customfieldtexttitle'), $this->Language->Get('sample_customfieldtextdescription'),
                                  substr(BuildHash(), 1, 12), false, 2, 1, 1, 1, false, array());

        // Create a notification -  new ticket reply
        SWIFT_NotificationRule::Create($this->Language->Get('sample_notificationnewreply'), SWIFT_NotificationRule::TYPE_TICKET, 1, array(array('ticketevent', '1', 'newstaffreply', '2'), array('ticketevent', '1', 'newclientreply', '2')),
                                       array(array(SWIFT_NotificationAction::ACTION_EMAILSTAFF, '0'), array(SWIFT_NotificationAction::ACTION_POOLSTAFF, '0')), $_staffIDContainer['staffid']);

        // Create a notification - new ticket created
        SWIFT_NotificationRule::Create($this->Language->Get('sample_notificationnewticketcreated'), SWIFT_NotificationRule::TYPE_TICKET, 1, array(array('ticketevent', '1', 'newticket', '1')),
                                       array(array(SWIFT_NotificationAction::ACTION_EMAILDEPARTMENT, '0'), array(SWIFT_NotificationAction::ACTION_POOLDEPARTMENT, '0')), $_staffIDContainer['staffid']);

        // Create a rating
        SWIFT_Rating::Create($this->Language->Get('sample_ratinglabel'), 2, SWIFT_Rating::TYPE_TICKET, 0, true, true, 5, SWIFT_PUBLIC, false, false, array(), array());

        // Create a ticket with creation as 3 days ago
        $_Ticket = SWIFT_Ticket::Create($this->Language->Get('sample_ticketsubject'), $this->Language->Get('sample_userfullname'), $this->Language->Get('sample_useremailaddress'),
                                        sprintf($this->Language->Get('sample_ticketcontent'), $_POST['producturl'], RemoveTrailingSlash($_POST['producturl'] . SWIFT_BASENAME), $_POST['producturl']),
                                        0, $_DepartmentSupport->GetDepartmentID(), $_ticketStatusID_Open, $_ticketPriorityID_Normal, $_ticketTypeID_Issues, 2, 0, SWIFT_Ticket::TYPE_DEFAULT,
                                        SWIFT_Ticket::CREATOR_USER, SWIFT_Ticket::CREATIONMODE_SUPPORTCENTER, '', 0, false, '', false, (DATENOW - 259200));

        $this->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('ownerstaffid' => $_Staff->GetID(), 'ownerstaffname' => $_Staff->Get('fullname')), 'UPDATE', 'ticketid = ' . $_Ticket->GetID());

        // Create a demo ticket tag
        SWIFT_Tag::Process(SWIFT_TagLink::TYPE_TICKET, $_Ticket->GetTicketID(), array($this->Language->Get('sample_tag')), 0);

        return true;
    }

    /**
     * Uninstalls the App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Uninstall()
    {
        SWIFT_Department::DeleteOnApp(array(APP_TICKETS));

        parent::Uninstall();

        SWIFT_Widget::DeleteOnApp(array(APP_TICKETS));

        SWIFT_Cron::DeleteOnName(array('tickets', 'ticketfollowup', 'ticketautoclose'));

        SWIFT_Rating::DeleteOnType(array(SWIFT_Rating::TYPE_TICKET, SWIFT_Rating::TYPE_TICKETPOST));

        SWIFT_NotificationRule::DeleteOnType(array(SWIFT_NotificationRule::TYPE_TICKET));

        return true;
    }

    /**
     * Upgrade from 4.00.911
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_00_911() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * ---------------------------------------------
         * WORKFLOWS
         * ---------------------------------------------
         */

        $_processActionList = array(SWIFT_TicketWorkflow::ACTION_ADDTAGS, SWIFT_TicketWorkflow::ACTION_REMOVETAGS);
        $_finalUpdateContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflowactions WHERE name IN (" . BuildIN($_processActionList) . ")");
        while ($this->Database->NextRecord()) {
            $_unserializedData = @unserialize($this->Database->Record['typedata']);

            if (false !== $_unserializedData) {
                $_finalUpdateContainer[$this->Database->Record['name']] = json_encode($_unserializedData);
            }
        }

        foreach ($_finalUpdateContainer as $_name => $_typeData) {
            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketworkflowactions', array('typedata' => $_typeData), 'UPDATE', "name = '" . $this->Database->Escape($_name) . "'");
        }

        /**
         * ---------------------------------------------
         * ESCALATIONS
         * ---------------------------------------------
         */

        $_finalUpdateContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "escalationrules");
        while ($this->Database->NextRecord()) {
            $_finalUpdateContainer[$this->Database->Record['escalationruleid']] = array();

            $_finalUpdateContainer[$this->Database->Record['escalationruleid']]['addtags'] = json_encode(@unserialize($this->Database->Record['addtags']));
            $_finalUpdateContainer[$this->Database->Record['escalationruleid']]['removetags'] = json_encode(@unserialize($this->Database->Record['removetags']));
        }

        foreach ($_finalUpdateContainer as $_escalationRuleID => $_escalationRuleContainer) {
            $this->Database->AutoExecute(TABLE_PREFIX . 'escalationrules', array('addtags' => $_escalationRuleContainer['addtags'], 'removetags' => $_escalationRuleContainer['removetags']),
                    'UPDATE', "escalationruleid = '" . (int) ($_escalationRuleID) . "'");
        }

        return true;
    }

    /**
     * Upgrade from 4.00.932
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_00_932() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_resolvedTicketStatusIDList = $_unresolvedTicketStatusIDList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY ticketstatusid ASC");
        while ($this->Database->NextRecord()) {
            if ($this->Database->Record['markasresolved'] == '1')
            {
                $_resolvedTicketStatusIDList[] = $this->Database->Record['ticketstatusid'];
            } else {
                $_unresolvedTicketStatusIDList[] = $this->Database->Record['ticketstatusid'];
            }
        }

        if (count($_resolvedTicketStatusIDList))
        {
            $this->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('isresolved' => '1'), 'UPDATE', "ticketstatusid IN (" . BuildIN($_resolvedTicketStatusIDList) . ")");
        }

        if (count($_unresolvedTicketStatusIDList))
        {
            $this->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('isresolved' => '0'), 'UPDATE', "ticketstatusid IN (" . BuildIN($_unresolvedTicketStatusIDList) . ")");
        }

        return true;
    }

    /**
     * Upgrade from 4.01.191
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_01_191() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // ======= CRON =======
        SWIFT_Cron::Create('ticketautoclose', 'Tickets', 'TicketsMinute', 'AutoClose', '0', '15', '0', true);

        return true;
    }

    /**
     * Upgrade from 4.01.218
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_01_218() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_CustomFieldManager::RebuildCache();

        return true;
    }

    /**
     * Upgrade from 4.50.1636
     * Update ticket filters
     *
     * @author Parminder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_50_1637() {

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("UPDATE " . TABLE_PREFIX . "ticketfilterfields SET fieldvalue='nwfd' WHERE fieldvalue='nwtd'");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "ticketfilterfields SET fieldvalue='nmfd' WHERE fieldvalue='nmtd'");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "ticketfilterfields SET fieldvalue='nyfd' WHERE fieldvalue='nytd'");

        return true;
    }

    /**
     * Upgrade from 4.60.0.3971
     *
     * @author Utsav Handa
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_60_0_3972() {

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Remove displayicon
        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "ticketpriorities DROP COLUMN displayicon");

        return true;
    }

    /**
     * Upgrade from 4.80.0
     *
     * @author Ankit Saini
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_90_0000() {

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Change Ticket Type displayicon to svg icons (added new)
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "tickettypes SET displayicon='{\$themepath}icon_typeissue.svg' WHERE displayicon='{\$themepath}icon_typeissue.gif'");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "tickettypes SET displayicon='{\$themepath}icon_typetask.svg' WHERE displayicon='{\$themepath}icon_typetask.gif'");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "tickettypes SET displayicon='{\$themepath}icon_typebug.svg' WHERE displayicon='{\$themepath}icon_typebug.gif'");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "tickettypes SET displayicon='{\$themepath}icon_typelead.svg' WHERE displayicon='{\$themepath}icon_typelead.png'");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "tickettypes SET displayicon='{\$themepath}icon_lightbulb.svg' WHERE displayicon='{\$themepath}icon_lightbulb.png'");

        //change ticket status default colors for users that use the default old colors.
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "ticketstatus SET statusbgcolor='#4eafcb' WHERE title='Open' AND statusbgcolor='#8BB467'");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "ticketstatus SET statusbgcolor='#e0af2a' WHERE title='In Progress' AND statusbgcolor='#b34a4a'");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "ticketstatus SET statusbgcolor='#36a148' WHERE title='Closed' AND statusbgcolor='#5f5f5f'");


        return true;
    }

    /**
     * Upgrade from 4.92.4
     *
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_92_5() {

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "ticketworkflows ADD COLUMN uservisibility INT(2) NOT NULL DEFAULT 0 AFTER staffvisibilitycustom");
        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "tickets ADD INDEX tickets20 (tickettypeid, isresolved, departmentid)");
        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "tickets ADD INDEX tickets21 (priorityid, isresolved, departmentid)");

        return true;
    }

    /**
     * Upgrade from 4.93.00
     *
     * @author Werner Garcia <werner.garcia@crossover.com>
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_93_01() {

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentContainer = [];
        $this->Database->Query("SELECT departmentid, title FROM " . TABLE_PREFIX . "departments");
        while ($this->Database->NextRecord()) {
            $_departmentContainer[$this->Database->Record['departmentid']] = $this->Database->Record;
        }

        foreach ($_departmentContainer as $_departmentId => $_department) {
            $this->Database->AutoExecute(TABLE_PREFIX . 'departments', [
                'title' => html_entity_decode($_department['title']),
            ],
                'UPDATE', "departmentid = '" . $_departmentId . "'");
        }

        $_ticketContainer = [];
        $this->Database->Query("SELECT ticketid, departmenttitle, fullname, lastreplier FROM " . TABLE_PREFIX . "tickets");
        while ($this->Database->NextRecord()) {
            $_ticketContainer[$this->Database->Record['ticketid']] = $this->Database->Record;
        }

        foreach ($_ticketContainer as $_ticketId => $_ticket) {
            $this->Database->AutoExecute(TABLE_PREFIX . 'tickets', [
                'departmenttitle' => html_entity_decode($_ticket['departmenttitle']),
                'lastreplier' => html_entity_decode($_ticket['lastreplier']),
                'fullname' => html_entity_decode($_ticket['fullname']),
            ],
                'UPDATE', "ticketid = '" . $_ticketId . "'");
        }

        return true;
    }

    /**
     *
     * @author Busayo Arotimi
     * @return bool "true" on Success,
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_93_08()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName =  TABLE_PREFIX . 'bayeswords';

        // ALTER column word
        $_console = new SWIFT_Console();
        $this->Database->Query("ALTER TABLE $_tableName MODIFY word VARCHAR(500)");
        $_console->Message('Modified word column on ' . $_tableName . '...' . $_console->Green('Done'));

        return true;
     }
}
