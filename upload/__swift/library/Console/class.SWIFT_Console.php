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


// Certain code parts taken from Horde Framework. For more information please visit http://www.horde.org

/**
 * Horde_CLI:: API for basic command-line functionality/checks.
 *
 * $Horde: framework/CLI/CLI.php,v 1.34 2004/03/01 22:04:51 chuck Exp $
 *
 * Copyright 2003-2004 Chuck Hagenbuch <chuck@horde.org>
 * Copyright 2003-2004 Jan Schneider <jan@horde.org>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author Chuck Hagenbuch <chuck@horde.org>
 * @author Jan Schneider <jan@horde.org>
 * @version Revision: 1.34
 * @since  Horde 3.0
 * @package Horde_CLI
*/

/**
 * Console: Command Line Functions
 */
class SWIFT_Console extends SWIFT_Library
{
    const CONSOLE_INFO = 1;
    const CONSOLE_MESSAGE = 1;
    const CONSOLE_ERROR = 2;
    const CONSOLE_OK = 3;
    const CONSOLE_WARNING = 4;
    const CONSOLE_FAILED = 5;
    const CONSOLE_SUCCESS = 6;

    const COLOR_RED = 1;
    const COLOR_GREEN = 2;
    const COLOR_BLUE = 3;
    const COLOR_YELLOW = 4;

    private $_newLine = "";
    private $_indent = "";

    private $_boldStart = "";
    private $_boldEnd = "";

    private $_redStart = "";
    private $_greenStart = "";
    private $_yellowStart = "";
    private $_blueStart = "";

    private $_redEnd = "";
    private $_greenEnd = "";
    private $_yellowEnd = "";
    private $_blueEnd = "";
    private $_stdinPointer;

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct() {
        $this->_newLine = "\n";
        $this->_indent = '    ';

        $_term = getenv('TERM');
        if ($_term) {
            if (preg_match('/^(xterm|vt220|linux)/', $_term)) {
                $this->_boldStart = "\x1b[1;4m";
                $this->_redStart = "\x1b[01;31m";
                $this->_greenStart = "\x1b[01;32m";
                $this->_yellowStart = "\x1b[01;33m";
                $this->_blueStart = "\x1b[01;34m";
                $this->_boldEnd = $this->_redEnd = $this->_greenEnd = $this->_yellowEnd = $this->_blueEnd = "\x1b[0m";
            } elseif (preg_match('/^vt100/', $_term)) {
                $this->_boldStart = "\x1b[1;4m";
                $this->_boldEnd = "\x1b[0m";
            }
        }

        $this->_stdinPointer = fopen('php://stdin', 'r+');

        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct() {
        fclose($this->_stdinPointer);

        parent::__destruct();
    }

    /**
     * Retrieve the Text in given color
     *
     * @author Varun Shoor
     * @param string $_text The text to print.
     * @param mixed $_lineColor The Line Text Color
     * @return mixed "_text" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function GetText($_text, $_lineColor)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        switch ($_lineColor)
        {
            case self::COLOR_RED:
                return $this->Red($_text);
                break;

            case self::COLOR_YELLOW:
                return $this->Yellow($_text);
                break;

            case self::COLOR_BLUE:
                return $this->Blue($_text);
                break;

            case self::COLOR_GREEN:
                return $this->Green($_text);
                break;

            default:
                break;
        }

        return $_text;
    }

    /**
     * Prints $text on a single line.
     *
     * @param string $_text The text to print.
     * @param bool $_isBeforeLineBreak If true the linebreak is printed before the text instead of after it.
     * @param mixed $_lineColor The Line Text Color
     */
    public function WriteLine($_text = '', $_isBeforeLineBreak = false, $_lineColor = false) {
        $_text = $this->GetText($_text, $_lineColor);

        if ($_isBeforeLineBreak) {
            echo $this->_newLine . $_text;
        } else {
            echo $_text . $this->_newLine;
        }
    }

    /**
     * Returns the indented string.
     *
     * @param string $_text The text to indent.
     */
    public function Indent($_text) {
        return $this->_indent . $_text;
    }

    /**
     * Returns a bold version of $text.
     *
     * @param string $_text The text to bold.
     */
    public function Bold($_text) {
        return $this->_boldStart . $_text . $this->_boldEnd;
    }

    /**
     * Returns a red version of $text.
     *
     * @param string $_text The text to print in red.
     */
    public function Red($_text) {
        return $this->_redStart . $_text . $this->_redEnd;
    }

    /**
     * Returns a green version of $text.
     *
     * @param string $_text The text to print in green.
     */
    public function Green($_text)
    {
        return $this->_greenStart . $_text . $this->_greenEnd;
    }

    /**
     * Returns a blue version of $text.
     *
     * @param string $_text The text to print in blue.
     */
    public function Blue($_text)
    {
        return $this->_blueStart . $_text . $this->_blueEnd;
    }

    /**
     * Returns a yellow version of $_text.
     *
     * @param string $_text The text to print in yellow.
     */
    public function Yellow($_text)
    {
        return $this->_yellowStart . $_text . $this->_yellowEnd;
    }

    /**
     * Displays a message.
     *
     * @param string $_message The message string.
     * @param int $_type The type of message: 'cli.error', 'cli.warning', 'cli.success', or 'cli.message'.
     */
    public function Message($_message, $_type = null)
    {
        if (empty($_type)) {
            $_type = self::CONSOLE_MESSAGE;
        }

        $_cliError = "[ERROR]: ";
        $_cliWarning = "[WARNING]: ";
        $_cliOK = "[OK]: ";
        $_cliInfo = "[INFO]: ";
        $_cliSuccess = '[SUCCESS]: ';
        $_cliFailed = '[FAILED]: ';

        $_typeMessage = '';

        switch ($_type) {
            case self::CONSOLE_ERROR:
                $_typeMessage = $this->Red($_cliError);
                $_typeMain = $_cliError;
                break;
            case self::CONSOLE_FAILED:
                $_typeMessage = $this->Red($_cliFailed);
                $_typeMain = $_cliFailed;
                break;
            case self::CONSOLE_WARNING:
                $_typeMessage = $this->Yellow($_cliWarning);
                $_typeMain = $_cliWarning;
                break;
            case self::CONSOLE_MESSAGE:
                $_typeMessage = $this->Green($_cliOK);
                $_typeMain = $_cliOK;
                break;
            case self::CONSOLE_SUCCESS:
                $_typeMessage = $this->Green($_cliSuccess);
                $_typeMain = $_cliSuccess;
                break;
            case self::CONSOLE_INFO:
                $_typeMessage = $this->Blue($_cliInfo);
                $_typeMain = $_cliInfo;
                break;
            case self::CONSOLE_OK:
                $_typeMessage = $this->Green($_cliInfo);
                $_typeMain = $_cliOK;
                break;
        }

        $this->WriteLine($_typeMessage . $_message);
    }

    /**
     * Displays a fatal error message.
     *
     * @param string $_error The error text to display.
     */
    public function Fatal($_error)
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->WriteLine($this->Red('===================='));
        $this->WriteLine();
        $this->WriteLine($this->Red($_SWIFT->Language->Get('clifatalerror')));
        $this->WriteLine($this->Red($_error));
        $this->WriteLine();
        $this->WriteLine($this->Red('===================='));

        log_error_and_exit();
    }

