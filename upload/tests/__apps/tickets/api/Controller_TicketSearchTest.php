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
 * Class Controller_TicketSearchTest
 * @group tickets
 * @group tickets-api
 * @group tickets-search
 */
class Controller_TicketSearchTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Api\Controller_TicketSearch', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Delete');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPutReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Put(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Put');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Get(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Get');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->GetList(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['query'] = '1';
        $_POST['ticketid'] = '1';
        $_POST['contents'] = '1';
        $_POST['phrase'] = '1';
        $_POST['fullname'] = '1';
        $_POST['creatoremail'] = '1';
        $_POST['email'] = '1';
        $_POST['author'] = '1';
        $_POST['tags'] = '1';
        $_POST['user'] = '1';
        $_POST['userorganization'] = '1';
        $_POST['usergroup'] = '1';
        $_POST['notes'] = '1';

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertTrue($obj->Post(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Post');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketSearchMock
     */
    private function getMocked()
    {
        $mgr = $this->getMockBuilder('Tickets\Library\API\SWIFT_TicketAPIManager')
            ->disableOriginalConstructor()
            ->getMock();

        $rest = $this->getMockBuilder('SWIFT_RESTServer')
            ->disableOriginalConstructor()
            ->getMock();

        $rest->method('GetVariableContainer')->willReturn(['salt' => 'salt']);
        $rest->method('Get')->willReturnArgument(0);

        return $this->getMockObject('Tickets\Api\Controller_TicketSearchMock', [
            'RESTServer' => $rest,
            'TicketAPIManager' => $mgr,
        ]);
    }
}

class Controller_TicketSearchMock extends Controller_TicketSearch
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

