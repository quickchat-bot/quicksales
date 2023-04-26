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

namespace {

    // This allow us to configure the behavior of the "global mock"
    global $mockIsUploadedFile;
    $mockIsUploadedFile = false;
}

namespace Tickets\Client {

    use Knowledgebase\Admin\LoaderMock;
    use SWIFT_Exception;
    use Base\Models\User\SWIFT_User;

    function is_uploaded_file($f)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return $f === 'file.gif';
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

    function sha1_file($f)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return 1;
        }

        return call_user_func_array('\sha1_file', func_get_args());
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
     * @group tickets-clients
     */
    class Controller_TicketTest extends \SWIFT_TestCase
    {
        public static $_prop = [];

        public function setUp()
        {
            parent::setUp();

            global $mockIsUploadedFile;
            $mockIsUploadedFile = true;
        }

        public function testConstructorReturnsClassInstance()
        {
            $obj = $this->getMocked();
            $obj->_canExit = false;
            $this->assertInstanceOf('Tickets\Client\Controller_Ticket', $obj);

            $user = $this->getMockBuilder('Base\Models\User\SWIFT_User')
                ->disableOriginalConstructor()
                ->getMock();
            $user->method('GetIsClassLoaded')->willReturn(true);

            \SWIFT::GetInstance()->User = $user;
            \SWIFT::GetInstance()->Session->method('IsLoggedIn')->willReturn(true);

            \Base\Models\User\SWIFT_User::$_permissionCache['perm_canchangepriorities'] = 0;
            $obj = $this->getMocked();
            $obj->_canExit = false;
            $this->assertInstanceOf('Tickets\Client\Controller_Ticket', $obj);
        }

        /**
         * @throws \ReflectionException
         */
        public function test_GetTicketObjectReturnsTicket()
        {
            $obj = $this->getMocked();
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod('_GetTicketObject');
            $method->setAccessible(true);

            $this->assertFalse($method->invoke($obj, 0));

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'creator' => 2,
                'email' => &static::$_prop['email'],
            ];
            static::$_prop['c'] = 2;
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                if (false !== strpos($x, 'oldticketmaskid') ||
                    false !== strpos($x, 'oldticketid')) {
                    static::$_prop['c']--;
                    if (!isset(static::$_prop['r'])) {
                        static::$_prop['email'] = 'me@mail.com';
                    }
                    return ['ticketid' => static::$_prop['c']];
                }

                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([1], []);

            static::$_prop['email'] = 'me@mail.com';
            $this->assertInstanceOf('Tickets\Models\Ticket\SWIFT_Ticket', $method->invoke($obj, 'no'));

            static::$_prop['email'] = 'me2@mail.com';
            $this->assertInstanceOf('Tickets\Models\Ticket\SWIFT_Ticket', $method->invoke($obj, 'no'));
            static::$_prop['c'] = 2;
            static::$_prop['r'] = 1;
            static::$_prop['email'] = 'me2@mail.com';
            $this->assertNotNull($method->invoke($obj, 'no'));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj, 1);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testReplyReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertFalse($obj->Reply(1),
                'Returns false with invalid ticket');

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'ticketpostid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

