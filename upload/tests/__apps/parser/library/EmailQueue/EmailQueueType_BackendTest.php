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
use Parser\Models\EmailQueue\SWIFT_EmailQueueMailbox;
use Parser\Models\EmailQueue\SWIFT_EmailQueuePipe;
use SWIFT_Exception;

/**
 * Class EmailQueueType_BackendTest
 * @group parser-library
 */
class EmailQueueType_BackendTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Library\EmailQueue\SWIFT_EmailQueueType_Backend', $obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_EmailQueueType_BackendMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Library\EmailQueue\SWIFT_EmailQueueType_BackendMock');
    }
}

class SWIFT_EmailQueueType_BackendMock extends SWIFT_EmailQueueType_Backend
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
            'type' => SWIFT_EmailQueueType::TYPE_BACKEND,
        ]);

        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

