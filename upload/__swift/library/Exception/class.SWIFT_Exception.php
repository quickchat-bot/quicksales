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

/**
 * Global Exception Handler
 *
 * @author Varun Shoor
 */
class SWIFT_Exception extends Exception
{

    static private $levels = array(
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parsing Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Runtime Notice'
    );

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_errorMessage The Error Message
     * @param int $_errorCode The Error Codee
     */
    public function __construct($_errorMessage, $_errorCode = 0)
    {
        parent::__construct($_errorMessage, $_errorCode);

        $_stackTrace = $this->getTraceAsString();
        $_exceptionMessage = $this->getMessage();
        $_exceptionCode = $this->getCode();
        $_exceptionFile = $this->getFile();
        $_exceptionLine = $this->getLine();

        $_formattedExceptionCode = (!empty($_exceptionCode) ? ' (' . $_exceptionCode . ')' : '');

        $_errorString = $_exceptionMessage . $_formattedExceptionCode . ' in ' . SecureFilePath($_exceptionFile) . ':' . $_exceptionLine;

        // If we have a server loaded in cluster, we need to report back the error to master..
        if (isset($this->Server) && $this->Server instanceof SWIFT_Library && $this->Server->GetIsClassLoaded() &&
                isset($this->Server->JobQueueMaster) && $this->Server->JobQueueMaster instanceof SWIFT_JobQueue &&
                $this->Server->JobQueueMaster->GetIsClassLoaded()) {
            $this->Server->JobQueueMaster->Dispatch('/Backend/Cluster/Error', $_errorString . SWIFT_CRLF . $_stackTrace);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {    }

    /**
     * The Global Exception Handler
     *
     * @author Varun Shoor
     * @param mixed $_Exception The Exception Object
     * @throws SWIFT_Exception
     */
    public static function GlobalExceptionHandler($_Exception)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_isDebugEnabled = defined('SWIFT_DEBUG') && constant('SWIFT_DEBUG') == true;
        if ($_Exception instanceof Throwable) {
            $_stackTrace = $_Exception->getTraceAsString();
            $_stackTraceContainer = $_Exception->getTrace();
            $_exceptionMessage = $_Exception->getMessage();
            $_exceptionCode = $_Exception->getCode();
            $_exceptionFile = $_Exception->getFile();
            $_exceptionLine = $_Exception->getLine();
            $_formattedExceptionCode = (!empty($_exceptionCode) ? ' (' . $_exceptionCode . ')' : '');

            $_errorString = $_exceptionMessage . $_formattedExceptionCode . ' in ' . SecureFilePath($_exceptionFile) . ':' . $_exceptionLine;
            $_routerData = '';
            if ($_SWIFT->Router instanceof SWIFT_Router && $_SWIFT->Router->GetIsClassLoaded()) {
                $_routerData = 'Router: ' . $_SWIFT->Router->GetCurrentURL();
            }

            $_SWIFT->FirePHP->Group('Uncaught Exception', array('Collapsed' => false, 'Color' => '#ff0000'));
            $_SWIFT->FirePHP->Error($_routerData . SWIFT_CRLF . $_errorString);
            $_stackTraceExploded = explode("\n", $_stackTrace);
            foreach ($_stackTraceExploded as $_val) {
                $_SWIFT->FirePHP->Info($_val);
            }
            $_SWIFT->FirePHP->GroupEnd();

            // If we have a server loaded in cluster, we need to report back the error to master..
            if (isset($_SWIFT->Server) && $_SWIFT->Server instanceof SWIFT_Library && $_SWIFT->Server->GetIsClassLoaded() &&
                    isset($_SWIFT->Server->JobQueueMaster) && $_SWIFT->Server->JobQueueMaster instanceof SWIFT_JobQueue &&
                    $_SWIFT->Server->JobQueueMaster->GetIsClassLoaded()) {
                $_SWIFT->Server->JobQueueMaster->Dispatch('/Backend/Cluster/Error', $_routerData . SWIFT_CRLF . $_errorString . SWIFT_CRLF . $_stackTrace);
            }

            if (SWIFT_INTERFACE == 'tests') {
                // Do nothing
            } else if (SWIFT_INTERFACE == 'client' && (!defined('SWIFT_ENVIRONMENT') || 'PRODUCTION' == strtoupper(constant('SWIFT_ENVIRONMENT')))) {
                ExceptionHandle($_Exception->getmessage());
            } else {
                SWIFT_Loader::LoadLibrary('Debug:Debug');
                $_debugStatement = SWIFT_Debug::RetrunStackTrace($_stackTraceContainer);

                $_errorTitle = 'Uncaught Exception';
                $_errorDescription = $_errorString;
                $_errorStackTrace = $_debugStatement;

                self::RenderError($_errorTitle, $_errorDescription, '', $_errorStackTrace, $_stackTrace);
            }

            // Added for PHPUnit compatibility
            if (class_exists('SWIFT_Loader', false)) {
                SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_EXCEPTION, $_routerData . SWIFT_CRLF . 'Uncaught Exception: ' . $_errorString . SWIFT_CRLF . SWIFT_CRLF . $_stackTrace);
            }

            if ($_isDebugEnabled) {
                error_log($_errorString . PHP_EOL . $_stackTrace);
            }
        } elseif ($_isDebugEnabled) {
            error_log('Uncaught Exception: ' . $_Exception);
        }

        log_error_and_exit();
    }

