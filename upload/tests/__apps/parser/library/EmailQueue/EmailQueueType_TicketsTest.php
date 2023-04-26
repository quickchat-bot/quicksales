<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Banjo Mofesola Paul <banjo.paul@aurea.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Library\EmailQueue;

use Base\Models\User\SWIFT_UserEmailManager;
use Knowledgebase\Admin\LoaderMock;
use Parser\Library\MailParser\SWIFT_MailParser;
use Parser\Library\MailParser\SWIFT_MailParserEmail;
use Parser\Library\Rule\SWIFT_ParserRuleManager;
use Parser\Models\EmailQueue\SWIFT_EmailQueuePipe;
use SWIFT_Exception;
use Tickets\Library\EmailParser\SWIFT_TicketEmailParser;
use Tickets\Library\EmailParser\SWIFT_TicketEmailParserMock;

/**
 * Class EmailQueueType_TicketsTest
 * @group parser-library
 */
class EmailQueueType_TicketsTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Library\EmailQueue\SWIFT_EmailQueueType_Tickets', $obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_EmailQueueType_TicketsMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Library\EmailQueue\SWIFT_EmailQueueType_TicketsMock');
    }
}

class SWIFT_EmailQueueType_TicketsMock extends SWIFT_EmailQueueType_Tickets
{
    public $_sharedMailStructure;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'averageresponsetimehits' => 1,
            'averageslaresponsetime' => 1,
            'departmentid' => 1,
            'email' => 'email@email.com',
            'emailqueueid' => 1,
            'fullname' => 'fullname',
            'isresolved' => false,
            'iswatched' => false,
            'languageid' => 1,
            'lastpostid' => 1,
            'linktype' => SWIFT_UserEmailManager::LINKTYPE_USER,
            'overduehrs' => 0,
            'priorityid' => 1,
            'queuesignatureid' => 1,
            'registrationrequired' => false,
            'regusergroupid' => 1,
            'replyto' => 'reply-to@email.com',
            'ruletype' => 1,
            'salutation' => 'hello',
            'slaplanid' => 1,
            'slascheduleid' => 1,
            'tgroupid' => 1,
            'ticketautoresponder' => false,
            'ticketid' => 1,
            'ticketpostid' => 1,
            'ticketslaplanid' => 1,
            'ticketstatusid' => 1,
            'tickettypeid' => 1,
            'title' => 'title',
            'totalreplies' => 1,
            'type' => SWIFT_EmailQueueType::TYPE_TICKETS,
            'userdesignation' => 1,
            'useremailid' => 1,
            'usergroupid' => 1,
            'userid' => 1,
        ]);

        parent::__construct(1, 1, 1, 1, 1, false);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

