<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Banjo Mofesola Paul <banjo.paul@aurea.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Library\Rule;

use Base\Library\Rules\SWIFT_Rules;
use Knowledgebase\Admin\LoaderMock;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType;
use Parser\Library\MailParser\SWIFT_MailParser;
use Parser\Library\MailParser\SWIFT_MailParserEmail;
use Parser\Models\EmailQueue\SWIFT_EmailQueuePipe;
use Parser\Models\Rule\SWIFT_ParserRule;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * Class ParserRuleManagerTest
 * @group parser-library
 */
class ParserRuleManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Library\Rule\SWIFT_ParserRuleManager', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertiesReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->GetProperties(new SWIFT_Ticket(new \SWIFT_DataID(1))));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetProperties', new SWIFT_Ticket(new \SWIFT_DataID(1)));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExecutePreParseReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) {
            switch ($x) {
                case 'parserrulecache':
                    return [ '1' => [
                        'ruletype' => SWIFT_ParserRule::TYPE_PREPARSE,
                        'isenabled' => '1',
                        'matchtype' => SWIFT_Rules::RULE_MATCHALL,
                        '_criteria' => [
                            [ 1, 1, 1 ]
                        ],
                    ] ];
                default:
                    return [ '1' => [ '1' => [1] ]];
            }
        };

        $this->assertTrue($obj->ExecutePreParse());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'ExecutePreParse');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExecutePostParseReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) {
            switch ($x) {
                case 'parserrulecache':
                    return [ '1' => [
                        'ruletype' => SWIFT_ParserRule::TYPE_PREPARSE,
                        'isenabled' => '1',
                        'matchtype' => SWIFT_Rules::RULE_MATCHALL,
                        '_criteria' => [
                            [ 1, 1, 1 ]
                        ],
                    ] ];
                default:
                    return [ '1' => [ '1' => [1] ]];
            }
        };

        $this->assertTrue($obj->ExecutePostParse(new SWIFT_Ticket(new \SWIFT_DataID(1))));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'ExecutePostParse', new SWIFT_Ticket(new \SWIFT_DataID(1)));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_ParserRuleManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Library\Rule\SWIFT_ParserRuleManagerMock');
    }
}

class SWIFT_ParserRuleManagerMock extends SWIFT_ParserRuleManager
{
    public $_data;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);
        $this->_data = [
            'departmentid' => 1,
            'emailqueueid' => 1,
            'flagtype' => 1,
            'isresolved' => false,
            'iswatched' => false,
            'ownerstaffid' => 1,
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
        ];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->_data);

        $_mailStructure = new \stdClass();
        $_mailStructure->fromEmail = 'from@email.com';
        $_mailStructure->replyto = 'reply-to@email.com';
        $_mailStructure->recipientAddresses = [ 'recipient@address.com' ];
        $_mailStructure->bccRecipientAddresses = [ 'bcc@address.com' ];
        $_mailStructure->toEmail = 'to-email@address.com';
        $_mailStructure->toEmailList = [ 'to-email@address.com' ];
        $_mailStructure->attachments = [
            [ 'data' => '', 'size' => 1, 'filename' => 'filename', 'extension' => 'txt', 'contenttype' => 'text/plain' ]
        ];

        $_mailParserEmailObject = new SWIFT_MailParserEmail($_mailStructure);
        $_mailParserObject = new SWIFT_MailParser('rawEmailData');

        parent::__construct($_mailParserEmailObject, new SWIFT_EmailQueuePipe(new \SWIFT_DataID(1)), $_mailParserObject);
    }

    public function GetProperties(SWIFT_Ticket $_SWIFT_TicketObject = null)
    {
        return parent::GetProperties($_SWIFT_TicketObject); // TODO: Change the autogenerated stub
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