    /**
     * The Global Exception Handler (Handles the PHP Errors)
     *
     * @author Varun Shoor
     * @param int $_errorNumber The Error Number
     * @param string $_errorString The Error String
     * @param string $_errorFile The Error File
     * @param int $_errorLine The Error Line
     * @return bool|void "true" on Success, "false" otherwise
     */
    public static function GlobalErrorHandler($_errorNumber, $_errorString, $_errorFile, $_errorLine)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Prepended with @?
        if (error_reporting() == 0 || ($_errorNumber == E_STRICT && stristr($_errorString, 'Declaration of'))) {
            return false;
        }
        $_errorType = (!isset(self::$levels[$_errorNumber])) ? $_errorNumber : self::$levels[$_errorNumber];

        $_filePath = str_replace("\\", "/", $_errorFile);

        if (ob_get_level() > 1 && (!defined('SWIFT_DEBUG') || constant('SWIFT_DEBUG') == false)) {
            ob_end_flush();
        }


        $_errorTitle = $_errorType;
        $_errorDescription = $_errorString . ' (' . SecureFilePath($_filePath) . ':' . $_errorLine . ')';

        if ($_errorNumber < E_NOTICE || (defined('SWIFT_DEBUG') && SWIFT_DEBUG == true))
            self::RenderError($_errorTitle, $_errorDescription);

        $_routerData = '';
        if ($_SWIFT->Router instanceof SWIFT_Router && $_SWIFT->Router->GetIsClassLoaded()) {
            $_routerData = 'Router: ' . $_SWIFT->Router->GetCurrentURL();
        }
        $_errorDescriptionInternal = $_routerData . SWIFT_CRLF . $_errorDescription;

        // If we have a server loaded in cluster, we need to report back the error to master..
        if (isset($_SWIFT->Server) && $_SWIFT->Server instanceof SWIFT_Library && $_SWIFT->Server->GetIsClassLoaded() &&
                isset($_SWIFT->Server->JobQueueMaster) && $_SWIFT->Server->JobQueueMaster instanceof SWIFT_JobQueue && $_SWIFT->Server->JobQueueMaster->GetIsClassLoaded()) {
            $_SWIFT->Server->JobQueueMaster->Dispatch('/Backend/Cluster/Error', $_errorDescriptionInternal);
        }

