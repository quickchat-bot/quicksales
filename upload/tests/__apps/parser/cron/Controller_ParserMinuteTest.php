<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Cron;

use Knowledgebase\Admin\LoaderMock;
use Parser\Library\MailParser\SWIFT_MailParserIMAP;
use SWIFT_Exception;

/**
 * Class Controller_ParserMinuteTest
 * @group parser
 * @group parser-cron
 */
class Controller_ParserMinuteTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Cron\Controller_ParserMinute', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPOP3IMAPReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['cronid' => 1, 'name' => 'test']);

        $mailParserIMAPMock = $this->getMockBuilder(SWIFT_MailParserIMAP::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mailParserIMAPMock->method('Process')->willReturn(true);

        $obj->MailParserIMAP = $mailParserIMAPMock;

        $this->expectOutputRegex('/.*/');

        $this->assertTrue($obj->POP3IMAP(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'POP3IMAP');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_ParserMinuteMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Cron\Controller_ParserMinuteMock');
    }
}

class Controller_ParserMinuteMock extends Controller_ParserMinute
{
    public $MailParserIMAP;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

