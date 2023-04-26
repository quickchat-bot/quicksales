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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_TicketReleaseTest extends \SWIFT_TestCase
{
    public static $_afterreplyaction = 4;
    public static $_next = 0;

    public function testReleaseThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Release', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReleaseReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
        ]);

        $this->assertTrue($obj->Release(1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->Release(1),
            'Returns true with staff_tcanviewtickets = 0');

        $this->assertClassNotLoaded($obj, 'Release', 1);
    }

    public function testTakeThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Take', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testTakeReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'ownerstaffid' => 1,
            'emailqueueid' => 0,
            'flagtype' => 1,
            'creator' => 1,
            'userid' => 1,
            'lastreplier' => 1,
            'tgroupid' => 1,
            'isresolved' => 1,
            'tickettypeid' => 1,
            'wasreopened' => 0,
            'totalreplies' => 0,
            'bayescategoryid' => 0,
            'charset' => 'UTF-8',
            'subject' => 'subject',
            'fullname' => 'fullname',
            'email' => 'me@mail.com',
        ]);

        $this->assertTrue($obj->Take(1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->assertFalse($obj->Take(1),
            'Returns true with staff_tcanviewtickets = 0');

        $this->assertClassNotLoaded($obj, 'Take', 1);
    }

    public function testSurrenderThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Surrender', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSurrenderReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'ownerstaffid' => 1,
            'emailqueueid' => 0,
            'flagtype' => 1,
            'creator' => 1,
            'userid' => 1,
            'lastreplier' => 1,
            'tgroupid' => 1,
            'isresolved' => 1,
            'tickettypeid' => 1,
            'wasreopened' => 0,
            'totalreplies' => 0,
            'bayescategoryid' => 0,
            'resolutionlevel' => 0,
            'subject' => 'subject',
            'charset' => 'UTF-8',
            'fullname' => 'fullname',
            'email' => 'me@mail.com',
        ]);

        $this->assertTrue($obj->Surrender(1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->assertFalse($obj->Surrender(1),
            'Returns true with staff_tcanviewtickets = 0');

        $this->assertClassNotLoaded($obj, 'Surrender', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReleaseSubmitThrowsInvalidDataException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'ownerstaffid' => 1,
            'emailqueueid' => 0,
            'flagtype' => 1,
            'creator' => 1,
            'userid' => 1,
            'lastreplier' => 1,
            'tgroupid' => 1,
            'isresolved' => 1,
            'tickettypeid' => 1,
            'wasreopened' => 0,
            'totalreplies' => 0,
            'bayescategoryid' => 0,
            'charset' => 'UTF-8',
            'subject' => 'subject',
            'fullname' => 'fullname',
            'email' => 'me@mail.com',
        ]);

        $this->assertFalse($obj->ReleaseSubmit('1'));
    }

    public function testReleaseSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'ReleaseSubmit', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReleaseSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $mockDb = $staff = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            if (self::$_next > 15) {
                return 0;
            }

            return self::$_next % 2;
        });
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'ownerstaffid' => 1,
            'emailqueueid' => 0,
            'flagtype' => 1,
            'creator' => 1,
            'userid' => 1,
            'lastreplier' => 1,
            'tgroupid' => 1,
            'isresolved' => 1,
            'tickettypeid' => 1,
            'tickettimetrackid' => 1,
            'timeworked' => 0,
            'timebilled' => 0,
            'wasreopened' => 0,
            'totalreplies' => 0,
            'bayescategoryid' => 0,
            'duetime' => 1,
            'resolutionduedateline' => 1,
            'userorganizationid' => 0,
            'charset' => 'UTF-8',
            'subject' => 'subject',
            'fullname' => 'fullname',
            'email' => 'me@mail.com',
            'searchstoreid' => 1,
        ]);

        $mockDb->Record = [
            'dataid' => 1,
            'ticketid' => 1,
        ];

        \SWIFT::GetInstance()->Database = $mockDb;

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturnOnConsecutiveCalls(1, 0);
        \SWIFT::GetInstance()->Staff = $staff;

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->ReleaseSubmit(1),
            'Returns false without access');

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
                'afterreplyaction' => &self::$_afterreplyaction,
                'fields' => [
                    [
                        'ticketviewfieldid' => 1,
                    ],
                ],
            ],
            2 => [

            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1, 2]);

        $_POST['reldepartmentid'] = 2;
        $_POST['relownerstaffid'] = 1;
        $_POST['reltickettypeid'] = 1;
        $_POST['relticketstatusid'] = 1;
        $_POST['relticketpriorityid'] = 1;
        $_POST['releaseticketnotes'] = 1;
        $_POST['relbillingtimeworked'] = 1;
        $_POST['relbillingtimebillable'] = 1;

        $this->assertTrue($obj->ReleaseSubmit(1));

        $this->assertTrue($obj->ReleaseSubmit(1),
            'Returns true with invalid _nextTicketID');

        $_POST['releasedue'] = '1:00';
        $_POST['releasedue_hour'] = '1';
        $_POST['releasedue_minute'] = '1';
        $_POST['releasedue_meridian'] = 'pm';
        $_POST['releaseresolutiondue'] = '1:00';
        $_POST['releaseresolutiondue_hour'] = '1';
        $_POST['releaseresolutiondue_minute'] = '1';
        $_POST['releaseresolutiondue_meridian'] = 'pm';

        self::$_afterreplyaction = 3;
        $this->assertTrue($obj->ReleaseSubmit(1),
            'Returns true with afterreplyaction = ticket');

        self::$_afterreplyaction = 2;
        $this->assertTrue($obj->ReleaseSubmit(1),
            'Returns true with afterreplyaction = activeticketlist');

        self::$_afterreplyaction = 1;
        $this->assertTrue($obj->ReleaseSubmit(1),
            'Returns true with afterreplyaction = topticketlist');

        $_POST['resdepartmentid'] = 3;

        $this->assertTrue($obj->ReleaseSubmit(1),
            'Returns true with invalid resdepartmentid');

        $this->assertClassNotLoaded($obj, 'ReleaseSubmit', 1);
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
