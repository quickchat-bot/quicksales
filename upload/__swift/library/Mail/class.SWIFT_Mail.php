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

require_once ('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_THIRDPARTYDIRECTORY . '/SwiftMailer/swiftmailer_required.php');
use Parser\Models\EmailQueue\SWIFT_EmailQueue;

/**
 * The Mail Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Mail extends SWIFT_Model
{

    private $_addedFieldCount = 0;
    private $SwiftMailer = false;
    private $SwiftMessage = false;
    private $_mailerType = false;
    private $_swiftSMTPResource = false;
    private $_isHTML = false;
    private $_swiftMailContainer = array();
    private $_mailCC = array();
    private $_mailBCC = array();
    private $_mailTo = array();
    private $_mailToEmailList = array();

    public $MailQueueManager;

    // Core Constants
    const TYPE_SWIFTMAILER = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct($_mailerType = 3)
    {
        parent::__construct();
        _swiftmailer_init();
        $this->_isHTML = (int) ($this->Settings->Get('cpu_enablehtmlmails'));
        if ($_mailerType == self::TYPE_SWIFTMAILER) {
            $this->_mailerType = self::TYPE_SWIFTMAILER;
            if (SWIFT_INTERFACE !== 'tests') {
                $this->SwiftMessage = SwiftMailer_Message::newInstance();
                /**
                 * BUG Fix: Ashish Kataria
                 *
                 * SWIFT-2616 Notifications do not respect the mail encoding configured in Admin CP
                 * SWIFT-2439 Even if you select Base64 and Binary under Outgoing emails, it always use 'Quoted printable' . It doesn't send it in that format that is selected
                 *
                 * Comments: None
                 */
                if ($this->Settings->Get('cpu_messageencoding') == "quoted-printable") {
                    $this->SwiftMessage->setEncoder(SwiftMailer_Encoding::getQpEncoding());
                } else {
                    if ($this->Settings->Get('cpu_messageencoding') == "base64") {
                        $this->SwiftMessage->setEncoder(SwiftMailer_Encoding::getBase64Encoding());
                    } else {
                        if ($this->Settings->Get('cpu_messageencoding') == "binary") {
                            $this->SwiftMessage->setEncoder(SwiftMailer_Encoding::get8BitEncoding());
                        }
                    }
                }
                $this->SwiftMessage->setPriority((int)($this->Settings->Get('cpu_maildefaultpriority')));
            }
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Attach a File
     *
     * @author Varun Shoor
     * @param string $_content The Contents of the File
     * @param string $_mimeType The File Mime TYpe
     * @param string $_fileName The Filename
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Attach($_content, $_mimeType, $_fileName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            $_attachment = SwiftMailer_Attachment::newInstance($_content, $_fileName, $_mimeType);
            $this->SwiftMessage->attach($_attachment);
        }

        return true;
    }

    /**
     * Embed a File
     *
     * @author   Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @param string $_content  The file content
     * @param string $_mimeType The file mime type
     * @param string $_fileName The file name
     *
     * @return string cid
     * @throws SWIFT_Exception
     */
    public function Embed($_content, $_mimeType, $_fileName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            $_attachment = SwiftMailer_Image::newInstance($_content, $_fileName, $_mimeType)->setDisposition('inline');
            return $this->SwiftMessage->embed($_attachment);
        }

        return '';
    }

    /**
     * Sets the From Field for the Outgoing Email
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Email Address
     * @param string $_name The Name (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetFromField($_emailAddress, $_name = '')
    {

        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            $this->SwiftMessage->setFrom(array($_emailAddress => $_name));
            $this->SwiftMessage->setReplyTo($_emailAddress);
            $this->SwiftMessage->setReturnPath($_emailAddress);
        }

        $this->_swiftMailContainer['fromname'] = $_name;
        $this->_swiftMailContainer['fromemail'] = $_emailAddress;

        return true;
    }

    /**
     * @author Mansi Wason<mansi.wason@kayako.com>
     *
     * @param array $referencesMessageID
     *
     * @return self|false
     */
    public function SetReferences($referencesMessageID)
    {
        if (!_is_array($referencesMessageID)) {
            return false;
        }

        $this->SwiftMessage->getHeaders()
                           ->addTextHeader('References', implode(' ', $referencesMessageID));

        return $this;
    }

    /**
     * Sets the Destination Email Address
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Email Address
     * @param string $_name The Name (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetToField($_emailAddress, $_name = '')
    {
        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            if (!empty($_name)) {
                $this->SwiftMessage->addTo($_emailAddress, $_name);
            } else {
                $this->SwiftMessage->addTo($_emailAddress);
            }
        }

        $this->_mailTo[] = array('address' => $_emailAddress, 'name' => $_name);
        $this->_swiftMailContainer['toname'] = $_name;
        $this->_swiftMailContainer['toemail'] = $_emailAddress;

        return true;
    }

    /**
     * Adds the Destination Email Address
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Email Address
     * @param string $_name The Name (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function AddToField($_emailAddress, $_name = '')
    {
        $_emailAddress = mb_strtolower($_emailAddress);

        if (in_array($_emailAddress, $this->_mailToEmailList)) {
            return true;
        }

        $this->_mailTo[] = array('address' => $_emailAddress, 'name' => $_name);
        $this->_mailToEmailList[] = $_emailAddress;
        $this->_addedFieldCount++;

        return true;
    }

    /**
     * Sets the Destination Email Address
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Email Address
     * @param string $_name The Name (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function OverrideToField($_emailAddress, $_name = '')
    {
        $_emailAddress = mb_strtolower($_emailAddress);

        if (in_array($_emailAddress, $this->_mailToEmailList)) {
            return true;
        }

        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            if (!empty($_name)) {
                $this->SwiftMessage->setTo($_emailAddress, $_name);
            } else {
                $this->SwiftMessage->setTo($_emailAddress);
            }
        }

        $this->_mailTo = array(array('address' => $_emailAddress, 'name' => $_name));
        $this->_swiftMailContainer['toname'] = $_name;
        $this->_swiftMailContainer['toemail'] = $_emailAddress;

        return true;
    }

    /**
     * Deletes the Destination Email Address
     *
     * @author Parminder Singh
     * @param string $_emailAddress The Email Address (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function DelToField($_emailAddress = '')
    {
    }

    /**
     * Sets the Email Subject
     *
     * @author Varun Shoor
     * @param string $_subject The Email Subject
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetSubjectField($_subject)
    {
        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            $this->SwiftMessage->setSubject($_subject);
        }

        $this->_swiftMailContainer['subject'] = $_subject;

        return true;
    }

    /**
     * Sets the Data As Text
     *
     * @author Varun Shoor
     * @param string $_content The Content Holder
     * @param bool $_forceAddPart
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetDataText($_content, $_forceAddPart = false)
    {
        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            if ($_forceAddPart) {
                $this->SwiftMessage->addPart($_content, 'text/plain');
            } else {
                $this->SwiftMessage->setBody($_content, 'text/plain');
            }
        }

        $this->_swiftMailContainer['text'] = $_content;

        return true;
    }

    /**
     * Sets the Data as HTML
     *
     * @author Varun Shoor
     *
     * @param mixed $_content
     * @param bool   $_forceAddPart
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetDataHTML($_content, $_forceHTML = false, $_forceAddPart = true)
    {
        $this->_swiftMailContainer['html'] = '';

        if ($this->Settings->Get('cpu_enablehtmlmails') != '1' && $_forceHTML == false) {
            return false;
        }

        $_content = AutoLink($_content);

        // convert inline images into embedded attachments
        // Should be processed by SWIFT_MailQueueManager
        $_isPartAllowed = true;    
        if (!$_forceHTML){
            $_images = [];
            $_content = preg_replace_callback('~(["\'])data:([^;]+);base64,\s*([a-zA-Z0-9+/\\=]+)~', function($matches) use(&$_images){
            $crc = hash('crc32', $matches[3]);
            $_images[$crc] = [
                'type' => $matches[2],
                'data' => $matches[3],
            ];
            return $matches[1] . "cid:$crc";
        }, $_content);
            $_search = $_replace = [];
            foreach ($_images as $crc => $image) {
                $filename = $crc . '.' . GetExtensionFromContentType($image['type']);
                $content = base64_decode($image['data']);
                if (false !== $content) {
                    $cid = $this->Embed($content, $image['type'], $filename);
                    $_search[] = "cid:$crc";
                    $_replace[] = $cid;
                }
                $_isPartAllowed = false;
            }
            $_content = str_replace($_search, $_replace, $_content);
        }

        // If inline image present. Don't add as part otherwise images might be processed as attachment
        if ($_isPartAllowed){
            if ($this->_mailerType == self::TYPE_SWIFTMAILER && $this->SwiftMessage) {
                // Checking whether we have already set text content, then add as multipart
                if (!empty($this->_swiftMailContainer['text']) && $_forceAddPart) {
                    $this->SwiftMessage->addPart($_content, 'text/html');
                } else {
                        $this->SwiftMessage->setBody($_content, 'text/html');
                }
            }
        }
        else {
            $this->SwiftMessage->setBody($_content, 'text/html');
        }

        $this->_isHTML = true;

        $this->_swiftMailContainer['html'] = $_content;

        return true;
    }

    /**
     * Gets the Probable Local Hostname for this System
     *
     * @author Varun Shoor
     * @return string Local Hostname
     */
    public static function GetMailLocalHost()
    {
        if (!empty($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        if (!empty($_SERVER['HOST'])) {
            return $_SERVER['HOST'];
        }

        return "localhost";
    }

    /**
     * Add a CC Recipient
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Email Address
     * @param string $_name The Name (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function AddCC($_emailAddress, $_name = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_emailAddress)) {
            return false;
        }

        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            if (!empty($_name)) {
                $this->SwiftMessage->addCc($_emailAddress, $_name);
            } else {
                $this->SwiftMessage->addCc($_emailAddress);
            }
        }

        $this->_mailCC[] = array('address' => $_emailAddress, 'name' => $_name);

        return true;
    }

    /**
     * Add a BCC Recipient
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Email Address
     * @param string $_name The Name (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function AddBCC($_emailAddress, $_name = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_emailAddress)) {
            return false;
        }

        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            if (!empty($_name)) {
                $this->SwiftMessage->addBcc($_emailAddress, $_name);
            } else {
                $this->SwiftMessage->addBcc($_emailAddress);
            }
        }

        $this->_mailBCC[] = array('address' => $_emailAddress, 'name' => $_name);

        return true;
    }

    /**
     * Connects to SMTP Server
     *
     * @author Varun Shoor
     * @param int $_emailQueueID (OPTIONAL) The Email Queue ID for Custom SMTP Dispatch
     * @return SwiftMailer_SmtpTransport|bool "true" on Success, "false" otherwise
     */
    public function ConnectSMTP($_emailQueueID = null)
    {
        $_emailQueueCache = $this->Cache->Get('queuecache');

        $_smtpHost = $this->Settings->Get('cpu_smtphost');

        $_smtpPort = false;

        if ($this->Settings->Get('cpu_smtptype') == 'nonssl') {
            $_smtpPort = $this->Settings->Get('cpu_smtpport');
        } else {
            $_smtpPort = $this->Settings->Get('cpu_smtpportssl');
        }

        $_smtpVssl = $this->Settings->Get('cpu_smtptype');

        $_smtpUser = $_smtpPass = $_authType = '';
        $_useEmailQueue = false;
        $_tokenEndpoint = $_clientId = $_clientSecret = $_refreshToken = '';
        $_tokenExpiry = 0;

        if ($this->Settings->Get('cpu_smtpuseauth') == '1') {
            $_smtpUser = $this->Settings->Get('cpu_smtpuser');
            $_smtpPass = $this->Settings->Get('cpu_smtppass');
        }

        if (!empty($_emailQueueID) && isset($_emailQueueCache['list'][$_emailQueueID]) && SWIFT_App::IsInstalled(APP_PARSER)) {
            SWIFT_Loader::LoadModel('EmailQueue:EmailQueue', APP_PARSER);
            $_SWIFT_EmailQueueObject = SWIFT_EmailQueue::Retrieve($_emailQueueID);

            if ($_SWIFT_EmailQueueObject instanceof SWIFT_EmailQueue && $_SWIFT_EmailQueueObject->GetIsClassLoaded() && $_SWIFT_EmailQueueObject->GetProperty('usequeuesmtp') == '1') {
                $_smtpUser = $_SWIFT_EmailQueueObject->GetProperty('username') != '' ? $_SWIFT_EmailQueueObject->GetProperty('username') : $_SWIFT_EmailQueueObject->GetProperty('email');
                $_encrypted = $_SWIFT_EmailQueueObject->GetProperty('userpassword');
                $_useEmailQueue = true;
                try {
                    // try to decrypt the password if it's already encrypted
                    $_smtpPass = SWIFT_Cryptor::Decrypt($_encrypted);
                } catch (\Exception $ex) {
                    // if the password is not encrypted, use it
                    $_smtpPass = $_encrypted;
                }


                $_smtpVssl = $_SWIFT_EmailQueueObject->GetProperty('smtptype');
                $_smtpPort = $_smtpVssl == 'nonssl' ? '25' : '587';
                $_queueSMTPHost = $_SWIFT_EmailQueueObject->GetProperty('smtphost');
                $_queueSMTPPort = $_SWIFT_EmailQueueObject->GetProperty('smtpport');
                $_authType = $_SWIFT_EmailQueueObject->GetProperty('authtype');

                if ($_queueSMTPHost != ''){
                    $_smtpHost = $_queueSMTPHost;
                }
                if ($_queueSMTPPort != ''){
                    $_smtpPort = $_queueSMTPPort;
                }


                if ($_authType == 'oauth'){
                    $_smtpPass = $_SWIFT_EmailQueueObject->GetProperty('accesstoken');
                    $_tokenExpiry = (int)$_SWIFT_EmailQueueObject->GetProperty('tokenexpiry');
                    $_tokenEndpoint =  $_SWIFT_EmailQueueObject->GetProperty('tokenendpoint');
                    $_clientId = $_SWIFT_EmailQueueObject->GetProperty('clientid');
                    $_clientSecret = $_SWIFT_EmailQueueObject->GetProperty('clientsecret'); 
                    $_refreshToken = $_SWIFT_EmailQueueObject->GetProperty('refreshtoken');
                }
            }
        }


        // We reset the value if we arent using SSL.
        if ($_smtpVssl == 'nonssl') {
            $_smtpVssl = null;
        }

        if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
            /*
             * BUG FIX - Varun Shoor
             * IMPROVEMENT - Mansi Wason
             *
             * SWIFT-1300 SMTP does not works with SMTP Type TLS
             * SWIFT-4926 Add SMTP hostname under connectSMTP function
             *
             */
            $_smtpResource = false;
            if ($_useEmailQueue && $_authType == 'oauth'){
                //Expire within the minute
                $_validExpiryTime = DATENOW + 60;

                if ($_tokenExpiry < $_validExpiryTime){
                    $_tokens = \SWIFT_OAuth::refreshToken($_tokenEndpoint, $_clientId, $_clientSecret, $_refreshToken);
                    if (isset($_tokens) && isset($_tokens["access_token"])) {
                        $_dbFields = array("accesstoken" => $_tokens["access_token"]);
                        $_smtpPass = $_tokens["access_token"];
                        if (isset($_tokens["refresh_token"])) {
                            $_dbFields["refreshtoken"] = $_tokens["refresh_token"];
                        }
                        if (isset($_tokens["expires_in"])){
                            $_dbFields["tokenexpiry"] = DATENOW + $_tokens["expires_in"];
                        }
                        $this->Database->AutoExecute(TABLE_PREFIX . "emailqueues", $_dbFields, "UPDATE", "emailqueueid = " . $_emailQueueID);
                    }
                }
                
                if ($_smtpVssl == 'tls') {
                    $_smtpResource = SwiftMailer_SmtpTransport::newInstance($_smtpHost, $_smtpPort, $_smtpVssl)
                            ->setAuthMode('XOAUTH2')
                            ->setEncryption('tls')
                            ->setUsername($_smtpUser)
                            ->setPassword($_smtpPass)
                            ->setLocalDomain($_smtpHost);
                } else {
                    $_smtpResource = SwiftMailer_SmtpTransport::newInstance($_smtpHost, $_smtpPort, $_smtpVssl)
                            ->setAuthMode('XOAUTH2')
                            ->setUsername($_smtpUser)
                            ->setPassword($_smtpPass)
                            ->setLocalDomain($_smtpHost);
                }
            }
            else {
                if ($_smtpVssl == 'tls') {
                    $_smtpResource = SwiftMailer_SmtpTransport::newInstance($_smtpHost, $_smtpPort, $_smtpVssl)
                            ->setEncryption('tls')
                            ->setUsername($_smtpUser)
                            ->setPassword($_smtpPass)
                            ->setLocalDomain($_smtpHost);
                } else {
                    $_smtpResource = SwiftMailer_SmtpTransport::newInstance($_smtpHost, $_smtpPort, $_smtpVssl)
                            ->setUsername($_smtpUser)
                            ->setPassword($_smtpPass)
                            ->setLocalDomain($_smtpHost);
                }
            }

            if (!is_object($_smtpResource)) {
                SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_MAILERROR, $this->Language->Get('errorsmtpconnect'), '');
            }

            $this->_swiftSMTPResource = $_smtpResource;

            return $_smtpResource;
        }

        return false;
    }

    /**
     * Retrieves the combined email list of To, CC & BCC Emails
     *
     * @author Varun Shoor
     * @return array Email List Holder
     */
    private function GetEmailList()
    {
        $_emailList = array();

        // ======= TO =======
        if (_is_array($this->_mailTo)) {
            foreach ($this->_mailTo as $_key => $_val) {
                if (!in_array(strtolower($_val['address']), $_emailList)) {
                    $_emailList[] = strtolower($_val['address']);
                }
            }
        }

        // ======= CC =======
        if (_is_array($this->_mailCC)) {
            foreach ($this->_mailCC as $_key => $_val) {
                if (!in_array(strtolower($_val['address']), $_emailList)) {
                    $_emailList[] = strtolower($_val['address']);
                }
            }
        }

        // ======= BCC =======
        if (_is_array($this->_mailBCC)) {
            foreach ($this->_mailBCC as $_key => $_val) {
                if (!in_array(strtolower($_val['address']), $_emailList)) {
                    $_emailList[] = strtolower($_val['address']);
                }
            }
        }

        return $_emailList;
    }

    /**
     * Sends the Mail
     *
     * @author Varun Shoor
     * @param bool $_useMailQueue Whether or not to use the inbuilt Mail Queue for dispatching email
     * @param int $_emailQueueID (OPTIONAL) The Email Queue ID to check for custom SMTP server
     * @return bool "true" on Success, "false" otherwise
     */
    public function SendMail($_useMailQueue = false, $_emailQueueID = null)
    {
        // Check to see if mail queue is enabled
        if ($this->Settings->Get('cpu_enablemailqueue') != 1) {
            $_useMailQueue = false;
        }

        $_result = false;

        // Check to see if we need to use smtp
        $_mailType = 'mail';
        $_smtpResource = false;

        if ($this->Settings->Get('cpu_enablesmtp') == '1') {
            $_mailType = 'smtp';

            if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
                $_smtpResource = $this->ConnectSMTP($_emailQueueID);
            }
        }

        if (!$_useMailQueue) {
            if ($this->_mailerType == self::TYPE_SWIFTMAILER && SWIFT_INTERFACE !== 'tests') {
                if ($_mailType == 'smtp') {
                    $this->SwiftMailer = SwiftMailer_Mailer::newInstance($_smtpResource);
                    $_result = $this->SwiftMailer->send($this->SwiftMessage);
                } else {
                    $_localResource = SwiftMailer_MailTransport::newInstance();
                    $this->SwiftMailer = SwiftMailer_Mailer::newInstance($_localResource);
                    $_result = $this->SwiftMailer->send($this->SwiftMessage);
                }
            }
        } else {
            $this->Load->Model('MailQueueManager', [], true, false, '');

            foreach ($this->GetEmailList() as $_val) {
                $this->MailQueueManager->AddToQueue($_val, $this->_swiftMailContainer['fromemail'], $this->_swiftMailContainer['fromname'], $this->_swiftMailContainer['subject'], $this->_swiftMailContainer['text'], $this->_swiftMailContainer['html'], IIF(!empty($this->_isHTML), '1', '0'), false);
            }

            $this->MailQueueManager->RecountMailQueue();
            $this->MailQueueManager->ProcessMailQueue();
        }

        return $_result;
    }

    public function GetInstance() {
        return new self();
    }
}
