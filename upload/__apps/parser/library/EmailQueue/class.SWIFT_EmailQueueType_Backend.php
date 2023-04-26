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
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Library\EmailQueue;

use Parser\Library\EmailQueue\SWIFT_EmailQueueType;
use SWIFT_Exception;
use SWIFT_Loader;
use Parser\Library\MailParser\SWIFT_MailParser;
use Parser\Library\MailParser\SWIFT_MailParserEmail;
use Parser\Library\Rule\SWIFT_ParserRuleManager;

/**
 * Email Queue Type (Backend) Management Class
 *
 * @property SWIFT_MailParser $BackendEmailParser
 * @author Varun Shoor
 */
class SWIFT_EmailQueueType_Backend extends SWIFT_EmailQueueType
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_BACKEND);
    }

    /**
     * Process the incoming email
     *
     * @author Varun Shoor
     *
     * @param SWIFT_MailParserEmail                       $_SWIFT_MailParserEmailObject   The Mail Parser Email Objects
     * @param \Parser\Library\MailParser\SWIFT_MailParser $_SWIFT_MailParserObject        The Parser\Library\MailParser\SWIFT_MailParser Object Pointer
     * @param SWIFT_ParserRuleManager                     $_SWIFT_ParserRuleManagerObject The Parser Rule Manager Object
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     *
     */
    public function Process(SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject, SWIFT_MailParser $_SWIFT_MailParserObject, SWIFT_ParserRuleManager $_SWIFT_ParserRuleManagerObject)
    {
        // @codeCoverageIgnoreStart
        // Nonexistent library 'EmailParser:BackendEmailParser' referenced - needs attention
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_Loader::LoadLibrary('EmailParser:BackendEmailParser', APP_BACKEND);

        $this->Load->Library('EmailParser:BackendEmailParser', array($_SWIFT_MailParserEmailObject, $this->GetEmailQueue(), $_SWIFT_MailParserObject, $_SWIFT_ParserRuleManagerObject));

        $this->BackendEmailParser->Process();

        return true;
        // @codeCoverageIgnoreEnd
    }
}

?>
