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
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The SWIFT Log File Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_Log extends SWIFT_Library
{
    protected $_logFileName;
    protected $_logFilePointer;
    protected $_logFilePrefix;
    protected $_logPrefix = '';
    protected $_clearLog = false;

    protected $_alertLogContents = '';
    static protected $_alertList = array();

    // Core Constants
    const LOG_HEADER = 'SWIFT - ';

    const FILENAME_PREFIX    = 'log.';
    const FILENAME_EXTENSION = 'txt';

    const LOG_CHURNDAYS = 3; // We preserve all logs for seven days

    const TYPE_NONE    = 0;
    const TYPE_OK      = 1;
    const TYPE_ERROR   = 2;
    const TYPE_WARNING = 3;
    const TYPE_SUCCESS = 4;
    const TYPE_FAILED  = 5;

    const ALERTTYPE_EMAIL       = 1;

    /**
     * The constructor
     *
     * @author Varun Shoor
     *
     * @param string $_logFilePrefix (OPTIONAL) The Log File Prefix
     */
    public function __construct($_logFilePrefix = '')
    {
        parent::__construct();

        if (!empty($_logFilePrefix)) {
            $this->SetLogFilePrefix($_logFilePrefix);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        $this->TriggerAlerts();

        parent::__destruct();

        if ($this->GetLogFilePointer()) {

            fclose($this->GetLogFilePointer());

            chdir(SWIFT_BASEPATH);
            $_logFileName = $this->GetLogFileName();
            if ($_logFileName && file_exists($_logFileName) && $this->GetClearLog()) {
                unlink($_logFileName);
            }
        }
    }

    /**
     * Check to see if its a valid message type
     *
     * @author Varun Shoor
     *
     * @param int $_messageType
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidMessageType($_messageType)
    {
        return ($_messageType == self::TYPE_NONE || $_messageType == self::TYPE_OK || $_messageType == self::TYPE_ERROR || $_messageType == self::TYPE_WARNING || $_messageType == self::TYPE_SUCCESS
            || $_messageType == self::TYPE_FAILED);
    }

    /**
     * Retrieve the Log File Prefix
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLogFilePrefix()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return $this->_logFilePrefix;
    }

    /**
     * Set the Log File Prefix
     *
     * @author Varun Shoor
     *
     * @param string $_logFilePrefix
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetLogFilePrefix($_logFilePrefix)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $this->_logFilePrefix = $_logFilePrefix;

        return true;
    }

    /**
     * Retrieve the Log Prefix
     *
     * @author Varun Shoor
     * @return string The Log Prefix
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLogPrefix()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return $this->_logPrefix;
    }

    /**
     * Set the Log Prefix
     *
     * @author Varun Shoor
     *
     * @param string $_logPrefix
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetLogPrefix($_logPrefix)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $this->_logPrefix = $_logPrefix;

        // Rebuild the filename
        $this->BuildLogFileName();

        return true;
    }

    /**
     * Retrieve the log file name
     *
     * @author Varun Shoor
     * @return mixed "_logFileName" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLogFileName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_logFileName;
    }

    /**
     * Set the Clear Log flag
     *
     * @author Varun Shoor
     *
     * @param bool $_clearLog True if the log should be cleared on end, false otherwise
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetClearLog($_clearLog)
    {
        $_clearLog = (int) ($_clearLog);

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_clearLog = $_clearLog;

        return true;
    }

    /**
     * Gets the Clear Log flag
     *
     * @author Varun Shoor
     * @return bool True if the log should be cleared on end, false otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetClearLog()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_clearLog;
    }

    /**
     * Set the log file name
     *
     * @author Varun Shoor
     *
     * @param string $_logFileName The log file name
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetLogFileName($_logFileName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_logFileName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_logFileName = $_logFileName;

        return true;
    }

    /**
     * Adds Log Message to local instance contents
     *
     * @author Varun Shoor
     *
     * @param string $_logMessage
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function AddToAlertContents($_logMessage)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $this->_alertLogContents .= $_logMessage;

        return true;
    }

    /**
     * Get instance log contents
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAlertContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return $this->_alertLogContents;
    }

    /**
     * Builds the log file name
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function BuildLogFileName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        /**
         * BUG FIX : Saloni Dhall <saloni.dhall@kayako.com>
         *
         * SWIFT-4667 : Duplicate 'log.cache' on SaaS domains.
         *
         * Comments : SWIFT::Get('InstallationHash') found to be empty & not being set, when the first time helpdesk gets executed in class.SWIFT.php
         */
        $this->Settings = new SWIFT_Settings();
        $_logFilePrefix = $this->GetLogFilePrefix();
        $_logFileDate   = date('d_M_Y', DATENOW);
        $_logFileHash   = substr(md5($_logFileDate . $this->Settings->GetKey('core', 'installationhash')), 0, 10);

        $_logFileName = self::FILENAME_PREFIX;

        if (!empty($_logFilePrefix)) {
            $_logFileName .= $_logFilePrefix . '_';
        }

        $_logFileName .= $_logFileDate;

        // Add the salted hash so that each file name is unique and cannot be guessed
        $_logFileName .= '_' . $_logFileHash;

        $_logFileName .= '.' . self::FILENAME_EXTENSION;

        // Set the log file name
        $this->SetLogFileName($_logFileName);
        unset($this->Settings);

        return true;
    }

    /**
     * Get the log loaded pointer
     *
     * @author Varun Shoor
     * @return bool Whether the log is loaded
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLogFilePointer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_logFilePointer;
    }

    /**
     * Set the log file loaded pointer
     *
     * @author Varun Shoor
     *
     * @param string $_logFilePointer The log file loaded pointer
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetLogFilePointer($_logFilePointer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_logFilePointer = $_logFilePointer;

        return true;
    }

    /**
     * Starts the logging system
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception If the Class is not Loaded
     * @return bool "true" on Success, "false" otherwise
     */
    public function Start()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetLogFilePointer()) {
            return true;
        }

        $_currentWorkingDirectory = getcwd();

        chdir(SWIFT_BASEPATH);

        $this->BuildLogFileName();
        $_logFileName = $this->GetLogFileName();
        $_logFilePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_LOG_DIRECTORY . '/' . $_logFileName;

        $_fileExistResult = false;
        if (file_exists($_logFilePath)) {
            $_fileExistResult = true;
        }

        $_filePointer = fopen($_logFilePath, 'a+');
        @chmod($_logFilePath, 0666);

        if ($_currentWorkingDirectory != SWIFT_BASEPATH) {
            chdir($_currentWorkingDirectory);
        }

        if ($_filePointer) {
            $_logPadding = SWIFT_CRLF . SWIFT_CRLF;
            if (!$_fileExistResult) {
                $_logPadding = '';
            }

            $_routerData = '';
            if ($this->Router instanceof SWIFT_Router && $this->Router->GetIsClassLoaded()) {
                $_routerData = 'Router: ' . $this->Router->GetCurrentURL();
            }

            fwrite($_filePointer, $_logPadding . self::LOG_HEADER . date('d M Y h:i:s A') . SWIFT_CRLF);
            fwrite($_filePointer, $_routerData . SWIFT_CRLF);
            fwrite($_filePointer, '_____________________________________________________________________________' . SWIFT_CRLF . SWIFT_CRLF);

            $this->SetLogFilePointer($_filePointer);

            return true;
        }

        return false;
    }

    /**
     * Retrieves the log file contents
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLogFileContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_logFileName = $this->GetLogFileName();
        $_logFilePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_LOG_DIRECTORY . '/' . $_logFileName;

        if (file_exists($_logFilePath)) {
            return file_get_contents($_logFilePath);
        }

        return '';
    }

    /**
     * Retrieve the message prefix
     *
     * @author Varun Shoor
     *
     * @param int $_messageType
     *
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetMessagePrefix($_messageType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (!self::IsValidMessageType($_messageType)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA . ': ' . $_messageType);
        }

        switch ($_messageType) {
            case self::TYPE_NONE:
                return '';

                break;

            case self::TYPE_OK:
                return '[OK]: ';

            case self::TYPE_SUCCESS:
                return '[SUCCESS]: ';

            case self::TYPE_FAILED:
                return '[FAILED]: ';

            case self::TYPE_ERROR:
                return '[ERROR]: ';

            case self::TYPE_WARNING:
                return '[WARNING]: ';

            default:
                break;
        }

        return '';
    }

    /**
     * Logs the given entry in the defined log file
     *
     * @author Varun Shoor
     *
     * @param string $_logMessage    The message to write
     * @param int    $_messageType   (OPTIONAL) The Message Type
     * @param string $_messagePrefix (OPTIONAL) The Message Prefix
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Log($_logMessage, $_messageType = self::TYPE_NONE, $_messagePrefix = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!self::IsValidMessageType($_messageType)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA . ': ' . $_messageType);
        }

        if (stristr($_logMessage, 'Declaration of')) {
            return false;
        }

        if (!$this->Start()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_finalConsoleMessage = '';
        $_finalLogMessage     = $this->GetMessagePrefix($_messageType);

        if (!empty($_messagePrefix)) {
            $_finalLogMessage .= '(' . $_messagePrefix . ') ';
            $_finalConsoleMessage .= '(' . $_messagePrefix . ') ';
        }

        $_finalLogMessage .= $_logMessage;
        $_finalConsoleMessage .= $_logMessage;

        // Display console data
        if ($_SWIFT->Console instanceof SWIFT_Console && defined('SWIFT_DEBUG') && constant('SWIFT_DEBUG') == true) {
            switch ($_messageType) {
                case self::TYPE_NONE:
                    $_SWIFT->Console->Message($_finalConsoleMessage, SWIFT_Console::CONSOLE_INFO);

                    break;

                case self::TYPE_OK:
                    $_SWIFT->Console->Message($_finalConsoleMessage, SWIFT_Console::CONSOLE_OK);

                    break;

                case self::TYPE_ERROR:
                    $_SWIFT->Console->Message($_finalConsoleMessage, SWIFT_Console::CONSOLE_ERROR);

                    break;

                case self::TYPE_FAILED:
                    $_SWIFT->Console->Message($_finalConsoleMessage, SWIFT_Console::CONSOLE_FAILED);

                    break;

                case self::TYPE_SUCCESS:
                    $_SWIFT->Console->Message($_finalConsoleMessage, SWIFT_Console::CONSOLE_SUCCESS);

                    break;

                case self::TYPE_WARNING:
                    $_SWIFT->Console->Message($_finalConsoleMessage, SWIFT_Console::CONSOLE_WARNING);

                    break;

                default:
                    break;
            }
        }

        if ($_messageType == self::TYPE_ERROR || $_messageType == self::TYPE_FAILED || $_messageType == self::TYPE_WARNING) {
            $this->AddToAlertContents($_finalLogMessage);
        }

        if ($this->GetLogFilePointer()) {
            fwrite($this->GetLogFilePointer(), $_finalLogMessage . SWIFT_CRLF);
        } else {
            return false;
        }

        return false;
    }

    /**
     * Passes execution to self::Log() whenever Log is called as a function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __invoke()
    {
        call_user_func_array(array($this, 'Log'), func_get_args());

        return true;
    }

    /**
     * Cleans up all old logs
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CleanUp()
    {
        chdir(SWIFT_BASEPATH);

        self::ChurnDirectory('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_LOG_DIRECTORY);

        return true;
    }

    /**
     * Churns logs in a directory
     *
     * @author Varun Shoor
     *
     * @param string $_directoryPath
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function ChurnDirectory($_directoryPath)
    {
        if (!file_exists($_directoryPath) || !is_dir($_directoryPath)) {
            return false;
        }

        $_directoryPath = StripTrailingSlash($_directoryPath);

        if ($_directoryHandle = opendir($_directoryPath)) {
            while (false !== ($_fileName = readdir($_directoryHandle))) {
                $_filePath          = $_directoryPath . '/' . $_fileName;
                $_fileStatContainer = stat($_filePath);

                if ($_fileName != '.' && $_fileName != '..' && $_fileName != 'index.html' && !is_dir($_filePath)) {
                    $_churnDate = DATENOW - (self::LOG_CHURNDAYS * 86400);

                    $_fileModificationTime = 0;

                    if (isset($_fileStatContainer['mtime'])) {
                        $_fileModificationTime = $_fileStatContainer['mtime'];
                    }

                    if ($_fileModificationTime <= $_churnDate) {
                        unlink($_filePath);
                    }
                    // Continue looping till all sub directories are also churned
                } else if ($_fileName != '.' && $_fileName != '..' && is_dir($_filePath)) {
                    self::ChurnDirectory($_filePath);
                }
            }

            closedir($_directoryHandle);
        }

        return true;
    }

    /**
     * Check to see if its a valid alert type
     *
     * @author Varun Shoor
     *
     * @param int $_alertType
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidAlertType($_alertType)
    {
        return ($_alertType == self::ALERTTYPE_EMAIL);
    }

    /**
     * Adds an email to alert queue
     *
     * @author Varun Shoor
     *
     * @param int    $_alertType
     * @param string $_alertContent
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Alert($_alertType, $_alertContent)
    {
        if (!self::IsValidAlertType($_alertType)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_alertList[$_alertType])) {
            self::$_alertList[$_alertType] = array();
        }

        self::$_alertList[$_alertType][] = $_alertContent;

        return true;
    }

    /**
     * Triggers the alerts
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function TriggerAlerts()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (!isset(self::$_alertList[self::ALERTTYPE_EMAIL])) {
            return false;
        }

        $_logContents = $this->GetAlertContents();
        if (empty($_logContents)) {
            return false;
        }

        $_finalSubject = self::LOG_HEADER . date('d M Y h:i:s A') . ' (' . $this->GetLogFileName() . ')';

        if (isset(self::$_alertList[self::ALERTTYPE_EMAIL]) && _is_array(self::$_alertList[self::ALERTTYPE_EMAIL])) {
            foreach (self::$_alertList[self::ALERTTYPE_EMAIL] as $_emailAddress) {
                if (!IsEmailValid($_emailAddress)) {
                    continue;
                }

                mail($_emailAddress, $_finalSubject, $_logContents);
            }
        }

        return true;
    }
}
