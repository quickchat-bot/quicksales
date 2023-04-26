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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_TicketDispatchTest extends \SWIFT_TestCase
{
    public static $perms = [];
    public static $_next = 0;

    public function testDispatchThrowsInvalidException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Dispatch', '0');
    }

    public function testDispatchThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Dispatch', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDispatchReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([0], [1]);
        $staff->method('GetIsClassLoaded')->willReturn(true);
        $staff->method('GetPermission')->willReturnOnConsecutiveCalls(0, 1, 1);
        \SWIFT::GetInstance()->Staff = $staff;

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSession->method('GetProperty')->willReturn(1);
        \SWIFT::GetInstance()->Session = $mockSession;

        $this->assertFalse($obj->Dispatch(1),
            'Returns false without access');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->Dispatch(1),
            'Returns false with staff_tcanviewtickets = 0');

        $this->assertTrue($obj->Dispatch(1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->assertClassNotLoaded($obj, 'Dispatch', 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetDispatchFromEmailReturnsEmail()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_GetDispatchFromEmail');
        $method->setAccessible(true);

        $_POST['emailfrom'] = '-1';
        $this->assertEquals('email', $method->invoke($obj, 'email'),
            'Will return staff email');

        $_POST['emailfrom'] = '0';
        $this->assertEquals('me@mail.com', $method->invoke($obj, 'email'),
            'Will return general_returnemail setting');

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            'list' => [
                1 => [
                    'customfromemail' => 'me@email.com',
                ],
                2 => [
                    'email' => 'metoo@email.com',
                ],
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $_POST['emailfrom'] = '1';
        $this->assertEquals('me@email.com', $method->invoke($obj, 'email'),
            'will return customfromemail address from emailqueue');

        $_POST['emailfrom'] = '2';
        $this->assertEquals('metoo@email.com', $method->invoke($obj, 'email'),
            'will return email address from emailqueue');
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessDispatchTabThrowsException()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_ProcessDispatchTab');
        $method->setAccessible(true);

        $mock = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invokeArgs($obj, [$mock, '']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessDispatchTabReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_ProcessDispatchTab');
        $method->setAccessible(true);

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetProperty')->willReturnCallback(function ($x) {
            if ($x === 'duetime') {
                return '1:00';
            }

            if ($x === 'hasdraft' || $x === 'resolutionduedateline') {
                return '1';
            }

            if ($x === 'isresolved' || $x === 'resolutiondue') {
                return '0';
            }

            return 1;
        });

        $mgr = $this->getMockBuilder('Base\Library\Notification\SWIFT_NotificationManager')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->NotificationManager = $mgr;

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'tickettimetrackid' => 1,
        ]);

        $_POST['ticketnotes'] = 1;
        $_POST['ticketpriorityid'] = 2;
        $_POST['ticketstatusid'] = 2;
        $_POST['tickettypeid'] = 2;
        $_POST['ownerstaffid'] = 2;
        $_POST['departmentid'] = 2;
        $_POST['taginput_to'] = 'me@mail.com';
        $_POST['containertaginput_to'] = ['me@mail.com', 'me3@mail.com'];
        $_POST['taginput_cc'] = 'me2@mail.com';
        $_POST['taginputcheck_cc'] = ['me2@mail.com'];
        $_POST['taginput_bcc'] = 'me@mail.com';
        $_POST['containertaginput_bcc'] = ['me2@mail.com'];
        $_POST['taginputcheck_bcc'] = ['me3@mail.com'];
        $_POST[md5('taginputcheck_ccme@mail.com')] = 'me@mail.com';
        $_POST[md5('taginputcheck_bccme2@mail.com')] = 'me2@mail.com';
        $_POST['due'] = '1:00';
        $_POST['due_hour'] = '1';
        $_POST['due_minute'] = '0';
        $_POST['resolutiondue'] = '1:00';
        $_POST['resolutiondue_hour'] = '1';
        $_POST['resolutiondue_minute'] = '0';
        $_POST['billingtimebillable'] = 1;

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'languagecache') {
                return [1 => ['languagecode' => 'en-us']];
            }

            if ($x == 'templategroupcache') {
                return [1 => ['languageid' => 1, 'tgroupid' => 1]];
            }
            return [
                1 => [
                    'departmentapp' => 'tickets',
                    'parentdepartmentid' => 0,
                    'uservisibilitycustom' => 0,
                    'departmenttype' => 'public',
                    'type' => 'public',
                    'ticketpriorityid' => '1',
                    'ruletype' => '1',
                    'isenabled' => '1',
                    'tgroupid' => '1',
                    '_criteria' => [
                        1 => [
                            'event',
                            'event',
                            'event',
                        ],
                    ],
                ],
            ];
        };

        $this->assertTrue($method->invokeArgs($obj, [$ticket, '']));

        unset($_POST['due'], $_POST['resolutiondue']);
        $this->assertTrue($method->invokeArgs($obj, [$ticket, '']));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invokeArgs($obj, [$ticket, '']);
    }

    public function testDispatchSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'DispatchSubmit', 'none');
    }

    public function testDispatchSubmitThrowsInvalidException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'DispatchSubmit', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDispatchSubmitReturnsFalse()
    {
        $obj = $this->getMocked();

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn('0');
        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->DispatchSubmit(1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDispatchSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);
        $mockDb = $staff = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            return self::$_next % 2;
        });
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
            'userorganizationid' => 0,
            'duetime' => 1,
            'isresolved' => 1,
            'ticketviewid' => 1,
            'resolutionduedateline' => 0,
            'hasdraft' => 0,
            'userdesignation' => '',
            'salutation' => '',
            'fullname' => 'fullname',
            'emailqueueid' => 0,
            'ticketmaskid' => 0,
            'tgroupid' => 1,
            'ticketslaplanid' => 0,
            'slaplanid' => 0,
            'firstresponsetime' => 0,
            'ticketpostid' => 1,
            'averageresponsetimehits' => 0,
            'dateline' => 0,
            'totalreplies' => 0,
            'searchstoreid' => 1,
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 0,
            'ownerstaffid' => 1,
            'priorityid' => 1,
            'tickettypeid' => 1,
        ]);

        $mockDb->Record = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'departmentid' => 1,
            'ticketrecipientid' => 1,
            'ticketemailid' => 1,
            'recipienttype' => 1,
            'dataid' => &self::$perms['dataid'],
            'email' => 'me@mail.com',
        ];
        \SWIFT::GetInstance()->Database = $mockDb;
        $this->expectOutputRegex('/msgnoperm/');
        $this->assertFalse($obj->DispatchSubmit(1),
            'Returns false without access');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturnCallback(function ($x) {
            if ($x === 'staff_tcanforward') {
                return '1';
            }

            if ($x === 'staff_tcanchangeunassigneddepartment') {
                return self::$perms[$x];
            }

            return '1';
        });
        self::$perms['staff_tcanchangeunassigneddepartment'] = '0';
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        $staff->method('GetIsClassLoaded')->willReturn(true);
        $staff->method('GetProperty')->willReturnArgument(0);
        $staff->method('GetStaffID')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSession->method('GetProperty')->willReturn(1);
        \SWIFT::GetInstance()->Session = $mockSession;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'ticketviewid' => 1,
                'staffid' => 1,
                'viewscope' => 1,
                'viewalltickets' => 0,
                'viewassigned' => 0,
                'viewunassigned' => 0,
                'afterreplyaction' => &self::$perms['afterreplyaction'],
                'fields' => [
                    1 => [
                        'ticketviewid' => 1,
                        'ticketviewfieldid' => 1,
                    ],
                ],
            ],
            2 => [
            ],
            'list' => [1 => 1],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $_POST['dispatchstaffid'] = '1';
        self::$perms['dataid'] = '0';

        for ($ii = 1; $ii <= 4; $ii++) {
            self::$perms['afterreplyaction'] = $ii;
            $this->assertTrue($obj->DispatchSubmit(1),
                'Returns true with afterreplyaction = ' . $ii);
        }

        self::$perms['dataid'] = '1';
        $this->assertTrue($obj->DispatchSubmit(1),
            'Returns true with valid _nextTicketID');

        $this->assertClassNotLoaded($obj, 'DispatchSubmit', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketMock
     */
    private function getMocked(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Staff\View_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Staff\Controller_TicketMock', array_merge([
            'View' => $view,
        ], $services));
    }
}
