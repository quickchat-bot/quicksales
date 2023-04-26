<?php
/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-QuickSupport Singapore Pte. Ltd.h Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

use Base\Library\KQL2\SWIFT_KQL2Lexer;

/**
 * KQL2 Lexer Tests
 *
 * @author Andriy Lesyuk
 */
class SWIFT_KQL2LexerTest extends SWIFT_TestCase
{
    /**
     * Tests the NextToken()
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     */
    public function testNextToken()
    {

        /**
         * Check Numbers in Format -X.XX
         */

        $_sqlQuery = "SELECT -3.14 FROM PI";
        $_checkList = explode(' ', $_sqlQuery);

        $SWIFT_KQL2Lexer = new SWIFT_KQL2Lexer($_sqlQuery);

        for ($_i = 0; $_i < count($_checkList); $_i++) {
            if ($_i > 0) {
                $this->assertEquals(' ', $SWIFT_KQL2Lexer->NextToken());
            }

            $this->assertEquals($_checkList[$_i], $SWIFT_KQL2Lexer->NextToken());
        }

        /**
         * Testing Apostrophes and Slashes Inside the String
         */

        $_sqlQuery = "SELECT '\\\"QuickSupport\'s products\\\"' FROM QuickSupport";
        $_checkList = array('SELECT', ' ', '"QuickSupport\'s products"', ' ', 'FROM', ' ', 'QuickSupport');

        $SWIFT_KQL2Lexer = new SWIFT_KQL2Lexer($_sqlQuery);

        foreach ($_checkList as $_checkToken) {
            $this->assertEquals($_checkToken, $SWIFT_KQL2Lexer->NextToken());

            if ($_checkToken == '"QuickSupport\'s products"') {
                $this->assertEquals("'\\\"QuickSupport\'s products\\\"'", $SWIFT_KQL2Lexer->GetTokenString());
            }
        }

        /**
         * Testing Functions Nesting Levels
         */

        $_sqlQuery = "SELECT CONCAT('DIFFERENCE: ', MAX('Tickets.First Response Time') - MIN('Tickets.First Response Time'))";
        $_checkList = array('SELECT', 'CONCAT', '(', 'DIFFERENCE: ', ',', 'MAX', '(', 'Tickets.First Response Time', ')',  '-', 'MIN', '(', 'Tickets.First Response Time', ')', ')');

        $this->TestAgainstCheckList($_sqlQuery, $_checkList);

        /**
         * Testing Minimal Spaces
         */

        $_sqlQuery = "SELECT'Tickets.Response Time'>-5AS Result FROM'Tickets'";
        $_checkList = array('SELECT', 'Tickets.Response Time', '>', '-5', 'AS', 'Result', 'FROM', 'Tickets');

        $this->TestAgainstCheckList($_sqlQuery, $_checkList);

        /**
         * Testing Minus Before Alpha-Numeric
         */

        $_sqlQuery = "SELECT 50-COUNT(*)";
        $_checkList = array('SELECT', '50', '-', 'COUNT', '(', '*', ')');

        $this->TestAgainstCheckList($_sqlQuery, $_checkList);

        /**
         * Testing Non-Quoted Field
         */

        $_sqlQuery = "SELECT Tickets.Subject FROM Tickets";
        $_checkList = array('SELECT', 'Tickets.Subject', 'FROM', 'Tickets');

        $this->TestAgainstCheckList($_sqlQuery, $_checkList);

        /**
         * Testing Dot Before Alpha-Numeric
         */

        $_sqlQuery = "SELECT .NET";
        $_checkList = array('SELECT', '.', 'NET');

        $this->TestAgainstCheckList($_sqlQuery, $_checkList);

        /**
         * Testing MultiChar Operators
         */

        $_sqlQuery = "SELECT IF('Tickets.Creation Date':Day == 1, 'First', 'Others')";
        $_checkList = array('SELECT', 'IF', '(', 'Tickets.Creation Date', ':', 'Day', '=', '=', '1', ',', 'First', ',', 'Others', ')');

        $this->TestAgainstCheckList($_sqlQuery, $_checkList);

        /**
         * Testing if Single Space is Returned
         */

        $_sqlQuery = "SELECT   OK";

        $SWIFT_KQL2Lexer = new SWIFT_KQL2Lexer($_sqlQuery);

        $_spaces = 0;
        while (true) {
            $_token = $SWIFT_KQL2Lexer->NextToken();
            if ($_token) {
                if ($_token == ' ') {
                    $_spaces++;
                }
            } else {
                break;
            }
        }

        $this->assertEquals(1, $_spaces);

        /**
         * Testing asterisk at the end of field names
         */

        $_sqlQuery = "SELECT Tickets.* FROM Tickets";
        $_checkList = array('SELECT', 'Tickets.*', 'FROM', 'Tickets');

        $this->TestAgainstCheckList($_sqlQuery, $_checkList);

        /*
        $SWIFT_KQL2Lexer = new SWIFT_KQL2Lexer($_sqlQuery);

        $_first = true;
        echo "\$_checkList = array(";
        while (true) {
            $_token = $SWIFT_KQL2Lexer->NextToken();

            if ($_token == null) {
                break;
            }

            if ($_token != ' ') {
                if ($_first) {
                    $_first = false;
                } else {
                    echo ", ";
                }

                echo "'", $_token, "'";
            }
        }
        echo ");\n";
        */

        return true;
    }

    /**
     * Checks Tokens Against Array
     *
     * @author Andriy Lesyuk
     * @param string The Query
     * @param array The Check List
     * @return bool "true" on Success, "false" otherwise
     */
    private function TestAgainstCheckList($_sqlQuery, $_checkList)
    {
        $SWIFT_KQL2Lexer = new SWIFT_KQL2Lexer($_sqlQuery);

        $_checkIndex = 0;
        while (true) {
            $_token = $SWIFT_KQL2Lexer->NextToken();

            if ($_token == null) {
                break;
            }

            if ($_token != ' ') {
                $this->assertEquals($_checkList[$_checkIndex++], $_token);
            }
        }

        return true;
    }

}
?>
