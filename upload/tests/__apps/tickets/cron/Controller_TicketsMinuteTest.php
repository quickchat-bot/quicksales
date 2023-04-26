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

namespace Tickets\Cron;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketsMinuteTest
 * @group tickets
 * @group tickets-cron
 */
class Controller_TicketsMinuteTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Cron\Controller_TicketsMinute', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testFollowUpReturnsTrue()
    {
        $obj = $this->getMocked();

        $SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketfollowupid' => 1,
            'cronid' => 1,
            'name' => 'name',
        ];
        $SWIFT->Database->method('QueryFetch')->willReturn($arr);
        $SWIFT->Database->Record = $arr;

        $this->assertTrue($obj->FollowUp(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'FollowUp');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGeneralTasksReturnsTrue()
    {
        $obj = $this->getMocked();

        $SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketrecurrenceid' => 1,
            'cronid' => 1,
            'name' => 'name',
            'slaplanid' => '0',
        ];
        $SWIFT->Database->method('QueryFetch')->willReturn($arr);
        $SWIFT->Database->Record = $arr;

        $this->assertTrue($obj->GeneralTasks(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'GeneralTasks');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testAutoCloseReturnsTrue()
    {
        $obj = $this->getMocked();

        $SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketrecurrenceid' => 1,
            'cronid' => 1,
            'name' => 'name',
            'slaplanid' => '0',
        ];
        $SWIFT->Database->method('QueryFetch')->willReturn($arr);
        $SWIFT->Database->Record = $arr;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'autocloseruleid' => 1,
                '_criteria' => [1],
                'isenabled' => 1,
                'targetticketstatusid' => 1,
                'markasresolved' => 1,
                'inactivitythreshold' => 0,
                'title' => 1,
                'sendpendingnotification' => 0,
                'closurethreshold' => 0,
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->expectOutputRegex('/Pending/');

        $this->assertTrue($obj->AutoClose(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'AutoClose');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketsMinuteMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Cron\Controller_TicketsMinuteMock');
    }
}

class Controller_TicketsMinuteMock extends Controller_TicketsMinute
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

