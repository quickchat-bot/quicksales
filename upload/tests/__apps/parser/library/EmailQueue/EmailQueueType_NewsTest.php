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

use Knowledgebase\Admin\LoaderMock;
use Parser\Library\MailParser\SWIFT_MailParser;
use Parser\Library\MailParser\SWIFT_MailParserEmail;
use Parser\Library\Rule\SWIFT_ParserRuleManager;
use Parser\Models\EmailQueue\SWIFT_EmailQueuePipe;
use SWIFT_Exception;

/**
 * Class EmailQueueType_NewsTest
 * @group parser-library
 */
class EmailQueueType_NewsTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Library\EmailQueue\SWIFT_EmailQueueType_News', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessReturnsTrue()
    {
        $obj = $this->getMocked();

        $_mailStructure = new \stdClass();

        $_mailStructure->fromEmail = 'from@email.com';
        $_mailStructure->replytoEmail = 'reply-to@email.com';
        $_mailStructure->toEmail = 'to-email@email.com';
        $_mailStructure->recipientAddresses = [ 'recepient@address.com' ];
        $_mailStructure->bccRecipientAddresses = [ 'bcc@address.com' ];
        $_mailStructure->toEmailList = [ 'to-email@list.com' ];

        $_dataObject = new \SWIFT_DataID(1);

        $_mailParserObject = new SWIFT_MailParser('rawEmailData');
        $_mailParserEmailObject = new SWIFT_MailParserEmail($_mailStructure);

        $_parserRuleManagerObject = new SWIFT_ParserRuleManager($_mailParserEmailObject, new SWIFT_EmailQueuePipe($_dataObject), $_mailParserObject);

        $this->assertTrue($obj->Process($_mailParserEmailObject, $_mailParserObject, $_parserRuleManagerObject));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Process', $_mailParserEmailObject, $_mailParserObject, $_parserRuleManagerObject);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_EmailQueueType_NewsMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Library\EmailQueue\SWIFT_EmailQueueType_NewsMock');
    }
}

class SWIFT_EmailQueueType_NewsMock extends SWIFT_EmailQueueType_News
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'emailqueueid' => 1,
            'queuesignatureid' => 1,
            'type' => SWIFT_EmailQueueType::TYPE_NEWS,
        ]);

        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

