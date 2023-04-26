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

namespace {

    // This allow us to configure the behavior of the "global mock"
    global $mockIsUploadedFile;
    $mockIsUploadedFile = false;
}

namespace Tickets\Staff {

    use Knowledgebase\Admin\LoaderMock;
    use SWIFT;
    use SWIFT_Exception;

    function is_uploaded_file($f)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return $f !== 'notfound';
        }

        return call_user_func_array('\is_uploaded_file', func_get_args());
    }

    function move_uploaded_file($f1, $f2)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return true;
        }

        return call_user_func_array('\move_uploaded_file', func_get_args());
    }

    function header($h)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            echo($h);

            return true;
        }

        return call_user_func_array('\header', func_get_args());
    }

    /**
     * Class Controller_TicketTest
     * @group tickets
 * @group tickets-staff
     */
    class Controller_TicketTest extends \SWIFT_TestCase
    {
        public static $_afterreplyaction = 4;
        public static $permCount = 0;
        public static $deptCount = 0;
        public static $_next = 0;
        public static $_id = 2;
        public static $_deptId = 1;
        public static $_resolved = 0;
        public static $_notifType = 'user';

        public function setUp()
        {
            parent::setUp();

            global $mockIsUploadedFile;
            $mockIsUploadedFile = true;
        }

        public function testConstructorReturnsClassInstance()
        {
            $obj = $this->getMocked();
            $this->assertInstanceOf('Tickets\Staff\Controller_Ticket', $obj);
        }

        public function testEditThrowsException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'Edit', '-1');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testEditReturnsTrue()
        {
            $obj = $this->getMocked();

            \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
            ]);

            $this->assertTrue($obj->Edit(1),
                'Returns true with staff_tcanupdateticket = 1');

            $this->expectOutputRegex('/msgnoperm/');
            $this->assertFalse($obj->Edit(1),
                'Returns true with staff_tcanupdateticket = 0');

            $this->assertClassNotLoaded($obj, 'Edit', 1);
        }

        public function testAuditLogThrowsException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'AuditLog', '-1');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testAuditLogReturnsTrue()
        {
            $obj = $this->getMocked();

            \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
            ]);

            $this->assertTrue($obj->AuditLog(1),
                'Returns true with staff_tcanviewauditlog = 1');

            $this->expectOutputRegex('/msgnoperm/');
            $this->assertFalse($obj->AuditLog(1),
                'Returns true with staff_tcanviewauditlog = 0');

            $this->assertClassNotLoaded($obj, 'AuditLog', 1);
        }

        public function testGetQuoteThrowsException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'GetQuote', '-1', '-1');
        }

        public function testGetQuoteThrowsInvalidDataException()
        {
            $obj = $this->getMocked();

            \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturnCallback(function ($x) {
                $arr = [
                    'ticketid' => 1,
                    'ticketpostid' => 1,
                    'iswatched' => 0,
                    'lastpostid' => 0,
                    'departmentid' => 1,
                    'contents' => 'contents',
                ];

                if (false !== strpos($x, "ticketpostid = '2'")) {
                    $arr['ticketid'] = 2;
                }

                return $arr;
            });

            $this->assertInvalidData($obj, 'GetQuote', 1, 2);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testGetQuoteReturnsTrue()
        {
            $obj = $this->getMocked();

            \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
                'ticketid' => 1,
                'ticketpostid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'contents' => 'contents',
            ]);

            $this->assertTrue($obj->GetQuote(1, 1),
                'Returns true with staff_tcanviewauditlog = 1');

            $this->expectOutputRegex('/msgnoperm/');
            $this->assertFalse($obj->GetQuote(1, 1),
                'Returns true with staff_tcanviewauditlog = 0');

            $this->assertClassNotLoaded($obj, 'GetQuote', 1, 1);
        }

        public function testGetAttachmentThrowsException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'GetAttachment', '-1', '-1');
        }

        public function testGetAttachmentThrowsInvalidDataException()
        {
            $obj = $this->getMocked();

            \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturnCallback(function ($x) {
                $arr = [
                    'ticketid' => 1,
                    'attachmentid' => 1,
                    'iswatched' => 0,
                    'lastpostid' => 0,
                    'departmentid' => 1,
                    'contents' => 'contents',
                ];

                if (false !== strpos($x, "attachmentid = '2'")) {
                    $arr['ticketid'] = 2;
                }

                return $arr;
            });

            $this->assertInvalidData($obj, 'GetAttachment', 1, 2);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testGetAttachmentReturnsTrue()
        {
            $obj = $this->getMocked();

            \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
                'ticketid' => 1,
                'attachmentid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'contents' => 'contents',
            ]);

            $this->assertTrue($obj->GetAttachment(1, 1),
                'Returns true with staff_tcanviewtickets = 1');

            $this->assertFalse($obj->GetAttachment(1, 1),
                'Returns true with staff_tcanviewtickets = 0');

            $this->assertClassNotLoaded($obj, 'GetAttachment', 1, 1);
        }

        public function testSaveAsDraftThrowsInvalidDataException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'SaveAsDraft', 0);
        }

        public function testSaveAsDraftThrowsAnotherInvalidDataException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'SaveAsDraft', '-1');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testSaveAsDraftReturnsTrue()
        {
            self::$permCount = 0;
            self::$deptCount = 0;

            $obj = $this->getMocked();

            $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
                ->disableOriginalConstructor()
                ->getMock();
            $mockStaff->method('GetPermission')->willReturnCallback(function ($x) {
                self::$permCount++;

                return self::$permCount > 1;
            });
            $mockStaff->method('GetAssignedDepartments')->willReturnCallback(function ($x) {
                self::$deptCount++;

                if (self::$deptCount === 1) {
                    return [];
                }

                if (self::$deptCount === 10) {
                    return [2];
                }

                return [1];
            });
            $mockStaff->method('GetIsClassLoaded')->willReturn(true);
            $mockStaff->method('GetStaffID')->willReturn(1);
            $mockStaff->method('GetProperty')->willReturnArgument(0);

            \SWIFT::GetInstance()->Staff = $mockStaff;

            $this->assertFalse($obj->SaveAsDraft(1),
                'Returns true with staff_tcanviewtickets = 0');

            $arr = [
                'ticketid' => 1,
                'ticketviewid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'searchstoreid' => 1,
                'userid' => 0,
                'departmentid' => 1,
                'ticketdraftid' => 1,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'hasdraft' => 0,
                'contents' => 'contents',
            ];
            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);

            $oldDb = \SWIFT::GetInstance()->Database;

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
            ]);
            \SWIFT::GetInstance()->Cache = $mockCache;

            $this->expectOutputRegex('/msgnoperm/');

            $this->assertFalse($obj->SaveAsDraft(1),
                'Returns false with staff_tcanviewtickets = 1');

            $_POST['taginput_replycc'] = 'me@email.com';
            $this->assertTrue($obj->SaveAsDraft(1),
                'Returns true with staff_tcanviewtickets = 1');

            $mockDb = $this->getMockBuilder('SWIFT_Database')
                ->disableOriginalConstructor()
                ->getMock();

            $mockDb->method('AutoExecute')->willReturn(true);
            $mockDb->method('Insert_ID')->willReturn(1);
            $mockDb->method('NextRecord')->willReturnCallback(function () {
                self::$_next++;

                if (self::$_next === 2) {
                    self::$_id = 2;
                }

                return self::$_next % 2 !== 0 || self::$_next === 2 || self::$_next === 6;
            });
            $mockDb->method('QueryFetch')->willReturn($arr);
            $this->mockProperty($mockDb, 'Record', [
                'dataid' => &self::$_id,
                'ticketid' => 1,
            ]);

            \SWIFT::GetInstance()->Database = $mockDb;

            $this->assertTrue($obj->SaveAsDraft(1),
                'Returns true with staff_tcanviewtickets = 1');
            \SWIFT::GetInstance()->Database = $oldDb;

            $_POST['replycontents'] = 'replycontents';
            $_POST['optreply_watch'] = '1';
            $_POST['replydepartmentid'] = '1';
            $this->assertTrue($obj->SaveAsDraft(1),
                'Returns true with replydepartmentid');

            unset($_POST);
            self::$_afterreplyaction = 3;
            $this->assertTrue($obj->SaveAsDraft(1),
                'Returns true with staff_tcanviewtickets = 1');

            self::$_afterreplyaction = 2;
            $this->assertTrue($obj->SaveAsDraft(1),
                'Returns true with staff_tcanviewtickets = 1');

            self::$_afterreplyaction = 1;
            $this->assertTrue($obj->SaveAsDraft(1),
                'Returns true with staff_tcanviewtickets = 1');

            $this->assertClassNotLoaded($obj, 'SaveAsDraft', 1);
        }

        public function testUploadImageWorks()
        {
            $obj = $this->getMocked();
            $_FILES['file_displayicon']['tmp_name'] = 'notfound';
            $this->expectOutputRegex('/500 Server Error/');
            $obj->UploadImage();

            $tmpfile = __DIR__ . '/test.txt';
            file_put_contents($tmpfile, '1');
            $_FILES['file_displayicon']['tmp_name'] = $tmpfile;
            $_SERVER['HTTP_ORIGIN'] = 'origin';
            $this->expectOutputRegex('/403 Origin Denied/');
            $obj->UploadImage();

            $_SERVER['HTTP_ORIGIN'] = 'http://localhost';
            $_FILES['file_displayicon']['name'] = '..';
            $this->expectOutputRegex('/500 Invalid file name/');
            $obj->UploadImage();

            $_FILES['file_displayicon']['name'] = 'file.txt';
            $this->expectOutputRegex('/500 Invalid extension/');
            $obj->UploadImage();

            $_FILES['file_displayicon']['name'] = 'file.gif';
            $this->expectOutputRegex('/location/');
            $obj->UploadImage();
            unlink($tmpfile);
        }

        public function testExecuteWorkflowThrowsInvalidDataException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'ExecuteWorkflow', 0, 0);
        }

        public function testExecuteWorkflowThrowsAnotherInvalidDataException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'ExecuteWorkflow', '-1', '-1');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testExecuteWorkflowReturnsTrue()
        {
            self::$permCount = 0;
            self::$deptCount = 0;

            $obj = $this->getMocked();

            $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
                ->disableOriginalConstructor()
                ->getMock();
            $mockStaff->method('GetPermission')->willReturnCallback(function ($x) {
                self::$permCount++;

                return self::$permCount > 1;
            });
            $mockStaff->method('GetAssignedDepartments')->willReturnCallback(function ($x) {
                self::$deptCount++;

                if (self::$deptCount === 1) {
                    return [];
                }

                if (self::$deptCount === 10) {
                    return [2];
                }

                return [1];
            });
            $mockStaff->method('GetIsClassLoaded')->willReturn(true);
            $mockStaff->method('GetStaffID')->willReturn(1);
            $mockStaff->method('GetProperty')->willReturnArgument(0);

            \SWIFT::GetInstance()->Staff = $mockStaff;

            $arr = [
                'ticketid' => 1,
                'userid' => 1,
                'ticketworkflowid' => 1,
                'iswatched' => 0,
                'lastpostid' => 1,
                'ticketpostid' => 1,
                'departmentid' => &self::$_deptId,
                'trasholddepartmentid' => 0,
                '_criteria' => 1,
                'ruletype' => 1,
                'isresolved' => 1,
            ];

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);

            $this->assertFalse($obj->ExecuteWorkflow(1, 1),
                'Returns false with staff_tcanworkflow = 0');

            $this->expectOutputRegex('/msgnoperm/');
            $this->assertFalse($obj->ExecuteWorkflow(1, 1),
                'Returns false without access');

            self::$_deptId = 0;
            self::$_next = 0;
            $mockDb = $this->getMockBuilder('SWIFT_Database')
                ->disableOriginalConstructor()
                ->getMock();

            $mockDb->method('AutoExecute')->willReturn(true);
            $mockDb->method('Insert_ID')->willReturn(1);
            $mockDb->method('NextRecord')->willReturnCallback(function () {
                self::$_next++;

                if (self::$_next === 2) {
                    self::$_id = 2;
                }

                return self::$_next % 2 !== 0 || self::$_next === 2 || self::$_next === 6;
            });
            $mockDb->method('QueryFetch')->willReturn($arr);
            $this->mockProperty($mockDb, 'Record', [
                'dataid' => &self::$_id,
                'ticketid' => 1,
                'userid' => 1,
                'linktype' => 1,
                'ticketlinkedtableid' => 1,
                'linktypeid' => 1,
                'notificationtype' => &self::$_notifType,
                'ticketworkflownotificationid' => 1,
            ]);

            \SWIFT::GetInstance()->Database = $mockDb;

            $this->assertTrue($obj->ExecuteWorkflow(1, 1),
                'Returns true with staff_tcanworkflow = 1');

            self::$_deptId = 1;
            self::$_notifType = 'staff';
            $this->assertTrue($obj->ExecuteWorkflow(1, 1, 'inbox', 2),
                'Returns true with staff_tcanworkflow = 1');

            self::$_notifType = 'userorganization';
            $this->assertTrue($obj->ExecuteWorkflow(1, 1),
                'Returns true with staff_tcanworkflow = 1');

            self::$_notifType = 'team';
            $this->assertTrue($obj->ExecuteWorkflow(1, 1),
                'Returns true with staff_tcanworkflow = 1');

            self::$_notifType = 'department';
            $this->assertTrue($obj->ExecuteWorkflow(1, 1),
                'Returns true with staff_tcanworkflow = 1');

            self::$_notifType = 'other';
            $this->assertTrue($obj->ExecuteWorkflow(1, 1),
                'Returns true with staff_tcanworkflow = 1');

            $this->assertClassNotLoaded($obj, 'ExecuteWorkflow', 1, 1);
        }

        public function testEditSubmitThrowsInvalidDataException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'EditSubmit', '-1');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testEditSubmitReturnsTrue()
        {
            self::$permCount = 0;
            self::$deptCount = 0;

            $mockMgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
                ->disableOriginalConstructor()
                ->getMock();
            $mockMgr->method('Check')->willReturnOnConsecutiveCalls([false], [1], [1], [1], [1]);
            $obj = $this->getMocked([
                'CustomFieldManager' => $mockMgr,
            ]);

            $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
                ->disableOriginalConstructor()
                ->getMock();
            $mockStaff->method('GetPermission')->willReturn(true);
            $mockStaff->method('GetAssignedDepartments')->willReturnCallback(function ($x) {
                self::$deptCount++;

                if (self::$deptCount === 1) {
                    return [];
                }

                if (self::$deptCount === 10) {
                    return [2];
                }

                return [1];
            });
            $mockStaff->method('GetIsClassLoaded')->willReturn(true);
            $mockStaff->method('GetStaffID')->willReturn(1);
            $mockStaff->method('GetProperty')->willReturnArgument(0);

            \SWIFT::GetInstance()->Staff = $mockStaff;

            $arr = [
                'ticketid' => 1,
                'ticketworkflowid' => 1,
                'iswatched' => 0,
                'lastpostid' => 1,
                'ticketpostid' => 1,
                'ticketstatusid' => 1,
                'departmentid' => &self::$_deptId,
                'trasholddepartmentid' => 0,
                '_criteria' => 1,
                'ruletype' => 1,
                'isresolved' => &self::$_resolved,
                'usergroupid' => 1,
                'userid' => false,
                'ticketslaplanid' => 1,
                'linktypeid' => 1,
                'ownerstaffid' => 1,
                'priorityid' => 1,
                'tickettypeid' => 1,
                'totalreplies' => 0,
                'dateline' => 1,
                'lastactivity' => 0,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'email' => 'me@email.com',
                'replyto' => 'me@email.com',

                'slaplanid' => 1,
                'slarulecriteriaid' => 1,
                'slascheduleid' => 1,
                'title' => 'title',
                'name' => 'name',
                'ruleop' => 1,
                'rulematchtype' => 1,
                'rulematch' => 1,

                'overduehrs' => 1,
                'resolutionduehrs' => 1,

                'emailqueueid' => 0,
                'flagtype' => 1,
                'creator' => 1,
                'lastreplier' => 0,
                'charset' => 'utf-8',
                'tgroupid' => 0,
                'wasreopened' => 0,
                'bayescategoryid' => 0,
            ];

            \SWIFT::GetInstance()->Database->method('QueryFetch')
                ->willReturnCallback(function ($x) use ($arr) {
                    if (false !== strpos($x, "ticketmaskid = ''")) {
                        return [
                            'ticketid' => ($_POST['editticketslaplanid'] + 1) % 2,
                        ];
                    }

                    return $arr;
                });

            $this->expectOutputRegex('/msgnoperm/');
            $this->assertFalse($obj->EditSubmit(1),
                'Returns true without access');

            $this->assertFalse($obj->EditSubmit(1),
                'Returns true with staff_tcanupdateticket = 0');

            $_POST['editemail'] = '';
            $_POST['editsubject'] = 'subject';
            $_POST['editfullname'] = 'fullname';
            $_POST['editticketslaplanid'] = '';
            $this->assertFalse($obj->EditSubmit(1),
                'Returns false with SLA exception');

            $_POST['editticketslaplanid'] = '2';
            $_POST['taginput_editthirdparty'] = 'me@email.com';
            $_POST['taginput_editcc'] = 'me@email.com';
            $_POST['taginput_editbcc'] = 'me@email.com';
            $_POST['mergeticketid'] = 1;
            $this->assertTrue($obj->EditSubmit(1),
                'Returns false without SLA exception');

            $_POST['mergeticketid'] = 'zero';
            $_POST['editticketslaplanid'] = '0';
            self::$_resolved = 1;
            $this->assertTrue($obj->EditSubmit(1),
                'Returns true with staff_tcanupdateticket = 1');

            $_POST['mergeticketid'] = 'none';
            $_POST['editticketslaplanid'] = 1;
            self::$_resolved = 1;
            $this->assertTrue($obj->EditSubmit(1),
                'Returns true with null _SWIFT_TicketObject_Merge');

            $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
        }

        public function testCheckForValidAttachmentsReturnsArray()
        {
            $obj = $this->getMocked();
            $obj::$_checkParentAttachments = true;

            $this->assertTrue($obj->CheckForValidAttachments('testattachments'),
                'Returns true without attachments');

            $tmpFile = tempnam(sys_get_temp_dir(), 'swift');

            $_FILES['testattachments'] = [
                'name' => ['', 'file.txt'],
                'size' => ['0', '1'],
                'type' => ['', 'text/plain'],
                'tmp_name' => ['', $tmpFile],
            ];

            $mockSettings = $this->getMockBuilder('SWIFT_Settings')
                ->disableOriginalConstructor()
                ->getMock();
            $mockSettings->method('Get')->willReturnOnConsecutiveCalls('0', '1');
            SWIFT::GetInstance()->Settings = $mockSettings;

            $this->assertTrue($obj->CheckForValidAttachments('testattachments'),
                'Returns true with tickets_resattachments = 0');

            $this->assertIsArray($obj->CheckForValidAttachments('testattachments'),
                'Returns an array with attachment information');

            $obj::$_checkParentAttachments = false;
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

            $mgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
                ->disableOriginalConstructor()
                ->getMock();

            return $this->getMockObject('Tickets\Staff\Controller_TicketMock', array_merge([
                'View' => $view,
                'CustomFieldManager' => $mgr,
            ], $services));
        }
    }

    class Controller_TicketMock extends Controller_Ticket
    {
        protected static $_resultType = 'previous';
        protected static $_requireChanges = true;
        protected $_noSLACalculation = true;
        public $UserInterface;
        protected static $_sendEmail = false;
        public static $_checkAttachments = false;
        public static $_checkParentAttachments = false;
        protected static $_dispatchAutoResponder = false;

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

        public static function CheckForValidAttachments(string $_finalFieldName = 'ticketattachments') {
            if (static::$_checkParentAttachments) {
                return parent::CheckForValidAttachments($_finalFieldName);
            }

            if (!static::$_checkAttachments) {
                return true;
            }

            return [false, ['test.txt']];
        }
    }
}
