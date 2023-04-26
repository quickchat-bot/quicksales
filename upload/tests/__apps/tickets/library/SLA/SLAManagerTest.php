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

namespace Tickets\Library\SLA;

use Base\Models\User\SWIFT_UserOrganization;
use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Models\SLA\SWIFT_SLASchedule;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * Class SLAManagerTest
 * @group tickets
 * @group tickets-lib4
 */
class SLAManagerTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(SWIFT_SLAManager::class, $obj);
    }

    public function testGetSecondsFromHourReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::GetSecondsFromHour(1.85) > 0);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetDefaultOverdueSecondsReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetDefaultOverdueSeconds');

        $this->assertNotEmpty($method->invoke($obj));

        static::$databaseCallback['SettingsGet'] = function () {
            return 0;
        };

        $this->assertEquals([0, 0], $method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetDefaultResolutionDueSecondsReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetDefaultResolutionDueSeconds');

        $this->assertNotEmpty($method->invoke($obj));

        static::$databaseCallback['SettingsGet'] = function () {
            return 0;
        };

        $this->assertEquals([0, 0], $method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws \ReflectionException
     */
    public function testExecuteSlaPlansReturnsFalse()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'ExecuteSLAPlans');

        $ticket = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetSLAProperties')->willReturn([1]);
        $ticket->method('GetOldTicketProperties')->willReturn([]);
        $ticket->method('GetProperty')->willReturnCallback(function ($x) {
            return $x;
        });

        $obj->_slaPlanCache = [
            1 => [
                1 => 1,
                'departmentapp' => 'tickets',
                'staffid' => 1,
                'ticketpostid' => 1,
                'slaid' => 1,
                'slaplanid' => 1,
                '_criteria' => [

                ],
                'ruletype' => 3,
                'isenabled' => 1,
                'fullname' => 1,
                'email' => 'me@mail.com',
            ],
        ];

        $obj->_slaPlanCache[1]['_criteria'] = [1, 11, 1, 3];
        $this->assertNotNull($method->invoke($obj, $ticket));

        $obj->_slaPlanCache[1]['isenabled'] = 0;
        $this->assertFalse($method->invoke($obj, $ticket));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $ticket);
    }

    public function testGetDueTimeThrowsException()
    {
        $obj = $this->getMocked();

        $ticket = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInvalidData($obj, 'GetDueTime', $ticket);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDueTimeReturnsTrue()
    {
        $_SWIFT = SWIFT::GetInstance();

        $obj = $this->getMocked();

        $obj->_ticketPropertiesChanged = [1];

        $ticket = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetSLAProperties')->willReturn([]);
        $ticket->method('GetOldTicketProperties')->willReturn([1]);
        $ticket->method('GetUserObject')->willReturnCallback(function () {
            return SWIFT::GetInstance()->User;
        });
        $ticket->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            return 1;
        });
        $userOrg = $this->getMockBuilder(SWIFT_UserOrganization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userOrg->method('GetIsClassLoaded')->willReturn(true);
        $userOrg->method('GetUserOrganizationID')->willReturn(1);
        $userOrg->method('Get')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }
            return 1;
        });
        $ticket->method('GetUserOrganizationObject')->willReturn($userOrg);

        $obj->_slaPlanCache = false;
        $this->assertNotEmpty($obj->GetDueTime($ticket));

        $obj->_slaPlanCache = [
            1 => [
                1 => 1,
                'departmentapp' => 'tickets',
                'staffid' => 1,
                'ticketpostid' => 1,
                'slaid' => 1,
                'slaplanid' => 1,
                '_criteria' => 1,
                'ruletype' => 1,
                'isenabled' => 1,
                'fullname' => 1,
                'email' => 'me@mail.com',
            ],
        ];

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,

            'slarulecriteriaid' => 1,
            'slascheduleid' => 1,
            'title' => 'title',
            'name' => 'name',
            'ruleop' => 1,
            'rulematchtype' => 1,
            'ruletype' => 1,
            'rulematch' => 1,
            'slaplanid' => 1,
            'overduehrs' => 1,
            'resolutionduehrs' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertNotEmpty($obj->GetDueTime($ticket));

        static::$_prop['ticketslaplanid'] = 0;
        static::$_prop['slaexpirytimeline'] = 0;

        static::$databaseCallback['UserGet'] = function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            return 1;
        };
        $this->assertNotEmpty($obj->GetDueTime($ticket));

        $_SWIFT->User = false;
        $this->assertNotEmpty($obj->GetDueTime($ticket));

        static::$_prop['slaexpirytimeline'] = 1;
        $this->assertNotEmpty($obj->GetDueTime($ticket));

        $obj->_ticketPropertiesChanged = false;
        $this->assertNotEmpty($obj->GetDueTime($ticket));

        $this->assertClassNotLoaded($obj, 'GetDueTime', $ticket);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDueTimeReturnsSLAObjectWhenUserProfileAppliedSLAAndEnabled()
    {
        $SLA_ENABLED = 1;
        $_SWIFT = SWIFT::GetInstance();

        $obj = $this->getMocked();

        $obj->_ticketPropertiesChanged = [1];

        $ticket = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetSLAProperties')->willReturn([]);
        $ticket->method('GetOldTicketProperties')->willReturn([1]);
        $ticket->method('GetUserObject')->willReturnCallback(function () {
            return SWIFT::GetInstance()->User;
        });
        $ticket->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            return 1;
        });
        $userOrg = $this->getMockBuilder(SWIFT_UserOrganization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userOrg->method('GetIsClassLoaded')->willReturn(true);
        $userOrg->method('GetUserOrganizationID')->willReturn(1);
        static::$_prop['slaplanid'] = 0;
        $userOrg->method('Get')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }
            return 1;
        });
        $ticket->method('GetUserOrganizationObject')->willReturn($userOrg);

        $obj->_slaPlanCache = false;
        $this->assertNotEmpty($obj->GetDueTime($ticket));

        $obj->_slaPlanCache = [
            1 => [
                1 => 1,
                'departmentapp' => 'tickets',
                'staffid' => 1,
                'ticketpostid' => 1,
                'slaid' => 1,
                'slaplanid' => 1,
                '_criteria' => 1,
                'ruletype' => 1,
                'isenabled' => 1,
                'fullname' => 1,
                'email' => 'me@mail.com',
            ],
        ];

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,

            'slarulecriteriaid' => 1,
            'slascheduleid' => 1,
            'title' => 'title',
            'name' => 'name',
            'ruleop' => 1,
            'rulematchtype' => 1,
            'ruletype' => 1,
            'rulematch' => 1,
            'slaplanid' => 1,
            'overduehrs' => 1,
            'resolutionduehrs' => 1,
            'isenabled' => $SLA_ENABLED,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$_prop['ticketslaplanid'] = 0;
        static::$_prop['slaexpirytimeline'] = 0;
        static::$databaseCallback['UserGet'] = function ($x) {
            if ($x === 'slaplanid') {
                return 1;
            }
            return 0;
        };

        $returnedSLAObject = $obj->GetDueTime($ticket);
        $this->assertNotEmpty($returnedSLAObject);
        $this->assertTrue($returnedSLAObject[0] instanceof SWIFT_SLA);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDueTimeReturnsNoSLAWhenUserProfileAppliedSLAAndDisabled()
    {
        $SLA_ENABLED = 0;
        $_SWIFT = SWIFT::GetInstance();

        $obj = $this->getMocked();

        $obj->_ticketPropertiesChanged = [1];

        $ticket = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetSLAProperties')->willReturn([]);
        $ticket->method('GetOldTicketProperties')->willReturn([1]);
        $ticket->method('GetUserObject')->willReturnCallback(function () {
            return SWIFT::GetInstance()->User;
        });
        $ticket->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            return 1;
        });
        $userOrg = $this->getMockBuilder(SWIFT_UserOrganization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userOrg->method('GetIsClassLoaded')->willReturn(true);
        $userOrg->method('GetUserOrganizationID')->willReturn(1);
        static::$_prop['slaplanid'] = 0;
        $userOrg->method('Get')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }
            return 1;
        });
        $ticket->method('GetUserOrganizationObject')->willReturn($userOrg);

        $obj->_slaPlanCache = false;
        $this->assertNotEmpty($obj->GetDueTime($ticket));

        $obj->_slaPlanCache = [
            1 => [
                1 => 1,
                'departmentapp' => 'tickets',
                'staffid' => 1,
                'ticketpostid' => 1,
                'slaid' => 1,
                'slaplanid' => 1,
                '_criteria' => 1,
                'ruletype' => 1,
                'isenabled' => 1,
                'fullname' => 1,
                'email' => 'me@mail.com',
            ],
        ];

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,

            'slarulecriteriaid' => 1,
            'slascheduleid' => 1,
            'title' => 'title',
            'name' => 'name',
            'ruleop' => 1,
            'rulematchtype' => 1,
            'ruletype' => 1,
            'rulematch' => 1,
            'slaplanid' => 1,
            'overduehrs' => 1,
            'resolutionduehrs' => 1,
            'isenabled' => $SLA_ENABLED,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$_prop['ticketslaplanid'] = 0;
        static::$_prop['slaexpirytimeline'] = 0;
        static::$databaseCallback['UserGet'] = function ($x) {
            if ($x === 'slaplanid') {
                return 1;
            }
            return 0;
        };

        $returnedSLAObject = $obj->GetDueTime($ticket);
        $this->assertNotEmpty($returnedSLAObject);
        $this->assertFalse($returnedSLAObject[0] instanceof SWIFT_SLA);
    }

    public function testGetResolutionTimeThrowsException()
    {
        $obj = $this->getMocked();

        $ticket = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInvalidData($obj, 'GetResolutionTime', $ticket);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetResolutionTimeReturnsTrue()
    {
        $obj = $this->getMocked();

        $ticket = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetSLAProperties')->willReturn([]);
        $ticket->method('GetOldTicketProperties')->willReturn([]);
        $ticket->method('GetProperty')->willReturnCallback(function ($x) {
            return $x;
        });

        $obj->_slaPlanCache = false;
        $this->assertNotEmpty($obj->GetResolutionTime($ticket));

        $obj->_slaPlanCache = [
            1 => [
                1 => 1,
                'departmentapp' => 'tickets',
                'staffid' => 1,
                'ticketpostid' => 1,
                'slaid' => 1,
                'slaplanid' => 1,
                '_criteria' => 1,
                'ruletype' => 1,
                'isenabled' => 1,
                'fullname' => 1,
                'email' => 'me@mail.com',
            ],
        ];

        $this->assertNotEmpty($obj->GetResolutionTime($ticket));

        $sla = $this->getMockBuilder(SWIFT_SLA::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sla->method('GetIsClassLoaded')->willReturn(true);
        $this->assertNotEmpty($obj->GetResolutionTime($ticket, $sla));

        $this->assertClassNotLoaded($obj, 'GetResolutionTime', $ticket);
    }

    public function testIsValidTypeReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj::IsValidType(-1));
    }

    public function testGetTimestampOnSlaHourThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'GetTimestampOnSLAHour', 0, 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTimestampOnSlaHourReturnsTime()
    {
        $obj = $this->getMocked();

        $this->assertTrue(is_numeric($obj::GetTimestampOnSLAHour('1:00', 0)));
    }

    public function testGetTimezoneTimestampReturnsTime()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::GetTimezoneTimestamp() > 0);
    }

    public function testGetDueSecondsThrowsException()
    {
        $obj = $this->getMocked();

        $sla = $this->getMockBuilder(SWIFT_SLA::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInvalidData($obj, 'GetDueSeconds', 1, $sla);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDueSecondsReturnsNumber()
    {
        $obj = $this->getMocked();

        $sched = $this->getMockBuilder(SWIFT_SLASchedule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sched->method('GetDayScheduleTable')->willReturnCallback(function () {
            if (isset(static::$_prop['GetDayScheduleTable'])) {
                return static::$_prop['GetDayScheduleTable'];
            }

            return [
                ['2:00', '3:00'],
                ['1:00', '2:00'],
                ['1:00', '1:00'],
                ['2:00', '1:00'],
            ];
        });
        $sched->method('GetDayType')->willReturnCallback(function () {
            if (isset(static::$_prop['GetDayType'])) {
                return static::$_prop['GetDayType'];
            }

            return 0;
        });

        $sla = $this->getMockBuilder(SWIFT_SLA::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sla->method('GetIsClassLoaded')->willReturn(true);
        $sla->method('GetScheduleObject')->willReturn($sched);
        $sla->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            return 1;
        });

        $this->assertTrue($obj->GetDueSeconds(1, $sla, time()) > 0);

        static::$_prop['GetDayType'] = 2;
        $this->assertTrue($obj->GetDueSeconds(1, $sla, 1) > 0);
        $this->assertTrue($obj->GetDueSeconds(1, $sla, strtotime('11:59pm')) > 0);

        static::$_prop['GetDayType'] = 1;
        static::$_prop['GetDayScheduleTable'] = false;
        $this->assertTrue($obj->GetDueSeconds(1, $sla, 1) > 0);

        unset(static::$_prop['GetDayScheduleTable']);

        $this->assertTrue($obj->GetDueSeconds(1, $sla, 1) > 0);
        $this->assertTrue($obj->GetDueSeconds(1, $sla, strtotime('11:59pm')) > 0);

        $this->assertTrue($obj->GetDueSeconds(1, $sla, strtotime('2:00')) > 0);

        $this->assertClassNotLoaded($obj, 'GetDueSeconds', 1, $sla);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetSlaResponseTimeReturnsTrue()
    {
        $obj = $this->getMocked();

        $sched = $this->getMockBuilder(SWIFT_SLASchedule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sched->method('GetDayScheduleTable')->willReturnCallback(function () {
            if (isset(static::$_prop['GetDayScheduleTable'])) {
                return static::$_prop['GetDayScheduleTable'];
            }

            return [
                ['1:00', '2:00'],
                ['2:00', '3:00'],
                ['1:00', '1:00'],
                ['2:00', '1:00'],
            ];
        });
        $sched->method('GetDayType')->willReturnCallback(function () {
            if (isset(static::$_prop['GetDayType'])) {
                return static::$_prop['GetDayType'];
            }

            return 0;
        });

        $sla = $this->getMockBuilder(SWIFT_SLA::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sla->method('GetIsClassLoaded')->willReturn(true);
        $sla->method('GetScheduleObject')->willReturn($sched);
        $sla->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            return 1;
        });

        static::$_prop['GetDayType'] = 0;
        $this->assertEquals(0, $obj->GetSLAResponseTime($sla, time(), strtotime('+1day')));

        static::$_prop['GetDayType'] = 2;
        $this->assertTrue($obj->GetSLAResponseTime($sla, time(), strtotime('+1day')) > 0);
        $this->assertEquals(0, $obj->GetSLAResponseTime($sla, strtotime('+1day'), time()));
        $this->assertEquals(0, $obj->GetSLAResponseTime($sla, time(), time()));

        static::$_prop['GetDayType'] = 1;
        static::$_prop['GetDayScheduleTable'] = false;
        $this->assertEquals(0, $obj->GetSLAResponseTime($sla, 0, 0));

        unset(static::$_prop['GetDayScheduleTable']);
        $this->assertTrue($obj->GetSLAResponseTime($sla, time(), strtotime('+1day')) > 0);
        $this->assertTrue($obj->GetSLAResponseTime($sla, strtotime('+1day'), time()) > 0);
        $this->assertTrue($obj->GetSLAResponseTime($sla, strtotime('2:30'), strtotime('2:45')) > 0);
        static::$_prop['GetDayScheduleTable'] = [
            ['2:00', '3:00'],
            ['1:00', '2:00'],
            ['1:00', '1:00'],
            ['2:00', '1:00'],
        ];
        $this->assertTrue($obj->GetSLAResponseTime($sla, strtotime('1:30'), strtotime('1:45')) > 0);

        static::$_prop['GetDayScheduleTable'] = [
            ['4:00', '5:00'],
            ['5:00', '6:00'],
            ['6:00', '7:00'],
            ['7:00', '8:00'],
        ];
        $this->assertNotNull($obj->GetSLAResponseTime($sla, strtotime('1:30'), strtotime('1:45')));
        $this->assertNotNull($obj->GetSLAResponseTime($sla, strtotime('8:00'), strtotime('9:00')));
    }

    /**
     * @throws \ReflectionException
     */
    public function testCheckIsCurrentTimeOpenReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'checkIsCurrentTimeOpen');

        $this->assertNotEmpty($method->invokeArgs($obj, [
            '_lastTimeStampClose' => 0,
            '_currentTimeline' => 2,
            '_overDueSeconds' => 0,
            '_openOverDueSeconds' => 1,
            '_shiftSeconds' => 1,
            '_timeStampOpen' => 1,
            '$_timeStampClose' => 3,
        ]));

        $this->assertNotEmpty($method->invokeArgs($obj, [
            '_lastTimeStampClose' => 0,
            '_currentTimeline' => 2,
            '_overDueSeconds' => 2,
            '_openOverDueSeconds' => 1,
            '_shiftSeconds' => 1,
            '_timeStampOpen' => 1,
            '$_timeStampClose' => 3,
        ]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testCheckIsCurrentTimeLessOpenReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'checkIsCurrentTimeLessOpen');

        $this->assertNotEmpty($method->invokeArgs($obj, [
            '_currentTimeline,' => 2,
            '_timeStampOpen,' => 4,
            '_lastTimeStampClose,' => 3,
            '_shiftSeconds,' => 1,
            '_actualOverDueSecondsLeft,' => 2,
            '_timeStampDuration,' => 1,
            '_openOverDueSeconds,' => 1,
            '_timeStampClose' => 1,
        ]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testCheckIsTimelineInDayReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'checkIsTimelineInDay');

        $this->assertNotEmpty($method->invokeArgs($obj, [
            '_currentTimeLine,' => 1,
            '_startTimeLine,' => 4,
            '_endTimeLine,' => 8,
            '_endOfTheDay,' => 5,
            '_startOfTheDay,' => 1,
            '_firstOpenTime,' => 4,
            '_totalWorkingTimeInDay,' => 1,
            '_SLAResponseTime,' => 1,
            '_scheduleList,' => 1,
            '_iterationCount,' => 1,
            '_scheduleContainer' => [1],
        ]));

        $this->assertNotEmpty($method->invokeArgs($obj, [
            '_currentTimeLine,' => 1,
            '_startTimeLine,' => 4,
            '_endTimeLine,' => 8,
            '_endOfTheDay,' => 5,
            '_startOfTheDay,' => 4,
            '_firstOpenTime,' => 4,
            '_totalWorkingTimeInDay,' => 1,
            '_SLAResponseTime,' => 1,
            '_scheduleList,' => 1,
            '_iterationCount,' => 1,
            '_scheduleContainer' => [1],
        ]));

        $this->assertNotEmpty($method->invokeArgs($obj, [
            '_currentTimeLine,' => 1,
            '_startTimeLine,' => 4,
            '_endTimeLine,' => 8,
            '_endOfTheDay,' => 5,
            '_startOfTheDay,' => 1,
            '_firstOpenTime,' => 1,
            '_totalWorkingTimeInDay,' => 1,
            '_SLAResponseTime,' => 1,
            '_scheduleList,' => [
                'opentimeline' => 1,
                'closetimeline' => 5,
            ],
            '_iterationCount,' => 1,
            '_scheduleContainer' => [1],
        ]));

        $this->assertNotEmpty($method->invokeArgs($obj, [
            '_currentTimeLine,' => 1,
            '_startTimeLine,' => 4,
            '_endTimeLine,' => 8,
            '_endOfTheDay,' => 5,
            '_startOfTheDay,' => 1,
            '_firstOpenTime,' => 1,
            '_totalWorkingTimeInDay,' => 1,
            '_SLAResponseTime,' => 1,
            '_scheduleList,' => [
                'opentimeline' => 5,
                'closetimeline' => 6,
            ],
            '_iterationCount,' => 1,
            '_scheduleContainer' => [1],
        ]));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_SLAManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject(SWIFT_SLAManagerMock::class);
    }
}

class SWIFT_SLAManagerMock extends SWIFT_SLAManager
{
    public $_slaPlanCache = false;
    public $_slaScheduleCache = false;
    public $_slaHolidayCache = false;
    public $_ticketPropertiesChanged = false;

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

