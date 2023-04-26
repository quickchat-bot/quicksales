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
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Library\MailParser;

use Parser\Library\EmailQueue\SWIFT_EmailQueueType;
use SWIFT;
use SWIFT_App;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;
use SWIFT_LanguageEngine;
use SWIFT_Library;
use SWIFT_Loader;
use Parser\Models\Ban\SWIFT_ParserBan;
use Parser\Models\Log\SWIFT_ParserLog;
use Parser\Library\Rule\SWIFT_ParserRuleManager;
use SWIFT_TemplateEngine;
use Tickets\Library\Bayesian\SWIFT_Bayesian;

/**
 * The Mail Parser Email Processing Class
 *
 * This class processes raw email and dispatches it to relevant email queue type objects for further processing.
 *
 * @property \SWIFT_MailMIME $MailMime
 * @property \Parser\Library\MailParser\SWIFT_MailParserEmail $MailParserEmail
 * @property \SWIFT_Mail $Mail
 * @property \Parser\Library\Loop\SWIFT_LoopChecker $LoopChecker
 * @author Varun Shoor
 */
class SWIFT_MailParser extends SWIFT_Library
{
    protected $_rawEmailData = '';

    // This option is to force reprocessing of the email in case the setting 'Create multiple emails' is not enabled
    protected $_forceReprocessing = false;

	/**
	 * @var SWIFT_MailIdChecker
	 */
    protected $mailidChecker;

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param string $_rawEmailData The RAW Email Data
     *
     * @throws SWIFT_Exception If invalid email data is provided
     */
    public function __construct($_rawEmailData)
    {
        parent::__construct();

        if (!$this->SetRawEmailData($_rawEmailData)) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            // @codeCoverageIgnoreEnd
        }

        $this->Language->Load('emailparser', SWIFT_LanguageEngine::TYPE_FILE);

        $this->Load->Library('Loop:LoopChecker', [], true, false, APP_PARSER);

