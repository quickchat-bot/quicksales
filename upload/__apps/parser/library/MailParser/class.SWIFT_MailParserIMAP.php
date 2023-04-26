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

namespace Parser\Library\MailParser;

use Exception;
use Parser\Library\MailParser\SWIFT_MailParser;
use SWIFT;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;
use SWIFT_LanguageEngine;
use SWIFT_Library;
use SWIFT_Log;
use Parser\Models\Log\SWIFT_ParserLog;
use Parser\Library\Protocol\SWIFT_Imap;
use SWIFT_TemplateEngine;
use SWIFT_OAuth;

/**
 * The IMAP Communication Lib
 *
 * @property \SWIFT_Cryptor $Cryptor
 * @property \SWIFT_Mail $Mail
 * @author Varun Shoor
 */
class SWIFT_MailParserIMAP extends SWIFT_Library
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

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4288 Result: description is not being shown in email parser logs in case of POP/IMAP.
         *
         * Comments: In case of IMAP/POP system should always use the language from files.
         */
        $this->Language->Load('emailparser', SWIFT_LanguageEngine::TYPE_FILE);
    }

    /**
     * Process the IMAP/POP3 Accounts
     *
     * @author Varun Shoor
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded or If a core error is encountered
     */
    public function Process()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // We require IMAP.  If it's not installed we can't fetch email through this script anyways.
        if (!extension_loaded('imap')) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Exception('Error: Your PHP was not compiled with IMAP support.' . SWIFT_CRLF .
                '*nix users should recompile their PHP with the \'--with-imap\' flag; Windows users can simply uncomment the extension=\'php_imap.dll\' line in their php.ini');
            // @codeCoverageIgnoreEnd
        }

        // First get all the queues
        $_emailQueuesContainer = array();

        $_debugMessages = '';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues WHERE isenabled = '1' ORDER BY emailqueueid ASC");
        while ($this->Database->NextRecord()) {
            if ($this->Database->Record['fetchtype'] == SWIFT_EmailQueue::FETCH_PIPE) {
                continue;
            }

            $_emailQueuesContainer[$this->Database->Record['emailqueueid']] = $this->Database->Record;
        }

        if (!count($_emailQueuesContainer)) {
            return false;
        }

        $_rejectedMessageCount = 0;
        $_acceptedMessageCount = 0;
        $_totalMessages = 0;

        $_processingResult = array($_totalMessages, $_acceptedMessageCount, $_rejectedMessageCount);


        foreach ($_emailQueuesContainer as $_key => $_emailQueueContainer) {
            $_isPOP3 = false;

            $_fetchType = false;

            switch ($_emailQueueContainer['fetchtype']) {
                case SWIFT_EmailQueue::FETCH_IMAP:
                    $_fetchType = 'imap/notls';
                    break;
                case SWIFT_EmailQueue::FETCH_IMAPSSL:
                    $_fetchType = 'imap/ssl/novalidate-cert/notls';
                    break;
                case SWIFT_EmailQueue::FETCH_IMAPTLS:
                    $_fetchType = 'imap/ssl/novalidate-cert';
                    break;
                case SWIFT_EmailQueue::FETCH_POP3SSL:
                    $_fetchType = 'pop3/ssl/novalidate-cert/notls';
                    $_isPOP3 = true;
                    break;
                case SWIFT_EmailQueue::FETCH_POP3TLS:
                    $_fetchType = 'pop3/ssl/novalidate-cert';
                    $_isPOP3 = true;
                    break;
                default:
                    $_fetchType = 'pop3/notls';
                    $_isPOP3 = true;
                    break;
            }

            if (!SWIFT::Get('iscron')) {
                echo "Running parser for Queue ID '" . $_emailQueueContainer['emailqueueid'] . "':  " . $_emailQueueContainer['email'] . SWIFT_CRLF;
            }

            $_processingResult = array(false, false, false, false);
            try {
                $_processingResult = $this->ProcessMailbox($_fetchType, $_isPOP3, $_emailQueueContainer);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                if (!SWIFT::Get('iscron')) {
                    echo $_SWIFT_ExceptionObject->getMessage();
                }
            }

            // array($_totalMessages, $_acceptedMessages, $_rejectedMessages, $_rejectedMessagesContainer);

            // Process the Rejected Messages
            if (_is_array($_processingResult[3])) {
                // @codeCoverageIgnoreStart
                foreach ($_processingResult[3] as $_key => $_MailPropertyContainerObject) {
                    $_subject = '';
                    if (isset($_MailPropertyContainerObject->subject) && $_MailPropertyContainerObject->subject != '') {
                        $_subject = $_MailPropertyContainerObject->subject;
                    }

                    $_from = '';
                    if (isset($_MailPropertyContainerObject->from) && $_MailPropertyContainerObject->from != '') {
                        $_from = $_MailPropertyContainerObject->from;
                    }

                    $_to = '';
                    if (isset($_MailPropertyContainerObject->to) && $_MailPropertyContainerObject->to != '') {
                        $_to = $_MailPropertyContainerObject->to;
                    }

                    $_messageID = '';
                    if (isset($_MailPropertyContainerObject->message_id) && $_MailPropertyContainerObject->message_id != '') {
                        $_messageID = $_MailPropertyContainerObject->message_id;
                    }

                    SWIFT_ParserLog::Create(
                        SWIFT_ParserLog::TYPE_FAILURE,
                        0,
                        0,
                        $_subject,
                        $_from,
                        $_to,
                        $_MailPropertyContainerObject->size,
                        sprintf(
                            $this->Language->Get('errtoobig'),
                            $this->Settings->Get('pr_sizelimit') . $this->Language->Get('kb'),
                            sprintf("%.02f", ($_MailPropertyContainerObject->size) / 1024) . $this->Language->Get('kb')
                        ),
                        '',
                        0,
                        array(),
                        $_messageID
                    );
                }
                // @codeCoverageIgnoreEnd
            }

            if (!SWIFT::Get('iscron')) {
                echo 'Queue Total: ' . $_processingResult[0] . ', Accepted: ' . $_processingResult[1] . ', Rejected: ' . $_processingResult[2] . SWIFT_CRLF;
            }

            $_acceptedMessageCount += $_processingResult[1];
            $_rejectedMessageCount += $_processingResult[2];
            $_totalMessages += $_processingResult[0];
        }

        if (!SWIFT::Get('iscron')) {
            echo 'Final Total: ' . $_totalMessages . ', Accepted: ' . $_acceptedMessageCount . ', Rejected: ' . $_rejectedMessageCount . SWIFT_CRLF;
        }

        return $_debugMessages;
    }

    /**
     * Process the given mailbox
     *
     * @author Varun Shoor
     *
     * @param string $_fetchType           The Fetch Type String
     * @param bool   $_isPOP3              Whether this is a POP3 mailbox
     * @param array  $_emailQueueContainer The Email Queue Container
     *
     * @return array The Result Array on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If an error is encountered
     */
    protected function ProcessMailbox($_fetchType, $_isPOP3, $_emailQueueContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * Verem Dugeri
         *
         * BUG-FIX KAYAKO-2895 - Email queue passwords are saved in plain text in the database
         */
        $this->Load->Library('Cryptor:Cryptor');
        try {
            $authType = 'basic';
            if (isset($_emailQueueContainer['authtype']) && $_emailQueueContainer['authtype'] != '') {
                $authType = $_emailQueueContainer['authtype'];
            }
            if ($authType == 'basic') {
                $userPassword = $this->Cryptor->decrypt($_emailQueueContainer['userpassword']);
                [$protocol, $protocolType] = $this->fetchZendRequest($_emailQueueContainer);
                $protocol->login($_emailQueueContainer['username'], $userPassword);
                $storage = $this->fetchZendRequest($_emailQueueContainer, true, $protocol);
            } else if ($authType == 'oauth') {
                list($protocol, $storage, $OAuthError) = $this->ConnectOAuth($_emailQueueContainer);
            }

            if (!isset($protocol) || !isset($storage)) {
                throw new SWIFT_Exception('One or more errors occurred when reading Queue #' . $_emailQueueContainer['emailqueueid']);
            }

            $_totalMessages = 0;
            $_acceptedMessages = 0;
            $_rejectedMessages = 0;
            $_rejectedMessagesContainer = array();

            $_maxMessages = (int)($this->Settings->Get('pr_procno'));

            // @codeCoverageIgnoreStart
            // remove "mailbox is empty" notices from the beginning of a queue behavior when a later box is broken

            $_imapMessageCount = $storage->countMessages();
            // Get the Message List. We only process unread messages if leave a copy on server setting is true
            $_messageList = $_messagePropertiesContainer = array();

            if ($_imapMessageCount != '0') {
                if($_isPOP3 == true) {
                    $_messageList = array();
                    // Pick all messages. Read/Unread information is not presented in POP3 protocol.
                    for ($i = 1; $i <= $_imapMessageCount; $i++) {
                        $message = $storage->getMessage($i);
                        $_messageList[] = $i;
                    }
                } else {
                    //
                    if ($_emailQueueContainer['leavecopyonserver'] == '1') {
                        $_messageList = $protocol->search(array("UNSEEN"));
                    } else {
                        $_messageList = $protocol->search(array("ALL"));
                    }
                }
            }

            if (!_is_array($_messageList)) {
                $_totalMessages = 0;
            } else {
                $_totalMessages = count($_messageList);
            }

            // Mailbox empty?
            if ($_totalMessages == 0) {
                return array(0, 0, 0, $_rejectedMessagesContainer);
            }

            $_processMessages = 0;
            $_newMessageList = array();

            for ($i = 0; $i < $_totalMessages; $i++) {
                if ($_processMessages >= $_maxMessages) {
                    break;
                }
                $_newMessageList[] = $_messageList[$i];
                $_processMessages++;
            }

            // Attempt to set time limit
            @set_time_limit(0);
            // Processing in reverse order to be able to delete without changing the messageIDs
            $_key = count($_newMessageList);
            while ($_key) {
                $_messageID = $_newMessageList[--$_key];
                // There is a limit on how many messages we are to process at once and we have reached it; bail.
                if ($_acceptedMessages >= $_maxMessages) {
                    break;
                }

                $_message = $storage->getMessage($_messageID);
                $_mailHeaders = $_message->getHeaders()->toString();

                // Check size limit
                if (($_message->getSize() / 1024) > $this->Settings->Get('pr_sizelimit')) {

                    if (SWIFT_ParserLog::IsMessageIDExist($_message->getHeader('Message-ID', 'string'))) {
                        $_rejectedMessages++;
                        continue;
                    }

                    $_rejectedMessagesContainer[$_messageID] = (object)[
                        'message_id' => $_message->getHeader('Message-ID', 'string')
                    ];

                    /**
                     * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                     *
                     * SWIFT-4619 Warn users when their email is rejected due to size limit.
                     */
                    preg_match('/From:\s*(.*[^\s])\s*<\s*(.*[^\s])\s*>/', $_mailHeaders, $_matches);

                    if (IsEmailValid(end($_matches))) {
                        $this->Load->Library('Mail:Mail');

                        // Load the phrases from the database..
                        $this->Language->Load('emailparser', SWIFT_LanguageEngine::TYPE_FILE);
                        $this->Language->Load('ticketemails', SWIFT_LanguageEngine::TYPE_DB);

                        $_textEmailContents = sprintf($this->Template->Get('email_sizelimit_text', SWIFT_TemplateEngine::TYPE_DB), $this->Settings->Get('pr_sizelimit'));
                        $_htmlEmailContents = sprintf($this->Template->Get('email_sizelimit_html', SWIFT_TemplateEngine::TYPE_DB), $this->Settings->Get('pr_sizelimit'));

                        $this->Mail->SetFromField($_emailQueueContainer['email'], SWIFT::Get('companyname'));
                        $this->Mail->SetToField(end($_matches));
                        $this->Mail->SetSubjectField($this->Language->Get('sizelimitrejectsub'));

                        $this->Mail->SetDataText($_textEmailContents);
                        $this->Mail->SetDataHTML($_htmlEmailContents);

                        $this->Mail->SendMail(false, $_emailQueueContainer['emailqueueid']);
                    }

                    $_rejectedMessages++;
                    continue;
                }

                // Process the Message
                $_mailHeaders = preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_mailHeaders);
                $_body = $_message->getContent();

                $_finalMailData = $_mailHeaders . SWIFT_CRLF . SWIFT_CRLF . preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_body);

                $_acceptedMessages++;

                $_SWIFT_MailParserObject = new SWIFT_MailParser($_finalMailData);

                $_SWIFT_EmailQueueObjectDispatch = null;
                if (!isset($_emailQueueContainer['forcequeue']) || $_emailQueueContainer['forcequeue'] == '1') {
                    $_SWIFT_EmailQueueObjectDispatch = SWIFT_EmailQueue::RetrieveStore($_emailQueueContainer);
                }

                try {
                    $_SWIFT_MailParserObject->Process(false, $_SWIFT_EmailQueueObjectDispatch);
                } catch (Exception $Exception) {
                    $this->Log->Log('Failed to process message with ID: ' . $_messageID . ' from IMAP Email Queue: ' . $_emailQueueContainer['host'], SWIFT_Log::TYPE_ERROR, 'Parser\Library\MailParser\SWIFT_MailParserIMAP');
                }

                // Delete the message only if leave copy on server is set to false..the seen flag is set by default
                // POP3 Messages should be deleted from the server
                if ($_emailQueueContainer['leavecopyonserver'] != '1' || $_isPOP3) {
                    $storage->removeMessage($_messageID);
                } else {
                    // The message is marked as seen by default when the imap client reads it
                }
            }
            return array($_totalMessages, $_acceptedMessages, $_rejectedMessages, $_rejectedMessagesContainer);
        } catch (\Exception $e) {
            $_imapErrors = '---------------------------------------------------' . SWIFT_CRLF;
            $_imapErrors .= $e->getMessage();
            $_imapErrors .= '---------------------------------------------------' . SWIFT_CRLF;
            throw new SWIFT_Exception('One or more errors occurred when reading Queue #' . $_emailQueueContainer['emailqueueid'] . SWIFT_CRLF . $_imapErrors);
        }
    }

    protected function ConnectOAuth(&$_emailQueueContainer)
    {
        $_tryCount = 0;
        $protocol = NULL;
        $storage = NULL;
        $OAuthError = NULL;
        if (!(isset($_emailQueueContainer['host']) && $_emailQueueContainer['host'] != "")) {
            $OAuthError = "IMAP host is not set";
        } else if (!(isset($_emailQueueContainer['port']) && $_emailQueueContainer['port'] != "")) {
            $OAuthError = "IMAP port is not set";
        } else if (!(isset($_emailQueueContainer['username']) && $_emailQueueContainer['username'] != "")) {
            $OAuthError = "User email is not set";
        } else if (!(isset($_emailQueueContainer['accesstoken']) && $_emailQueueContainer['accesstoken'] != "")) {
            $OAuthError = "Access token is not retreived";
        } else {
            // Given we are not managing access token expiry and Zend doesn't return proper error code, any exception will lead to trying to refresh the token once
            while ($_tryCount < 2 && !isset($storage)) {
                try {
                    $_tryCount = $_tryCount + 1;
                    [$protocol, $protocolType] = $this->fetchZendRequest($_emailQueueContainer);
                    $b64str = base64_encode("user=" . trim($_emailQueueContainer['username']) . "\1auth=Bearer " . $_emailQueueContainer['accesstoken'] . "\1\1");
                    if ($protocolType == 'pop3'){
	                    $result = $protocol->sendRequests(['AUTH XOAUTH2', $b64str]);
	                    $this->Log->Log('Authentication result ' . $result, SWIFT_Log::TYPE_OK, 'Parser\Library\MailParser\SWIFT_MailParserIMAP');
	                    if (!strpos($result, '+OK')) {
		                    throw new \Exception('Fail when authenticate POP3 oauth for user '.$_emailQueueContainer['username']);
	                    }
                    }
                    else if ($protocolType == 'imap'){
                        $authenticateParams = array('XOAUTH2', $b64str);
                        $protocol->sendRequest('AUTHENTICATE', $authenticateParams);
                    }
                    $storage = $this->fetchZendRequest($_emailQueueContainer, true, $protocol);
                } catch (\Exception $e) {
                    try {
                        $_tokens = SWIFT_OAuth::refreshToken(
                            $_emailQueueContainer['tokenendpoint'],
                            $_emailQueueContainer['clientid'],
                            $_emailQueueContainer['clientsecret'],
                            $_emailQueueContainer['refreshtoken']
                        );
                        if (isset($_tokens) && isset($_tokens["access_token"])) {
                            $_emailQueueContainer['accesstoken'] = $_tokens["access_token"];
                            $_dbFields = array("accesstoken" => $_tokens["access_token"]);
                            if (isset($_tokens["refresh_token"])) {
                                $_emailQueueContainer['refreshtoken'] = $_tokens["refresh_token"];
                                $_dbFields["refreshtoken"] = $_tokens["refresh_token"];
                            }
                            if (isset($_tokens["expires_in"])){
                                $_dbFields["tokenexpiry"] = $_emailQueueContainer['tokenexpiry'] = DATENOW + $_tokens["expires_in"];
                            }
                            $this->Database->AutoExecute(TABLE_PREFIX . "emailqueues", $_dbFields, "UPDATE", "emailqueueid = " . $_emailQueueContainer["emailqueueid"]);
                        } else {
                            $OAuthError = "No access token received upon refresh";
                        }
                    } catch (\Exception $e2) {
                        $OAuthError = $e2;
                    }
                }
            }
        }

        if (!isset($protocol) || !isset($storage)) {
            throw new SWIFT_Exception('Error connecting to IMAP using access token: ' . $OAuthError);
        } else {
            return array($protocol, $storage, $OAuthError);
        }
    }

    public static function fetchZendRequest($_emailQueueContainer, $is_Storage = false, $protocol = null){
        $pop3Types = array(SWIFT_EmailQueue::FETCH_POP3, SWIFT_EmailQueue::FETCH_POP3SSL, SWIFT_EmailQueue::FETCH_POP3TLS);
        $noSSLTypes = array(SWIFT_EmailQueue::FETCH_POP3, SWIFT_EmailQueue::FETCH_IMAP);
        $useSSL = in_array($_emailQueueContainer['fetchtype'], $noSSLTypes) ? false : 'SSL';

        if (in_array($_emailQueueContainer['fetchtype'], $pop3Types)){
            if ($is_Storage) {
                return new \Zend\Mail\Storage\Pop3($protocol);
            }
            return array(new \Zend\Mail\Protocol\Pop3($_emailQueueContainer['host'], trim($_emailQueueContainer['port']), $useSSL), 'pop3');
        }
        else {
            if ($is_Storage) {
                return new \Zend\Mail\Storage\Imap($protocol);
            }
            return array(new SWIFT_Imap($_emailQueueContainer['host'], trim($_emailQueueContainer['port']), $useSSL), 'imap');
        }
    }
}
