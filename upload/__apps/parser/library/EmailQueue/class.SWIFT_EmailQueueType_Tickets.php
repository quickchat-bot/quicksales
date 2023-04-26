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
 * Email Queue Type (Tickets) Management Class
 *
 * @property \Tickets\Library\EmailParser\SWIFT_TicketEmailParser $TicketEmailParser
 * @author Varun Shoor
 */
class SWIFT_EmailQueueType_Tickets extends SWIFT_EmailQueueType
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int  $_templateGroupID     The Template Group ID
     * @param int  $_departmentID        The Department ID
     * @param int  $_ticketTypeID        The Ticket Type ID
     * @param int  $_ticketPriorityID    The Ticket Priority ID
     * @param int  $_ticketStatusID      The Ticket Status ID
     * @param bool $_ticketAutoResponder Whether to send the Ticket Auto Responder for this Queue
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_templateGroupID, $_departmentID, $_ticketTypeID, $_ticketPriorityID, $_ticketStatusID, $_ticketAutoResponder)
    {
        parent::__construct(self::TYPE_TICKETS);

        $this->SetValue('tgroupid', $_templateGroupID);
        $this->SetValue('departmentid', $_departmentID);
        $this->SetValue('tickettypeid', $_ticketTypeID);
        $this->SetValue('priorityid', $_ticketPriorityID);
        $this->SetValue('ticketstatusid', $_ticketStatusID);
        $this->SetValue('ticketautoresponder', (int)($_ticketAutoResponder));
    }

    /**
     * Process the incoming email
     *
     * @author Varun Shoor
     *
     * @param \Parser\Library\MailParser\SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject   The Mail Parser Email Objects
     * @param \Parser\Library\MailParser\SWIFT_MailParser      $_SWIFT_MailParserObject        The Parser\Library\MailParser\SWIFT_MailParser Object Pointer
     * @param \Parser\Library\Rule\SWIFT_ParserRuleManager     $_SWIFT_ParserRuleManagerObject The Parser Rule Manager Object
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Process(SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject, SWIFT_MailParser $_SWIFT_MailParserObject, SWIFT_ParserRuleManager $_SWIFT_ParserRuleManagerObject)
    {
        // @codeCoverageIgnoreStart
        // not used anywhere
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_Loader::LoadLibrary('EmailParser:TicketEmailParser', APP_TICKETS);

        $this->Load->Library('EmailParser:TicketEmailParser', array($_SWIFT_MailParserEmailObject, $this->GetEmailQueue(), $_SWIFT_MailParserObject, $_SWIFT_ParserRuleManagerObject), true, false, APP_TICKETS);
        $this->TicketEmailParser->Process();

        return true;
        // @codeCoverageIgnoreEnd
    }
}
