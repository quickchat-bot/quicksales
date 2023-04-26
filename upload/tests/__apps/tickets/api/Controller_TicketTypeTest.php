<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Tickets\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketTypeTest
 * @group tickets
 * @group tickets-api
 */
class Controller_TicketTypeTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Api\Controller_TicketType', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'priorityid' => 1,
            'usergroupid' => 1,
            'tickettypeid' => 1,
            'toassignid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturn($arr);
        $_SWIFT->Database->Record = $arr;
        $obj->Database->Record = $arr;

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertTrue($obj->GetList(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessTicketTypesReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('ProcessTicketTypes');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, 'no'));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 'no');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Get(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Get', 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketTypeMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Api\Controller_TicketTypeMock');
    }
}

class Controller_TicketTypeMock extends Controller_TicketType
{
    public $Database;

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

