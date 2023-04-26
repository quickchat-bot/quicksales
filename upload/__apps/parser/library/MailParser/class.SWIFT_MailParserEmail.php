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

use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Library;

/**
 * The Mail Parser Email Storage Class
 *
 * This class stores all properties related to a raw email and gets passed to the queue processing objects
 *
 * @property \SWIFT_StringCSSParser $StringCSSParser
 * @author Varun Shoor
 */
class SWIFT_MailParserEmail extends SWIFT_Library
{
    const SUBJECT_DECODE_REGX = '/=\?([\w\-]+)\?\w\?([\w\=]+)/m';

    const ENCODING_SUPERSEDE = [
        'gb2312' => 'gb18030'
    ];

    protected $_fromName = '';
    protected $_fromEmail = '';

    protected $_toName = '';
    protected $_toEmail = '';
    protected $_toEmailList = '';

    protected $_replyToName = '';
    protected $_replyToEmail = '';

    protected $_subject = '';
    protected $_returnAddress = '';
    protected $_returnAddressName = '';
    protected $_returnAddressEmail = '';

    protected $_recipientsList = array();
    protected $_attachmentsContainer = array();

    protected $_textContents = '';
    protected $_textSize = '';
    protected $_textCharset = '';

    protected $_htmlContents = '';
    protected $_htmlSize = '';
    protected $_htmlCharset = '';

    protected $_finalContents = '';
    protected $_finalContentSize = '';
    protected $_finalContentCharset = '';
    protected $_finalContentIsHTML = false;

    protected $_inReplyTo = '';

    protected $_propertyContainer = array();

    protected $_bayesCategoryID = 0;

    protected $_toEmailSuffix = '';

    protected $_dateTime = '';
    protected $_messageID = '';

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param object $_mailStructure The Mail Structure
     *
     * @throws SWIFT_Exception If Object Creation Fails
     */
    public function __construct($_mailStructure)
    {
        parent::__construct();

        $this->Load->Library('String:StringCSSParser');

        if (!$this->ProcessMailStructure($_mailStructure)) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            // @codeCoverageIgnoreEnd
        }

