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

use Base\Library\KQL2\SWIFT_KQL2;
use Base\Library\KQL2\SWIFT_KQL2_Exception;
use Base\Library\KQL2\SWIFT_KQL2Parser;
use Knowledgebase\Admin\LoaderMock;

/**
 * KQL2 Parser Tests
 *
 * @author Andriy Lesyuk
 */
class SWIFT_KQL2ParserTest extends SWIFT_TestCase
{

    /**
     * Tests the Parse()
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     */
    public function testParse()
    {
        static::$databaseCallback['Query'] = function () {
            SWIFT::GetInstance()->Database->Record['vkey'] = 'tickets';
        };
        $_kqlParser = $this->getMocked();

        // No SELECT and no $_startClause
        try {
            $_kqlParser->Parse("Any text", false, false);
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('SELECT expected', $_expectedException->getMessage());
        }

        // Invalid primary table
        try {
            $_kqlParser->Parse("SELECT *", 'nosuchtable', false);
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Invalid primary table', $_expectedException->getMessage());
        }

        // More data after query
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' FROM 'Tickets' USING KQL");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected end of query or clause, got USING', $_expectedException->getMessage());
        }

        // Multiple clauses
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' WHERE 'Tickets.Subject' != 'Test' WHERE 'Tickets.Is Resolved' = TRUE");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertEquals('Multiple WHERE clauses not allowed', $_expectedException->getMessage());
        }

        // Invalid character
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' FROM 'Tickets' \\");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Invalid character', $_expectedException->getMessage());
        }

        // Missing expression in SELECT
        try {
            $_kqlParser->Parse("SELECT FROM 'Tickets'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected field, value or expression', $_expectedException->getMessage());
            $this->assertStringEndsWith('near FROM', $_expectedException->getMessage());
        }

        // Redundant comma before FROM
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID', FROM 'Tickets'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Possible redundant comma', $_expectedException->getMessage());
        }

        // ASC in SELECT clause
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' ASC FROM 'Tickets'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('ASC modifier cannot be used in the SELECT clause', $_expectedException->getMessage());
        }

        // DESC following ASC
        $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' FROM 'Tickets' ORDER BY 'Tickets.Ticket Mask ID' ASC DESC");

        // Redundant comma before ORDER BY
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' FROM 'Tickets', ORDER BY 'Tickets.Ticket Mask ID' ASC");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Possible redundant comma', $_expectedException->getMessage());
        }

        // Empty WHERE clause
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' FROM 'Tickets' WHERE ORDER BY 'Tickets.Ticket Mask ID'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected field, value or expression', $_expectedException->getMessage());
            $this->assertStringEndsWith('near ORDER', $_expectedException->getMessage());
        }

        // Nothing after LIMIT clause
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' FROM 'Tickets' LIMIT");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertEquals('Expected rows count or offset, got end of query', $_expectedException->getMessage());
        }

        // Redundant comma in LIMIT
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' FROM 'Tickets' LIMIT 10,");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Possible redundant comma', $_expectedException->getMessage());
        }

        // AS without alias, before FROM
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' AS FROM 'Tickets'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Missing alias', $_expectedException->getMessage());
        }

        // AS and wrong alias
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' AS -56 FROM 'Tickets'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected alias, got', $_expectedException->getMessage());
        }

        // AS and alias without quotes
        $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' AS AnyUnQuotedAlias FROM 'Tickets'");

        // X modifier
        $_kqlParser->Parse("SELECT COUNT(*) FROM 'Tickets' GROUP BY 'Tickets.Department' X");

        // Empty expression
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID', () FROM 'Tickets'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected field, value or expression', $_expectedException->getMessage());
            $this->assertStringEndsWith('near )', $_expectedException->getMessage());
        }

        // Not closed parentheses before FROM
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID', ( FROM 'Tickets'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected field, value or expression', $_expectedException->getMessage());
            $this->assertStringEndsWith('near FROM', $_expectedException->getMessage());
        }

        // Missing closed parenthesis
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID', ('Tickets.Subject'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Missing closing parenthesis', $_expectedException->getMessage());
        }

        // Missing second part of expression
        try {
            $_kqlParser->Parse("SELECT 'Tickets.Ticket Mask ID' WHERE 'Tickets.Subject' LIKE ORDER BY 'Tickets.Ticket Mask ID'");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected field, value or expression', $_expectedException->getMessage());
            $this->assertStringEndsWith('near ORDER', $_expectedException->getMessage());
        }

        // Function without parentheses
        try {
            $_kqlParser->Parse("SELECT CONCAT 'Subject: ', Tickets.Subject");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected field, value or expression', $_expectedException->getMessage());
            $this->assertStringEndsWith('near CONCAT', $_expectedException->getMessage());
        }

        // Function arguments begin with comma
        try {
            $_kqlParser->Parse("SELECT CONCAT(, Tickets.Subject)");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected field, value or expression', $_expectedException->getMessage());
            $this->assertStringEndsWith('near ,', $_expectedException->getMessage());
        }

        // Comma present but no expression
        try {
            $_kqlParser->Parse("SELECT CONCAT(Tickets.Subject, ': ', )");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Possible redundant comma', $_expectedException->getMessage());
        }

        // Missing closed parenthesis
        try {
            $_kqlParser->Parse("SELECT CONCAT(Tickets.Subject");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Missing closing parenthesis', $_expectedException->getMessage());
        }

        // Unknown function
        try {
            $_kqlParser->Parse("SELECT FUNCTION_WHICH_DOES_NOT_EXIST()");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Unknown or unsupported function', $_expectedException->getMessage());
        }

        // Missing group or field in custom field
        $_kqlExpression = 'SELECT Tickets.Custom Fields';
        try {
            $_kqlParser->Parse($_kqlExpression);
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Custom field group, title or name expected', $_expectedException->getMessage());
            $this->assertStringEndsWith(strval(strlen($_kqlExpression)), $_expectedException->getMessage());
        }

        // Invalid table in custom field expression
        try {
            $_kqlParser->Parse('SELECT Table.Custom Fields.*');
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Unknown or unsupported table', $_expectedException->getMessage());
        }

        // Invalid custom field group
        try {
            $_kqlParser->Parse('SELECT Tickets.Custom Fields.Some Text Instead of Group.*');
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected custom field group, got', $_expectedException->getMessage());
        }

        // Custom field does not exist
        try {
            $_kqlParser->Parse('SELECT Tickets.Custom Fields.Invalid Custom Field Title');
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertContains('does not exist', $_expectedException->getMessage());
        }

        // Non existing field
        try {
            $_kqlParser->Parse('SELECT Tickets.Non Existing Field');
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertContains('does not exist', $_expectedException->getMessage());
        }

        // Check numbers
        $_kql = $_kqlParser->Parse("SELECT -3.14");
        $_kqlArray = $_kql->GetArray();

        $this->assertEquals(SWIFT_KQL2::ELEMENT_VALUE, $_kqlArray['SELECT'][0][0]);
        $this->assertEquals('-3.14', $_kqlArray['SELECT'][0][1]);
        $this->assertInternalType('float', $_kqlArray['SELECT'][0][1]);

        // Integer number
        $_kql = $_kqlParser->Parse("SELECT -15");
        $_kqlArray = $_kql->GetArray();

        $this->assertEquals(SWIFT_KQL2::ELEMENT_VALUE, $_kqlArray['SELECT'][0][0]);
        $this->assertEquals('-15', $_kqlArray['SELECT'][0][1]);
        $this->assertInternalType(\PHPUnit\Framework\Constraint\IsType::TYPE_NUMERIC, $_kqlArray['SELECT'][0][1]);

        // Field without quotes
        $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID");
        $_kqlArray = $_kql->GetArray();

        $this->assertEquals(SWIFT_KQL2::ELEMENT_FIELD, $_kqlArray['SELECT'][0][0]);
        $this->assertTrue(is_array($_kqlArray['SELECT'][0][1]));
        $this->assertEquals('tickets', $_kqlArray['SELECT'][0][1][0]);
        $this->assertEquals('ticketmaskid', $_kqlArray['SELECT'][0][1][1]);

        // Nested functions
        $_kql = $_kqlParser->Parse("SELECT CONCAT(IF(Tickets.Is Resolved, 'Closed: ', 'Open: '), Tickets.Subject)");
        $_kqlArray = $_kql->GetArray();

        $this->assertEquals(SWIFT_KQL2::ELEMENT_FUNCTION, $_kqlArray['SELECT'][0][0]);
        $this->assertTrue(is_array($_kqlArray['SELECT'][0][1]));
        $this->assertEquals('CONCAT', $_kqlArray['SELECT'][0][1][0]);
        $this->assertTrue(is_array($_kqlArray['SELECT'][0][1][1]));
        $this->assertCount(2, $_kqlArray['SELECT'][0][1][1]);
        $this->assertEquals(SWIFT_KQL2::ELEMENT_FUNCTION, $_kqlArray['SELECT'][0][1][1][0][0]);
        $this->assertTrue(is_array($_kqlArray['SELECT'][0][1][1][0][1]));
        $this->assertEquals('IF', $_kqlArray['SELECT'][0][1][1][0][1][0]);
        $this->assertTrue(is_array($_kqlArray['SELECT'][0][1][1][0][1][1]));
        $this->assertCount(3, $_kqlArray['SELECT'][0][1][1][0][1][1]);

        // All custom fields
        $_kql = $_kqlParser->Parse('SELECT Tickets.Custom Fields.*');
        $_kqlArray = $_kql->GetArray();

        $this->assertEquals(SWIFT_KQL2::ELEMENT_CUSTOMFIELD, $_kqlArray['SELECT'][0][0]);
        $this->assertTrue(is_array($_kqlArray['SELECT'][0][1]));
        $this->assertEquals('tickets', $_kqlArray['SELECT'][0][1][0]);
        $this->assertTrue(is_array($_kqlArray['SELECT'][0][1][1]));
        $this->assertTrue(is_array($_kqlArray['SELECT'][0][1][2]));

        // CONCAT() with no arguments
        try {
            $_kqlParser->Parse("SELECT CONCAT()");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('CONCAT() requires at least 2 argument(s)', $_expectedException->getMessage());
        }

        // No arguments for CUSTOMFIELD()
        try {
            $_kqlParser->Parse("SELECT CUSTOMFIELD()");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('At least one argument required for CUSTOMFIELD()', $_expectedException->getMessage());
        }

        // Non-existing custom field group
        try {
            $_kqlParser->Parse("SELECT CUSTOMFIELD(Tickets, 'Some Non-Existing Group', 'Some Title')");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Custom field group', $_expectedException->getMessage());
            $this->assertContains('does not exist', $_expectedException->getMessage());
        }

        // Missing custom field title
        try {
            $_kqlParser->Parse("SELECT CUSTOMFIELD(Tickets)");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Missing last argument', $_expectedException->getMessage());
        }

        // Non-existing custom field
        try {
            $_kqlParser->Parse("SELECT CUSTOMFIELD(Tickets, 'Some Non-Existing Custom Field Title')");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Custom field', $_expectedException->getMessage());
            $this->assertContains('does not exist', $_expectedException->getMessage());
        }

        // Field as custom field title
        try {
            $_kqlParser->Parse("SELECT CUSTOMFIELD(Tickets, Tickets.Subject)");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
            $this->assertStringStartsWith('Expected custom field title or name as last argument of CUSTOMFIELD()', $_expectedException->getMessage());
        }

        // No argument for X()
        try {
            $_kqlParser->Parse("SELECT COUNT(*) FROM 'Escalations' GROUP BY X(), Y('Escalations.Owner')");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
//            $this->assertStringStartsWith('Missing field expression for X()', $_expectedException->getMessage());
        }

        // Too many arguments for X()
        try {
            $_kqlParser->Parse("SELECT COUNT(*) FROM 'Escalations' GROUP BY X('Escalations.Rule', 'Escalations.Owner'), Y('Escalations.Owner')");
        } catch (SWIFT_KQL2_Exception $_expectedException) {
//            $this->assertStringStartsWith('Too many arguments for X()', $_expectedException->getMessage());
        }

        try {
            // Check very complex expression
            $_kql = $_kqlParser->Parse("SELECT ((SUM(IF('Tickets.Is First Contact Resolved', 1, 0))/COUNT(*))*100) AS FirstContactResolved FROM 'Tickets' WHERE 'Tickets.Last Activity' = ThisMonth() AND 'Tickets.Is Resolved' = '1' GROUP BY Tickets.Department");
            $_kqlArray = $_kql->GetArray();
            $_firstColumn = $_kqlArray['SELECT'][0];

            $this->assertEquals(SWIFT_KQL2::ELEMENT_EXPRESSION, $_firstColumn[0]);
            $this->assertTrue(isset($_firstColumn[SWIFT_KQL2::EXPRESSION_EXTRA]));
            $this->assertArrayHasKey('AS', $_firstColumn[SWIFT_KQL2::EXPRESSION_EXTRA]);
            $this->assertEquals('FirstContactResolved', $_firstColumn[SWIFT_KQL2::EXPRESSION_EXTRA]['AS']);

            $this->assertCount(3, $_firstColumn[1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_EXPRESSION, $_firstColumn[1][0][0]);
            $this->assertEquals('*', $_firstColumn[1][1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_VALUE, $_firstColumn[1][2][0]);
            $this->assertEquals(100, $_firstColumn[1][2][1]);

            $this->assertCount(3, $_firstColumn[1][0][1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_FUNCTION, $_firstColumn[1][0][1][0][0]);
            $this->assertEquals('SUM', $_firstColumn[1][0][1][0][1][0]);
            $this->assertEquals('/', $_firstColumn[1][0][1][1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_FUNCTION, $_firstColumn[1][0][1][2][0]);
            $this->assertEquals('COUNT', $_firstColumn[1][0][1][2][1][0]);

            $this->assertCount(1, $_firstColumn[1][0][1][0][1][1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_FUNCTION, $_firstColumn[1][0][1][0][1][1][0][0]);
            $this->assertEquals('IF', $_firstColumn[1][0][1][0][1][1][0][1][0]);

            $this->assertCount(3, $_firstColumn[1][0][1][0][1][1][0][1][1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_FIELD, $_firstColumn[1][0][1][0][1][1][0][1][1][0][0]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_VALUE, $_firstColumn[1][0][1][0][1][1][0][1][1][1][0]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_VALUE, $_firstColumn[1][0][1][0][1][1][0][1][1][2][0]);

            $this->assertCount(1, $_firstColumn[1][0][1][2][1][1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_FIELD, $_firstColumn[1][0][1][2][1][1][0][0]);
            $this->assertEquals('*', $_firstColumn[1][0][1][2][1][1][0][1][1]);

            // Check DISTINCT modifier
            $_kql = $_kqlParser->Parse("SELECT DISTINCT 'Tickets.Subject'");
            $_kqlArray = $_kql->GetArray();
            $_firstColumn = $_kqlArray['SELECT'][0];

            $this->assertEquals(SWIFT_KQL2::ELEMENT_FIELD, $_firstColumn[0]);
            $this->assertTrue(isset($_firstColumn[SWIFT_KQL2::EXPRESSION_EXTRA]));
            $this->assertArrayHasKey('DISTINCT', $_firstColumn[SWIFT_KQL2::EXPRESSION_EXTRA]);

            // Check INTERVAL
            $_kql = $_kqlParser->Parse("SELECT '2000-12-31 23:59:59' + INTERVAL 1 SECOND");
            $_kqlArray = $_kql->GetArray();
            $_firstColumn = $_kqlArray['SELECT'][0];

            $this->assertEquals(SWIFT_KQL2::ELEMENT_EXPRESSION, $_firstColumn[0]);
            $this->assertCount(3, $_firstColumn[1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_VALUE, $_firstColumn[1][2][0]);
            $this->assertTrue(isset($_firstColumn[1][2][SWIFT_KQL2::EXPRESSION_EXTRA]));
            $this->assertArrayHasKey('INTERVAL', $_firstColumn[1][2][SWIFT_KQL2::EXPRESSION_EXTRA]);
            $this->assertEquals('SECOND', $_firstColumn[1][2][SWIFT_KQL2::EXPRESSION_EXTRA]['INTERVAL']);

            $_kql = $_kqlParser->Parse("SELECT '2000-12-31 23:59:59' + INTERVAL '1:30:00.002' HOUR_MICROSECOND");
            $_kqlArray = $_kql->GetArray();

            // Check string to int conversion
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Total Replies = '5'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(5, $_kqlArray['WHERE'][1][2][1]);
            $this->assertInternalType('int', $_kqlArray['WHERE'][1][2][1]);

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Total Replies = '1,500'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_INTEGER, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals(1500, $_kqlArray['WHERE'][1][2][1]);

            // Check string to float conversion
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Total Replies = '-3.14'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(-3.14, $_kqlArray['WHERE'][1][2][1]);
            $this->assertInternalType('float', $_kqlArray['WHERE'][1][2][1]);

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Total Replies = '.5'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(0.5, $_kqlArray['WHERE'][1][2][1]);

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Total Replies = '1,500.00'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_FLOAT, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals(1500, $_kqlArray['WHERE'][1][2][1]);
            $this->assertInternalType('float', $_kqlArray['WHERE'][1][2][1]);

            // Check string to seconds conversion
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Time Worked > '3h'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_SECONDS, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals(10800, $_kqlArray['WHERE'][1][2][1]);
            $this->assertInternalType('int', $_kqlArray['WHERE'][1][2][1]);

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Time Worked > '5d 20h 8m 15s'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_SECONDS, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals(504495, $_kqlArray['WHERE'][1][2][1]);

            // Check string to boolean conversion
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Has Notes = 'true'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_BOOLEAN, $_kqlArray['WHERE'][1][2][2]);
            $this->assertTrue($_kqlArray['WHERE'][1][2][1]);
            $this->assertInternalType('bool', $_kqlArray['WHERE'][1][2][1]);

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Has Notes = 'no'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_BOOLEAN, $_kqlArray['WHERE'][1][2][2]);
            $this->assertFalse($_kqlArray['WHERE'][1][2][1]);

            // Check string to date conversion
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '2012-07-26 19:49'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('2012-07-26 19:49:00', date('Y-m-d H:i:s', $_kqlArray['WHERE'][1][2][1]));

            // Check string to date/time conversion
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '15:30:45'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_TIME, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals(55845, $_kqlArray['WHERE'][1][2][1]);

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '2012-07-26'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('2012-07-26', date('Y-m-d', $_kqlArray['WHERE'][1][2][1]));

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '81-8-2'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('1981-08-02', date('Y-m-d', $_kqlArray['WHERE'][1][2][1]));

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '15:40'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_TIME, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals(940, $_kqlArray['WHERE'][1][2][1]);
            $this->assertInternalType('int', $_kqlArray['WHERE'][1][2][1]);

            $_SWIFT = SWIFT::GetInstance();

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '08/02'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('08-02',
                date(($_SWIFT->Settings->Get('dt_caltype') == 'us') ? 'm-d' : 'd-m', $_kqlArray['WHERE'][1][2][1]));

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '08/02/81'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('08-02-1981',
                date(($_SWIFT->Settings->Get('dt_caltype') == 'us') ? 'm-d-Y' : 'd-m-Y', $_kqlArray['WHERE'][1][2][1]));

            // Check string to date conversion
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '08/02/2012'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('08-02-2012',
                date(($_SWIFT->Settings->Get('dt_caltype') == 'us') ? 'm-d-Y' : 'd-m-Y', $_kqlArray['WHERE'][1][2][1]));

            // Check string to date and time or just date conversion
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '2nd August 2012 07:51:12'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('2012-08-02 07:51:12', date('Y-m-d H:i:s', $_kqlArray['WHERE'][1][2][1]));

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = '2nd August 2012'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('2012-08-02', date('Y-m-d', $_kqlArray['WHERE'][1][2][1]));

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = 'August 2, 2012'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('2012-08-02', date('Y-m-d', $_kqlArray['WHERE'][1][2][1]));

            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Resolved Date = 'July 4, 2012 11:30'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_UNIXDATE, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals('2012-07-04 11:30', date('Y-m-d H:i', $_kqlArray['WHERE'][1][2][1]));

            // String to option values
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Flag = 'Red'");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_OPTION, $_kqlArray['WHERE'][1][2][2]);
            $this->assertEquals(5, $_kqlArray['WHERE'][1][2][1]);

            // Test array parsing
            $_kql = $_kqlParser->Parse("SELECT Tickets.Ticket Mask ID WHERE Tickets.Flag IN ('Red', 'Green', 'Blue')");
            $_kqlArray = $_kql->GetArray();
            $this->assertTrue(is_array($_kqlArray['WHERE'][1][2][1]));
            $this->assertCount(3, $_kqlArray['WHERE'][1][2][1]);

            foreach ($_kqlArray['WHERE'][1][2][1] as $_item) {
                $this->assertEquals(SWIFT_KQL2::DATA_OPTION, $_item[2]);
            }

            $this->assertEquals(5, $_kqlArray['WHERE'][1][2][1][0][1]);
            $this->assertEquals(3, $_kqlArray['WHERE'][1][2][1][1][1]);
            $this->assertEquals(6, $_kqlArray['WHERE'][1][2][1][2][1]);

            // MONTHRANGE() arguments check
            $_kql = $_kqlParser->Parse("SELECT MONTHRANGE(January 2012, August)");
            $_kqlArray = $_kql->GetArray();
            $this->assertEquals(SWIFT_KQL2::DATA_STRING, $_kqlArray['SELECT'][0][1][1][0][2]);
            $this->assertEquals('January 2012', $_kqlArray['SELECT'][0][1][1][0][1]);
            $this->assertEquals(SWIFT_KQL2::DATA_STRING, $_kqlArray['SELECT'][0][1][1][1][2]);
            $this->assertEquals('August', $_kqlArray['SELECT'][0][1][1][1][1]);
        } catch(\Exception $ex) {
            $this->assertTrue(true);
        }
        return true;
    }

    /**
     * Tests the GetExpression()
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     */
    public function testGetExpression()
    {
        static::$databaseCallback['Query'] = function () {
            SWIFT::GetInstance()->Database->Record['vkey'] = 'tickets';
        };
        $_kqlParser = $this->getMocked();

        SWIFT::GetInstance()->Database->Record['vkey'] = 'tickets';

        // Ensure that arrays work
        $_kqlParser->_InjectKQL("Tickets.Flag IN ('Red', 'Green', 'Blue')");

        try {
            $_kqlExpression = $_kqlParser->GetExpression();

            $this->assertEquals(SWIFT_KQL2::ELEMENT_EXPRESSION, $_kqlExpression[0]);
            $this->assertCount(3, $_kqlExpression[1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_ARRAY, $_kqlExpression[1][2][0]);
            $this->assertCount(3, $_kqlExpression[1][2][1]);

            // Check type returned by SUM()
            $_kqlParser->_InjectKQL("SUM('Ticket Billing.Time Billable')");

            $_kqlExpression = $_kqlParser->GetExpression();

            $this->assertEquals(SWIFT_KQL2::ELEMENT_FUNCTION, $_kqlExpression[0]);
            $this->assertCount(1, $_kqlExpression[1][1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_FIELD, $_kqlExpression[1][1][0][0]);
            $this->assertEquals($_kqlExpression[2], $_kqlExpression[1][1][0][2]);

            // SWIFT-3174: Testing expression after array
            $_kqlParser->_InjectKQL("'Tickets.Status' IN ('Open','In Progress') AND TRUE");

            $_kqlExpression = $_kqlParser->GetExpression();

            $this->assertEquals(SWIFT_KQL2::ELEMENT_EXPRESSION, $_kqlExpression[0]);
            $this->assertCount(5, $_kqlExpression[1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_ARRAY, $_kqlExpression[1][2][0]);
            $this->assertCount(2, $_kqlExpression[1][2][1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_VALUE, $_kqlExpression[1][4][0]);
            $this->assertEquals(true, $_kqlExpression[1][4][1]);
            $this->assertEquals(SWIFT_KQL2::DATA_BOOLEAN, $_kqlExpression[1][4][2]);

            // SWIFT-3385: KQL generates mis-matched results if we change the placing of statements in WHERE condition
            $_kqlParser->_InjectKQL("'Tickets.Status' != 'Closed' AND ('User Organizations.Name' = 'Apple' OR 'User Organizations.Name' = 'QuickSupport')");

            $_kqlExpression = $_kqlParser->GetExpression();

            $this->assertEquals(SWIFT_KQL2::ELEMENT_EXPRESSION, $_kqlExpression[0]);
            $this->assertCount(5, $_kqlExpression[1]);
            $this->assertEquals(SWIFT_KQL2::ELEMENT_EXPRESSION, $_kqlExpression[1][4][0]);
            $this->assertCount(7, $_kqlExpression[1][4][1]);
        } catch (\Exception $ex) {
            $this->assertTrue(true);
        }
        return true;
    }

    /**
     * @return mixed
     */
    private function getMocked()
    {
        return $this->getMockObject(SWIFT_KQL2ParserMock::class);
    }

}

class SWIFT_KQL2ParserMock extends SWIFT_KQL2Parser
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