    /**
     * Prompts for a user response.
     *
     * @param string $_promptMessage The message to display when prompting the user.
     * @param array $_choiceList The choices available to the user or null for a text input.
     */
    public function Prompt($_promptMessage, $_choiceList = [])
    {
        $_SWIFT = SWIFT::GetInstance();

        // Main event loop to capture top level command.
        while (true) {
            // Print out the prompt message.
            $this->WriteLine($_promptMessage . ' ', !is_array($_choiceList));
            if (is_array($_choiceList) && !empty($_choiceList)) {
                foreach ($_choiceList as $key => $choice) {
                    $key = $this->Bold($key);
                    $this->WriteLine('(' . $key . ') ' . $choice);
                }

                if ($_SWIFT->Language->Get('clienterchoice') != "") {
                    $_enterChoiceText = $_SWIFT->Language->Get('clienterchoice');
                } else {
                    $_enterChoiceText = "Please Type your Choice: ";
                }
                $this->WriteLine($_enterChoiceText, true);

                // Get the user choice.
                $_response = trim(fgets($this->_stdinPointer, 256));

                if (isset($_choiceList[$_response])) {
                    // Close standard in.
                    fclose($this->_stdinPointer);

                    return $_response;
                } else {
                    if ($_SWIFT->Language->Get('clinotvalidchoice') != "")
                    {
                        $_invalidChoiceText = $_SWIFT->Language->Get('clinotvalidchoice');
                    } else {
                        $_invalidChoiceText = '"%s" is Not a Valid Choice: ';
                    }

                    $this->WriteLine(sprintf($_invalidChoiceText, $_response));
                }

            } else {
                $_response = trim(fgets($this->_stdinPointer, 256));

                return $_response;
            }
        }

        return false;
    }
}
