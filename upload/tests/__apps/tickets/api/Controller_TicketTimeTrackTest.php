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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Tickets\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketTimeTrackTest
 * @group tickets
 * @group tickets-api
 */
class Controller_TicketTimeTrackTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Api\Controller_TicketTimeTrack', $obj);
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

        $this->assertFalse($obj->ListAll(1),
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

        $this->assertTrue($obj->ListAll(1, 1));

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
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "tickettimetrackid = '2'")) {
                $arr['tickettimetrackid'] = 2;
            }

            if (false !== strpos($x, "tickettimetrackid = '1'")) {
                $arr['tickettimetrackid'] = 1;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Get(2, 3));
        $this->assertFalse($obj->Get(2, 2));
        $this->assertTrue($obj->Get(1, 1));

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
            'tickettimetrackid' => 1,
            'timeworked' => 0,
            'timebilled' => 0,

            // staff properties
            'staffid' => 1,
            'fullname' => 'fullname',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Post(),
            'Returns false without staffid');

        $_POST['staffid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false without contents');

        $_POST['contents'] = 'contents';
        $this->assertFalse($obj->Post(),
            'Returns false without worktimeline');

        $_POST['worktimeline'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false without billtimeline');

        $_POST['billtimeline'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false without timespent');

        $_POST['timespent'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false without timebillable');

        $_POST['timebillable'] = 1;
        $_POST['notecolor'] = 1;
        $_POST['workerstaffid'] = '';
        $this->assertFalse($obj->Post(),
            'Returns false without workerstaffid');

        $_POST['workerstaffid'] = 1;
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
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "tickettimetrackid = '2'")) {
                $arr['tickettimetrackid'] = 2;
            }

            if (false !== strpos($x, "tickettimetrackid = '1'")) {
                $arr['tickettimetrackid'] = 1;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Delete(2, 3));
        $this->assertFalse($obj->Delete(2, 2));
        $this->assertTrue($obj->Delete(1, 1));

        $this->assertClassNotLoaded($obj, 'Delete', 1, 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketTimeTrackMock
     */
    private function getMocked()
    {
        $rest = $this->getMockBuilder('SWIFT_RESTServer')
            ->disableOriginalConstructor()
            ->getMock();

        $rest->method('GetVariableContainer')->willReturn(['salt' => 'salt']);
        $rest->method('Get')->willReturnArgument(0);

        return $this->getMockObject('Tickets\Api\Controller_TicketTimeTrackMock', [
            'RESTServer' => $rest,
        ]);
    }
}

class Controller_TicketTimeTrackMock extends Controller_TicketTimeTrack
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

