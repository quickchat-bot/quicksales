<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2014, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Console;

use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Department\SWIFT_Department;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Template\SWIFT_TemplateGroup;
use Base\Models\User\SWIFT_User;
use Controller_console;
use SWIFT_App;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT_Exception;
use Tickets\Models\FollowUp\SWIFT_TicketFollowUp;
use Tickets\Models\Macro\SWIFT_MacroCategory;
use Tickets\Models\Macro\SWIFT_MacroReply;
use Tickets\Models\Priority\SWIFT_TicketPriority;
use Tickets\Models\Recurrence\SWIFT_TicketRecurrence;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Models\SLA\SWIFT_SLASchedule;
use Tickets\Models\Status\SWIFT_TicketStatus;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;

/**
 * @author Varun Shoor
 */
class Controller_Statistics extends Controller_console
{
    /**
     * Constructor
     *
     * @author Atul Atri
     */
    public function __construct()
    {
        parent::__construct(false);
    }

    /**
     * @author Varun Shoor
     * @author Amarjeet Kaur
     *
     * @return bool
     *
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * Extracting table count from MySQL DB is done in two ways:
         * - SELECT COUNT
         * - SHOW TABLE STATUS
         *
         * The 'show table status' provides statistics for a database, including a table rows, which can be used to
         * extract count for large tables, where SELECT COUNT method does a scan for identifying rows count.
         */

        /** @var array $_statisticsTableContainer */
        $_statisticsTableContainer = array(
            'usercount' => TABLE_PREFIX . SWIFT_User::TABLE_NAME,
            'staffcount' => TABLE_PREFIX . SWIFT_Staff::TABLE_NAME,
            'templategroupcount' => TABLE_PREFIX . SWIFT_TemplateGroup::TABLE_NAME,
            'customfieldgroupcount' => TABLE_PREFIX . SWIFT_CustomFieldGroup::TABLE_NAME,
        );

        /** @var array $_statisticsSQLContainer */
        $_statisticsSQLContainer = array(
            'activestaffcount' => 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . 'staff WHERE isenabled = 1'
        );

        // Tickets Statistics
        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $this->Load->Model('FollowUp:TicketFollowUp', [], false, '', APP_TICKETS);

            $_statisticsSQLContainer['openticketcount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . ' WHERE isresolved = 0';
            $_statisticsSQLContainer['closedticketcount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . ' WHERE isresolved = 1';
            $_statisticsSQLContainer['phoneticketcount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . ' WHERE isphonecall = 1';
            $_statisticsSQLContainer['customprioritycount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_TicketPriority::TABLE_NAME . ' WHERE iscustom = 1';
            $_statisticsSQLContainer['customstatuscount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_TicketStatus::TABLE_NAME . ' WHERE iscustom = 1';
            $_statisticsSQLContainer['workflowcount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_TicketWorkflow::TABLE_NAME . ' WHERE isenabled = 1';
            $_statisticsSQLContainer['billableticketcount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . ' WHERE timeworked != 0 AND timebilled != 0';
            $_statisticsSQLContainer['timetrackingticketcount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME . ' WHERE timeworked != 0';
            $_statisticsSQLContainer['ticketdepartmentcount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_Department::TABLE_NAME . ' WHERE departmentapp = \'tickets\'';
            $_statisticsSQLContainer['activerecurringticketcount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_TicketRecurrence::TABLE_NAME . ' WHERE nextrecurrence != 0';

            $_statisticsTableContainer['ticketcount'] = TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME;
            $_statisticsTableContainer['slaplancount'] = TABLE_PREFIX . SWIFT_SLA::TABLE_NAME;
            $_statisticsTableContainer['workschedulecount'] = TABLE_PREFIX . SWIFT_SLASchedule::TABLE_NAME;
            $_statisticsTableContainer['ticketfollowupcount'] = TABLE_PREFIX . SWIFT_TicketFollowUp::TABLE_NAME;
            $_statisticsTableContainer['macrocategorycount'] = TABLE_PREFIX . SWIFT_MacroCategory::TABLE_NAME;
            $_statisticsTableContainer['macroreplycount'] = TABLE_PREFIX . SWIFT_MacroReply::TABLE_NAME;
            $_statisticsTableContainer['recurringticketcount'] = TABLE_PREFIX . SWIFT_TicketRecurrence::TABLE_NAME;
        }

        // Troubleshooter Statistics
        if (SWIFT_App::IsInstalled(APP_TROUBLESHOOTER)) {
            $this->Load->Model('Category:TroubleshooterCategory', [], false, APP_TROUBLESHOOTER);

            $_statisticsTableContainer['troubleshootercategorycount'] = TABLE_PREFIX . SWIFT_TroubleshooterCategory::TABLE_NAME;
        }

        // LiveChat Statistics
        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $this->Load->Model('Call:Call', [], false, '',APP_LIVECHAT);
            $this->Load->Model('Chat:Chat', [], false, '',APP_LIVECHAT);

            $_statisticsSQLContainer['chatdepartmentcount'] = 'SELECT COUNT(1) AS totalitems FROM ' . TABLE_PREFIX . SWIFT_Department::TABLE_NAME . ' WHERE departmentapp = \'livechat\'';

            $_statisticsTableContainer['chatcount'] = TABLE_PREFIX . SWIFT_Chat::TABLE_NAME;
            $_statisticsTableContainer['callcount'] = TABLE_PREFIX . SWIFT_Call::TABLE_NAME;

        }

        /** @var array $_statisticsContainer */
        $_statisticsContainer = [];

        /**
         * Retrieve the statistics for SQL
         */
        foreach ($_statisticsSQLContainer as $_statisticName => $_statisticQuery) {
            $_statisticsContainer[$_statisticName] = 0;

            $_statisticCount = $this->Database->QueryFetch($_statisticQuery);
            if (isset($_statisticCount['totalitems']) && !empty($_statisticCount['totalitems'])) {
                $_statisticsContainer[$_statisticName] = (int)($_statisticCount['totalitems']);
            }
        }

        // Retrieve Table(s) status
        $_tableStatusContainer = $this->Database->QueryFetchAll('show table status');

        /**
         * Retrieve the statistics for Table status
         */
        foreach ($_tableStatusContainer as $_tableStatusList) {
            // Checking whether tableName is listed for statistics collection
            $_statisticName = array_search($_tableStatusList['Name'], $_statisticsTableContainer);
            if (!empty($_statisticName)) {
                $_statisticsContainer[$_statisticName] = (int)($_tableStatusList['Rows']);
            }
        }

        echo json_encode($_statisticsContainer);

        return true;
    }
}
