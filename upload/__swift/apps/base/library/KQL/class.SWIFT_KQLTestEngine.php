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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\KQL;

use Base\Library\KQL\SWIFT_KQLAutoComplete;
use Base\Library\KQL\SWIFT_KQLParser;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The KQL Test Engine
 *
 * @author Varun Shoor
 */
class SWIFT_KQLTestEngine extends SWIFT_Library
{

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The Main Test Executor
     *
     * @author Varun Shoor
     * @return array Test Results
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Run()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_results = array();

        $_results[] = $this->RunTest('CheckStartOfStatement');
        $_results[] = $this->RunTest('ParseBasicKQL');

        return $_results;
        $_results[] = $this->RunTest('ParseAutoJoinKQL');
        $_results[] = $this->RunTest('ParseTwoColumnKQL');
        $_results[] = $this->RunTest('ParseThreeColumnKQL');
        $_results[] = $this->RunTest('ParseFunctionKQL');
        $_results[] = $this->RunTest('ParseAsInExpressionKQL');
        $_results[] = $this->RunTest('ParseMathInExpressionKQL');
        $_results[] = $this->RunTest('ParseMathAndAutoJoinInExpressionKQL');
        $_results[] = $this->RunTest('ParseBasicWhereKQL');
        $_results[] = $this->RunTest('ParseBasicINWhereKQL');
        $_results[] = $this->RunTest('ParseBasicNOTINWhereKQL');
        $_results[] = $this->RunTest('ParseBasicLIKEWhereKQL');
        $_results[] = $this->RunTest('ParseBasicNOTLIKEWhereKQL');
        $_results[] = $this->RunTest('ParseBasicJoinLinked1WhereKQL');
        $_results[] = $this->RunTest('ParseBasicJoinLinked2WhereKQL');
        $_results[] = $this->RunTest('ParseBasicJoinEQWhereKQL');
        $_results[] = $this->RunTest('ParseJoinIN1WhereKQL');
        $_results[] = $this->RunTest('ParseJoinIN2WhereKQL');
        $_results[] = $this->RunTest('ParseDate1WhereKQL');
        $_results[] = $this->RunTest('ParseDate2WhereKQL');
        $_results[] = $this->RunTest('ParseGroupBy');
        $_results[] = $this->RunTest('ParseOrderBy');
        $_results[] = $this->RunTest('ParseGroupByExtended1');
        $_results[] = $this->RunTest('ParseGroupByExtended2');

        $_results[] = $this->RunTest('ParsePivot1');

