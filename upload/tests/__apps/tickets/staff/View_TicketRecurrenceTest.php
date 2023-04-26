<?php
/**
 * ###############################################
 *
 * Kayako Classic
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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_RecurrenceTest
 * @group tickets
 * @group tickets-staff
 */
class View_TicketRecurrenceTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderRecurrenceReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "WHERE recurrencefromticketid = '1'")) {
                self::$_next = 1;
                return;
            }

            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            return self::$_next % 2;
        });

        static::$_prop['departmentid'] = 1;
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketmaskid' => 0,
            'workerstaffid' => 1,
            'dateline' => 0,
            'workdateline' => 0,
            'timespent' => 0,
            'timebillable' => 0,
            'tickettimetracknoteid' => 1,
            'notecolor' => '#ffffff',
            'notes' => 'notes',
            'editedstaffid' => 0,
            'isedited' => 1,
            'recurrencefromticketid' => 1,
            'tickettypeid' => &static::$_prop['departmentid'],
            'ticketstatusid' => &static::$_prop['departmentid'],
            'priorityid' => &static::$_prop['departmentid'],
            'subject' => 'subject',
        ];

        $mockDb->method('QueryFetch')->willReturn($arr);

        $mockDb->Record = $arr;

        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getMocked([
            'Database' => $mockDb,
            'Emoji' => $mockEmoji,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetPermission')->willReturn('1');
        static::$_prop['GetAssignedDepartments'] = [];
        $mockStaff->method('GetAssignedDepartments')->willReturnCallback(function () {
            return static::$_prop['GetAssignedDepartments'];
        });
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        static::$_prop['bgcolorcode'] = '';
        $cache->method('Get')->willReturn(
            [
                1 => [
                    1 => 1,
                    'bgcolorcode' => &static::$_prop['bgcolorcode'],
                    'displayicon' => &static::$_prop['bgcolorcode'],
                ],
            ]
        );
        $obj->Cache = $cache;
        \SWIFT::GetInstance()->Cache = $cache;

        $mock = $obj->getTicketMock($this);
        static::$_prop['GetID'] = 1;
        $mock->method('GetID')->willReturnCallback(function () {
            return static::$_prop['GetID'];
        });

        $mock2 = $this->getMockBuilder('Tickets\Models\Recurrence\SWIFT_TicketRecurrence')
            ->disableOriginalConstructor()
            ->getMock();

        $mock2->method('Get')->willReturnCallback(function ($x) {
            if (!isset(static::$_prop[$x])) {
                static::$_prop[$x] = 1;
            }

            return static::$_prop[$x];
        });

        $this->expectOutputRegex('/script/');

        $this->assertTrue($obj->RenderRecurrence($mock, $mock2),
            'Returns true with interval daily');

        static::$_prop['intervalstep'] = 0;
        static::$_prop['endtype'] = 2;
        static::$_prop['GetID'] = 2; // add recurrencecontainer
        $this->assertTrue($obj->RenderRecurrence($mock, $mock2),
            'Returns true with interval daily y recurrence weekly');

        static::$_prop['bgcolorcode'] = '#ffffff';
        $this->assertTrue($obj->RenderRecurrence($mock, $mock2),
            'Returns true with interval daily y recurrence weekly');

        static::$_prop['intervalstep'] = 1;
        static::$_prop['intervaltype'] = 2;
        static::$_prop['endtype'] = 3;
        static::$_prop['GetAssignedDepartments'] = [1, 2]; // canaccess
        static::$_prop['departmentid'] = 2; // not in departmentcache
        $this->assertTrue($obj->RenderRecurrence($mock, $mock2),
            'Returns true with interval weekly');

        static::$_prop['intervaltype'] = 3;
        $this->assertTrue($obj->RenderRecurrence($mock, $mock2),
            'Returns true with interval monthly');

        static::$_prop['monthly_type'] = 2;
        $this->assertTrue($obj->RenderRecurrence($mock, $mock2),
            'Returns true with interval monthly y type extended');

        static::$_prop['intervaltype'] = 4;
        $this->assertTrue($obj->RenderRecurrence($mock, $mock2),
            'Returns true with interval yearly');

        static::$_prop['yearly_type'] = 2;
        $this->assertTrue($obj->RenderRecurrence($mock, $mock2),
            'Returns true with interval yearly y type extended');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_TicketMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_TicketMock', $services);
    }
}
