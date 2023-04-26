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
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_TicketTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_TicketTraitTest extends \SWIFT_TestCase
{
    public static $_perms = [];
    public static $_next = 0;

    /**
     * @throws SWIFT_Exception
     */
    public function testDuplicateTicketThrowsException()
    {
        $obj = $this->getMocked();

        // advance permissions
        \SWIFT::GetInstance()->Staff->GetPermission('staff_tcanduplicateticket');
        $this->setExpectedException('SWIFT_Exception', SWIFT_NOPERMISSION);
        $obj->DuplicateTicket(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDuplicateTicketReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->View->method('RenderSplitOrDuplicate')->wilLReturn(true);
        $this->assertTrue($obj->DuplicateTicket(1));

        $this->assertClassNotLoaded($obj, 'DuplicateTicket', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSplitTicketThrowsException()
    {
        $obj = $this->getMocked();

        // advance permissions
        \SWIFT::GetInstance()->Staff->GetPermission('staff_tcansplitticket');
        $this->setExpectedException('SWIFT_Exception', SWIFT_NOPERMISSION);
        $obj->SplitTicket(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSplitTicketReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->View->method('RenderSplitOrDuplicate')->wilLReturn(true);
        $this->assertTrue($obj->SplitTicket(1));

        $this->assertClassNotLoaded($obj, 'SplitTicket', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testNewTicketReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $this->assertTrue($obj->NewTicket(),
            'Returns true with staff_tcaninsertticket = 1');

        $this->assertFalse($obj->NewTicket(),
            'Returns false with staff_tcaninsertticket = 0');

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn(1);

        $this->assertTrue($obj->NewTicket(1),
            'Returns true with numeric id');

        $this->assertClassNotLoaded($obj, 'NewTicket');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testNewTicketFormThrowsException()
    {
        $obj = $this->getMocked();
        $_POST['departmentid'] = 1;
        $_POST['tickettype'] = 1;
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);
        $this->setExpectedException('SWIFT_Exception', 'No Permission to Department');
        $obj->NewTicketForm();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testNewTicketFormThrowsInvalidUserGroupException()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $_SWIFT->Staff->method('GetPermission')->willReturn(1);
        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturn([1]);
        $_SWIFT->Database->method('QueryFetch')->willReturn([
            'chatobjectid' => 1,
            'departmentid' => 1,
            'useremail' => '',
            'departmenttitle' => 'departmenttitle',
            'staffname' => 'staffname',
            'userfullname' => 'userfullname',
            'fullname' => 'fullname',
            'subject' => 'subject',
            'contents' => serialize([
                1 => [
                    'actiontype' => 'message',
                    'type' => 1,
                ],
                2 => [
                    'actiontype' => 'message',
                    'type' => 3,
                ],
                3 => [
                    'actiontype' => 'message',
                    'type' => 4,
                ],
            ]),
            'chattype' => '7',
            'userid' => 0,
            'tgroupid' => '1',
            'chatdataid' => '1',
            'chatobjectmaskid' => '0',
        ]);

        $_POST['departmentid'] = 1;
        $_POST['tickettype'] = 'user';
        $_POST['chatobjectid'] = 1;

        $this->setExpectedException('SWIFT_Exception', 'Invalid User Group ID');
        $obj->NewTicketForm();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testNewTicketFormReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();
        $this->assertFalse($obj->NewTicketForm(),
            'Returns false without POST');

        $this->assertFalse($obj->NewTicketForm(),
            'Returns false with staff_tcaninsertticket = 0');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(1);
        $mockStaff->method('GetPermission')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetProperty')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'email')) {
                return 'me@mail.com';
            }

            return $x;
        });

        $_SWIFT->Staff = $mockStaff;

        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) {
            if (!isset(static::$_perms['QueryFetch'])) {
                static::$_perms['QueryFetch'] = 0;
            }

            static::$_perms['QueryFetch']++;

            if (static::$_perms['QueryFetch'] === 6) {
                static::$_perms['userid'] = 0;
            }

            if (static::$_perms['QueryFetch'] === 12) {
                static::$_perms['tgroupid'] = 1;
            }

            return [
                'chatobjectid' => 1,
                'departmentid' => 1,
                'useremail' => '',
                'departmenttitle' => 'departmenttitle',
                'staffname' => 'staffname',
                'userfullname' => 'userfullname',
                'fullname' => 'fullname',
                'subject' => 'subject',
                'contents' => serialize([
                    1 => [
                        'actiontype' => 'message',
                        'type' => 1,
                    ],
                    2 => [
                        'actiontype' => 'message',
                        'type' => 3,
                    ],
                    3 => [
                        'actiontype' => 'message',
                        'type' => 4,
                    ],
                ]),
                'chattype' => '7',
                'userid' => static::$_perms['userid'],
                'tgroupid' => static::$_perms['tgroupid'],
                'usergroupid' => '1',
                'chatdataid' => '1',
                'linktypeid' => '1',
                'chatobjectmaskid' => '0',
            ];
        });

        static::$_perms['userid'] = 1;
        static::$_perms['tgroupid'] = 1;
        $_POST['departmentid'] = 1;
        $_POST['tickettype'] = 'user';
        $_POST['chatobjectid'] = 1;
        $this->assertTrue($obj->NewTicketForm());

        $this->assertFalse($obj->NewTicketForm(),
            'Returns false with invalid chat user object');

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'regusergroupid' => '1',
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;
        $this->mockProperty($obj, 'Cache', $mockCache);

        static::$_perms['userid'] = 0;
        $this->assertTrue($obj->NewTicketForm());

        static::$_perms['tgroupid'] = 2;
        $this->assertTrue($obj->NewTicketForm());

        $this->assertClassNotLoaded($obj, 'NewTicketForm');
    }

    public function testPrintTicketThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'PrintTicket', 0);
    }

    public function testPrintTicketThrowsInvalidDataException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'PrintTicket', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPrintTicketThrowsNoPermissionException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        $this->setExpectedException('SWIFT_Exception', SWIFT_NOPERMISSION);
        $obj->PrintTicket(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPrintTicketReturnsTrue()
    {
        $cfrs = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldRendererStaff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();
        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturnCallback(function ($x) {
            if ($x === 't_postorder') {
                return 'desc';
            }

            return '1';
        });

        $obj = $this->getMocked([
            'Settings' => $settings,
            'Emoji' => $mockEmoji,
            'CustomFieldRendererStaff' => $cfrs,
        ]);

        $mockDb = $this->getMockBuilder('SWIFT_Database')
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

        static::$_perms['creator'] = 1;
        $mockDb->Record = [
            'ticketid' => 1,
            'ticketpostid' => 1,
            'creator' => &static::$_perms['creator'],
            'staffid' => 1,
            'userid' => 0,
            'dateline' => 0,
            'ishtml' => 0,
            'contents' => 'contents',
            'fullname' => 'fullname',
            'isthirdparty' => 0,
            'issurveycomment' => 0,
        ];
        static::$_perms['departmentid'] = 1;
        static::$_perms['ticketstatusid'] = 1;
        static::$_perms['priorityid'] = 1;
        static::$_perms['tickettypeid'] = 1;
        static::$_perms['ownerstaffid'] = 1;
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 1,
            'departmentid' => &static::$_perms['departmentid'],
            'trasholddepartmentid' => 1,
            'userid' => 1,
            'ticketpostid' => 1,
            'ticketstatusid' => &static::$_perms['ticketstatusid'],
            'priorityid' => &static::$_perms['priorityid'],
            'ownerstaffid' => &static::$_perms['ownerstaffid'],
            'hasbilling' => 1,
            'tickettypeid' => &static::$_perms['tickettypeid'],
            'ticketmaskid' => 0,
            'dateline' => 0,
            'lastactivity' => 0,
            'timeworked' => 0,
            'timebilled' => 0,
            'subject' => 'subject',
            'userdesignation' => 'mr',
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetIsClassLoaded')->willReturn(true);
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertTrue($obj->PrintTicket(1),
            'Returns true with creator = staff');

        static::$_perms['creator'] = 2;
        static::$_perms['departmentid'] = 0;
        static::$_perms['ticketstatusid'] = 2;
        static::$_perms['priorityid'] = 2;
        static::$_perms['tickettypeid'] = 2;
        static::$_perms['ownerstaffid'] = 0;
        $this->assertTrue($obj->PrintTicket(1),
            'Returns true with creator = user');

        static::$_perms['ownerstaffid'] = 2;
        static::$_perms['creator'] = 5;
        $this->assertTrue($obj->PrintTicket(1),
            'Returns true with creator = thirdparty');

        static::$_perms['creator'] = 4;
        $this->assertTrue($obj->PrintTicket(1),
            'Returns true with creator = bcc');

        static::$_perms['creator'] = 3;
        $this->assertTrue($obj->PrintTicket(1),
            'Returns true with creator = cc');

        $this->assertClassNotLoaded($obj, 'PrintTicket', 1);
    }

    public function testNewTicketSubmitThrowsInvalidDataException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'NewTicketSubmit', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testNewTicketSubmitReturnsTrue()
    {
        $mockMgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
            ->disableOriginalConstructor()
            ->getMock();
        $mockMgr->method('Check')->willReturnOnConsecutiveCalls([false, ['']], [1], [1], [1], [1], [1], [1], [1], [1], [1], [1], [1], [1], [1], [1], [1]);
        $obj = $this->getMocked([
            'CustomFieldManager' => $mockMgr,
        ]);

        $this->assertFalse($obj->NewTicketSubmit('user'),
            'Returns false without POST');

        $this->assertFalse($obj->NewTicketSubmit('user'),
            'Returns false with staff_tcaninsertticket = 0');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetIsClassLoaded')->willReturn(true);
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetStaffID')->willReturn(1);
        $staff->method('GetProperty')->willReturnArgument(0);
        $staff->method('GetAssignedDepartments')->willReturnCallback(function () {
            if (!isset(static::$_perms['GetAssignedDepartments'])) {
                static::$_perms['GetAssignedDepartments'] = 0;
            }
            static::$_perms['GetAssignedDepartments']++;

            if (static::$_perms['GetAssignedDepartments'] === 5) {
                return [2];
            }

            return [1];
        });

        \SWIFT::GetInstance()->Staff = $staff;

        $_POST['newticketdepartmentid'] = 1;
        $_POST['newticketsubject'] = 'subject';
        $_POST['newticketcontents'] = 'contents';
        $this->assertFalse($obj->NewTicketSubmit('sendmail'),
            'Returns false without newticketto');

        $_POST['newticketticketstatusid'] = 1;
        $_POST['newticketownerstaffid'] = 1;
        $_POST['newtickettickettypeid'] = 1;
        $_POST['newticketticketpriorityid'] = 0;
        $this->assertFalse($obj->NewTicketSubmit('user'),
            'Returns false without POST values');

        $_POST['newticketticketpriorityid'] = 1;
        $this->assertFalse($obj->NewTicketSubmit('user'),
            'Returns false without autocomplete_userid');

        $_POST['autocomplete_userid'] = 1;
        $this->assertFalse($obj->NewTicketSubmit('user'),
            'Returns false if Custom Field Check fails');

        $mockDb = $this->getMockBuilder('SWIFT_Database')
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

        $mockDb->Record = [
            'email' => 'me@mail.com',
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 1,
            'departmentid' => 1,
            'userid' => 1,
            'ticketmaskid' => 0,
            'ticketpostid' => 1,
            'isthirdparty' => 0,
            'issurveycomment' => 0,
            'dateline' => 0,
            'isprivate' => 0,
            'ishtml' => 0,
            'creator' => 1,
            'contents' => 'contents',
            'fullname' => 'fullname',
        ];
        $mockDb->method('QueryFetchAll')->willReturn([
            'messageid' => 1,
        ]);
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 1,
            'departmentid' => 1,
            'userid' => 1,
            'fullname' => 'fullname',
            'phone' => 'phone',
            'userdesignation' => 'mr',
            'salutation' => '',
            'ticketslaplanid' => 0,
            'slaplanid' => 0,
            'ticketpostid' => 1,
            'averageresponsetimehits' => 0,
            'isresolved' => 1,
            'ownerstaffid' => 1,
            'tickettypeid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'lastactivity' => 0,
            'totalreplies' => 0,
            'dateline' => 0,
            'duetime' => 0,
            'hasdraft' => 0,
            'usergroupid' => 1,
            'tgroupid' => 1,
            'email' => 'me@mail.com',
            'emailqueueid' => 1,
            'resolutionduedateline' => 0,
            'userorganizationid' => 0,
            'guestusergroupid' => 0,
            'contents' => 'contents',
            'ishtml' => 0,
            'creator' => 1,
            'replyto' => 0,
            'tickethash' => 'a',
            'subject' => 'subject',
            'ticketmaskid' => 0,
            'ticketrecurrenceid' => 1,
            'nextrecurrence' => 0,
            'intervaltype' => 1,
            'daily_everyweekday' => 1,
            'title' => 'title',
            'useremailid' => 1,
            'linktype' => 1,
            'linktypeid' => 1,
            'firstresponsetime' => 0,
            'languageid' => 1,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $_POST['newticketdepartmentid'] = 2;
        $this->assertFalse($obj->NewTicketSubmit('user'),
            'Returns false with invalid department id');

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'languagecache')
                return [1 => ['languagecode' => 'en-us']];

            if ($x == 'templategroupcache')
                return [1 => ['languageid' => 1, 'regusergroupid' => 1]];

            return [
                1 => [
                    1 => 1,
                    'regusergroupid' => '1',
                    'departmentapp' => 'tickets',
                    'languagecode' => 'en-us',
                ],
                2 => [
                    1 => 1,
                    'regusergroupid' => '1',
                    'departmentapp' => 'tickets',
                    'tgroupid' => '1',
                ],
                'list' => [
                    1 => [
                        'email' => 'me@email.com',
                        'tgroupid' => '1',
                        'contents' => 'contents',
                    ],
                    2 => [
                        'email' => 'me@email.com',
                        'tgroupid' => '1',
                        'contents' => 'contents',
                    ],
                ],
            ];
        };

        $olc = \SWIFT::Get('loopcontrol');
        \SWIFT::Set('loopcontrol', true);

        $_POST['newticketdepartmentid'] = 1;
        $_POST['newticketfrom'] = 1;
        $_POST['optnewticket_sendemail'] = 0;
        $_POST['optnewticket_sendar'] = 1;
        $_POST['optnewticket_private'] = 1;
        $_POST['optnewticket_isphone'] = 1;
        $_POST['optnewticket_watch'] = 1;
        $_POST['optnewticket_addmacro'] = 1;
        $_POST['optnewticket_addkb'] = 1;

        $_POST['recurrencetype'] = 1;
        $_POST['recurrence_endtype'] = 2;
        $_POST['recurrence_daily_type'] = 'default';

        $_POST['newticketownerstaffid'] = 1;
        $_POST['newtickettickettypeid'] = 1;
        $_POST['newticketticketstatusid'] = 1;
        $_POST['newticketticketpriorityid'] = 1;
        $_POST['recurrence_endcount'] = 1;
        $_POST['recurrence_start'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_enddateline'] = strftime('%H:%M', strtotime('now +1 hour'));

        $obj::$_checkAttachments = true;
        $this->assertFalse($obj->NewTicketSubmit('user'),
            'Return false if attachment check fails');
        $obj::$_checkAttachments = false;

        $this->assertTrue($obj->NewTicketSubmit('user'));

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturnCallback(function ($x) {
            if ($x === 't_ochtml') {
                return 'entities';
            }

            if ($x === 'user_dispatchregemail') {
                return 0;
            }

            return '1';
        });

        \SWIFT::GetInstance()->Settings = $settings;

        unset($_POST['optnewticket_watch'], $_POST['optnewticket_addmacro']);

        $_POST['newticketfrom'] = 1;
        $_POST['optnewticket_sendar'] = 1;
        $_POST['optnewticket_private'] = 1;
        $_POST['optnewticket_isphone'] = 1;
        $_POST['optnewticket_addkb'] = 1;

        $_POST['recurrencetype'] = 1; // daily
        $_POST['recurrence_endtype'] = 3;
        $_POST['recurrence_start'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_enddateline'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_endcount'] = 1;
        $_POST['recurrence_daily_type'] = 'extended';

        $_POST['newticketownerstaffid'] = 1;
        $_POST['newtickettickettypeid'] = 1;
        $_POST['newticketticketstatusid'] = 1;
        $_POST['newticketticketpriorityid'] = 1;

        $_POST['optnewticket_sendemail'] = 1;
        $_POST['newticketdepartmentid'] = 2;
        $_POST['newticketsubject'] = 'subject';
        $_POST['newticketcontents'] = 'contents';
        $_POST['taginput_newticketto'] = 'me@mail.com';

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            if (false !== strpos($x, 'log')) {
                return '%s ';
            }

            return $x;
        });

        \SWIFT::GetInstance()->Language = $mockLang;

        $this->assertTrue($obj->NewTicketSubmit('sendmail'));


        unset($_POST['optnewticket_addkb'], $_POST['optnewticket_private']);
        $_POST['newticketfrom'] = 1;
        $_POST['optnewticket_sendar'] = 1;
        $_POST['optnewticket_isphone'] = 1;

        $_POST['recurrencetype'] = 2; // weekly
        $_POST['recurrence_endtype'] = 2;

        $_POST['newticketownerstaffid'] = 1;
        $_POST['newtickettickettypeid'] = 1;
        $_POST['newticketticketstatusid'] = 1;
        $_POST['newticketticketpriorityid'] = 1;
        $_POST['recurrence_start'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_enddateline'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_endcount'] = 1;

        $_POST['optnewticket_sendemail'] = 1;
        $_POST['newticketdepartmentid'] = 1;
        $_POST['newticketsubject'] = 'subject';
        $_POST['newticketcontents'] = 'contents';
        $_POST['taginput_newticketto'] = 'me@mail.com';

        $_POST['recurrence_weekly_ismonday'] = 1;
        $_POST['recurrence_weekly_istuesday'] = 1;
        $_POST['recurrence_weekly_iswednesday'] = 1;
        $_POST['recurrence_weekly_isthursday'] = 1;
        $_POST['recurrence_weekly_isfriday'] = 1;
        $_POST['recurrence_weekly_issaturday'] = 1;
        $_POST['recurrence_weekly_issunday'] = 1;

        $this->assertTrue($obj->NewTicketSubmit('sendmail'));


        $_POST['newticketfrom'] = 1;
        $_POST['optnewticket_sendar'] = 1;
        $_POST['optnewticket_private'] = 1;
        $_POST['optnewticket_isphone'] = 1;
        $_POST['optnewticket_addkb'] = 1;

        $_POST['recurrencetype'] = 3; // monthly
        $_POST['recurrence_endtype'] = 2;
        $_POST['recurrence_monthly_type'] = 'extended';

        $_POST['newticketownerstaffid'] = 1;
        $_POST['newtickettickettypeid'] = 1;
        $_POST['newticketticketstatusid'] = 1;
        $_POST['newticketticketpriorityid'] = 1;
        $_POST['recurrence_start'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_enddateline'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_endcount'] = 1;

        $_POST['optnewticket_sendemail'] = 1;
        $_POST['newticketdepartmentid'] = 1;
        $_POST['newticketsubject'] = 'subject';
        $_POST['newticketcontents'] = 'contents';
        $_POST['taginput_newticketto'] = 'me@mail.com';

        $this->assertTrue($obj->NewTicketSubmit('sendmail'));


        $_POST['newticketfrom'] = 1;
        $_POST['optnewticket_sendar'] = 1;
        $_POST['optnewticket_private'] = 1;
        $_POST['optnewticket_isphone'] = 1;
        $_POST['optnewticket_addkb'] = 1;

        $_POST['recurrencetype'] = 3; // monthly
        $_POST['recurrence_endtype'] = 2;
        $_POST['recurrence_monthly_day'] = 1;

        $_POST['newticketownerstaffid'] = 1;
        $_POST['newtickettickettypeid'] = 1;
        $_POST['newticketticketstatusid'] = 1;
        $_POST['newticketticketpriorityid'] = 1;
        $_POST['recurrence_start'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_enddateline'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_endcount'] = 1;

        $_POST['optnewticket_sendemail'] = 1;
        $_POST['newticketdepartmentid'] = 1;
        $_POST['newticketsubject'] = 'subject';
        $_POST['newticketcontents'] = 'contents';
        $_POST['taginput_newticketto'] = 'me@mail.com';

        $this->assertTrue($obj->NewTicketSubmit('sendmail'));


        $_POST['newticketfrom'] = 1;
        $_POST['optnewticket_sendar'] = 1;
        $_POST['optnewticket_private'] = 1;
        $_POST['optnewticket_isphone'] = 1;
        $_POST['optnewticket_addkb'] = 1;

        $_POST['recurrencetype'] = 4; // yearly
        $_POST['recurrence_endtype'] = 2;

        $_POST['newticketownerstaffid'] = 1;
        $_POST['newtickettickettypeid'] = 1;
        $_POST['newticketticketstatusid'] = 1;
        $_POST['newticketticketpriorityid'] = 1;
        $_POST['recurrence_start'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_enddateline'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_endcount'] = 1;

        $_POST['optnewticket_sendemail'] = 1;
        $_POST['newticketdepartmentid'] = 1;
        $_POST['newticketsubject'] = 'subject';
        $_POST['newticketcontents'] = 'contents';
        $_POST['taginput_newticketto'] = 'me@mail.com';

        $this->assertTrue($obj->NewTicketSubmit('sendmail'));


        $_POST['newticketfrom'] = 1;
        $_POST['optnewticket_sendar'] = 1;
        $_POST['optnewticket_private'] = 1;
        $_POST['optnewticket_isphone'] = 1;
        $_POST['optnewticket_addkb'] = 1;

        $_POST['recurrencetype'] = 4; // yearly
        $_POST['recurrence_endtype'] = 2;
        $_POST['recurrence_yearly_type'] = 'extended';

        $_POST['newticketownerstaffid'] = 1;
        $_POST['newtickettickettypeid'] = 1;
        $_POST['newticketticketstatusid'] = 1;
        $_POST['newticketticketpriorityid'] = 1;
        $_POST['recurrence_start'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_enddateline'] = strftime('%H:%M', strtotime('now +1 hour'));
        $_POST['recurrence_endcount'] = 1;

        $_POST['optnewticket_sendemail'] = 1;
        $_POST['newticketdepartmentid'] = 1;
        $_POST['newticketsubject'] = 'subject';
        $_POST['newticketcontents'] = 'contents';
        $_POST['taginput_newticketto'] = 'me@mail.com';

        $this->assertTrue($obj->NewTicketSubmit('sendmail'));

        \SWIFT::Set('loopcontrol', $olc);

        $this->assertClassNotLoaded($obj, 'NewTicketSubmit', 1);
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
