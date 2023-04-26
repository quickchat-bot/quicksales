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
class Controller_TicketForwardTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $perms = [];

    public function testForwardThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Forward', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testForwardReturnsTrue()
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

        $this->assertTrue($obj->Forward(1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->Forward(1),
            'Returns true with staff_tcanviewtickets = 0');

        $this->assertClassNotLoaded($obj, 'Forward', 1);
    }

    public function testForwardSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'ForwardSubmit', 'none');
    }

    public function testForwardSubmitThrowsInvalidException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'ForwardSubmit', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testForwardSubmitReturnsFalse()
    {
        $obj = $this->getMocked();

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn('0');
        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->ForwardSubmit(1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testForwardSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $mockDb = $staff = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                static::$databaseCallback['stop'] = true;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            if (isset(static::$databaseCallback['stop'])) {
                unset(static::$databaseCallback['stop']);
                return false;
            }

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

        self::$perms['dataid'] = 1;

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        $this->expectOutputRegex('/msgnoperm/');
        $this->assertFalse($obj->ForwardSubmit(1),
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
            2 => [],
            'list' => [
                1 => [
                    'contents' => 'contents',
                ],
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $_POST['forwarddepartmentid'] = 1;
        $_POST['frdepartmentid'] = 2;

        self::$perms['afterreplyaction'] = 4;

        $this->assertFalse($obj->ForwardSubmit(1),
            'Returns false with invalid frdepartmentid');

        $_POST['frdepartmentid'] = 1;
        $this->assertFalse($obj->ForwardSubmit(1),
            'Returns false without forwardto emails');

        $_POST['taginput_forwardto'] = 'me2@mail.com';
        $_POST['forwarddue'] = '1:00';
        $_POST['forwarddue_hour'] = '1';
        $_POST['forwarddue_minute'] = '1';
        $_POST['forwarddue_meridian'] = 'pm';
        $_POST['optforward_private'] = '1';
        $_POST['forwardfrom'] = '1';
        $_POST['forwardcontents'] = 'contents';
        $_POST['optforward_sendemail'] = '0';
        $_POST['optforward_private'] = '1';
        $_POST['optforward_addrecipients'] = ['me2@mail.com'];
        $old = \SWIFT::Get('loopcontrol');
        \SWIFT::Set('loopcontrol', true);

        $_POST['frfollowuptype'] = 'minutes';
        $_POST['frfollowupvalue'] = '1';
        $_POST['frdochangeproperties'] = '1';
        $_POST['frdonote'] = '1';
        $_POST['frdoreply'] = '1';
        $_POST['frdoforward'] = '1';

        $obj::$_checkAttachments = true;
        $this->assertFalse($obj->ForwardSubmit(1),
            'Return false if attachment check fails');
        $obj::$_checkAttachments = false;

        $this->assertTrue($obj->ForwardSubmit(1),
            'Returns true with valid forwardto emails');

        self::$perms['dataid'] = 0;

        $this->assertTrue($obj->ForwardSubmit(1),
            'Returns true with valid forwardto emails');

        for ($ii = 1; $ii <= 3; $ii++) {
            $_POST['optforward_addmacro'] = '1';
            $_POST['optforward_addkb'] = '1';
            self::$perms['afterreplyaction'] = $ii;
            for ($jj = 1; $jj <= 3; $jj++) {
                if ($jj === 2){
                    unset($_POST['optforward_addmacro']);
                }
                if ($jj === 3){
                    unset($_POST['optforward_addkb']);
                }
                $this->assertTrue($obj->ForwardSubmit(1),
                    'Returns true with valid forwardto emails');
            }
        }

        \SWIFT::Set('loopcontrol', $old);

        $_POST['optforward_addmacro'] = '1';
        $_POST['optforward_addkb'] = '1';
        $_POST['optforward_watch'] = '1';
        $_POST['optforward_private'] = '0';
        $_POST['forwarddepartmentid'] = 2;
        self::$perms['staff_tcanchangeunassigneddepartment'] = '1';
        $this->assertTrue($obj->ForwardSubmit(1),
            'Returns true with optforward_watch = 1');

        unset($_POST['optforward_addmacro']);
        $this->assertTrue($obj->ForwardSubmit(1),
            'Returns true without optforward_addmacro');

        unset($_POST['optforward_addkb']);
        $this->assertTrue($obj->ForwardSubmit(1),
            'Returns true without optforward_addmacro');

        $this->assertClassNotLoaded($obj, 'ForwardSubmit', 1);
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