        return $_results;
    }

    /**
     * Execute the test
     *
     * @author Varun Shoor
     * @param string $_testName Runs the given test
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RunTest($_testName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_controllerReflectionClassObject = new ReflectionClass('Base\Library\KQL\SWIFT_KQLTestEngine');
        if (!$_controllerReflectionClassObject instanceof ReflectionClass) {
            throw new SWIFT_Exception('Unable to load Reflection Class: KQLTestengine');
        }

        $_controllerReflectionMethodObject = false;

        try {
            $_controllerReflectionMethodObject = $_controllerReflectionClassObject->getMethod($_testName);
        } catch (Exception $_ExceptionObject) {

        }

        if (!$_controllerReflectionMethodObject instanceof ReflectionMethod) {
            throw new SWIFT_Exception('Test Case Not Found: ' . $_testName);
        }

        $_parameters = array();

        $_resultMessage = '';

        $_testResult = false;

        try {
            $_testResult = call_user_func_array(array($this, $_testName), $_parameters);

        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_resultMessage = $_SWIFT_ExceptionObject->getTraceAsString() . SWIFT_CRLF . $_SWIFT_ExceptionObject->getMessage();
        }

        return array($_testName, $_testResult, $_resultMessage);
    }

    /**
     * Checks the Start of Statement
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CheckStartOfStatement()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_primaryTableName = 'Tickets';
        $_kqlStatementPrefix = "SELECT * FROM 'Tickets'";
        $_baseTableList = array('Tickets');

        $_kqlStatement = '';
        $_textSelection = '';
        $_start = 0;
        $_end = 0;

        ob_start();
        $_SWIFT_KQLAutoCompleteObject = new SWIFT_KQLAutoComplete($_kqlStatement, $_kqlStatementPrefix, $_primaryTableName, $_baseTableList, $_start, $_end, $_textSelection);

        $_result = $_SWIFT_KQLAutoCompleteObject->RetrieveOptions();
        ob_end_clean();

        if ($_result[0] != 0 || $_result[1] != 0) {
            throw new SWIFT_Exception('Invalid Start/End Pointers: ' . $_result[0] . '->' . $_result[1]);
        } elseif (!_is_array($_result[2])) {
            throw new SWIFT_Exception('No Options Available');
        } else {
            if ($_result[2][0][0] != 'Tickets.' || $_result[2][0][1] != '\'Tickets.') {
                throw new SWIFT_Exception('Tickets Option Not Available!');
            }
        }

        return true;
    }

    /**
     * Parse a Basic KQL
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseBasicKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID' FROM 'Tickets';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_SWIFT_KQLParserResultObject = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_statementResult = $_SWIFT_KQLParserResultObject->GetSQL();

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid FROM swtickets AS tickets LEFT JOIN swusers AS users ON (tickets.userid = users.userid AND tickets.creator = '2')";

        if (!isset($_statementResult[0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse a KQL which initiates an Auto Join
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseAutoJoinKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', 'Tickets.Department' FROM 'Tickets';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = 'SELECT tickets.ticketid AS tickets_ticketid, departments.title AS tickets_departmentid FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid)';

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse a KQL which fetches two columns
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseTwoColumnKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', 'Tickets.Department' FROM 'Tickets', 'Departments';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = 'SELECT tickets.ticketid AS tickets_ticketid, departments.title AS tickets_departmentid FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid)';

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse a KQL which fetches three columns
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseThreeColumnKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', 'Tickets.Department', 'Tickets.Status' FROM 'Tickets', 'Departments', 'Ticket Status';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = 'SELECT tickets.ticketid AS tickets_ticketid, departments.title AS tickets_departmentid, ticketstatus.title AS tickets_ticketstatusid FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) LEFT JOIN swticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)';

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse a KQL which has functions
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseFunctionKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT SUM('Tickets.Ticket ID'), COUNT('Tickets.Ticket ID') FROM 'Tickets';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = 'SELECT SUM(tickets.ticketid), COUNT(tickets.ticketid) FROM swtickets AS tickets';

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse 'AS' in Expression
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseAsInExpressionKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT SUM('Tickets.Ticket ID') AS TotalTickets FROM 'Tickets';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != 'SELECT SUM(tickets.ticketid) AS TotalTickets FROM swtickets AS tickets') {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "SELECT SUM(tickets.ticketid) AS TotalTickets FROM swtickets AS tickets"');
        }

        return true;
    }

    /**
     * Parse 'Math' in Expression
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseMathInExpressionKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT (SUM('Tickets.Ticket ID')+1) AS Test1, (COUNT('Tickets.Ticket ID')/5) AS Test2 FROM 'Tickets';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != 'SELECT (SUM(tickets.ticketid) + 1) AS Test1, (COUNT(tickets.ticketid) / 5) AS Test2 FROM swtickets AS tickets') {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "SELECT (SUM(tickets.ticketid) + 1) AS Test1, (COUNT(tickets.ticketid) / 5) AS Test2 FROM swtickets AS tickets"');
        }

        return true;
    }

    /**
     * Parse 'Math' and Auto Join in Expression
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseMathAndAutoJoinInExpressionKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT (SUM('Tickets.Ticket ID')+1) AS Test1, (COUNT('Tickets.Ticket ID')/5) AS Test2, 'Tickets.Department' AS Test3, 'Tickets.Status' AS Test4 FROM 'Tickets';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != 'SELECT (SUM(tickets.ticketid) + 1) AS Test1, (COUNT(tickets.ticketid) / 5) AS Test2, departments.title AS Test3, ticketstatus.title AS Test4 FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) LEFT JOIN swticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)') {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "SELECT (SUM(tickets.ticketid) + 1) AS Test1, (COUNT(tickets.ticketid) / 5) AS Test2, departments.title AS Test3, ticketstatus.title AS Test4 FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) LEFT JOIN swticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid)"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE Clause
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseBasicWhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID' FROM 'Tickets' WHERE 'Tickets.Ticket ID' = '1';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid FROM swtickets AS tickets WHERE tickets.ticketid = '1'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE Clause having IN
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseBasicINWhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID' FROM 'Tickets' WHERE 'Tickets.Ticket ID' IN(1, '2') AND 'Tickets.Ticket ID' = '1';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid FROM swtickets AS tickets WHERE tickets.ticketid IN(1, '2') AND tickets.ticketid = '1'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE Clause having NOT IN
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseBasicNOTINWhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID' FROM 'Tickets' WHERE 'Tickets.Ticket ID' NOT IN(1, '2') AND 'Tickets.Ticket ID' = '1';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid FROM swtickets AS tickets WHERE tickets.ticketid NOT IN(1, '2') AND tickets.ticketid = '1'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE Clause having LIKE
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseBasicLIKEWhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID' FROM 'Tickets' WHERE 'Tickets.Ticket ID' LIKE '%1%';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid FROM swtickets AS tickets WHERE tickets.ticketid LIKE '%1%'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE Clause having NOT LIKE
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseBasicNOTLIKEWhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID' FROM 'Tickets' WHERE 'Tickets.Ticket ID' NOT LIKE '%1%';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid FROM swtickets AS tickets WHERE tickets.ticketid NOT LIKE '%1%'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with JOIN WHERE clause having linked call
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseBasicJoinLinked1WhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', 'Tickets.Department' FROM 'Tickets', 'Departments' WHERE 'Tickets.Department' = 'General' AND 'Tickets.Status' = 'in progress';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid, departments.title AS tickets_departmentid FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) WHERE tickets.departmentid = '2' AND tickets.ticketstatusid = '2'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with JOIN WHERE clause having linked call
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseBasicJoinLinked2WhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', 'Tickets.Department' FROM 'Tickets', 'Departments' WHERE 'Tickets.Department' LIKE '%General%' AND 'Tickets.Status' NOT LIKE '%in progress%';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid, departments.title AS tickets_departmentid FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) WHERE tickets.departmentid LIKE '2' AND tickets.ticketstatusid NOT LIKE '2'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE clause having linked call
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseBasicJoinEQWhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', 'Tickets.Department' FROM 'Tickets' WHERE 'Tickets.Department' = 'general';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid, departments.title AS tickets_departmentid FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) WHERE tickets.departmentid = '2'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE clause having linked call using IN
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseJoinIN1WhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', 'Tickets.Department' FROM 'Tickets' WHERE 'Tickets.Department' IN('general');";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid, departments.title AS tickets_departmentid FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) WHERE tickets.departmentid IN('2')";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE clause having linked call using IN
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseJoinIN2WhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', 'Tickets.Department' FROM 'Tickets' WHERE 'Tickets.Department' IN('general') AND 'Tickets.Status' NOT IN('Open', 'Closed');";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid, departments.title AS tickets_departmentid FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) WHERE tickets.departmentid IN('2') AND tickets.ticketstatusid NOT IN('1', '3')";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE clause having a date function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseDate1WhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID' FROM 'Tickets' WHERE 'Tickets.Creation Date' > 'Yesterday()';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid FROM swtickets AS tickets WHERE tickets.dateline > '" . strtotime('yesterday') . "'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a basic KQL statement with WHERE clause having a date function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseDate2WhereKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID' FROM 'Tickets' WHERE 'Tickets.Creation Date' > Today();";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid FROM swtickets AS tickets WHERE tickets.dateline > '" . strtotime('today') . "'";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a GROUP BY statement
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseGroupBy()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', COUNT('Tickets.Ticket ID') AS ticketidcount FROM 'Tickets' WHERE 'Tickets.Department' = 'General' GROUP BY 'Tickets.Department', 'Tickets.Status';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid, COUNT(tickets.ticketid) AS ticketidcount FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) LEFT JOIN swticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid) WHERE tickets.departmentid = '2' GROUP BY departments.title, ticketstatus.title";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a ORDER BY statement
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseOrderBy()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Ticket ID', COUNT('Tickets.Ticket ID') AS ticketidcount FROM 'Tickets' WHERE 'Tickets.Department' = 'General' ORDER BY 'Tickets.Creation Date' DESC, 'Tickets.Department' ASC;";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT tickets.ticketid AS tickets_ticketid, COUNT(tickets.ticketid) AS ticketidcount FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) WHERE tickets.departmentid = '2' ORDER BY tickets.dateline DESC, departments.title ASC";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out an extended GROUP BY statement
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseGroupByExtended1()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Department', COUNT('Tickets.Ticket ID') AS ticketcount FROM 'Tickets' GROUP BY 'Tickets.Department', 'Tickets.Creation Date':Day;";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        $_finalStatement = "SELECT departments.title AS tickets_departmentid, COUNT(tickets.ticketid) AS ticketcount, DAY(FROM_UNIXTIME(tickets.dateline)) AS day_tickets_dateline FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) GROUP BY departments.title, day_tickets_dateline";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out an extended GROUP BY statement
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseGroupByExtended2()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Department', COUNT('Tickets.Ticket ID') AS ticketcount FROM 'Tickets' GROUP BY 'Tickets.Department', 'Tickets.duetime':Minute;";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        print_r($_statementResult);

        $_finalStatement = "SELECT departments.title AS tickets_departmentid, COUNT(tickets.ticketid) AS ticketcount, (tickets.duetime/60) AS minute_tickets_duetime FROM swtickets AS tickets LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) GROUP BY departments.title, minute_tickets_duetime";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }

    /**
     * Parse out a pivot table
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParsePivot1()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = "SELECT 'Tickets.Department', SUM(IF('Tickets.Status' = 'Open', 1, 0)) AS Open, SUM(IF('Tickets.Status' = 'In Progress', 1, 0)) AS InProgress, SUM(IF('Tickets.Status' = 'Closed', 1, 0)) AS Closed, (SUM(IF('Tickets.Status' = 'Open', 1, 0)) + SUM(IF('Tickets.Status' = 'In Progress', 1, 0)) + SUM(IF('Tickets.Status' = 'Closed', 1, 0))) AS total FROM 'Tickets' GROUP BY 'Tickets.Department';";

        $_SWIFT_KQLParserObject = new SWIFT_KQLParser();
        $_statementResult = $_SWIFT_KQLParserObject->ParseStatement($_kqlStatement);

        print_r($_statementResult);
        $_finalStatement = "SELECT departments.title AS tickets_departmentid, SUM(IF(ticketstatus.title='Open', 1, 0)) AS Open, SUM(IF(ticketstatus.title='In Progress', 1, 0)) AS InProgress, SUM(IF(ticketstatus.title='Closed', 1, 0)) AS Closed, (SUM(IF(ticketstatus.title='Open', 1, 0)) + SUM(IF(ticketstatus.title='In Progress', 1, 0)) + SUM(IF(ticketstatus.title='Closed', 1, 0))) AS total FROM swtickets AS tickets LEFT JOIN swticketstatus AS ticketstatus ON (tickets.ticketstatusid = ticketstatus.ticketstatusid) LEFT JOIN swdepartments AS departments ON (tickets.departmentid = departments.departmentid) GROUP BY departments.title";

        if (!isset($_statementResult[0][0])) {
            throw new SWIFT_Exception('No Parsed KQL Statement Received!');
        } elseif (count($_statementResult[0]) > 1) {
            throw new SWIFT_Exception('Invalid KQL Statement Count Received.. Should be ONE');
        } elseif ($_statementResult[0][0] != $_finalStatement) {
            throw new SWIFT_Exception('KQL Result Mistmatch, received: "' . $_statementResult[0][0] . '", should have been: "' . $_finalStatement . '"');
        }

        return true;
    }
}

?>
