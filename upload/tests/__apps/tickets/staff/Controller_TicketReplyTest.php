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
class Controller_TicketReplyTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_count = 0;
    public static $perms = [];

    public function testRatingThrowsExceptionWithInvalidId()
    {
        $obj = $this->getMocked();

        $_POST['ratingvalue'] = 1;
        $_POST['ratingid'] = 1;

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertInvalidData($obj, 'Rating', 'none');
    }

    public function testRatingThrowsExceptionWithEmptyId()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Rating', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRatingReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Rating(1),
            'Returns false without POST');

        $_POST['ratingvalue'] = 1;
        $_POST['ratingid'] = 1;

        $this->assertFalse($obj->Rating(1),
            'Returns false with staff_canupdateratings = 0');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Staff = $staff;

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ratingid' => 1,
            'iseditable' => 1,
            'isclientonly' => 1,
        ]);

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->Rating(1),
            'Returns false with staff_canviewratings = 0');

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);

        $this->assertTrue($obj->Rating(1),
            'Returns true with staff_canviewratings = 1');

        $this->assertClassNotLoaded($obj, 'Rating', 1);
    }

    public function testFlagThrowsExceptionWithInvalidId()
    {
        $obj = $this->getMocked();

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetIsClassLoaded')->willReturn(true);
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Staff = $staff;

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $this->assertInvalidData($obj, 'Flag', 1, 0);
    }

    public function testFlagThrowsExceptionWithEmptyId()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Flag', 'empty', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testFlagReturnsTrue()
    {
        $obj = $this->getMocked();

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Staff = $staff;

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
        ]);

        $this->assertFalse($obj->Flag(1, 1),
            'Returns false without permission');

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);

        $this->assertTrue($obj->Flag(1, 1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Flag', 1, 1);
    }

    public function testJumpThrowsExceptionWithInvalidId()
    {
        $obj = $this->getMocked();

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetIsClassLoaded')->willReturn(true);
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Staff = $staff;

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $this->assertInvalidData($obj, 'Jump', 1, 0);
    }

    public function testJumpThrowsExceptionWithEmptyId()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Jump', 'empty', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testJumpReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->GetPermission('none');

        $this->assertFalse($obj->Jump(1, 1),
            'Returns false with staff_tcanviewtickets = 0');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Staff = $staff;

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
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'searchstoreid' => 1,
            'dataid' => 1,
        ]);

        $mockDb->Record = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'searchstoreid' => 1,
            'dataid' => 1,
        ];
        \SWIFT::GetInstance()->Database = $mockDb;

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
                'afterreplyaction' => 4,
                'fields' => [
                    1 => [
                        'ticketviewid' => 1,
                        'ticketviewfieldid' => 1,
                    ],
                ],
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->expectOutputRegex('/msgnoperm/');
        $this->assertFalse($obj->Jump(1, 1),
            'Returns false without permission');

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);

        $this->assertTrue($obj->Jump(1, 1),
            'Returns true with permission');

        $this->assertTrue($obj->Jump('next', 1),
            'Returns true with jumType = next');

        $this->assertClassNotLoaded($obj, 'Jump', 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUnlinkReturnsTrue()
    {
        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getMocked([
            'Emoji' => $mockEmoji,
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
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'totalrecords' => 1,
            'islinked' => 1,
            'ticketmaskid' => 1,
            'ownerstaffid' => 1,
            'userid' => 0,
            'subject' => 'subject',
        ]);

        $mockDb->Record = [
            'ticketid' => 1,
            'chainhash' => 1,
            'ticketlinkchainid' => 1,
            'ticketpostid' => 1,
            'creator' => 1,
            'staffid' => 1,
            'userid' => 0,
            'fullname' => 'fullname',
        ];
        \SWIFT::GetInstance()->Database = $mockDb;

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $this->assertTrue($obj->Unlink(1, 1, 1));
    }

    public function testGeneralSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'GeneralSubmit', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGeneralSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

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
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'departmentid' => 1,
            'lastpostid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'ownerstaffid' => 1,
            'emailqueueid' => 0,
            'flagtype' => 0,
            'creator' => 1,
            'userid' => 0,
            'fullname' => 'fullname',
            'subject' => 'subject',
            'charset' => 'UTF-8',
            'email' => 'me@mail.com',
            'lastreplier' => '0',
            'tgroupid' => '0',
            'isresolved' => '1',
            'tickettypeid' => '1',
            'wasreopened' => '0',
            'totalreplies' => 0,
            'bayescategoryid' => 0,
            'searchstoreid' => 1,
            'dataid' => 1,
        ]);

        $mockDb->Record = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'searchstoreid' => 1,
            'dataid' => 1,
        ];
        \SWIFT::GetInstance()->Database = $mockDb;

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
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        self::$perms['afterreplyaction'] = 4;

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')
            ->willReturnCallback(function () {
                self::$_count++;

                if (self::$_count === 1) {
                    return [];
                }

                if (self::$_count === 6) {
                    return [2];
                }

                return [1];
            });

        $this->assertFalse($obj->GeneralSubmit(1),
            'Returns false without access');

        $this->assertFalse($obj->GeneralSubmit(1),
            'Returns false with staff_tcanchangeunassigneddepartment = 0');

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn(1);

        $_POST['gendepartmentid'] = 1;
        $_POST['genticketpriorityid'] = 1;
        $_POST['genticketstatusid'] = 1;
        $_POST['gentickettypeid'] = 1;
        $_POST['genownerstaffid'] = 1;

        $this->assertTrue($obj->GeneralSubmit(1));

        $_POST['gendepartmentid'] = 1;
        $this->assertTrue($obj->GeneralSubmit(1));

        foreach ([1, 2, 3, 5] as $ii) {
            self::$perms['afterreplyaction'] = $ii;
            $this->assertTrue($obj->GeneralSubmit(1),
                'Returns true with afterreplyaction = ' . $ii);
        }

        $this->assertClassNotLoaded($obj, 'GeneralSubmit', 1);
    }

    public function testSplitOrDuplicateSubmitThrowsInvalidDataException()
    {
        $obj = $this->getMocked();
        $_POST['operationmode'] = 2;
        $this->assertInvalidData($obj, 'SplitOrDuplicateSubmit');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSplitOrDuplicateSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->setExpectedException('SWIFT_Exception', SWIFT_NOPERMISSION);
        // advance permission
        \SWIFT::GetInstance()->Staff->GetPermission('staff_tcansplitticket');
        $obj->SplitOrDuplicateSubmit();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSplitOrDuplicateSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

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
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'departmentid' => 1,
            'lastpostid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'ownerstaffid' => 1,
            'emailqueueid' => 0,
            'flagtype' => 0,
            'creator' => 1,
            'userid' => 0,
            'fullname' => 'fullname',
            'subject' => 'subject',
            'charset' => 'UTF-8',
            'email' => 'me@mail.com',
            'lastreplier' => '0',
            'tgroupid' => '0',
            'isresolved' => '1',
            'tickettypeid' => '1',
            'wasreopened' => '0',
            'totalreplies' => 0,
            'bayescategoryid' => 0,
            'searchstoreid' => 1,
            'dataid' => 1,
            'ticketpostid' => 1,
            'dateline' => &static::$perms['dateline'],
            'tickettype' => 1,
            'creationmode' => 1,
            'replyto' => '',
            'isprivate' => '1',
            'ticketslaplanid' => '0',
            'slaplanid' => '0',
            'firstresponsetime' => '0',
            'averageresponsetimehits' => '0',
            'staffid' => 1,
            'ticketwatcherid' => 1,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'lastactivity' => 0,
            'resolutionduedateline' => 0,
            'attachmentid' => 1,
            'contentid' => '1',
            'filename' => 'file.txt',
            'storefilename' => 'file.txt',
            'attachmenttype' => 1,
            'filesize' => 1,
            'filetype' => 'file',
            'ticketnoteid' => 1,
            'linktype' => 1,
        ]);

        $mockDb->Record = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'searchstoreid' => 1,
            'dataid' => 1,
            'staffid' => 1,
            'ticketrecipientid' => 1,
            'ticketemailid' => 1,
            'recipienttype' => 1,
            'email' => 'me@mail.com',
            'creator' => 1,
            'ticketpostid' => 1,
            'fullname' => 1,
            'contents' => 'contents',
            'userid' => 1,
            'creationmode' => '1',
            'subject' => 'subject',
            'emailto' => 'me@mail.com',
            'ishtml' => 1,
            'isthirdparty' => '0',
            'issurveycomment' => 0,
            'dateline' => &static::$perms['dateline'],
            'isprivate' => 0,
            'attachmentid' => 1,
            'contentid' => '1',
            'notbase64' => 1,
        ];
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'departmentapp' => 'tickets',
                'markasresolved' => '1',
                'departmentid' => '2',
            ],
            2 => [
                'departmentapp' => 'tickets',
                'markasresolved' => '1',
                'departmentid' => '1',
                'ticketstatusid' => '1',
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        static::$perms['dateline'] = 1;

        $_POST['operationmode'] = 1;
        $_POST['ticketid'] = 1;
        $_POST['splitat'] = 1;
        $_POST['newtitle'] = 1;
        $_POST['closeold'] = 1;
        $obj->SplitOrDuplicateSubmit();

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetIsClassLoaded')->willReturn(1);
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Staff = $staff;

        $_POST['operationmode'] = 0;
        $this->assertTrue($obj->SplitOrDuplicateSubmit(),
        'Returns true with duplicate mode');

        static::$perms['dateline'] = 0;
        $this->assertTrue($obj->SplitOrDuplicateSubmit(),
        'Returns true if valid mode');

        $this->assertClassNotLoaded($obj, 'SplitOrDuplicateSubmit');
    }

    public function testCancelReplyThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'CancelReply', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCancelReplyReturnsTrue()
    {
        $_SWIFT = \SWIFT::GetInstance();
        $ted = $this->getMockBuilder('Tickets\Library\Ticket\SWIFT_TicketEmailDispatch')
            ->disableOriginalConstructor()
            ->getMock();
        $obj = $this->getMocked([
            'TicketEmailDispatch' => $ted,
        ]);
        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        static::$perms['GetPermission'] = 0;
        $staff->method('GetPermission')->willReturnCallback(function ($x) {
            if (!isset(static::$perms['GetPermission'])) {
                static::$perms['GetPermission'] = 0;
            }
            static::$perms['GetPermission']++;

            return !in_array(static::$perms['GetPermission'], [1, 4, 6], true);
        });
        $staff->method('GetIsClassLoaded')->willReturn(1);
        $staff->method('GetStaffID')->willReturn(1);
        $staff->method('GetProperty')->willReturnArgument(0);
        static::$perms['GetAssignedDepartments'] = 0;
        $staff->method('GetAssignedDepartments')->willReturnCallback(function () {
            if (!isset(static::$perms['GetAssignedDepartments'])) {
                static::$perms['GetAssignedDepartments'] = 0;
            }
            static::$perms['GetAssignedDepartments']++;

            return in_array(static::$perms['GetAssignedDepartments'], [1, 3, 10, 13,16], true) ? [] : [1];
        });
        $_SWIFT->Staff = $staff;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Query')->willReturnCallback(function () {
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            return self::$_next % 2;
        });
        $mockDb->method('Insert_ID')->willReturn(1);
        static::$perms['dataid'] = 0;
        $mockDb->Record = [
            'email' => 'me@mail.com',
            'ticketpostid' => 1,
            'isthirdparty' => 0,
            'issurveycomment' => 0,
            'dateline' => 1,
            'isprivate' => 0,
            'attachmentid' => 1,
            'notbase64' => 1,
            'creator' => 1,
            'dataid' => &static::$perms['dataid'],
            'staffid' => 1,
            'userid' => 1,
            'ishtml' => 0,
            'fullname' => 'fullname',
            'subject' => 'subject',
            'contents' => 'contents',
            'ticketid' => 1,
            'iswatched' => 0,
            'departmentid' => 1,
            'lastpostid' => 1,
            'ticketmaskid' => 0,
            'isresolved' => 1,
            'duedateline' => 1,
            'emailqueueid' => 1,
        ];
        static::$perms['userid'] = 1;
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {

            static::$perms['userid']++;

            return [
                'ticketid' => 1,
                'iswatched' => 0,
                'departmentid' => 1,
                'lastpostid' => 1,
                'ticketviewid' => 1,
                'ticketstatusid' => 1,
                'linktypeid' => 1,
                'userid' => &static::$perms['userid'],
                'userdesignation' => '',
                'salutation' => '',
                'dataid' => 1,
                'staffid' => 1,
                'duetime' => &static::$perms['duetime'],
                'resolutionduedateline' => 0,
                'hasdraft' => 0,
                'emailqueueid' => 1,
                'ticketmaskid' => 0,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'firstresponsetime' => '0',
                'averageresponsetimehits' => '0',
                'ticketwatcherid' => 1,
                'trasholddepartmentid' => 0,
                'lastactivity' => 0,
                'totalreplies' => 0,
                'attachmentid' => 1,
                'filename' => 'file.txt',
                'subject' => 'subject',
                'storefilename' => 'file.txt',
                'attachmenttype' => 1,
                'filesize' => 1,
                'filetype' => 'file',
                'ticketnoteid' => 1,
                'linktype' => 1,
                'ticketpostid' => 1,
                'tgroupid' => 1,
                'usergroupid' => 1,
                'dateline' => 1,
                'duedateline' => 1,
                'userorganizationid' => 0,
                'fullname' => 'fullname',
                'title' => 'title',
                'email' => 'me@mail.com',
                'replyto' => '',
                'tickethash' => '',
                'isresolved' => 1,
                'searchstoreid' => 1,
            ];
        });

        $_SWIFT->Database = $mockDb;

        static::$perms['duetime'] = 0;

        $this->expectOutputRegex('/msgnoperm/');
        $this->assertFalse($obj->ReplySubmit(1),
            'Returns false without access');

        $_POST['redepartmentid'] = 1;
        $this->assertFalse($obj->ReplySubmit(1),
            'Returns false without department');

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSession->method('GetProperty')->willReturn(1);
        \SWIFT::GetInstance()->Session = $mockSession;

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSettings->method('Get')->willReturnCallback(function ($x) {
            if ($x === 't_slaresets') {
                return 0;
            }

            return 1;
        });
        \SWIFT::GetInstance()->Settings = $mockSettings;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        self::$perms['afterreplyaction'] = 4;
        $mockCache->method('Get')->willReturn([
            1 => [
                'ticketviewid' => 1,
                'email' => 'me@mail.com',
                'staffid' => 1,
                'viewscope' => 1,
                'triggersurvey' => 1,
                'viewalltickets' => 0,
                'viewassigned' => 0,
                'viewunassigned' => 0,
                'afterreplyaction' => &self::$perms['afterreplyaction'],
                'regusergroupid' => 1,
                'fields' => [
                    1 => [
                        'ticketviewid' => 1,
                        'ticketviewfieldid' => 1,
                    ],
                ],
            ],
            'list' => [
                1 => [
                    'email' => 'me@mail.com',
                    'tgroupid' => 1,
                ],
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;
        $this->mockProperty($obj, 'Cache', $mockCache);

        $this->assertTrue($obj->CancelReply(1));

        $this->assertClassNotLoaded($obj, 'ReplySubmit', 1);
    }

    public function testReplySubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'ReplySubmit', 0);
    }

    public function testReplySubmitThrowsInvalidException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'ReplySubmit', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReplySubmitReturnsTrue()
    {
        $_SWIFT = \SWIFT::GetInstance();
        $ted = $this->getMockBuilder('Tickets\Library\Ticket\SWIFT_TicketEmailDispatch')
            ->disableOriginalConstructor()
            ->getMock();
        $obj = $this->getMocked([
            'TicketEmailDispatch' => $ted,
        ]);

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        static::$perms['GetPermission'] = 0;
        $staff->method('GetPermission')->willReturnCallback(function ($x) {
            if (!isset(static::$perms['GetPermission'])) {
                static::$perms['GetPermission'] = 0;
            }
            static::$perms['GetPermission']++;

            return !in_array(static::$perms['GetPermission'], [1, 4, 6], true);
        });
        $staff->method('GetIsClassLoaded')->willReturn(1);
        $staff->method('GetStaffID')->willReturn(1);
        $staff->method('GetProperty')->willReturnArgument(0);
        static::$perms['GetAssignedDepartments'] = 0;
        $staff->method('GetAssignedDepartments')->willReturnCallback(function () {
            if (!isset(static::$perms['GetAssignedDepartments'])) {
                static::$perms['GetAssignedDepartments'] = 0;
            }
            static::$perms['GetAssignedDepartments']++;

            return in_array(static::$perms['GetAssignedDepartments'], [1, 3, 10, 13, 16], true) ? [] : [1];
        });
        $_SWIFT->Staff = $staff;

        $this->assertFalse($obj->ReplySubmit(1),
            'Returns false with staff_tcanreply = 0');

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
        static::$perms['dataid'] = 0;
        $mockDb->Record = [
            'email' => 'me@mail.com',
            'ticketpostid' => 1,
            'isthirdparty' => 0,
            'issurveycomment' => 0,
            'dateline' => 1,
            'isprivate' => 0,
            'attachmentid' => 1,
            'notbase64' => 1,
            'creator' => 1,
            'dataid' => &static::$perms['dataid'],
            'staffid' => 1,
            'userid' => 1,
            'ishtml' => 0,
            'fullname' => 'fullname',
            'subject' => 'subject',
            'contents' => 'contents',
            'ticketid' => 1,
            'iswatched' => 0,
            'departmentid' => 1,
            'lastpostid' => 1,
            'ticketmaskid' => 0,
            'isresolved' => 1,
            'duedateline' => 1,
            'emailqueueid' => 1,
        ];
        static::$perms['userid'] = 1;
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {

            static::$perms['userid']++;

            return [
                'ticketid' => 1,
                'iswatched' => 0,
                'departmentid' => 1,
                'lastpostid' => 1,
                'ticketviewid' => 1,
                'ticketstatusid' => 1,
                'linktypeid' => 1,
                'userid' => &static::$perms['userid'],
                'userdesignation' => '',
                'salutation' => '',
                'dataid' => 1,
                'staffid' => 1,
                'duetime' => &static::$perms['duetime'],
                'resolutionduedateline' => 0,
                'hasdraft' => 0,
                'emailqueueid' => 1,
                'ticketmaskid' => 0,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'firstresponsetime' => '0',
                'averageresponsetimehits' => '0',
                'ticketwatcherid' => 1,
                'trasholddepartmentid' => 0,
                'lastactivity' => 0,
                'totalreplies' => 0,
                'attachmentid' => 1,
                'filename' => 'file.txt',
                'subject' => 'subject',
                'storefilename' => 'file.txt',
                'attachmenttype' => 1,
                'filesize' => 1,
                'filetype' => 'file',
                'ticketnoteid' => 1,
                'linktype' => 1,
                'ticketpostid' => 1,
                'tgroupid' => 1,
                'usergroupid' => 1,
                'dateline' => 1,
                'duedateline' => 1,
                'userorganizationid' => 0,
                'fullname' => 'fullname',
                'title' => 'title',
                'email' => 'me@mail.com',
                'replyto' => '',
                'tickethash' => '',
                'isresolved' => 1,
                'searchstoreid' => 1,
            ];
        });

        $_SWIFT->Database = $mockDb;

        static::$perms['duetime'] = 0;

        $this->expectOutputRegex('/.*/');
        $this->assertFalse($obj->ReplySubmit(1),
            'Returns false without access');

        $_POST['redepartmentid'] = 1;
        $this->assertFalse($obj->ReplySubmit(1),
            'Returns false without department');

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSession->method('GetProperty')->willReturn(1);
        \SWIFT::GetInstance()->Session = $mockSession;

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSettings->method('Get')->willReturnCallback(function ($x) {
            if ($x === 't_slaresets') {
                return 0;
            }

            return 1;
        });
        \SWIFT::GetInstance()->Settings = $mockSettings;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        self::$perms['afterreplyaction'] = 4;
        $mockCache->method('Get')->willReturn([
            1 => [
                'ticketviewid' => 1,
                'email' => 'me@mail.com',
                'staffid' => 1,
                'viewscope' => 1,
                'triggersurvey' => 1,
                'viewalltickets' => 0,
                'viewassigned' => 0,
                'viewunassigned' => 0,
                'afterreplyaction' => &self::$perms['afterreplyaction'],
                'regusergroupid' => 1,
                'fields' => [
                    1 => [
                        'ticketviewid' => 1,
                        'ticketviewfieldid' => 1,
                    ],
                ],
            ],
            'list' => [
                1 => [
                    'email' => 'me@mail.com',
                    'tgroupid' => 1,
                ],
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;
        $this->mockProperty($obj, 'Cache', $mockCache);

        $_POST['optreply_private'] = 1;
        $_POST['replyfrom'] = 1;
        $_POST['replyticketstatusid'] = 1;
        $_POST['optreply_addmacro'] = 1;
        $_POST['optreply_addkb'] = 1;
        $_POST['optreply_createasuser'] = 0;
        $_POST['replycontents'] = 'replycontents';

        $obj::$_checkAttachments = true;
        $this->assertFalse($obj->ReplySubmit(1),
            'Return false if attachment check fails');
        $obj::$_checkAttachments = false;

        $this->assertTrue($obj->ReplySubmit(1),
            'Returns true with valid department');

        $_POST['replydepartmentid'] = 1;
        $this->assertTrue($obj->ReplySubmit(1),
            'Returns true with replydepartmentid and addmacro');

        unset($_POST['optreply_addmacro']);
        $this->assertTrue($obj->ReplySubmit(1),
            'Returns true with replydepartmentid and addkb');

        unset($_POST['optreply_addkb']);
        $this->assertTrue($obj->ReplySubmit(1),
            'Returns true with replydepartmentid');

        foreach ([1, 2, 3] as $ii) {
            self::$perms['afterreplyaction'] = $ii;
            $_POST['optreply_addmacro'] = 1;
            $_POST['optreply_addkb'] = 1;
            $this->assertTrue($obj->ReplySubmit(1),
                'Returns true with addmacro and afterreplyaction = ' . $ii);

            unset($_POST['optreply_addmacro']);
            $this->assertTrue($obj->ReplySubmit(1),
                'Returns true with  addkb and afterreplyaction = ' . $ii);

            unset($_POST['optreply_addkb']);
            $this->assertTrue($obj->ReplySubmit(1),
                'Returns true with afterreplyaction = ' . $ii);
        }

        unset($_POST['replydepartmentid']);
        $_POST['optreply_createasuser'] = 1;
        $_POST['optreply_watch'] = 1;
        $_POST['optreply_private'] = 0;
        $_POST['replydue'] = '1:00';
        $_POST['replydue_hour'] = '1';
        $_POST['replydue_minute'] = '0';
        $_POST['replycontents'] = 'replycontents';
        self::$perms['afterreplyaction'] = 4;
        static::$perms['duetime'] = 1;
        static::$perms['userid'] = -2;
        static::$perms['dataid'] = 1;
        $_POST['replyfrom'] = 1;
        $this->assertTrue($obj->ReplySubmit(1),
            'Returns true with valid department');

        $this->assertClassNotLoaded($obj, 'ReplySubmit', 1);
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