        $this->Cache->Queue('parsercatchallcache', 'queuecache', 'parserbancache', 'breaklinecache');
        $this->Cache->LoadQueue();
        $this->mailidChecker = new SWIFT_MailIdChecker($this->Cache);
    }

    /**
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param bool $_forceReprocessing
     */
    public function SetForceReprocessing($_forceReprocessing)
    {
        $this->_forceReprocessing = $_forceReprocessing;
    }

    /**
     * Set the Raw email data
     *
     * @author Varun Shoor
     *
     * @param string $_rawEmailData The RAW Email Data
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetRawEmailData($_rawEmailData)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_rawEmailData)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_rawEmailData = preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_rawEmailData);

        return true;
    }

    /**
     * Retrieve the RAW email data
     *
     * @author Varun Shoor
     * @return mixed "_rawEmailData" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRawEmailData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_rawEmailData;
    }

    /**
     * Log a Debug Message
     *
     * @author Varun Shoor
     *
     * @param string $_debugMessage The Debug Message
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LogDebug($_debugMessage)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        //echo $_debugMessage . SWIFT_CRLF;

        return true;
    }

    /**
     * Process the Raw Email Data
     *
     * @author Varun Shoor
     *
     * @param bool                                       $_shouldCheckSize        (OPTIONAL) Whether to check email message size.
     * @param \Parser\Models\EmailQueue\SWIFT_EmailQueue $_SWIFT_EmailQueueObject (OPTIONAL) Force load an email queue object rather than getting it from the to email
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Process($_shouldCheckSize = false, SWIFT_EmailQueue $_SWIFT_EmailQueueObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailQueueCache = $this->Cache->Get('queuecache');

        SWIFT::Set('parserstarttime', GetMicroTime());

        $this->LogDebug('Starting Processing of Message');

        $this->LogDebug('Initializing MIME Classes');

        $_emailSize = mb_strlen($this->GetRawEmailData());
        $this->Load->Library('Mail:MailMime', array($this->GetRawEmailData()));

        $this->LogDebug('MIME Classes Initialized');

        /*
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4873 Helpdesk should parse emails containing no recipient details.
         */
        $_emailQueue = '';
        if ($_SWIFT_EmailQueueObject InstanceOf SWIFT_EmailQueue) {
            $_emailQueue = $_SWIFT_EmailQueueObject->GetProperty('email');
        }

        $_mailStructure = $this->MailMime->Decode($_emailQueue);

        try {
            $this->Load->Library('MailParser:MailParserEmail', array($_mailStructure), true, false, APP_PARSER);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, 0, 0, '', '', '', 0, $_SWIFT_ExceptionObject->getMessage() . SWIFT_CRLF . $_SWIFT_ExceptionObject->getTraceAsString(),
                $this->GetRawEmailData(), 0, array());

            return false;
        }

        $this->LogDebug('Email Object Created');
        $this->LogDebug((string)$this->MailParserEmail);

        $this->LogDebug('Checking Email Size');
        if ($_shouldCheckSize === true) {

            if ($this->MailParserEmail->GetMessageID() != '' && SWIFT_ParserLog::IsMessageIDExist($this->MailParserEmail->GetMessageID())) {
                return false;
            }

            // Check the size of the message; if it exceeds the maximum size setting, ignore it.
            $_messageSize = strlen($this->GetRawEmailData()) / 1024;

            if ($_messageSize > (int)($this->Settings->Get('pr_sizelimit'))) {
                // @codeCoverageIgnoreStart
                $this->LogDebug('Message size (' . $_messageSize . 'KiB) exceeds the maximum allowed size of ' .
                    $this->Settings->Get('pr_sizelimit') . 'KiB; shutting down!');

                SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, 0, 0, $this->MailParserEmail->GetSubject(),
                    $this->MailParserEmail->GetFromEmail(), $this->MailParserEmail->GetToEmail(), $_emailSize,
                    sprintf($this->Language->Get('errtoobig'), $this->Settings->Get('pr_sizelimit') . $this->Language->Get('kb'),
                        sprintf("%.02f", $_messageSize) . $this->Language->Get('kb')), $this->GetRawEmailData(), 0, array(), $this->MailParserEmail->GetMessageID());

                /**
                 * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
                 *
                 * SWIFT-4619 Warn users when their email is rejected due to size limit.
                 */
                if (IsEmailValid($this->MailParserEmail->GetToEmail())) {
                    $this->Load->Library('Mail:Mail');

                    // Load the phrases from the database..
                    $this->Language->Load('emailparser', SWIFT_LanguageEngine::TYPE_FILE);
                    $this->Language->Load('ticketemails', SWIFT_LanguageEngine::TYPE_DB);

                    $_textEmailContents = sprintf($this->Template->Get('email_sizelimit_text', SWIFT_TemplateEngine::TYPE_DB), $this->Settings->Get('pr_sizelimit'));
                    $_htmlEmailContents = sprintf($this->Template->Get('email_sizelimit_html', SWIFT_TemplateEngine::TYPE_DB), $this->Settings->Get('pr_sizelimit'));

                    $this->Mail->SetFromField($this->MailParserEmail->GetToEmail(), SWIFT::Get('companyname'));
                    $this->Mail->SetToField($this->MailParserEmail->GetFromEmail());
                    $this->Mail->SetSubjectField($this->Language->Get('sizelimitrejectsub'));

                    $this->Mail->SetDataText($_textEmailContents);
                    $this->Mail->SetDataHTML($_htmlEmailContents);

                    $this->Mail->SendMail();
                }

                return false;
                // @codeCoverageIgnoreEnd
            }
        }

        /**
         * Attempt to retrieve email queues
         */
        $_emailQueueContainer = array();

        // If we have to override the email queue, which we do in case of IMAP accounts because we ALWAYS know the destination queue, then do it right now..
        if ($_SWIFT_EmailQueueObject instanceof SWIFT_EmailQueue && $_SWIFT_EmailQueueObject->GetIsClassLoaded()) {
            $_emailQueueContainer[$_SWIFT_EmailQueueObject->GetEmailQueueID()] = $_SWIFT_EmailQueueObject;
        } else {
            try {
                $_emailQueueContainer = $this->GetEmailQueue($this->MailParserEmail->GetRecipients());
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, 0, 0, '', '', '', 0, $_SWIFT_ExceptionObject->getMessage() . SWIFT_CRLF . $_SWIFT_ExceptionObject->getTraceAsString(),
                    $this->GetRawEmailData(), 0, array(), $this->MailParserEmail->GetMessageID());

                return false;
            }
        }

        if (!_is_array($_emailQueueContainer)) {
            // @codeCoverageIgnoreStart
            $this->LogDebug('No Email Queues Found for: ' . implode(', ', $this->MailParserEmail->GetRecipients()));

            SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, 0, 0, $this->MailParserEmail->GetSubject(),
                $this->MailParserEmail->GetFromEmail(), $this->MailParserEmail->GetToEmail(), $_emailSize,
                $this->Language->Get('errnoqueues'), $this->GetRawEmailData(), 0, array(), $this->MailParserEmail->GetMessageID());

            return false;
            // @codeCoverageIgnoreEnd
        }

        /**
         * Is the source email banned?
         */
        if (SWIFT_ParserBan::IsBanned($this->MailParserEmail->GetFromEmail())) {
            // @codeCoverageIgnoreStart
            $this->LogDebug('From Email is Banned: ' . $this->MailParserEmail->GetFromEmail());

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-5126 Admin receiving email parser alerts for banned email address as well.
             */
            SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, 0, SWIFT_ParserLog::TYPE_ID_BAN_EMAIL, $this->MailParserEmail->GetSubject(),
                $this->MailParserEmail->GetFromEmail(), $this->MailParserEmail->GetToEmail(), $_emailSize,
                $this->Language->Get('erremailbanned'), $this->GetRawEmailData(), 0, array(), $this->MailParserEmail->GetMessageID());

            return false;
            // @codeCoverageIgnoreEnd
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4506 Duplicate users accounts are created for same email address.
         *
         * Comments: Restricting the parallel executions of console.
         */
        if (isset($_emailQueueCache['pipe']) && count($_emailQueueCache['pipe']) > '1' && $this->MultiplePipeCheck($this->MailParserEmail->GetRecipients())) {
            time_nanosleep(mt_rand(1, 9), mt_rand(1, 999999999));
        }

        /**
         * Email Queue Source Check
         */
        $_fromEmail = mb_strtolower(trim($this->MailParserEmail->GetFromEmail()));
        if (!empty($_fromEmail) && isset($_emailQueueCache['pointer'][$_fromEmail]) && isset($_emailQueueCache['list'][$_emailQueueCache['pointer'][$_fromEmail]]) && $_emailQueueCache['list'][$_emailQueueCache['pointer'][$_fromEmail]]['isenabled'] == '1') {
            if ($_emailQueueCache['list'][$_emailQueueCache['pointer'][$_fromEmail]]['type'] != SWIFT_EmailQueueType::TYPE_BACKEND) {
                $this->LogDebug('From Email is of Email Queue: ' . $this->MailParserEmail->GetFromEmail());

                SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, 0, 0, $this->MailParserEmail->GetSubject(),
                    $this->MailParserEmail->GetFromEmail(), $this->MailParserEmail->GetToEmail(), $_emailSize,
                    $this->Language->Get('erremailqueuesame'), $this->GetRawEmailData(), 0, array(), $this->MailParserEmail->GetMessageID());

                return false;
            }
        }

        // Set default properties
        $this->MailParserEmail->SetProperty('sendautoresponder', true);
        $this->MailParserEmail->SetProperty('loopblock', false);

        /**
         * Retrieve Content on Priority and Process Breaklines on it
         */
        // This is where we parse out the original email content and decide which one to store, text or html
        $_finalEmailContent = '';
        $_finalEmailContentIsHTML = false;

        if ($this->Settings->Get('pr_contentpriority') == 'text') {
            // Text is empty but we do have HTML content in there. This will happen in very rare cases
            if (trim($this->MailParserEmail->GetText()) == '' && trim($this->MailParserEmail->GetHTML()) != '') {
                $_finalEmailContentIsHTML = true;
                $_finalEmailContent = $this->MailParserEmail->GetHTML();
            } else {
                $_finalEmailContent = $this->MailParserEmail->GetText();
            }
        } else if ($this->Settings->Get('pr_contentpriority') == 'html') {
            if (trim($this->MailParserEmail->GetHTML()) == '') {
                $_finalEmailContent = $this->MailParserEmail->GetText();
                if ($this->Settings->Get('t_tinymceeditor') != '0') {
                    $_finalEmailContent = nl2br($_finalEmailContent);
                    $_finalEmailContentIsHTML = true;
                }
            } else {
                $_finalEmailContentIsHTML = true;
                $_finalEmailContent = $this->MailParserEmail->GetHTML();
            }
        }

        $this->MailParserEmail->SetProperty('ishtml', $_finalEmailContentIsHTML);

        /**
         * Need to strip tags?
         */
        if ($this->Settings->Get('pr_stripscript') == 1) {
            $_finalEmailContent = strip_javascript($_finalEmailContent);
        }

        /**
         * Process Breaklines
         */

        // Set final content sans the breaklines
        $this->MailParserEmail->SetProperty('originalfinalcontent', $_finalEmailContent);

        $_finalEmailContent = $this->ProcessBreaklines($_finalEmailContent);

        $this->MailParserEmail->SetProperty('finalcontent', $_finalEmailContent);

        $this->LogDebug('Final Contents: ' . $_finalEmailContent);
        $this->LogDebug('Is HTML: ' . $_finalEmailContentIsHTML);

        /**
         * Loop Cutter Logic
         */
        if ($this->LoopChecker->Check($this->MailParserEmail->GetFromEmail()) == true) {
            // @codeCoverageIgnoreStart
            $this->LogDebug('WARNING: Loop Cutter Flood Protection Triggered');
            $this->MailParserEmail->SetProperty('loopblock', true);

            if ($this->Settings->Get('pr_loopcut_ignores_cut_mail')) {

                $this->LogDebug('ERROR: Flood protection / loop cutter rules have triggered; email is rejected.
                    No auto-response was sent, and no ticket was created.');

                SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, 0, 0, $this->MailParserEmail->GetSubject(),
                    $this->MailParserEmail->GetFromEmail(), $this->MailParserEmail->GetToEmail(), $_emailSize,
                    $this->Language->Get('errloopcontrol'), $this->GetRawEmailData(), 0, array(), $this->MailParserEmail->GetMessageID());

                return false;
            } else if ($this->Settings->Get('pr_loopcut_prevents_autoresponder')) {

                $this->LogDebug('WARNING: Loop cutter prevented the dispatch of an automatic response.  Ticket was created as normal.');
                $this->MailParserEmail->SetProperty('noautoresponder', true);
                SWIFT::Set('loopcontrol', true);
            } else {
                $this->LogDebug('WARNING: Flood protection / loop cutter took no action! Configuration of loop control settings allows
                    accepted message and does not prevent auto-response.');
            }
            // @codeCoverageIgnoreEnd
        } else {
            $this->LogDebug('Loop Cutter Check PASSED');
        }


        /**
         * Bayesian
         */
        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            SWIFT_Loader::LoadLibrary('Bayesian:Bayesian', APP_TICKETS);
            $_SWIFT_BayesianObject = new SWIFT_Bayesian();
            $_probabilityContainer = $_SWIFT_BayesianObject->Get($this->MailParserEmail->GetSubject() . ' ' . $this->MailParserEmail->GetFinalContents());
            if (_is_array($_probabilityContainer)) {
                $_finalBayesCategoryID = 0;
                $_finalBayesBenchmark = 0;
                foreach ($_probabilityContainer[0] as $_bayesCategoryID => $_probability) {
                    if ($_probability['combined'] >= 0.500 && $_probability['combined'] > $_finalBayesBenchmark) {
                        $_finalBayesCategoryID = $_bayesCategoryID;
                        $_finalBayesBenchmark = $_probability['combined'];
                    }
                }

                $this->MailParserEmail->SetBayesianCategory($_finalBayesCategoryID);
            }
        }

        /**
         * Content Processing
         */
        if ($this->Settings->Get('pr_allowmultiplecreation') == '1') {
            foreach ($_emailQueueContainer as $_SWIFT_EmailQueueObject) {
                try {
                    $_SWIFT_ParserRuleManagerObject = new SWIFT_ParserRuleManager($this->MailParserEmail, $_SWIFT_EmailQueueObject, $this);
                    $_SWIFT_EmailQueueObject->EmailQueueType->Process($this->MailParserEmail, $this, $_SWIFT_ParserRuleManagerObject);
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, $_SWIFT_EmailQueueObject->GetEmailQueueID(), 0, $this->MailParserEmail->GetSubject(),
                        $this->MailParserEmail->GetFromEmail(), $this->MailParserEmail->GetToEmail(), $this->MailParserEmail->GetFinalContentSize(),
                        $_SWIFT_ExceptionObject->getMessage(), $this->GetRawEmailData(),
                        (GetMicroTime() - SWIFT::Get('parserstarttime')), array(), $this->MailParserEmail->GetMessageID());

                    return false;
                }
            }
        } else {
            /**
             * Bugfix: KAYAKOC-6288
             * Here's how we ensure we don't get multiple tickets created for an email
             * @author Banjo Mofesola Paul <banjo.paul@aurea.com>
             */
            if (!$this->_forceReprocessing && isset($_mailStructure->headers['message-id'])) {
                $_mailID = $_mailStructure->headers['message-id'];


                // no need to proceed with ticket creation if the message has already been processed
                // put up a nice message to that effect too
                if ($this->mailidChecker->checkMessageId($_mailID)) {
                    SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, 0, 0, '', '', '', 0,
                        'Email is being discarded since we may already have a ticket created for it, this is likely to occur when we have more than one EmailQueue address in the email recepients list',
                        $this->GetRawEmailData(), 0, array());

                    return false;
                }
            }
            // @codeCoverageIgnoreStart

            $_SWIFT_EmailQueueObject = false;
            foreach ($_emailQueueContainer as $_SWIFT_EmailQueueObjectBase) {
                $_SWIFT_EmailQueueObject = $_SWIFT_EmailQueueObjectBase;

                break;
            }

            try {
                $_SWIFT_ParserRuleManagerObject = new SWIFT_ParserRuleManager($this->MailParserEmail, $_SWIFT_EmailQueueObject, $this);
                $_SWIFT_EmailQueueObject->EmailQueueType->Process($this->MailParserEmail, $this, $_SWIFT_ParserRuleManagerObject);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                //echo $_SWIFT_ExceptionObject->getTraceAsString();
                SWIFT_ParserLog::Create(SWIFT_ParserLog::TYPE_FAILURE, $_SWIFT_EmailQueueObject->GetEmailQueueID(), 0, $this->MailParserEmail->GetSubject(),
                    $this->MailParserEmail->GetFromEmail(), $this->MailParserEmail->GetToEmail(), $this->MailParserEmail->GetFinalContentSize(),
                    $_SWIFT_ExceptionObject->getMessage(), $this->GetRawEmailData(),
                    (GetMicroTime() - SWIFT::Get('parserstarttime')), array(), $this->MailParserEmail->GetMessageID());

                return false;
            }
        }


        return true;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Multiple pipe check
     *
     * @author Ravi Sharma <ravi.sharma@opencart.com.vn>
     *
     * @param  array $_recipientList
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    protected function MultiplePipeCheck($_recipientList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_resultContainer = $this->Database->QueryFetch("SELECT count(*) as total FROM " . TABLE_PREFIX . "emailqueues WHERE email IN (" . BuildIN($_recipientList) . ") AND isenabled = '1' AND fetchtype = 'pipe' ");

        return $_resultContainer['total'] > '1' ? true : false;
    }

    /**
     * Retrieve the list of email queue objects based on a list of recipient addresses
     *
     * @author Varun Shoor
     *
     * @param array $_recipientList The Recipient List
     *
     * @return mixed "$_finalRecipientMap" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetEmailQueue($_recipientList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        $_catchAllCache = $this->Cache->Get('parsercatchallcache');
        $_catchAllActive = false;
        if (_is_array($_catchAllCache)) {
            $_catchAllActive = true;
        }

        // First create a map that will hold our objects
        $_recipientMap = $_emailQueueMap = array();
        foreach ($_recipientList as $_val) {
            $_recipientMap[trim(mb_strtolower($_val))] = false;
        }

        // Now try to lookup email queues based on recipient list
        $_hasEmailQueues = false;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues
                                 WHERE email IN (" . BuildIN($_recipientList) . ")
                                   AND isenabled = '1'");
        while ($this->Database->NextRecord()) {
            // Removed check for only piped email queues

            $_hasEmailQueues = true;

            $_recipientMap[trim(mb_strtolower($this->Database->Record['email']))] = $this->Database->Record['emailqueueid'];
            $_emailQueueMap[$this->Database->Record['emailqueueid']] = $this->Database->Record;
        }

        // Try to match this against catch all rules and load relevant objects
        $_finalRecipientMap = array();
        foreach ($_recipientMap as $_emailAddress => $_emailQueueID) {

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-4255 - In case of PIPE email queue, Catch-all rule is triggered just after checking 'to' address (skips checking 'cc' and 'bcc') of the email headers.
             *
             * Comments: Catch all rule was running even there is email queue in cc.
             */
            // We have catch all rules and apparently we couldnt locate an email queue for this email?
            if ($_catchAllActive === true && $_emailQueueID === false && !$_hasEmailQueues) {
                // @codeCoverageIgnoreStart
                foreach ($_catchAllCache as $_catchAllRuleContainer) {

                    // We found a match?
                    $_matches = array();
                    if (@preg_match($_catchAllRuleContainer['ruleexpr'], $_emailAddress, $_matches)) {
                        $_emailQueueID = (int)($_catchAllRuleContainer['emailqueueid']);
                    }
                }
                // @codeCoverageIgnoreEnd
            }

            // Load the object
            if ($_emailQueueID !== false) {
                try {
                    if (isset($_emailQueueMap[$_emailQueueID])) {
                        $_finalRecipientMap[$_emailQueueID] = SWIFT_EmailQueue::RetrieveStore($_emailQueueMap[$_emailQueueID]);
                    } else {
                        // @codeCoverageIgnoreStart
                        $_finalRecipientMap[$_emailQueueID] = SWIFT_EmailQueue::Retrieve($_emailQueueID);
                        // @codeCoverageIgnoreEnd
                    }
                } catch (SWIFT_Exception $_SWIFT_EmailQueue_ExceptionObject) {
                    throw new SWIFT_Exception($_SWIFT_EmailQueue_ExceptionObject->getMessage());
                }
            }
        }

        // @codeCoverageIgnoreStart
        return $_finalRecipientMap;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Process Mail Parser Breaklines on the given content
     *
     * @author Varun Shoor
     *
     * @param string $_emailContents The Email Contents
     *
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessBreaklines($_emailContents)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_breaklineCache = $this->Cache->Get('breaklinecache');

        if (!_is_array($_breaklineCache)) {
            return $_emailContents;
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-865 Multiple breaklines are not working on a single ticket
         *
         * Comments: Made it loop till every breakline is parsed
         */

        foreach ($_breaklineCache as $_breaklineContainer) {

            if ($_breaklineContainer['isregexp'] == '1') {
                // This is a regular expression value.
                $_splitContainer = preg_split($_breaklineContainer['breakline'], $_emailContents);

                // If more than one item is in the array then the match was successful
                // and the reply is the first one.
                if (count($_splitContainer) > 1) {
                    // The regular expression was successful; return the first match, as it will be all
                    // the text before the regular expression matched.
                    $_emailContents = $_splitContainer[0];
                }
                // If the regex doesn't match, try the next breakline.
            } else {
                // Process normally; not a regular expression value.
                $_breakContent = reversestrchr($_emailContents, $_breaklineContainer['breakline']);

                // The breakline matched; return the text from the beginning of the string up to the breakline.
                if ($_breakContent) {
                    $_emailContents = $_breakContent;
                }
                // If it doesn't match, try the next breakline.
            }
        }

        // No breaklines matched, so return the content as-is.
        return $_emailContents;
    }
}
