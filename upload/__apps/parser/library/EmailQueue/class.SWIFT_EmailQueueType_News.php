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

namespace Parser\Library\EmailQueue;

use Parser\Library\EmailQueue\SWIFT_EmailQueueType;
use SWIFT_Exception;
use Parser\Library\MailParser\SWIFT_MailParser;
use Parser\Library\MailParser\SWIFT_MailParserEmail;
use Parser\Library\Rule\SWIFT_ParserRuleManager;

/**
 * Email Queue Type (News) Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_EmailQueueType_News extends SWIFT_EmailQueueType
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_NEWS);
    }

    /**
     * Process the incoming email
     *
     * @author Varun Shoor
     *
     * @param \Parser\Library\MailParser\SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject   The Mail Parser Email Objects
     * @param SWIFT_MailParser                                 $_SWIFT_MailParserObject        The Parser\Library\MailParser\SWIFT_MailParser Object Pointer
     * @param SWIFT_ParserRuleManager                          $_SWIFT_ParserRuleManagerObject The Parser Rule Manager Object
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Process(SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject, SWIFT_MailParser $_SWIFT_MailParserObject, SWIFT_ParserRuleManager $_SWIFT_ParserRuleManagerObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        return true;
    }
}

?>