//            $this->expectOutputRegex('/.*/');

            $this->assertFalse($obj->Reply(1),
                'Returns false without replycontents');

            $_POST['replycontents'] = 'replycontents';
            $this->assertFalse($obj->Reply(1),
                'Returns false without attachments');

            $obj->_getAttachmentsReturnValue = [true, ['ok']];
            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'regusergroupid' => '1',
                        'languageid' => '1',
                        'departmentapp' => 'tickets',
                        'languagecode' => 'en-us',
                    ],
                ];
            };
            $this->assertTrue($obj->Reply(1));

            $this->assertClassNotLoaded($obj, 'Reply', 1);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testUpdateReturnsTrue()
        {
            $mockMgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
                ->disableOriginalConstructor()
                ->getMock();
            $mockMgr->method('Check')->willReturnOnConsecutiveCalls([false, ['']], [1, [1]]);
            $obj = $this->getMocked([
                'CustomFieldManager' => $mockMgr,
            ]);

            $this->assertFalse($obj->Update(1),
                'Returns false with invalid ticket');

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'autoclosestatus' => 1,
                'ticketpostid' => 1,
                'priorityid' => 2,
                'ticketstatusid' => 2,
                'tickettypeid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            \SWIFT::Set('loopcontrol', true);

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => 0,
                        'uservisibilitycustom' => 0,
                        'departmenttype' => 'public',
                        'type' => 'public',
                        'ticketpriorityid' => '1',
                        'statustype' => 'public',
                        'ruletype' => '1',
                        'isenabled' => '1',
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

            $this->assertFalse($obj->Update(1),
                'Returns false if field check fails');

            $_POST['ticketstatusid'] = 1;
            $_POST['ticketpriorityid'] = 1;
            $this->assertTrue($obj->Update(1));

            $this->assertClassNotLoaded($obj, 'Update', 1);
        }

        public function testGetAttachmentThrowsException()
        {
            $obj = $this->getMocked();

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'autoclosestatus' => 1,
                'ticketpostid' => 1,
                'priorityid' => 2,
                'ticketstatusid' => 2,
                'tickettypeid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
                'hasattachments' => 1,
                'attachmentid' => 1,
                'filename' => 'file.txt',
                'filesize' => 1,
                'filetype' => 'file',
                'storefilename' => 'file.txt',
                'attachmenttype' => 0,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                if (false !== strpos($x, "attachmentid = '2'")) {
                    $arr['ticketid'] = 2;
                }
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->assertInvalidData($obj, 'GetAttachment', 1, 2);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testGetAttachmentReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertFalse($obj->GetAttachment(1, 1),
                'Returns false with invalid ticket');

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'autoclosestatus' => 1,
                'ticketpostid' => 1,
                'priorityid' => 2,
                'ticketstatusid' => 2,
                'tickettypeid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
                'hasattachments' => 1,
                'attachmentid' => 1,
                'filename' => 'file.txt',
                'filesize' => 1,
                'filetype' => 'file',
                'storefilename' => 'file.txt',
                'attachmenttype' => 0,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->assertTrue($obj->GetAttachment(1, 1));

            $this->assertClassNotLoaded($obj, 'GetAttachment', 1, 1);
        }

        public function testGetQuoteThrowsException()
        {
            $obj = $this->getMocked();

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'autoclosestatus' => 1,
                'ticketpostid' => 1,
                'priorityid' => 2,
                'ticketstatusid' => 2,
                'tickettypeid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                if (false !== strpos($x, "ticketpostid = '2'")) {
                    $arr['ticketid'] = 2;
                }
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->assertInvalidData($obj, 'GetQuote', 1, 2);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testGetQuoteReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertFalse($obj->GetQuote(1, 1),
                'Returns true without errors');

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'autoclosestatus' => 1,
                'ticketpostid' => 1,
                'priorityid' => 2,
                'ticketstatusid' => 2,
                'tickettypeid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'contents' => 'contents',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->expectOutputRegex('/Quote/');
            $this->assertTrue($obj->GetQuote(1, 1));

            $this->assertClassNotLoaded($obj, 'GetQuote', 1, 1);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testRatingReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertFalse($obj->Rating(1, 1),
                'Returns false with invalid ticket');

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'autoclosestatus' => 1,
                'ticketpostid' => 1,
                'priorityid' => 2,
                'ticketstatusid' => 2,
                'tickettypeid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'contents' => 'contents',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
                'ratingid' => 1,
                'ratingresultid' => 1,
                'typeid' => 1,
                'iseditable' => 0,
                'isclientonly' => 1,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->assertFalse($obj->Rating(1, 1),
                'Returns false without ratingvalue');

            $_POST['ratingvalue'] = 1;
            $this->assertTrue($obj->Rating(1, 1));

            $this->assertClassNotLoaded($obj, 'Rating', 1, 1);
        }

        public function testRatingPostThrowsException()
        {
            $obj = $this->getMocked();

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'autoclosestatus' => 1,
                'ticketpostid' => 1,
                'priorityid' => 2,
                'ticketstatusid' => 2,
                'tickettypeid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'contents' => 'contents',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
                'ratingid' => 1,
                'typeid' => 1,
                'iseditable' => 0,
                'isclientonly' => 0,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                if (false !== strpos($x, "ticketpostid = '2'")) {
                    $arr['ticketid'] = 2;
                }

                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->assertInvalidData($obj, 'RatingPost', 1, 2, 1);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testRatingPostReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertFalse($obj->RatingPost(1, 1, 1),
                'Returns true without errors');

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'autoclosestatus' => 1,
                'ticketpostid' => 1,
                'priorityid' => 2,
                'ticketstatusid' => 2,
                'tickettypeid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'contents' => 'contents',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
                'ratingid' => 1,
                'ratingresultid' => 1,
                'typeid' => 1,
                'iseditable' => 0,
                'isclientonly' => 1,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->assertFalse($obj->RatingPost(1, 1, 1),
                'Returns false without ratingvalue');

            $_POST['ratingvalue'] = 1;
            $this->assertTrue($obj->RatingPost(1, 1, 1));

            $this->assertClassNotLoaded($obj, 'RatingPost', 1, 1, 1);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testViewReturnsTrue()
        {
            $mockInput = $this->getMockBuilder('SWIFT_Input')
                ->disableOriginalConstructor()
                ->getMock();

            $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
                ->disableOriginalConstructor()
                ->getMock();

            $rdr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldRendererClient')
                ->disableOriginalConstructor()
                ->getMock();

            $obj = $this->getMocked([
                'CustomFieldRendererClient' => $rdr,
                'Input' => $mockInput,
                'Emoji' => $mockEmoji,
            ]);

            $this->assertFalse($obj->View(1),
                'Returns false with invalidticket');

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'flagtype' => 1,
                'isresolved' => 1,
                'departmentid' => &static::$_prop['departmentid'],
                'userid' => 1,
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'averageresponsetimehits' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'dateline' => 0,
                'userorganizationid' => 0,
                'emailqueueid' => 0,
                'tgroupid' => 1,
                'autoclosestatus' => 1,
                'ticketpostid' => 1,
                'priorityid' => 2,
                'ticketstatusid' => 2,
                'tickettypeid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'contents' => 'contents',
                'replyto' => '',
                'userdesignation' => '',
                'salutation' => '',
                'email' => 'me@mail.com',
                'ticketmaskid' => 0,
                'trasholddepartmentid' => 0,
                'ownerstaffid' => 1,
                'lastactivity' => 0,
                'creator' => &static::$_prop['creator'],
                'isprivate' => 0,
                'emailto' => '',
                'isthirdparty' => 0,
                'ishtml' => 0,
                'ratingid' => 1,
                'markasresolved' => 1,
                'typeid' => 1,
                'staffid' => 1,
                'iseditable' => 0,
                'userprofileimageid' => 1,
                'staffprofileimageid' => 1,

                'linktypeid' => 1,
                'hasattachments' => 1,
                'attachmentid' => 1,
                'filename' => 'file.txt',
                'filesize' => 1,
                'filetype' => 'file',
                'storefilename' => 'file.txt',
                'attachmenttype' => 0,

                'linktype' => 1,
                'ticketlinkedtableid' => 1,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->setNextRecordNoLimit();

            static::$_prop['creator'] = 0;
            static::$_prop['departmentid'] = 0;
            $this->assertFalse($obj->View(1),
                'Returns false with invalid department');

            static::$databaseCallback['SettingsGet'] = function ($x) {
                if ($x === 't_cpostorder') {
                    return 'desc';
                }

                return 1;
            };

            static::$databaseCallback['CacheGet'] = function ($x) {
                if ($x == 'ticketworkflowcache') {
                    return [
                        1 => [
                            'ticketworkflowid' => 1,
                            '_criteria' => [],
                            'ruletype' => 1,
                            'uservisibility' => '1',
                        ],
                    ];
                }

                return [
                    1 => [
                        1 => 1,
                        'parentdepartmentid' => 1,
                        'markasresolved' => 1,
                        'departmenttype' => 'public',
                        'type' => 'public',
                        'statustype' => 'public',
                        'department' => 1,
                        'title' => 1,
                    ],
                ];
            };

            static::$_prop['departmentid'] = 1;
            static::$_prop['creator'] = 2;
            $this->assertTrue($obj->View(1));

            static::$_prop['creator'] = 1;
            $this->assertTrue($obj->View(1, true, 1));

            $this->assertClassNotLoaded($obj, 'View', 1);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testExecuteWorkflowReturnsTrue()
        {
            $obj = $this->getMocked();

            $data = [
                'ticketid' => 1,
                'iswatched' => false,
                'userid' => 2,
                'email' => 'test@test.com',
                'creator' => 1,
                'replyto' => 'test@test.com',
                'ticketworkflowid' => 1,
                '_criteria' => [],
                'ruletype' => 1,
                'slaplanid' => 1,
                'departmentid' => 0,
            ];

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturnCallback(function ($x) use (&$data) {
                return $data;
            });


            $this->assertFalse($obj->ExecuteWorkflow(1, 1),
                'Returns false');

            static::$databaseCallback['CacheGet'] = function ($x) {
                if ($x == 'ticketworkflowcache')
                    return [1 => ['ticketworkflowid' => 1, '_criteria' => [], 'ruletype' => 1]];
            };

            $data['userid'] = 1;

            \SWIFT::GetInstance()->User = false;
            $this->assertFalse($obj->ExecuteWorkflow(1, 1),
                'Returns false');

            $userMock = $this->getMockBuilder(SWIFT_User::class)
                ->disableOriginalConstructor()
                ->setMethods(['GetIsClassLoaded', 'GetEmailList', 'GetUserID', 'GetOrganization', 'GetProperty'])
                ->getMock();
            $userMock->method('GetIsClassLoaded')->willReturn(true);
            $userMock->method('GetEmailList')->willReturn(['test@test.com']);
            $userMock->method('GetUserID')->willReturn(1);
            $userMock->method('GetOrganization')->willReturn(null);
            $userMock->method('GetProperty')->willReturn(1);

            \SWIFT::GetInstance()->User = $userMock;

            static::$databaseCallback['CacheGet'] = function ($x) {
                if ($x === 'ticketworkflowcache') {
                    return [
                        1 => [
                            'ticketworkflowid' => 1,
                            '_criteria' => [],
                            'ruletype' => 1,
                        ],
                    ];
                }
                return [
                    1 => [
                        1 => [1],
                    ],
                ];
            };

            $this->assertTrue($obj->ExecuteWorkflow(1, 1),
                'Returns true');

            $this->assertClassNotLoaded($obj, 'ExecuteWorkflow', 1, 1);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testExecuteWorkflowThrowsInvalidData()
        {
            $obj = $this->getMocked();
            $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
            $obj->ExecuteWorkflow('', '');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testExecuteWorkflowThrowsInvalidData2()
        {
            $obj = $this->getMocked();
            $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
            $obj->ExecuteWorkflow(1, 1);
        }

        public function testUploadImageReturnsFalse()
        {
            $obj = $this->getMocked();

            $this->expectOutputRegex('/Origin Denied/');
            $_SERVER['HTTP_ORIGIN'] = 'no';
            $_FILES['file_upload']['tmp_name'] = 'file.gif';
            $this->assertFalse($obj->UploadImage());

            $this->expectOutputRegex('/Origin Denied/');
            $_SERVER['HTTP_ORIGIN'] = 0;
            $_FILES['file_upload']['tmp_name'] = 'file.gif';
            $this->assertFalse($obj->UploadImage());

            $this->expectOutputRegex('/Invalid file name/');
            $_SERVER['HTTP_ORIGIN'] = 'http://localhost';
            $_FILES['file_upload']['name'] = '$$';
            $this->assertFalse($obj->UploadImage());

            $this->expectOutputRegex('/Invalid extension/');
            $_FILES['file_upload']['name'] = 'file.txt';
            $this->assertFalse($obj->UploadImage());

            $this->expectOutputRegex('/location/');
            $tmpfname = tempnam(sys_get_temp_dir(), 'file.gif');
            file_put_contents($tmpfname, PHP_EOL);
            $_FILES['file_upload']['tmp_name'] = $tmpfname;
            $_FILES['file_upload']['name'] = 'file.gif';
            $this->assertTrue($obj->UploadImage());
            unlink($tmpfname);
            global $mockIsUploadedFile;
            $mockIsUploadedFile = true;

            $this->expectOutputRegex('/Server Error/');
            $_FILES['file_upload']['tmp_name'] = 'notfound';
            $this->assertFalse($obj->UploadImage());
        }

        /**
         * @throws \ReflectionException
         */
        public function testProcessRatingsReturnsTrue()
        {
            $obj = $this->getMocked();
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod('processRatings');
            $method->setAccessible(true);

            $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
                ->disableOriginalConstructor()
                ->getMock();
            $ticket->method('GetTicketID')->willReturn(1);
            $ticket->method('GetIsClassLoaded')->willReturn(1);
            $ticket->method('GetProperty')->willReturn(1);
            $ticket->method('Get')->willReturn(1);

            static::$_prop['userid'] = 0;

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'userid' => &static::$_prop['userid'],
                'usergroupid' => 1,
                'useremailid' => 1,
                'ratingid' => 1,
                'typeid' => 1,
                'linktype' => 1,
                'ticketmaskid' => 0,
                'lastactivity' => 0,
                'email' => 'me@mail.com',
                'replyto' => 'me2@mail.com',
                'fullname' => 'fullname',
                'subject' => 'subject',
                'tickethash' => 'hash',
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                if (false !== strpos($x, "userid = '1'")) {
                    static::$_prop['userid'] = 1;
                }

                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $_SWIFT->Database->Record3 = [
                'ratingvisibility' => 'public',
                'ratingid' => 1,
                'departmentid' => 1,
                'iseditable' => 0,
            ];

            $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

            $this->assertTrue($method->invoke($obj, $_SWIFT, $ticket, [1]));
        }

        /**
         * @throws \ReflectionException
         */
        public function testProcessPropertiesReturnsArray()
        {
            $mockInput = $this->getMockBuilder('SWIFT_Input')
                ->disableOriginalConstructor()
                ->getMock();

            $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
                ->disableOriginalConstructor()
                ->getMock();

            $rdr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldRendererClient')
                ->disableOriginalConstructor()
                ->getMock();

            $obj = $this->getMocked([
                'CustomFieldRendererClient' => $rdr,
                'Input' => $mockInput,
                'Emoji' => $mockEmoji,
            ]);

            $class = new \ReflectionClass($obj);
            $method = $class->getMethod('processProperties');
            $method->setAccessible(true);

            $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
                ->disableOriginalConstructor()
                ->getMock();
            $ticket->method('GetTicketID')->willReturn(1);
            $ticket->method('GetIsClassLoaded')->willReturn(1);
            $ticket->method('GetProperty')->willReturnCallback(function ($x) {
                if (isset(static::$_prop[$x])) {
                    return static::$_prop[$x];
                }

                return 1;
            });
            $ticket->method('Get')->willReturn(1);

            $_SWIFT = \SWIFT::GetInstance();

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'parentdepartmentid' => &static::$_prop['parentdepartmentid'],
                        'markasresolved' => 1,
                        'departmenttype' => &static::$_prop['departmenttype'],
                        'type' => &static::$_prop['type'],
                        'statustype' => &static::$_prop['statustype'],
                        'department' => 1,
                        'title' => 1,
                    ],
                ];
            };
            static::$_prop['departmenttype'] = 'public';
            static::$_prop['type'] = 'public';
            static::$_prop['statustype'] = 'public';
            static::$_prop['parentdepartmentid'] = 1;
            $this->assertNotEmpty($method->invoke($obj, $_SWIFT, $ticket));

            static::$databaseCallback['SettingsGet'] = function ($x) {
                if ($x === 't_cstaffname') {
                    return 0;
                }

                return 1;
            };

            static::$_prop['parentdepartmentid'] = 0;
            $this->assertNotEmpty($method->invoke($obj, $_SWIFT, $ticket));

            static::$_prop['departmenttype'] = 'private';
            static::$_prop['type'] = 'private';
            static::$_prop['statustype'] = 'private';
            static::$_prop['ownerstaffid'] = 0;
            $this->assertNotEmpty($method->invoke($obj, $_SWIFT, $ticket));
        }

        /**
         * @throws \ReflectionException
         */
        public function testBuildOptionsReturnsTrue()
        {
            $obj = $this->getMocked();
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod('buildOptions');
            $method->setAccessible(true);

            $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
                ->disableOriginalConstructor()
                ->getMock();
            $ticket->method('GetTicketID')->willReturn(1);
            $ticket->method('GetIsClassLoaded')->willReturn(1);
            $ticket->method('GetProperty')->willReturnCallback(function ($x) {
                if ($x === 'ticketstatusid' ||
                    $x === 'priorityid') {
                    if (!isset(static::$_prop[$x])) {
                        static::$_prop[$x] = 0;
                    }
                    static::$_prop[$x]++;
                }
                if (isset(static::$_prop[$x])) {
                    return static::$_prop[$x];
                }

                return 1;
            });
            $ticket->method('Get')->willReturn(1);

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'departmentid' => 1,
                        'parentdepartmentid' => 1,
                        'markasresolved' => 1,
                        'departmenttype' => 'public',
                        'type' => 'public',
                        'statustype' => 'public',
                        'department' => 1,
                        'title' => 1,
                        'uservisibilitycustom' => 0,
                    ],
                    2 => [
                        1 => 1,
                        'departmentid' => 1,
                        'markasresolved' => 1,
                        'departmenttype' => 'private',
                        'type' => 'public',
                        'statustype' => 'private',
                        'department' => 1,
                        'title' => 1,
                        'uservisibilitycustom' => 0,
                    ],
                    3 => [
                        1 => 1,
                        'departmentid' => 2,
                        'markasresolved' => 1,
                        'departmenttype' => 'private',
                        'type' => 'private',
                        'statustype' => 'private',
                        'department' => 1,
                        'title' => 1,
                        'uservisibilitycustom' => 0,
                    ],
                    4 => [
                        1 => 1,
                        'departmentid' => 1,
                        'markasresolved' => 1,
                        'departmenttype' => 'private',
                        'type' => 'private',
                        'statustype' => 'private',
                        'department' => 1,
                        'title' => 1,
                    ],
                    6 => [
                        1 => 1,
                        'departmentid' => 1,
                        'markasresolved' => 1,
                        'departmenttype' => 'private',
                        'type' => 'private',
                        'statustype' => 'private',
                        'department' => 1,
                        'title' => 1,
                    ],
                ];
            };

            $this->assertTrue($method->invoke($obj, $ticket));
        }

        /**
         * @throws \ReflectionException
         */
        public function testDoPostProcessingReturnsTrue()
        {
            $obj = $this->getMocked();
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod('doPostProcessing');
            $method->setAccessible(true);

            $_SWIFT = \SWIFT::GetInstance();

            $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
                ->disableOriginalConstructor()
                ->getMock();
            $ticket->method('GetTicketID')->willReturn(1);
            $ticket->method('GetIsClassLoaded')->willReturn(1);
            $ticket->method('GetProperty')->willReturnCallback(function ($x) {
                if (isset(static::$_prop[$x])) {
                    return static::$_prop[$x];
                }

                return 1;
            });
            $ticket->method('Get')->willReturn(1);
            $ticket->method('GetUserObject')->willReturn($_SWIFT->User);

            $post = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_TicketPost')
                ->disableOriginalConstructor()
                ->getMock();
            $post->method('GetIsClassLoaded')->willReturn(1);
            $post->method('GetTicketPostID')->willReturn(1);
            $post->method('GetProperty')->willReturnCallback(function ($x) {
                if (isset(static::$_prop[$x])) {
                    return static::$_prop[$x];
                }

                return 1;
            });
            $post->method('Get')->willReturn(1);

            static::$databaseCallback['SettingsGet'] = function ($x) {
                if (isset(static::$_prop[$x])) {
                    return static::$_prop[$x];
                }

                return 1;
            };

            static::$_prop['userid'] = 1;
            $this->assertTrue($method->invoke($obj, $_SWIFT, [1 => $post], $ticket));

            static::$_prop['t_cthirdparty'] = 1;
            static::$_prop['isthirdparty'] = 2;
            static::$_prop['isprivate'] = 2;
            $this->assertTrue($method->invoke($obj, $_SWIFT, [1 => $post], $ticket));

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'designation' => 1,
                    ],
                ];
            };

            static::$_prop['t_cstaffname'] = 0;
            static::$_prop['firstpostid'] = 2;
            static::$_prop['creator'] = 1;
            $this->assertTrue($method->invoke($obj, $_SWIFT, [1 => $post], $ticket));

            static::$_prop['t_cstaffname'] = 1;
            static::$_prop['creator'] = 2;
            $this->assertTrue($method->invoke($obj, $_SWIFT, [1 => $post], $ticket));

            static::$_prop['creator'] = 3;
            $this->assertTrue($method->invoke($obj, $_SWIFT, [1 => $post], $ticket));

            static::$_prop['creator'] = 4;
            $this->assertTrue($method->invoke($obj, $_SWIFT, [1 => $post], $ticket));

            static::$_prop['creator'] = 5;
            $this->assertTrue($method->invoke($obj, $_SWIFT, [1 => $post], $ticket));
        }

        /**
         * @param array $services
         * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketMock
         */
        private function getMocked(array $services = [])
        {
            return $this->getMockObject('Tickets\Client\Controller_TicketMock', $services);
        }
    }

    class Controller_TicketMock extends Controller_Ticket
    {
        public $_getAttachmentsReturnValue;
        public $_canExit = true;

        public function __construct($services = [])
        {
            $this->Load = new LoaderMock($this);

            foreach ($services as $key => $service) {
                $this->$key = $service;
            }

            $this->SetIsClassLoaded(true);

            if (!$this->_canExit) {
                parent::__construct();
            }
        }

        public function Initialize()
        {
            // override
            return true;
        }
    }
}
