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
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Knowledgebase\Admin\LoaderMock;
use Parser\Library\MailParser\SWIFT_MailParser;
use SWIFT_Exception;

/**
 * Class Controller_ParserLogTest
 * @group parser
 * @group parser-admin
 */
class Controller_ParserLogTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\Controller_ParserLog', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DeleteList([]),
            'Returns false');

        $this->assertTrue($obj->DeleteList([1], true),
            'Returns true');

        $this->assertFalse($obj->DeleteList([], true),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Manage(),
            'Returns true');

        $this->assertTrue($obj->Manage(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Manage');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testViewReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['parserlogid' => 1]);

        $this->assertTrue($obj->View(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'View', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testViewThrowsInvalid()
    {
        $obj = $this->getMocked();
        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->View('');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReParseReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['parserlogid' => 1]);


        $this->assertTrue($obj->ReParse(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'ReParse', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReParseThrowsInvalid()
    {
        $obj = $this->getMocked();
        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->ReParse('');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_ParserLogMock
     */
    private function getMocked()
    {
        $mockView = $this->getMockBuilder(View_ParserLog::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockView->method('RenderGrid')->willReturn(true);

        $mockView->method('Render')->willReturn(true);

        $mailPasrserMock = $this->getMockBuilder(SWIFT_MailParser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mailPasrserMock->method('Process')->willReturn(true);

        return $this->getMockObject('Parser\Admin\Controller_ParserLogMock', ['View' => $mockView, 'MailParser' => $mailPasrserMock]);
    }
}

class Controller_ParserLogMock extends Controller_ParserLog
{
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

