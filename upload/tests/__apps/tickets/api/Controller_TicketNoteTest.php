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
 * Class Controller_TicketNoteTest
 * @group tickets
 * @group tickets-api
 */
class Controller_TicketNoteTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Api\Controller_TicketNote', $obj);
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
    public function testListAllReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ListAll('no'),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketnoteid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturn($arr);
        $_SWIFT->Database->Record = $arr;

        $this->assertTrue($obj->ListAll(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'ListAll', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Get(1, 1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketnoteid' => 1,
            'linktype' => 1,
            'linktypeid' => 1,
        ];

        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr){
            if (false !== strpos($x, "ticketnoteid = '0'")) {
                $arr['ticketnoteid'] = 0;
            }

            if (false !== strpos($x, "ticketnoteid = '2'")) {
                $arr['linktypeid'] = 0;
            }

            return $arr;
        });

        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Get(1, 0),
            'Returns false with invalid note id');

        $this->assertFalse($obj->Get(1, 2),
            'Returns false with invalid note id');

        $this->assertTrue($obj->Get(1, 1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Get', 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Post(),
            'Returns false without POST');

        $_POST['ticketid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturn($arr);
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Post(),
            'Returns false without fullname and staffid');

        $_POST['staffid'] = '1';
        $this->assertFalse($obj->Post(),
            'Returns false without contents');

        $_POST['contents'] = 'contents';
        $this->assertTrue($obj->Post());

        $_POST['fullname'] = 'fullname';
        $_POST['staffid'] = '2';
        $_POST['forstaffid'] = 1;
        $_POST['notecolor'] = 1;
        $this->assertTrue($obj->Post());

        $this->assertClassNotLoaded($obj, 'Post');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Delete(1, 1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketnoteid' => 1,
            'linktype' => 1,
            'linktypeid' => 1,
        ];

        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr){
            if (false !== strpos($x, "ticketnoteid = '0'")) {
                $arr['ticketnoteid'] = 0;
            }

            if (false !== strpos($x, "ticketnoteid = '2'")) {
                $arr['linktypeid'] = 0;
            }

            return $arr;
        });

        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Delete(1, 0),
            'Returns false with invalid note id');

        $this->assertFalse($obj->Delete(1, 2),
            'Returns false with invalid note id');

        $this->assertTrue($obj->Delete(1, 1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Delete', 1, 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketNoteMock
     */
    private function getMocked()
    {
        $rest = $this->getMockBuilder('SWIFT_RESTServer')
            ->disableOriginalConstructor()
            ->getMock();

        $rest->method('GetVariableContainer')->willReturn(['salt' => 'salt']);
        $rest->method('Get')->willReturnArgument(0);

        return $this->getMockObject('Tickets\Api\Controller_TicketNoteMock', [
            'RESTServer' => $rest,
        ]);
    }
}

class Controller_TicketNoteMock extends Controller_TicketNote
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

