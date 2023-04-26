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

namespace Parser\Models\EmailQueue;

use Parser\Library\EmailQueue\SWIFT_EmailQueueType;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_Exception;

/**
 * The Email Queue Mailbox Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_EmailQueueMailbox extends SWIFT_EmailQueue
{
    // Core Constants
    const SMTP_NONSSL = 'nonssl';
    const SMTP_TLS = 'tls';
    const SMTP_SSL = 'ssl';
    const SMTP_SSLV2 = 'sslv2';
    const SMTP_SSLV3 = 'sslv3';

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     *
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct($_SWIFT_DataObject);
    }

    /**
     * Check to see if its a valid SMTP Type
     *
     * @author Varun Shoor
     *
     * @param mixed $_smtpType The SMTP Type
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidSMTPType($_smtpType)
    {
        if ($_smtpType == self::SMTP_TLS || $_smtpType == self::SMTP_SSL || $_smtpType == self::SMTP_SSLV2 || $_smtpType == self::SMTP_SSLV3 ||
            $_smtpType == self::SMTP_NONSSL) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Email Queue
     *
     * @author Varun Shoor
     *
     * @param string $_queueEmail The Queue Email Address
     * @param SWIFT_EmailQueueType $_EmailQueueTypeObject The Email Queue Type Object Pointer (NEWS/TICKETS)
     * @param string $_fetchType The Queue Fetch Type
     * @param string $_queuePrefix The Queue Prefix
     * @param string $_customFromName The Custom From Name
     * @param string $_customFromEmail The Custom From Email
     * @param string $_queueSignature The Queue Signature
     * @param bool $_registrationRequired Whether the user should be registered for message acceptance to work
     * @param bool $_isEnabled Whether this Queue is Enabled
     * @param string $_hostName The Mailbox Hostname
     *
     * @return mixed "Parser\Models\EmailQueue\SWIFT_EmailQueueMailbox" (OBJECT) on Success, "false" otherwise
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception When Invalid Data is Specified or the Object couldnt be created
     * @throws SWIFT_Exception
     */
    public static function Create($_queueEmail, $_EmailQueueTypeObject, $_fetchType, $_queuePrefix, $_customFromName,
                                  $_customFromEmail, $_queueSignature, $_registrationRequired, $_isEnabled, $_hostName = null)
    {
        /** @var int $_port The Mailbox Port */
        $_port = func_get_arg(10);
        /** @var string $_userName The Mailbox User Name */
        $_userName = func_get_arg(11);
        /** @var string $_userPassword The Mailbox Password */
        $_userPassword = func_get_arg(12);
        /** @var string $_authType The selected authentication type */
        $_authType = func_get_arg(13);
        /** @var string $_authClientId The OAuth Client Id */
        $_authClientId = func_get_arg(14);
        /** @var string $_authClientSecret The OAuth Client Secret */
        $_authClientSecret = func_get_arg(15);
        /** @var string $_authEndpoint The OAuth authorization endpoint */
        $_authEndpoint = func_get_arg(16);
        /** @var string $_tokenEndpoint The OAuth token endpoint */
        $_tokenEndpoint = func_get_arg(17);
        /** @var string $_authScope the OAuth Scope */
        $_authScope = func_get_arg(18);
        /** @var string $_accessToken The access token */
        $_accessToken = func_get_arg(19);
        /** @var string $_refreshToken The refresh token */
        $_refreshToken = func_get_arg(20);
        /** @var string $_tokenExpiry The token expiry*/
        $_tokenExpiry = func_get_arg(21);
        /** @var bool $_leaveCopyOnServer Whether to Leave Copy on Server */
        $_leaveCopyOnServer = func_get_arg(22);
        /** @var bool $_useQueueSMTP Whether to use Queue SMTP (for all objects created from this queue) */
        $_useQueueSMTP = func_get_arg(23);
        /** @var mixed $_smtpHost The Queue SMTP Host */
        $_smtpHost = func_get_arg(24);
        /** @var mixed $_smtpPort The Queue SMTP Port */
        $_smtpPort = func_get_arg(25);
        /** @var mixed $_smtpType The Queue SMTP Type */
        $_smtpType = func_get_arg(26);
        /** @var bool $_forceQueue (OPTIONAL) */
        $_forceQueue = func_num_args() == 28 ? func_get_arg(27) : true;

        if (!self::IsValidSMTPType($_smtpType) || $_fetchType == self::FETCH_PIPE) {
            throw new SWIFT_EmailQueue_Exception('Invalid Data Specified');
        }

        $_emailQueueID = parent::Create($_queueEmail, $_fetchType, $_EmailQueueTypeObject, $_queuePrefix, $_customFromName, $_customFromEmail,
            $_queueSignature, $_registrationRequired, $_isEnabled, false);

        // @codeCoverageIgnoreStart
        $_SWIFT_EmailQueueMailboxObject = new SWIFT_EmailQueueMailbox(new SWIFT_DataID($_emailQueueID));
        if (!$_SWIFT_EmailQueueMailboxObject instanceof SWIFT_EmailQueueMailbox || !$_SWIFT_EmailQueueMailboxObject->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception('Unable to Load Email Queue (MAILBOX)');
        }
        // @codeCoverageIgnoreEnd

        $_SWIFT_EmailQueueMailboxObject->UpdatePool('host', $_hostName);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('port', $_port);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('username', $_userName);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('userpassword', $_userPassword);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('authtype', $_authType);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('clientid', $_authClientId);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('clientsecret', $_authClientSecret);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('authendpoint', $_authEndpoint);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('tokenendpoint', $_tokenEndpoint);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('authscopes', $_authScope);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('accesstoken', $_accessToken);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('refreshtoken', $_refreshToken);        
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('tokenexpiry', $_tokenExpiry);        
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('leavecopyonserver', (int)($_leaveCopyOnServer));
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('usequeuesmtp', (int)($_useQueueSMTP));
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('forcequeue', (int)($_forceQueue));
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('smtphost', $_smtpHost);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('smtpport', $_smtpPort);
        $_SWIFT_EmailQueueMailboxObject->UpdatePool('smtptype', $_smtpType);

        $_SWIFT_EmailQueueMailboxObject->ProcessUpdatePool();

        self::RebuildCache();

        return $_SWIFT_EmailQueueMailboxObject;
    }

    /**
     * Update the Email Queue Record
     *
     * @author Varun Shoor
     *
     * @param string $_queueEmail The Queue Email Address
     * @param SWIFT_EmailQueueType $_EmailQueueTypeObject The Email Queue Type Object Pointer (NEWS/TICKETS)
     * @param string $_fetchType The Queue Fetch Type
     * @param string $_queuePrefix The Queue Prefix
     * @param string $_customFromName The Custom From Name
     * @param string $_customFromEmail The Custom From Email
     * @param string|bool $_queueSignature The Queue Signature
     * @param bool $_registrationRequired Whether the user should be registered for message acceptance to work
     * @param bool $_isEnabled Whether this Queue is Enabled
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception If the Class is not Loaded or If Invalid Data is Provided
     * @throws \Parser\Library\EmailQueue\SWIFT_EmailQueue_Exception
     */
    public function Update($_queueEmail, $_EmailQueueTypeObject, $_fetchType, $_queuePrefix, $_customFromName,
                           $_customFromEmail, $_queueSignature, $_registrationRequired, $_isEnabled = true)
    {
        /** @var string $_hostName The Mailbox Hostname */
        $_hostName = func_get_arg(9);
        /** @var int $_port The Mailbox Port */
        $_port = func_get_arg(10);
        /** @var string $_userName The Mailbox User Name */
        $_userName = func_get_arg(11);
        /** @var string $_userPassword The Mailbox Password */
        $_userPassword = func_get_arg(12);
        /** @var string $_authType The selected authentication type */
        $_authType = func_get_arg(13);
        /** @var string $_authClientId The OAuth Client Id */
        $_authClientId = func_get_arg(14);
        /** @var string $_authClientSecret The OAuth Client Secret */
        $_authClientSecret = func_get_arg(15);
        /** @var string $_authEndpoint The OAuth authorization endpoint */
        $_authEndpoint = func_get_arg(16);
        /** @var string $_tokenEndpoint The OAuth token endpoint */
        $_tokenEndpoint = func_get_arg(17);
        /** @var string $_authScope the OAuth Scope */
        $_authScope = func_get_arg(18);
        /** @var string $_accessToken The access token */
        $_accessToken = func_get_arg(19);
        /** @var string $_refreshToken The refresh token */
        $_refreshToken = func_get_arg(20);
        /** @var string $_tokenExpiry The token expiry */
        $_tokenExpiry = func_get_arg(21);
        /** @var bool $_leaveCopyOnServer Whether to Leave Copy on Server */
        $_leaveCopyOnServer = func_get_arg(22);
        /** @var bool $_useQueueSMTP Whether to use Queue SMTP (for all objects created from this queue) */
        $_useQueueSMTP = func_get_arg(23);
        /** @var mixed $_smtpHost The Queue SMTP Host */
        $_smtpHost = func_get_arg(24);
        /** @var mixed $_smtpPort The Queue SMTP Port */
        $_smtpPort = func_get_arg(25);
        /** @var mixed $_smtpType The Queue SMTP Type */
        $_smtpType = func_get_arg(26);
        /** @var bool $_forceQueue (OPTIONAL) */
        $_forceQueue = func_num_args() == 28 ? func_get_arg(27) : true;

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidSMTPType($_smtpType) || $this->GetProperty('fetchtype') == self::FETCH_PIPE) {
            throw new SWIFT_EmailQueue_Exception('Invalid Data Specified');
        }

        // @codeCoverageIgnoreStart
        $_updateResult = parent::Update($_queueEmail, $_EmailQueueTypeObject, $_queuePrefix, $_customFromName, $_customFromEmail, $_queueSignature,
            $_registrationRequired, $_isEnabled, false);
        if (!$_updateResult) {
            throw new SWIFT_EmailQueue_Exception('Failed to Update Email Queue (MAILBOX)');
        }
        // @codeCoverageIgnoreEnd

        $this->UpdatePool('fetchtype', $_fetchType);
        $this->UpdatePool('host', $_hostName);
        $this->UpdatePool('port', $_port);
        $this->UpdatePool('username', $_userName);
        $this->UpdatePool('userpassword', $_userPassword);
        $this->UpdatePool('authtype', $_authType);
        $this->UpdatePool('clientid', $_authClientId);
        $this->UpdatePool('clientsecret', $_authClientSecret);
        $this->UpdatePool('authendpoint', $_authEndpoint);
        $this->UpdatePool('tokenendpoint', $_tokenEndpoint);
        $this->UpdatePool('authscopes', $_authScope);
        $this->UpdatePool('accesstoken', $_accessToken);
        $this->UpdatePool('refreshtoken', $_refreshToken);
        $this->UpdatePool('tokenexpiry', $_tokenExpiry);
        $this->UpdatePool('leavecopyonserver', (int)($_leaveCopyOnServer));
        $this->UpdatePool('usequeuesmtp', (int)($_useQueueSMTP));
        $this->UpdatePool('forcequeue', (int)($_forceQueue));
        $this->UpdatePool('smtphost', $_smtpHost);
        $this->UpdatePool('smtpport', $_smtpPort);
        $this->UpdatePool('smtptype', $_smtpType);

        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }
}
