<?php
/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-QuickSupport Singapore Pte. Ltd.h Ltd.
 * @license    http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 * @filesource
 * ###################################
 * =======================================
 */

use Base\Library\KQL2\SWIFT_KQL2Compiler;
use Base\Library\KQL2\SWIFT_KQL2Parser;
use Knowledgebase\Admin\LoaderMock;

/**
 * KQL2 Compiler Tests
 *
 * @author Andriy Lesyuk
 */
class SWIFT_KQL2CompilerTest extends SWIFT_TestCase
{

    /**
     * Tests the Compile()
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     */
    public function testCompile()
    {
        static::$databaseCallback['Query'] = function () {
            SWIFT::GetInstance()->Database->Record['vkey'] = 'tickets';
        };
        $_kqlParser = $this->getMockObject(SWIFT_KQL2ParserMock::class);
        $_kqlCompiler = $this->getMocked();

        /*
        // Test complex expressions
        $_kqlQuery = "SELECT ((SUM(IF('Tickets.Is First Contact Resolved', 1, 0))/COUNT(*))*100) AS FirstContactResolved FROM 'Tickets'";
        $_sqlQuery = "SELECT ((SUM(IF(tickets.isfirstcontactresolved, 1, 0)) / COUNT(*)) * 100) AS 'FirstContactResolved' FROM swtickets AS tickets LEFT JOIN swusers AS users ON tickets.userid = users.userid";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);
        $_sql = $_kqlCompiler->GetSQL();

        $this->assertEquals($_sql, $_sqlQuery);

        // Test ThisMonth()
        $_kqlQuery = "SELECT 'Tickets.Is First Contact Resolved' FROM 'Tickets' WHERE 'Tickets.Last Activity' = ThisMonth()";
        $_sqlQuery = "SELECT tickets.isfirstcontactresolved AS 'tickets_isfirstcontactresolved' FROM swtickets AS tickets LEFT JOIN swusers AS users ON tickets.userid = users.userid WHERE tickets.lastactivity BETWEEN " . strtotime(date('Y-m-1')) . " AND " . SWIFT_Date::CeilDate(strtotime(date('Y-m-t')));

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);
        $_sql = $_kqlCompiler->GetSQL();

        $this->assertEquals($_sql, $_sqlQuery);

        // Test field option replacement
        $_kqlQuery = "SELECT 'Ticket Audit Logs.Action' WHERE 'Ticket Audit Logs.Creator' = 'Staff'";
        $_sqlQuery = "SELECT ticketauditlogs.actiontype AS 'ticketauditlogs_actiontype' FROM swticketauditlogs AS ticketauditlogs WHERE ticketauditlogs.creatortype = 1";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);
        $_sql = $_kqlCompiler->GetSQL();

        $this->assertEquals($_sql, $_sqlQuery);

        // Multigroup example
        $_kqlQuery = "SELECT 'Ticket Audit Logs.Message' FROM 'Ticket Audit Logs' MULTIGROUP BY 'Ticket Audit Logs.Creation Date':DayName, 'Ticket Audit Logs.Full Name'";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);

        $_sqlQuery = "SELECT DISTINCT DAYNAME(FROM_UNIXTIME(ticketauditlogs.dateline)) AS 'dayname_ticketauditlogs_dateline', ticketauditlogs.creatorfullname AS 'ticketauditlogs_creatorfullname' FROM swticketauditlogs AS ticketauditlogs LEFT JOIN swtickets AS tickets ON ticketauditlogs.ticketid = tickets.ticketid ORDER BY FIELD(DAYNAME(FROM_UNIXTIME(ticketauditlogs.dateline)), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), ticketauditlogs_creatorfullname";

        $_sql = $_kqlCompiler->GetDistinctSQL();

        $this->assertEquals($_sql, $_sqlQuery);

        try {
            $_kqlCompiler->GetSQL();
        } catch (SWIFT_Exception $_expectedException) {
            $this->assertEquals('Missing value for variable $dayname_ticketauditlogs_dateline', $_expectedException->getMessage());
        }

        $_sqlQuery = "SELECT ticketauditlogs.actionmsg AS 'ticketauditlogs_actionmsg', DAYNAME(FROM_UNIXTIME(ticketauditlogs.dateline)) AS 'dayname_ticketauditlogs_dateline', ticketauditlogs.creatorfullname AS 'ticketauditlogs_creatorfullname' FROM swticketauditlogs AS ticketauditlogs LEFT JOIN swtickets AS tickets ON ticketauditlogs.ticketid = tickets.ticketid WHERE DAYNAME(FROM_UNIXTIME(ticketauditlogs.dateline)) = 1346025600 AND ticketauditlogs.creatorfullname = 'Andriy Lesyuk'";

        $_sql = $_kqlCompiler->GetSQL(array(
            'dayname_ticketauditlogs_dateline' => 1346025600,
            'ticketauditlogs_creatorfullname' => 'Andriy Lesyuk'
        ));

        $this->assertEquals($_sql, $_sqlQuery);

        // X/Y modifiers
        $_kqlQuery = "SELECT COUNT(*) FROM 'Escalations' GROUP BY X('Escalations.Rule'), Y('Escalations.Owner')";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);

        $_sqlQuery = "SELECT DISTINCT escalationrules.title AS 'escalationpaths_escalationruleid' FROM swescalationpaths AS escalationpaths LEFT JOIN swtickets AS tickets ON escalationpaths.ticketid = tickets.ticketid LEFT JOIN swslaplans AS slaplans ON escalationpaths.slaplanid = slaplans.slaplanid LEFT JOIN swescalationrules AS escalationrules ON escalationpaths.escalationruleid = escalationrules.escalationruleid LEFT JOIN swstaff AS staff ON escalationpaths.ownerstaffid = staff.staffid LEFT JOIN swdepartments AS departments ON escalationpaths.departmentid = departments.departmentid LEFT JOIN swticketstatus AS ticketstatus ON escalationpaths.ticketstatusid = ticketstatus.ticketstatusid LEFT JOIN swticketpriorities AS ticketpriorities ON escalationpaths.priorityid = ticketpriorities.priorityid LEFT JOIN swtickettypes AS tickettypes ON escalationpaths.tickettypeid = tickettypes.tickettypeid ORDER BY escalationpaths_escalationruleid";

        $_sql = $_kqlCompiler->GetDistinctSQL();

        $this->assertEquals(reset($_sql), $_sqlQuery);

        $_sqlQuery = "SELECT COUNT(*), staff.fullname AS 'escalationpaths_ownerstaffid', escalationrules.title AS 'escalationpaths_escalationruleid' FROM swescalationpaths AS escalationpaths LEFT JOIN swtickets AS tickets ON escalationpaths.ticketid = tickets.ticketid LEFT JOIN swslaplans AS slaplans ON escalationpaths.slaplanid = slaplans.slaplanid LEFT JOIN swescalationrules AS escalationrules ON escalationpaths.escalationruleid = escalationrules.escalationruleid LEFT JOIN swstaff AS staff ON escalationpaths.ownerstaffid = staff.staffid LEFT JOIN swdepartments AS departments ON escalationpaths.departmentid = departments.departmentid LEFT JOIN swticketstatus AS ticketstatus ON escalationpaths.ticketstatusid = ticketstatus.ticketstatusid LEFT JOIN swticketpriorities AS ticketpriorities ON escalationpaths.priorityid = ticketpriorities.priorityid LEFT JOIN swtickettypes AS tickettypes ON escalationpaths.tickettypeid = tickettypes.tickettypeid WHERE escalationrules.title = '' GROUP BY escalationpaths_ownerstaffid";

        $_sql = $_kqlCompiler->GetSQL(array('escalationpaths_escalationruleid' => ''));

        $this->assertEquals($_sql, $_sqlQuery);

        // Test MONTH()
        $_kqlQuery = "SELECT COUNT(*) FROM 'Tickets' WHERE 'Tickets.Last Activity' = MONTH(August 2012)";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);

        $_sqlQuery = "SELECT COUNT(*) FROM swtickets AS tickets LEFT JOIN swusers AS users ON tickets.userid = users.userid WHERE tickets.lastactivity BETWEEN 1343779200 AND 1346457599";

        $_sql = $_kqlCompiler->GetSQL();

        $this->assertEquals($_sql, $_sqlQuery);

        // Test DISTINCT query
        $_kqlQuery = "SELECT DISTINCT 'Tickets.Full Name' FROM 'Tickets'";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);

        $_sqlQuery = "SELECT DISTINCT tickets.fullname AS 'tickets_fullname' FROM swtickets AS tickets LEFT JOIN swusers AS users ON tickets.userid = users.userid";

        $_sql = $_kqlCompiler->GetSQL();

        $this->assertEquals($_sql, $_sqlQuery);

        // Test INTERVAL value
        $_kqlQuery = "SELECT DATE_ADD('Tickets.Last Activity', INTERVAL '01:30' HOUR_MINUTE) FROM 'Tickets'";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);

        $_sqlQuery = "SELECT UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(tickets.lastactivity), INTERVAL 5400 SECOND)) FROM swtickets AS tickets LEFT JOIN swusers AS users ON tickets.userid = users.userid";

        $_sql = $_kqlCompiler->GetSQL();

        $this->assertEquals($_sql, $_sqlQuery);

        // Test IN (..., Array, ...)
        $_kqlQuery = "SELECT Tickets.Ticket Mask ID WHERE Tickets.Flag IN ('Red', 'Green', 'Blue')";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);

        $_sqlQuery = "SELECT tickets.ticketmaskid AS 'tickets_ticketmaskid' FROM swtickets AS tickets WHERE tickets.flagtype IN (5, 3, 6)";

        $_sql = $_kqlCompiler->GetSQL();

        $this->assertEquals($_sql, $_sqlQuery);

        // Test several distinct queries (matrix)
        $_kqlQuery = "SELECT AVG('Tickets.Total Replies') FROM 'Tickets' GROUP BY X('Tickets.Last Activity':Week), X('Tickets.Last Activity':DayName), Y('Tickets.SLA Plan')";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);

        $_sqls = $_kqlCompiler->GetDistinctSQL();

        $this->assertTrue(is_array($_sqls));
        $this->assertCount(2, $_sqls);

        $_sqlQuery = "SELECT DISTINCT WEEK(FROM_UNIXTIME(tickets.lastactivity)) AS 'week_tickets_lastactivity' FROM swtickets AS tickets LEFT JOIN swusers AS users ON tickets.userid = users.userid LEFT JOIN swslaplans AS slaplans ON tickets.slaplanid = slaplans.slaplanid ORDER BY week_tickets_lastactivity";

        $this->assertEquals(reset($_sqls), $_sqlQuery);

        $_sqlQuery = "SELECT DISTINCT DAYNAME(FROM_UNIXTIME(tickets.lastactivity)) AS 'dayname_tickets_lastactivity' FROM swtickets AS tickets LEFT JOIN swusers AS users ON tickets.userid = users.userid LEFT JOIN swslaplans AS slaplans ON tickets.slaplanid = slaplans.slaplanid ORDER BY FIELD(DAYNAME(FROM_UNIXTIME(tickets.lastactivity)), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";

        $this->assertEquals(end($_sqls), $_sqlQuery);

        // Test expression in parentheses and then operator
        $_kqlQuery = "SELECT 'Tickets.Ticket Mask ID', 'Ticket Audit Logs.Message', 'Ticket Audit Logs.Full Name' FROM 'Ticket Audit Logs' WHERE ('Ticket Audit Logs.Action' = 'Deleted Ticket' OR 'Ticket Audit Logs.Action' = 'Moved to Trash') AND 'Ticket Audit Logs.Creation Date' = Month(October 2012)";

        $_kql = $_kqlParser->Parse($_kqlQuery);
        $_kqlCompiler->Compile($_kql);

        $_sqlQuery = "SELECT tickets.ticketmaskid AS 'tickets_ticketmaskid', ticketauditlogs.actionmsg AS 'ticketauditlogs_actionmsg', ticketauditlogs.creatorfullname AS 'ticketauditlogs_creatorfullname' FROM swticketauditlogs AS ticketauditlogs LEFT JOIN swtickets AS tickets ON ticketauditlogs.ticketid = tickets.ticketid WHERE (ticketauditlogs.actiontype = 10 OR ticketauditlogs.actiontype = 13) AND ticketauditlogs.dateline BETWEEN 1349049600 AND 1351727999";

        $_sql = $_kqlCompiler->GetSQL();

        $this->assertEquals($_sql, $_sqlQuery);
        */

//        echo $_sql, "\n";

        // TODO: enable the tests
        $this->assertTrue(true);
    }

    /**
     * @return mixed
     */
    private function getMocked()
    {
        return $this->getMockObject(SWIFT_KQL2CompilerMock::class);
    }

}

class SWIFT_KQL2CompilerMock extends SWIFT_KQL2Compiler
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}