        // Begin Hook: route
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('mailparser_email')) ? eval($_hookCode) : false;
        // End Hook
    }

    /**
     * Processes the Mail Structure
     *
     * @author Varun Shoor
     *
     * @param \stdClass $_mailStructure The Mail Structure
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessMailStructure($_mailStructure)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_queueCache = $this->Cache->Get('queuecache');

        $this->SetProperty('mailstructure', $_mailStructure);

        $_hasDeliveredTo = false;

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1675 [Warning]: strtotime() expects parameter 1 to be string, array given (Parser/class.Parser\Library\MailParser\SWIFT_MailParserEmail.php:113)
         * SWIFT-2220 QuickSupport shows e-mails with "UT" timezone as 1970-01-01
         * SWIFT-1917 [Notice]: Undefined index: date (Parser/class.Parser\Library\MailParser\SWIFT_MailParserEmail.php:120)
         *
         */
        $this->_dateTime = DATENOW;
        if (isset($_mailStructure->headers['date'])) {
            if (_is_array($_mailStructure->headers['date'])) {
                $_dateTimeString = preg_replace('/UT$/', 'UTC', $_mailStructure->headers['date'][0]);
                $this->_dateTime = strtotime($_dateTimeString);
            } else {
                $_dateTimeString = preg_replace('/UT$/', 'UTC', $_mailStructure->headers['date']);
                $this->_dateTime = strtotime($_dateTimeString);
            }
        }

        if (isset($_mailStructure->headers['delivered-to']) && !empty($_mailStructure->headers['delivered-to']) && IsEmailValid($_mailStructure->headers['delivered-to'])) {
            $_hasDeliveredTo = true;
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1700 Parsing emails with only a CC header
         * SWIFT-262 Parsing emails with only a BCC header
         *
         */
        $_hasPotentialQueueRecipient = false;
        foreach (array_merge($_mailStructure->recipientAddresses, $_mailStructure->bccRecipientAddresses) as $_recipientEmail) {
            $_recipientEmail = mb_strtolower($_recipientEmail);
            if (isset($_queueCache['pointer'][$_recipientEmail])) {
                $_hasPotentialQueueRecipient = true;
                break;
            }
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2125 Help desk should consider 'reply-to' header as 'from' address, if 'From address' is not coming in raw headers.
         *
         */
        if ((!isset($_mailStructure->fromEmail) || empty($_mailStructure->fromEmail) || !IsEmailValid($_mailStructure->fromEmail))
            && (!isset($_mailStructure->replytoEmail) || empty($_mailStructure->replytoEmail) || !IsEmailValid($_mailStructure->replytoEmail))) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Exception('From Email is Empty');
        } else if ((!isset($_mailStructure->toEmail) || empty($_mailStructure->toEmail)) && !$_hasDeliveredTo && !$_hasPotentialQueueRecipient) {
            throw new SWIFT_Exception('To Email is Empty');
        } else if (!_is_array($_mailStructure->recipientAddresses) && !$_hasDeliveredTo && !$_hasPotentialQueueRecipient) {
            throw new SWIFT_Exception('Recipient Addresses is empty');
            // @codeCoverageIgnoreEnd
        }

        // Process the From Name & Email
        if (isset($_mailStructure->fromEmail) && !empty($_mailStructure->fromEmail) && IsEmailValid($_mailStructure->fromEmail)) {
            $this->_fromEmail = trim(mb_strtolower($_mailStructure->fromEmail));
        } else if (isset($_mailStructure->replytoEmail) && !empty($_mailStructure->replytoEmail) && IsEmailValid($_mailStructure->replytoEmail)) {
            $this->_fromEmail = trim(mb_strtolower($_mailStructure->replytoEmail));
        }

        if (isset($_mailStructure->fromName) && !empty($_mailStructure->fromName)) {
            $this->_fromName = trim($_mailStructure->fromName);
        }

        if (isset($_mailStructure->headers['message-id']) && $_mailStructure->headers['message-id'] != '') {
            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-4783 Error " trim() expects parameter 1 to be string, array given (./__apps/parser/library/MailParser/class.Parser\Library\MailParser\SWIFT_MailParserEmail.php:194)" while executing Email Parser script
             */
            if (is_array($_mailStructure->headers['message-id'])) {
                $this->_messageID = trim($_mailStructure->headers['message-id'][0]);
            } else {
                $this->_messageID = trim($_mailStructure->headers['message-id']);
            }
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-3281 'Destination Email Address' criteria in mail parser rule doesn't works if email queue address is specified as second recipient or greater in 'To:' field
         *
         * Comments: Adding and using To email list.
         */
        // Adding the To list
        $this->_toEmailList = implode(',', $_mailStructure->toEmailList);

        // Process the To Name & Email
        $_baseToEmail = false;
        if (isset($_mailStructure->toEmail) && !empty($_mailStructure->toEmail)) {
            $_baseToEmail = trim(mb_strtolower($_mailStructure->toEmail));
        } else if ($_hasDeliveredTo) {
            $_baseToEmail = trim(mb_strtolower($_mailStructure->headers['delivered-to']));

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-1700 Parsing emails with only a CC header
             * SWIFT-262 Parsing emails with only a BCC header
             *
             */
        } else if ($_hasPotentialQueueRecipient == true) {
            foreach (array_merge($_mailStructure->recipientAddresses, $_mailStructure->bccRecipientAddresses) as $_recipientEmail) {
                $_recipientEmail = mb_strtolower($_recipientEmail);
                if (isset($_queueCache['pointer'][$_recipientEmail])) {
                    $_baseToEmail = $_recipientEmail;

                    break;
                }
            }
        } else {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Exception('Invalid To Email or BCC');
            // @codeCoverageIgnoreEnd
        }

        $_matches = array();
        $_finalToEmail = $_baseToEmail;

        /**
         * BUG FIX - Anjali Sharma
         *
         * SWIFT-1486 The +username gets stripped off from 'support+username@domain.com' address when the mail is fetched in the help desk
         *
         * Comments: Added regex to check the email address with format, if clean up subject setting is enabled (Like: support+abc+r.abc123@acme.com) then remove appended characters and check with queue address.
         */
        if (($this->Settings->Get('t_cleanmailsubjects') == '1') && preg_match('/^(.*?)\+([a|r|t]\.\w+\.\w+)@(.*?)$/i', $_baseToEmail, $_matches)) {

            $_finalToEmail = $_matches[1] . '@' . $_matches[3];

            $this->_toEmailSuffix = $_matches[2];
        }

        $this->_toEmail = $_finalToEmail;

        if (isset($_mailStructure->toName) && !empty($_mailStructure->toName)) {
            $this->_toName = trim($_mailStructure->toName);
        }

        // Process the Reply-To Name & Email
        if (isset($_mailStructure->replytoEmail) && !empty($_mailStructure->replytoEmail)) {
            $this->_replyToEmail = trim(mb_strtolower($_mailStructure->replytoEmail));
        }

        if (isset($_mailStructure->replytoName) && !empty($_mailStructure->replytoName)) {
            $this->_replyToName = trim($_mailStructure->replytoName);
        }

        // Process the Mail Subject
        if (isset($_mailStructure->subject) && !empty($_mailStructure->subject)) {
            if (_is_array($_mailStructure->subject)) {
                $this->_subject = trim($_mailStructure->subject[0]);
            } else {
                $this->_subject = trim($_mailStructure->subject);
            }
        } elseif (isset($_mailStructure->headers['subject']) && !empty($_mailStructure->headers['subject'])) {
            $this->_subject = $_mailStructure->headers['subject'];
        } else {
            $this->_subject = '(no subject)';
        }
        $this->convertSubjectEncoding();

        // Process the Return Address Properties
        if (isset($_mailStructure->returnAddress) && !empty($_mailStructure->returnAddress)) {
            if (_is_array($_mailStructure->returnAddress)) {
                $this->_returnAddress = trim($_mailStructure->returnAddress[0]);
            } else if (is_string($_mailStructure->returnAddress)) {
                $this->_returnAddress = trim($_mailStructure->returnAddress);
            }
        }

        if (isset($_mailStructure->returnAddressEmail) && !empty($_mailStructure->returnAddressEmail)) {
            $this->_returnAddressEmail = trim(mb_strtolower($_mailStructure->returnAddressEmail));
        }

        if (isset($_mailStructure->returnAddressName) && !empty($_mailStructure->returnAddressName)) {
            $this->_returnAddressName = trim($_mailStructure->returnAddressName);
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4599 Parsing an email with only BCC in case of PIPE email queue.
         *
         * Comment: System should check BCC address as well as it could a scenario when there is only a BCC address in the email header.
         */
        // Process Email Recipients
        $_baseRecipientList = array();
        if (_is_array($_mailStructure->recipientAddresses) || _is_array($_mailStructure->bccRecipientAddresses)) {
            $_baseRecipientList = array_merge($_mailStructure->recipientAddresses, $_mailStructure->bccRecipientAddresses);
            // @codeCoverageIgnoreStart
        } else if ($_hasDeliveredTo) {
            $_baseRecipientList = array($_mailStructure->headers['delivered-to']);
            // @codeCoverageIgnoreEnd
        }

        foreach ($_baseRecipientList as $_key => $_recipient) {
            $_matches = array();
            $_finalRecipient = $_recipient;

            /*
             * BUG FIX - Anjali Sharma
             *
             * SWIFT-1486 The +username gets stripped off from 'support+username@domain.com' address when the mail is fetched in the help desk
             *
             * Comments: Added the toEmailSuffix check in case the queue address is in recipient list
             */
            if (($this->Settings->Get('t_cleanmailsubjects') == '1') && preg_match('/^(.*?)\+([a|r|t]\.\w+\.\w+)@(.*?)$/i', $_recipient, $_matches)) {

                if (empty($this->_toEmailSuffix)) {

                    $this->_toEmailSuffix = $_matches[2];
                }

                // Reverting as this is causing issue with Clean Subject emails.
                $_finalRecipient = $_matches[1] . '@' . $_matches[3];
            }

            $_baseRecipientList[$_key] = $_finalRecipient;
        }

        $this->_recipientsList = $_baseRecipientList;

        // Process the Email Contents
        if (isset($_mailStructure->text)) {
            $this->_textContents = $_mailStructure->text;
        }

        if (isset($_mailStructure->textSize)) {
            $this->_textSize = $_mailStructure->textSize;
        } else {
            $this->_textSize = strlen($this->_textContents);
        }

        if (isset($_mailStructure->textCharset)) {
            $this->_textCharset = $_mailStructure->textCharset;
        }

        if (isset($_mailStructure->html)) {
            // @codeCoverageIgnoreStart
            $this->_htmlContents = $this->StringCSSParser->SanitizeCSS($_mailStructure->html);
            // @codeCoverageIgnoreEnd
        }

        if (isset($_mailStructure->htmlSize)) {
            $this->_htmlSize = $_mailStructure->htmlSize;
        } else {
            $this->_htmlSize = strlen($this->_htmlContents);
        }

        if (isset($_mailStructure->htmlCharset)) {
            $this->_htmlCharset = $_mailStructure->htmlCharset;
        }

        // Process In Reply-To Header
        if (isset($_mailStructure->inReplyTo)) {
            $this->_inReplyTo = trim($_mailStructure->inReplyTo);
        }

        /*
         * BUG FIX - Pankaj Garg
         *
         * SWIFT-2099 Image is not rendered in Staff CP for emails having an embedded image in its body
         */
        // Process the Attachments
        if (isset($_mailStructure->attachments) && count($_mailStructure->attachments)) {
            foreach ($_mailStructure->attachments as $_attachmentContainer) {
                $this->_attachmentsContainer[] = array('data' => $_attachmentContainer['data'], 'size' => $_attachmentContainer['size'],
                    'filename' => $_attachmentContainer['filename'], 'extension' => mb_strtolower($_attachmentContainer['extension']),
                    'contenttype' => mb_strtolower($_attachmentContainer['contenttype']), 'contentid' => (isset($_attachmentContainer['contentid']) ? $_attachmentContainer['contentid'] : ''));
            }
        }

        // Unset the mail structure variable to free up some memory
        unset($_mailStructure->attachments);

        // This is where we parse out the original email content and decide which one to store, text or html
        if ($this->Settings->Get('pr_contentpriority') == 'text') {
            // Text is empty but we do have HTML content in there. This will happen in very rare cases
            if (trim($this->_textContents) == '') {
                $this->_finalContentIsHTML = true;
                $this->_finalContents = $this->_htmlContents;
                $this->_finalContentCharset = $this->_htmlCharset;
                $this->_finalContentSize = $this->_htmlSize;
            } else {
                $this->_finalContentIsHTML = false;
                $this->_finalContents = $this->_textContents;
                $this->_finalContentCharset = $this->_textCharset;
                $this->_finalContentSize = $this->_textSize;
            }
        } else if ($this->Settings->Get('pr_contentpriority') == 'html') {
            if (trim($this->_htmlContents) == '') {
                // @codeCoverageIgnoreStart
                $this->_finalContentIsHTML = false;
                $this->_finalContents = $this->_textContents;
                $this->_finalContentCharset = $this->_textCharset;
                $this->_finalContentSize = $this->_textSize;
                // @codeCoverageIgnoreEnd
            } else {
                $this->_finalContentIsHTML = true;
                $this->_finalContents = $this->_htmlContents;
                $this->_finalContentCharset = $this->_htmlCharset;
                $this->_finalContentSize = $this->_htmlSize;
            }
        }

        // Strip script tags if the setting is enabled.
        if ($this->Settings->Get('pr_stripscript') == '1') {
            $this->_finalContents = strip_javascript($this->_finalContents);
            $this->_finalContentSize = mb_strlen($this->_finalContents);
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1985 Mail parse issue if email contents are coming blank
         *
         */
        if (trim($this->_finalContents) == '') {
            $this->_finalContentIsHTML = false;
            $this->_textContents = $this->_htmlContents = $this->_finalContents = '(no text received)';
            $this->_textCharset = $this->_htmlCharset = $this->_finalContentCharset = 'UTF-8';
            $this->_textSize = $this->_htmlSize = $this->_finalContentSize = strlen($this->_finalContents);
        }

        return true;
    }

    protected function convertSubjectEncoding() {
        preg_match_all(self::SUBJECT_DECODE_REGX, $this->_subject, $matches, PREG_SET_ORDER, 0);
        if (is_array($matches) && !empty($matches)) {
            $encoding = $matches[0][1];
            $base64 = $matches[0][2];
            
            if (array_key_exists(strtolower($encoding), self::ENCODING_SUPERSEDE)) {
                $encoding = self::ENCODING_SUPERSEDE[strtolower($encoding)];
            }
            $data = base64_decode($base64);
            $this->_subject = mb_convert_encoding($data, 'UTF-8', $encoding);
        }
    }

    /**
     * Return the Email Object Properties
     *
     * @author Varun Shoor
     * @return string The Properties of this Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function __toString()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_returnString = 'From Name: ' . $this->GetFromName() . SWIFT_CRLF;
        $_returnString .= 'From Email: ' . $this->GetFromEmail() . SWIFT_CRLF;
        $_returnString .= 'To Email: ' . $this->GetToEmail() . SWIFT_CRLF;
        $_returnString .= 'To Email Suffix: ' . $this->GetToEmailSuffix() . SWIFT_CRLF;
        $_returnString .= 'To Name: ' . $this->GetToName() . SWIFT_CRLF;
        $_returnString .= 'Reply-To Email: ' . $this->GetReplyToEmail() . SWIFT_CRLF;
        $_returnString .= 'Reply-To Name: ' . $this->GetReplyToName() . SWIFT_CRLF;
        $_returnString .= 'Subject: ' . $this->GetSubject() . SWIFT_CRLF;
        $_returnString .= 'Return Address: ' . $this->GetReturnAddress() . SWIFT_CRLF;
        $_returnString .= 'Return Address Name: ' . $this->GetReturnAddressName() . SWIFT_CRLF;
        $_returnString .= 'Return Address Email: ' . $this->GetReturnAddressEmail() . SWIFT_CRLF;
        $_returnString .= 'Recipients List: ' . implode(', ', $this->GetRecipients()) . SWIFT_CRLF;
        $_returnString .= 'Text: ' . $this->GetText() . SWIFT_CRLF;
        $_returnString .= 'Text Size: ' . $this->GetTextSize() . SWIFT_CRLF;
        $_returnString .= 'Text Charset: ' . $this->GetTextCharset() . SWIFT_CRLF;
        $_returnString .= 'HTML: ' . $this->GetHTML() . SWIFT_CRLF;
        $_returnString .= 'HTML Size: ' . $this->GetHTMLSize() . SWIFT_CRLF;
        $_returnString .= 'HTML Charset: ' . $this->GetHTMLCharset() . SWIFT_CRLF;
        $_returnString .= 'Final Contents: ' . $this->GetFinalContents() . SWIFT_CRLF;
        $_returnString .= 'Final Content Size: ' . $this->GetFinalContentSize() . SWIFT_CRLF;
        $_returnString .= 'Final Content Charset: ' . $this->GetFinalContentCharset() . SWIFT_CRLF;
        $_returnString .= 'Final Content Is HTML?: ' . $this->GetFinalContentIsHTML() . SWIFT_CRLF;
        $_returnString .= 'In Reply-To: ' . $this->GetInReplyTo() . SWIFT_CRLF;
        $_returnString .= 'Attachments Container: ' . var_export($this->GetAttachments(), true) . SWIFT_CRLF;
        $_returnString .= 'Properties Container: ' . var_export($this->GetPropertyContainer(), true) . SWIFT_CRLF;
        $_returnString .= 'Bayesian Category ID: ' . $this->GetBayesianCategory() . SWIFT_CRLF;

        return $_returnString;
    }

    /**
     * Retrieve the bayesian category id
     *
     * @author Varun Shoor
     * @return int The Bayesian Category ID
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetBayesianCategory()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_bayesCategoryID;
    }

    /**
     * Set the Bayesian Category ID
     *
     * @author Varun Shoor
     *
     * @param int $_bayesCategoryID The Bayesian Category ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetBayesianCategory($_bayesCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_bayesCategoryID = $_bayesCategoryID;

        return true;
    }

    /**
     * Retrieve the From Name
     *
     * @author Varun Shoor
     * @return string The From Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFromName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('pr_parsereplyto') == '1' && $this->GetReplyToName() != '') {
            return $this->GetReplyToName();
        }

        if (empty($this->_fromName)) {
            return $this->_fromEmail;
        }

        // @codeCoverageIgnoreStart
        return $this->_fromName;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Retrieve the From Email
     *
     * @author Varun Shoor
     * @return string The From Email Address
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFromEmail()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('pr_parsereplyto') == '1' && $this->GetReplyToEmail() != '') {
            return $this->GetReplyToEmail();
        }

        return $this->_fromEmail;
    }

    /**
     * Retrieve the From Email
     *
     * @author Varun Shoor
     * @return string The From Email Address
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOriginalFromEmail()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_fromEmail;
    }

    /**
     * Retrieve the To Name
     *
     * @author Varun Shoor
     * @return string The To Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetToName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_toName;
    }

    /**
     * Retrieve the Destination Email Address
     *
     * @author Varun Shoor
     * @return string The Destination Email Address
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetToEmail()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_toEmail;
    }

    /**
     * Retrieve the Destination Email Address Suffix. The data that appears after + in varun.shoor+abc1234@opencart.com.vn
     *
     * @author Varun Shoor
     * @return string The Destination Email Address Suffix
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetToEmailSuffix()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_toEmailSuffix;
    }

    /**
     * Retrieve the Reply-To Name
     *
     * @author Varun Shoor
     * @return string The Reply-To Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetReplyToName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_replyToName;
    }

    /**
     * Retrieve the Reply-To Email
     *
     * @author Varun Shoor
     * @return string The Reply-To Email Address
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetReplyToEmail()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_replyToEmail;
    }

    /**
     * Retrieve the Mail Subject
     *
     * @author Varun Shoor
     * @return string The Email Subject
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSubject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_subject;
    }

    /**
     * Retrieve the Mail Subject
     *
     * @author Varun Shoor
     *
     * @param string $_emailSubject The Email Subject
     *
     * @return bool "true" on Success, "false" on failure
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetSubject($_emailSubject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_subject = $_emailSubject;
    }

    /**
     * Retrieve the original return address
     *
     * @author Varun Shoor
     * @return string The Return Address
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetReturnAddress()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_returnAddress;
    }

    /**
     * Retrieve the return address name
     *
     * @author Varun Shoor
     * @return string The Return Address Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetReturnAddressName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_returnAddressName;
    }

    /**
     * Retrieve the Return Address Email
     *
     * @author Varun Shoor
     * @return string The Return Address Email
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetReturnAddressEmail()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_returnAddressEmail;
    }

    /**
     * Retrieve the recipients list
     *
     * @author Varun Shoor
     * @return array The Email Recipients List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRecipients()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_recipientsList;
    }

    /**
     * Retrieve the Email Attachments
     *
     * @author Varun Shoor
     * @return array The Email Attachment Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAttachments()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_attachmentsContainer;
    }

    /**
     * Retrieve the In Reply-To Message ID
     *
     * @author Varun Shoor
     * @return string The Message ID
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetInReplyTo()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_inReplyTo;
    }

    /**
     * Retrieve the Email Plain Text Contents
     *
     * @author Varun Shoor
     * @return string The Email Plain Text Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetText()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_textContents;
    }

    /**
     * Retrieve the Email Plain Text Charset
     *
     * @author Varun Shoor
     * @return string The Email Plain Text Character Set
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTextCharset()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_textCharset;
    }

    /**
     * Retrieve the Email Plain Text Size
     *
     * @author Varun Shoor
     * @return int The Email Plain Text Size
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTextSize()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_textSize;
    }

    /**
     * Retrieve the Email HTML Contents
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetHTML()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_htmlContents;
    }

    /**
     * Retrieve the Email HTML Contents Size
     *
     * @author Varun Shoor
     * @return int The Email HTML Contents Size
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetHTMLSize()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_htmlSize;
    }

    /**
     * Retrieve the Email HTML Contents Charset
     *
     * @author Varun Shoor
     * @return string The Email HTML Contents Character Set
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetHTMLCharset()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_htmlCharset;
    }

    /**
     * Retrieve the Final Email Contents
     *
     * @author Varun Shoor
     * @return string The Final Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFinalContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_finalContents;
    }

    /**
     * Retrieve the Final Contents Size
     *
     * @author Varun Shoor
     * @return int The Final Contents Size
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFinalContentSize()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_finalContentSize;
    }

    /**
     * Retrieve the Final Contents Charset
     *
     * @author Varun Shoor
     * @return string The Final Contents Character Set
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFinalContentCharset()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_finalContentCharset;
    }

    /**
     * Retrieve the Final Contents Is HTML Property
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFinalContentIsHTML()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_finalContentIsHTML;
    }

    /**
     * Retrieve the Property Container
     *
     * @author Varun Shoor
     * @return array The Property Container for this Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPropertyContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_propertyContainer;
    }

    /**
     * Retrieve a custom property set for this email object
     *
     * @author Varun Shoor
     *
     * @param string $_propertyName The Property Name
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_propertyName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_propertyContainer[$_propertyName])) {
            return false;
        }

        return $this->_propertyContainer[$_propertyName];
    }

    /**
     * Set a custom property for this email
     *
     * @author Varun Shoor
     *
     * @param string $_propertyName  The Property Name
     * @param mixed $_propertyValue The Property Value
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetProperty($_propertyName, $_propertyValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_propertyName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_propertyContainer[$_propertyName] = $_propertyValue;
    }

    /**
     * Processes the Content-Type and returns Mime Type and Charset.
     *
     * @author Varun Shoor
     *
     * @param string $_dataString The Data String
     *
     * @return array The Content Type Chunks
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessContentType($_dataString)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_returnValue = array();
        $_contentTypeParts = explode(';', $_dataString);
        $_returnValue['mimetype'] = trim($_contentTypeParts[0]);

        reset($_contentTypeParts);
        next($_contentTypeParts);


        /**
         * KAYAKOC-6082 <banjo.paul@aurea.com>
         * each() has been deprecated, I'm replacing its function here with a foreach()
         */
        foreach ($_contentTypeParts as $_value) {
            $_matches = array();
            if (preg_match('/([^=\s]*)=["]?([^"]*)["]?/', $_value, $_matches) &&
                !empty($_matches[1]) && !empty($_matches[2])) {
                $_returnValue[trim(mb_strtolower($_matches[1]))] = trim($_matches[2]);
            }
        }

        return $_returnValue;
    }

    /**
     * Retrieve the Mail Date
     *
     * @author Mahesh Salaria
     * @return string|int
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDate()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-2154 Creation date is showing up as 31st december 1969 in case of wrong date in raw headers
         *
         */
        if (empty($this->_dateTime) || (int)($this->_dateTime) < 1000) {
            return DATENOW;
        }

        return $this->_dateTime;
    }

    /**
     * Retrieve the Email TO list
     *
     * @author Ravi Sharma <ravi.sharma@opencart.com.vn>
     * @return string the comma separated
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetToEmailList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_toEmailList;
    }

    /**
     * Retrieve the Message ID
     *
     * @author Ravi Sharma <ravi.sharma@opencart.com.vn>
     * @return string the message id
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMessageID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_messageID;
    }
}

?>
