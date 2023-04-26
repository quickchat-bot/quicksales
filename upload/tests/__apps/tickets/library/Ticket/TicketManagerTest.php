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

namespace Tickets\Library\Ticket;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class TicketManagerTest
 * @group tickets
 * @group tickets-lib1
 */
class TicketManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRebuildCacheOnShutdownReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::RebuildCacheOnShutdown());
        $obj::Recount(1);
        $this->assertTrue($obj::RebuildCacheOnShutdown());
    }

    public function testExportXMLThrowsException()
    {
        $obj = $this->getMocked();

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertClassNotLoaded($obj, 'ExportXML', $ticket);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExportXMLReturnsTrue()
    {
        $obj = $this->getMocked();

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturnOnConsecutiveCalls(1, 0);
        $ticket->method('GetProperty')->willReturn(1);
        $ticket->method('Get')->willReturn(1);

        $this->assertTrue($obj->ExportXML($ticket),
            'Returns true without errors');

        $this->assertInvalidData($obj, 'ExportXML', $ticket);
    }

    public function testExportPDFThrowsException()
    {
        $obj = $this->getMocked();

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertClassNotLoaded($obj, 'ExportPDF', $ticket);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExportPDFReturnsTrue()
    {
        $obj = $this->getMocked();

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturnOnConsecutiveCalls(1, 0);
        $ticket->method('GetProperty')->willReturn(1);
        $ticket->method('Get')->willReturn(1);

        $this->assertTrue($obj->ExportPDF($ticket),
            'Returns true without errors');

        $this->assertInvalidData($obj, 'ExportPDF', $ticket);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRebuildCacheReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x === 'ticketcountcache') {
                return [];
            }

            if ($x === 'departmentcache') {
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    2 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    3 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                    4 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                ];
            }

            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                    ],
                    2 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '0',
                    ],
                ];
            }

            if ($x === 'groupassigncache') {
                return [
                    1 => [
                        1 => 1,
                        3 => 3,
                    ],
                ];
            }

            if ($x === 'tickettypecache' || $x === 'statuscache') {
                return [
                    1 => [
                        1 => 1,
                        'departmentid' => 2,
                    ],
                    2 => [
                        'departmentid' => 0,
                    ],
                ];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                ],
            ];
        };

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'lastactivity' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $obj::$_rebuildCacheExecuted = false;

        $this->assertTrue($obj::RebuildCache([1]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRecountReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj::$_shutdownQueued = true;
        $this->assertTrue($obj->Recount(false));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\Ticket\SWIFT_TicketManagerMock');
    }
}

class SWIFT_TicketManagerMock extends SWIFT_TicketManager
{
    public static $_rebuildCacheExecuted;
    public static $_shutdownQueued;

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