        // Added for PHPUnit compatibility
        if (class_exists('SWIFT_Loader', false)) {
            try {
                SWIFT_Loader::LoadModel('ErrorLog:ErrorLog');
                SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_PHPERROR, $_errorDescriptionInternal);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        if ($_SWIFT->FirePHP instanceof SWIFT_FirePHP && $_SWIFT->FirePHP->GetIsClassLoaded()) {
            if ($_errorNumber == E_NOTICE) {
                $_SWIFT->FirePHP->Warn($_errorDescriptionInternal);
            } else {
                $_SWIFT->FirePHP->Error($_errorDescriptionInternal);
            }
        }

        // Die on all errors except for notice
        if ($_errorNumber < E_NOTICE) {

            if (in_array(SWIFT_INTERFACE, ['tests', 'console'])) {
                fwrite(STDERR, sprintf("Error %d: %s\nFile: %s [Line %d]\n", $_errorNumber, $_errorString, $_errorFile, $_errorLine));
            }

            log_error_and_exit();
        }

        return true;
    }

    /**
     * Render the Error
     *
     * @author Varun Shoor
     * @param string $_title
     * @param string $_description
     * @param string $_contents
     * @param string $_stackTrace Formatted version of stack trace
     * @param string $_cleanStackTrace The Clean stack trace
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function RenderError($_title, $_description, $_contents = '', $_stackTrace = '', $_cleanStackTrace = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        // Do nothing in case of tests
        if (SWIFT_INTERFACE == 'tests') {
            return true;
        }

        // Show stack traces in every other area except for client area
        $_showStackTraces = true;
        if (!SWIFT::IsDebug()) {
            $_showStackTraces = false;
        }

        $_routerData = '';
        if ($_SWIFT->Router instanceof SWIFT_Router && $_SWIFT->Router->GetIsClassLoaded()) {
            $_routerData = 'Router: ' . $_SWIFT->Router->GetCurrentURL();
        }

        $_cleanErrorContents = '';
        $_cleanErrorContents .= $_title . SWIFT_CRLF;
        $_cleanErrorContents .= $_routerData . SWIFT_CRLF;
        $_cleanErrorContents .= $_description . SWIFT_CRLF . SWIFT_CRLF;
        $_cleanErrorContents .= '=================================================================================================================================' . SWIFT_CRLF . SWIFT_CRLF;
        if (!empty($_contents)) {
            $_cleanErrorContents .= $_contents . SWIFT_CRLF;
        }

        if (!empty($_cleanStackTrace)) {
            $_cleanErrorContents .= $_cleanStackTrace . SWIFT_CRLF;
        }

        // Write to Log
        $_SWIFT_LogObject = new SWIFT_Log('error');
        $_SWIFT_LogObject->Log($_cleanErrorContents);

        /*
         * BUG FIX - Mahesh Salaria
         *
         * SWIFT-3123: PHP Notice: Use of undefined constant SWIFT_INTERFACE...
         *
         * Comments: none
         */
        if (defined(SWIFT_INTERFACE) && SWIFT_INTERFACE == 'console' || SWIFT_INTERFACE == 'api' || SWIFT_INTERFACE == 'rss' || SWIFT_INTERFACE == 'staffapi' || SWIFT_INTERFACE == 'winapp' || SWIFT_INTERFACE == 'cron'
                || (defined('SETUP_CONSOLE') && constant('SETUP_CONSOLE') == '1')) {
            echo $_cleanErrorContents;

            return true;
        }

        // Prepare HTML Output
        $_output = '';

        $_output .= '<style type="text/css">';
        $_output .= '
.errorcopyarea {
    margin: 0 0 1em; padding: 0.4em; background: #fff; border: solid 1px #d6d6d6; box-shadow: 1px 1px 2px #CCCCCC; width: 100%; height: 200px;
}

.copytoclipboard {
    margin: 15px 0 15px 0; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8); font-weight: bold; border-bottom: 1px solid #d6d6d6; padding: 5px;
}

.titlegradient {
background-image: linear-gradient(bottom, rgb(144,23,17) 46%, rgb(166,27,17) 73%, rgb(184,24,6) 87%);
background-image: -o-linear-gradient(bottom, rgb(144,23,17) 46%, rgb(166,27,17) 73%, rgb(184,24,6) 87%);
background-image: -moz-linear-gradient(bottom, rgb(144,23,17) 46%, rgb(166,27,17) 73%, rgb(184,24,6) 87%);
background-image: -webkit-linear-gradient(bottom, rgb(144,23,17) 46%, rgb(166,27,17) 73%, rgb(184,24,6) 87%);
background-image: -ms-linear-gradient(bottom, rgb(144,23,17) 46%, rgb(166,27,17) 73%, rgb(184,24,6) 87%);

background-image: -webkit-gradient(
    linear,
    left bottom,
    left top,
    color-stop(0.46, rgb(144,23,17)),
    color-stop(0.73, rgb(166,27,17)),
    color-stop(0.87, rgb(184,24,6))
);
}

