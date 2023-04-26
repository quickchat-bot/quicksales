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

namespace Parser\Cron;

use Controller_cron;
use SWIFT;
use SWIFT_Cron;
use SWIFT_CronLog;
use SWIFT_Exception;

/**
 * The Minute Controller
 *
 * @property \Parser\Library\MailParser\SWIFT_MailParserIMAP $MailParserIMAP
 * @author Varun Shoor
 */
class Controller_ParserMinute extends Controller_cron
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

        SWIFT::Set('isparser', true);
    }

    /**
     * The POP3IMAP Fetcher
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function POP3IMAP()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('MailParser:MailParserIMAP', [], true, false, APP_PARSER);

        echo nl2br($this->MailParserIMAP->Process());

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1392: Task log does not get updated after manual excution of cron task from web browser
         *
         * Comments: Add an entry in cron log table
         */
        if (!SWIFT::Get('iscron')) {
            $_SWIFT_CronObject = SWIFT_Cron::Retrieve('parser');
            SWIFT_CronLog::Create($_SWIFT_CronObject, '');
        }

        return true;
    }
}

?>