#kayako_exception { text-align: left; color: #333; }
#kayako_exception h1,
#kayako_exception h2 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #911; color: #fff; }
#kayako_exception h1 a,
#kayako_exception h2 a { color: #fff; }
#kayako_exception h2 { background: #222; }
#kayako_exception h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
#kayako_exception p { margin: 0; padding: 0.2em 0; }
#kayako_exception a { color: #333333; text-decoration: none; }
#kayako_exception pre { overflow: auto; white-space: pre-wrap; }
#kayako_exception table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
#kayako_exception table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
#kayako_exception div.content { padding: 0.4em 1em 1em; overflow: hidden; }
#kayako_exception pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: solid 1px #d6d6d6; box-shadow: 1px 1px 2px #CCCCCC; line-height: 1.2em; }
#kayako_exception pre.source span.line { display: block; }
#kayako_exception pre.source.collapsed { display: none; }
#kayako_exception pre.source span.highlight { background: #FDEEF4; }
#kayako_exception pre.source span.line span.number { color: #666; }
#kayako_exception ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
#kayako_exception ol.trace li { margin: 0; padding: 0; }
#kayako_exception .collapsed { display: none; }
#kayako_exception .sourcetitle { text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8); }
#kayako_exception .sourcedesc { text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8); font-weight: bold; }
#kayako_exception .variabletype { font-weight: none; font-style: italic; }

';
        $_output .= '</style>';

        $_output .= '<div style="margin: 10px; border: 1px solid #4b3333; box-shadow: 1px 1px 2px #b2b1b1; font-family: \'Lucida Grande\', Verdana, Arial, Helvetica; font-size: 14px; border-radius: 8px 8px 0 0;">';
        $_output .= '<div style="border-top: 1px solid #e97572; border-radius: 8px 8px 0 0;">';

        // Title bar
        $_output .= '<div style="padding: 8px 8px 8px 14px; border-bottom: 1px solid #2f0200; border-radius: 7px 7px 0 0;" class="titlegradient">';
        $_output .= '<div style="color: #FFFFFF; font-size: 20px; font-weight: bold; text-shadow: -1px -1px #333333;">' . htmlspecialchars($_title) . '</div>';

        // Description
        if (!empty($_description)) {
            $_output .= '<div style="text-shadow: 0 1px 0 rgba(0, 0, 0, 0.8); color: #f5afae; font-size: 16px;">' . htmlspecialchars($_description) . '</div>';
        }

        $_output .= '</div>';

        // Contents
        $_output .= '<div style="background: #ececec; padding: 10px 10px 0px;">';
        $_output .= htmlspecialchars($_contents);

        if ($_showStackTraces) {
            $_output .= $_stackTrace;
        }

        // Text area for copyable contents
        $_output .= '<div class="copytoclipboard">Copy to clipboard:</div>';
        $_output .= '<textarea class="errorcopyarea">';
        $_output .= htmlspecialchars($_title) . SWIFT_CRLF;
        if (!empty($_description)) {
            $_output .= htmlspecialchars($_description) . SWIFT_CRLF;
        }
        $_output .= '=================================================================================================================================' . SWIFT_CRLF . SWIFT_CRLF;

        if (!empty($_contents)) {
            $_output .= htmlspecialchars($_contents) . SWIFT_CRLF;
        }
        $_output .= htmlspecialchars(SecureFilePath($_cleanStackTrace));
        $_output .= '</textarea>';

        $_output .= '</div>';

        $_output .= '</div>';
        $_output .= '</div>';


        echo $_output;

        return true;
    }

}
