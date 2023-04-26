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

namespace Tickets\Library\Ticket {

    use Knowledgebase\Admin\LoaderMock;
    use SWIFT;
    use SWIFT_Exception;

    function is_uploaded_file($f)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return true;
        }

        return call_user_func_array('\is_uploaded_file', func_get_args());
    }

    function file_get_contents($f)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return '';
        }

        return call_user_func_array('\file_get_contents', func_get_args());
    }

    /**
     * Class TicketEmailDispatchTest
     * @group tickets
     * @group tickets-lib1
     */
    class TicketEmailDispatchTest extends \SWIFT_TestCase
    {
        public static $_prop = [];

        public function setUp()
        {
            parent::setUp();

            global $mockIsUploadedFile;
            $mockIsUploadedFile = true;
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testConstructorReturnsClassInstance()
        {
            $obj = $this->getMocked();
            $this->assertInstanceOf('Tickets\Library\Ticket\SWIFT_TicketEmailDispatch', $obj);

            $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
                ->disableOriginalConstructor()
                ->getMock();
            $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
            new SWIFT_TicketEmailDispatch($ticket);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDispatchNotificationReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertTrue($obj->DispatchNotification(),
                'Returns true without errors');

            $this->assertClassNotLoaded($obj, 'DispatchNotification');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDispatchUserReplyReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);
            static::$databaseCallback['Query'] = function ($x) {
                if (false !== strpos($x, 'swticketrecipients')) {
                    static::$_prop['recipientid'] = 1;
                }
                if (false !== strpos($x, 'ticketemailid')) {
                    static::$_prop['emailid'] = 1;
                }
            };
            static::$databaseCallback['NextRecord'] = function () {
                if (isset(static::$_prop['recipientid'])) {
                    static::$_prop['recipientid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'recipienttype' => static::$_prop['recipientid'],
                        'ticketemailid' => static::$_prop['recipientid'],
                        'ticketrecipientid' => static::$_prop['recipientid'],
                    ];

                    if (static::$_prop['recipientid'] >= 4) {
                        unset(static::$_prop['recipientid']);
                        return false;
                    }

                    return true;
                }

                if (isset(static::$_prop['emailid'])) {
                    static::$_prop['emailid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'ticketemailid' => static::$_prop['emailid'],
                        'email' => 'me' . static::$_prop['emailid'] . '@mail.com',
                    ];

                    if (static::$_prop['emailid'] >= 4) {
                        unset(static::$_prop['emailid']);
                        return false;
                    }

                    return true;
                }

                return static::$nextRecordCount % 2;
            };

            $user = $this->getMockBuilder('Base\Models\User\SWIFT_User')
                ->disableOriginalConstructor()
                ->getMock();

            $this->assertTrue($obj->DispatchUserReply($user, 'contents', false, [1], 'me@mail.com', false),
                'Returns true without errors');

            $this->assertClassNotLoaded($obj, 'DispatchUserReply', $user, 'contents', false, [1], 'me@mail.com', true);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDispatchStaffReplyReturnsTrue()
        {
            $obj = $this->getMocked();

            $staff = \SWIFT::GetInstance()->Staff;

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'tgroupid' => 1,
                'languageid' => 1,
                'languageengineid' => 1,
                'linktype' => 1,
                'guestusergroupid' => 1,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                if (!isset(static::$_prop['tgroupid']) &&
                    false !== strpos($x, 'templategroups WHERE tgroupid')) {
                    $arr['tgroupid'] = 0;
                    static::$_prop['tgroupid'] = 1;
                }

                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'languageid' => '1',
                        'regusergroupid' => '1',
                        'departmentapp' => 'tickets',
                        'languagecode' => 'en-us',
                    ],
                ];
            };

            $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);
            static::$databaseCallback['Query'] = function ($x) {
                if (false !== strpos($x, 'swticketrecipients')) {
                    static::$_prop['recipientid'] = 0;
                }
                if (false !== strpos($x, 'ticketemailid')) {
                    static::$_prop['emailid'] = 0;
                }
            };
            static::$databaseCallback['NextRecord'] = function () {
                if (isset(static::$_prop['recipientid'])) {
                    static::$_prop['recipientid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'recipienttype' => static::$_prop['recipientid'],
                        'ticketemailid' => static::$_prop['recipientid'],
                        'ticketrecipientid' => static::$_prop['recipientid'],
                    ];

                    if (static::$_prop['recipientid'] >= 4) {
                        unset(static::$_prop['recipientid']);
                        return false;
                    }

                    return true;
                }

                if (isset(static::$_prop['emailid'])) {
                    static::$_prop['emailid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'ticketemailid' => static::$_prop['emailid'],
                        'email' => 'me' . static::$_prop['emailid'] . '@mail.com',
                    ];

                    if (static::$_prop['emailid'] >= 4) {
                        unset(static::$_prop['emailid']);
                        return false;
                    }

                    return true;
                }

                return static::$nextRecordCount % 2;
            };

            $_REQUEST['languageid'] = 1;

            $this->assertTrue($obj->DispatchStaffReply($staff, 'contents', false, 'me@mail.com',
                ['signature1', 'signature2']));

            $obj->Ticket->method('GetCCUserEmails')->willReturn([0 => 'me2@mail.com', 1 => 1]);

            $obj->returnLanguage = false;

            $this->assertTrue($obj->DispatchStaffReply($staff, 'contents', false, 'me@mail.com',
                ['signature1', 'signature2']));

            $this->assertClassNotLoaded($obj, 'DispatchStaffReply', $staff, 'contents', false, 'me@mail.com',
                'signature');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDispatchForwardReplyReturnsTrue()
        {
            $obj = $this->getMocked();

            $staff = \SWIFT::GetInstance()->Staff;

            $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);
            static::$databaseCallback['Query'] = function ($x) {
                if (false !== strpos($x, 'swticketrecipients')) {
                    static::$_prop['recipientid'] = 1;
                }
                if (false !== strpos($x, 'ticketemailid')) {
                    static::$_prop['emailid'] = 1;
                }
            };
            static::$databaseCallback['NextRecord'] = function () {
                if (isset(static::$_prop['recipientid'])) {
                    static::$_prop['recipientid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'recipienttype' => static::$_prop['recipientid'],
                        'ticketemailid' => static::$_prop['recipientid'],
                        'ticketrecipientid' => static::$_prop['recipientid'],
                    ];

                    if (static::$_prop['recipientid'] >= 4) {
                        unset(static::$_prop['recipientid']);
                        return false;
                    }

                    return true;
                }

                if (isset(static::$_prop['emailid'])) {
                    static::$_prop['emailid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'ticketemailid' => static::$_prop['emailid'],
                        'email' => 'me' . static::$_prop['emailid'] . '@mail.com',
                    ];

                    if (static::$_prop['emailid'] >= 4) {
                        unset(static::$_prop['emailid']);
                        return false;
                    }

                    return true;
                }

                return static::$nextRecordCount % 2;
            };

            $this->assertTrue($obj->DispatchForwardReply('me@mail.com', $staff, 'contents', false, 'me@mail.com',
                ['signature1', 'signature2'], 'subject', true),
                'Returns true without errors');

            $this->assertClassNotLoaded($obj, 'DispatchForwardReply', 'me@mail.com', $staff, 'contents', false,
                'me@mail.com',
                'signature', 'subject', false);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDispatchAutoresponderReturnsTrue()
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
                'tgroupid' => 1,
                'linktype' => 1,
                'languageid' => 1,
                'languageengineid' => 1,
                'guestusergroupid' => 1,

                'ticketpostid' => 1,
                'hasattachments' => 0,
                'creator' => 1,
                'ishtml' => 1,
                'contents' => 'contents',
            ];

            unset(static::$_prop['tgroupid']);
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                if (!isset(static::$_prop['tgroupid']) &&
                    false !== strpos($x, 'templategroups WHERE tgroupid')) {
                    $arr['tgroupid'] = 0;
                    static::$_prop['tgroupid'] = 1;
                }

                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'languageid' => '1',
                        'regusergroupid' => '1',
                        'departmentapp' => 'tickets',
                        'languagecode' => 'en-us',
                    ],
                ];
            };

            $_REQUEST['languageid'] = 1;

            $obj->returnLanguage = false;

            $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);
            static::$databaseCallback['Query'] = function ($x) {
                if (false !== strpos($x, 'swticketrecipients')) {
                    static::$_prop['recipientid'] = 1;
                }
                if (false !== strpos($x, 'ticketemailid')) {
                    static::$_prop['emailid'] = 1;
                }
            };
            static::$databaseCallback['NextRecord'] = function () {
                if (isset(static::$_prop['recipientid'])) {
                    static::$_prop['recipientid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'recipienttype' => static::$_prop['recipientid'],
                        'ticketemailid' => static::$_prop['recipientid'],
                        'ticketrecipientid' => static::$_prop['recipientid'],
                    ];

                    if (static::$_prop['recipientid'] >= 4) {
                        unset(static::$_prop['recipientid']);
                        return false;
                    }

                    return true;
                }

                if (isset(static::$_prop['emailid'])) {
                    static::$_prop['emailid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'ticketemailid' => static::$_prop['emailid'],
                        'email' => 'me' . static::$_prop['emailid'] . '@mail.com',
                    ];

                    if (static::$_prop['emailid'] >= 4) {
                        unset(static::$_prop['emailid']);
                        return false;
                    }

                    return true;
                }

                return static::$nextRecordCount % 2;
            };

            $this->assertTrue($obj->DispatchAutoresponder('me@mail.com', ['me2@mail.com']));

            $this->assertTrue($obj->DispatchAutoresponder('', false));

            $this->assertClassNotLoaded($obj, 'DispatchAutoresponder', 'me@mail.com', ['me2@mail.com']);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDispatchPendingAutoCloseReturnsTrue()
        {
            $obj = $this->getMocked();

            $rule = $this->getMockBuilder('Tickets\Models\AutoClose\SWIFT_AutoCloseRule')
                ->disableOriginalConstructor()
                ->getMock();

            $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);
            static::$databaseCallback['Query'] = function ($x) {
                if (false !== strpos($x, 'swticketrecipients')) {
                    static::$_prop['recipientid'] = 1;
                }
                if (false !== strpos($x, 'ticketemailid')) {
                    static::$_prop['emailid'] = 1;
                }
            };
            static::$databaseCallback['NextRecord'] = function () {
                if (isset(static::$_prop['recipientid'])) {
                    static::$_prop['recipientid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'recipienttype' => static::$_prop['recipientid'],
                        'ticketemailid' => static::$_prop['recipientid'],
                        'ticketrecipientid' => static::$_prop['recipientid'],
                    ];

                    if (static::$_prop['recipientid'] >= 4) {
                        unset(static::$_prop['recipientid']);
                        return false;
                    }

                    return true;
                }

                if (isset(static::$_prop['emailid'])) {
                    static::$_prop['emailid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'ticketemailid' => static::$_prop['emailid'],
                        'email' => 'me' . static::$_prop['emailid'] . '@mail.com',
                    ];

                    if (static::$_prop['emailid'] >= 4) {
                        unset(static::$_prop['emailid']);
                        return false;
                    }

                    return true;
                }

                return static::$nextRecordCount % 2;
            };

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
                'tgroupid' => 1,
                'languageid' => 1,
            ]);

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'languageid' => '1',
                        'regusergroupid' => '1',
                        'departmentapp' => 'tickets',
                        'languagecode' => 'en-us',
                    ],
                ];
            };

            $this->assertTrue($obj->DispatchPendingAutoClose($rule, 'me@mail.com', ['me2@mail.com']));

            $this->assertTrue($obj->DispatchPendingAutoClose($rule, '', false));

            $this->assertClassNotLoaded($obj, 'DispatchPendingAutoClose', $rule, 'me@mail.com', ['me2@mail.com']);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDispatchFinalAutoCloseReturnsTrue()
        {
            $obj = $this->getMocked();

            $rule = $this->getMockBuilder('Tickets\Models\AutoClose\SWIFT_AutoCloseRule')
                ->disableOriginalConstructor()
                ->getMock();

            $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);
            static::$databaseCallback['Query'] = function ($x) {
                if (false !== strpos($x, 'swticketrecipients')) {
                    static::$_prop['recipientid'] = 1;
                }
                if (false !== strpos($x, 'ticketemailid')) {
                    static::$_prop['emailid'] = 1;
                }
            };
            static::$databaseCallback['NextRecord'] = function () {
                if (isset(static::$_prop['recipientid'])) {
                    static::$_prop['recipientid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'recipienttype' => static::$_prop['recipientid'],
                        'ticketemailid' => static::$_prop['recipientid'],
                        'ticketrecipientid' => static::$_prop['recipientid'],
                    ];

                    if (static::$_prop['recipientid'] >= 4) {
                        unset(static::$_prop['recipientid']);
                        return false;
                    }

                    return true;
                }

                if (isset(static::$_prop['emailid'])) {
                    static::$_prop['emailid']++;

                    \SWIFT::GetInstance()->Database->Record = [
                        'ticketemailid' => static::$_prop['emailid'],
                        'email' => 'me' . static::$_prop['emailid'] . '@mail.com',
                    ];

                    if (static::$_prop['emailid'] >= 4) {
                        unset(static::$_prop['emailid']);
                        return false;
                    }

                    return true;
                }

                return static::$nextRecordCount % 2;
            };

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'languageid' => '1',
                        'languagecode' => 'en-us',
                    ],
                ];
            };

            $arr = [
                'tgroupid' => 1,
                'languageid' => 1,
            ];

            \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });

            $this->assertTrue($obj->DispatchFinalAutoClose($rule, 'me@mail.com', ['me2@mail.com']));

            $this->assertTrue($obj->DispatchFinalAutoClose($rule, '', false));

            $this->assertClassNotLoaded($obj, 'DispatchFinalAutoClose', $rule, 'me@mail.com', ['me2@mail.com']);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDispatchSurveyReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertTrue($obj->DispatchSurvey('me@mail.com', ['me2@mail.com']));

            $this->assertTrue($obj->DispatchSurvey('', false));

            $this->assertClassNotLoaded($obj, 'DispatchSurvey', 'me@mail.com', ['me2@mail.com']);
        }

        /**
         * @throws \ReflectionException
         */
        public function testProcessAttachmentsReturnsTrue()
        {
            $obj = $this->getMocked();
            $method = $this->getMethod($obj, 'ProcessAttachments');

            $this->assertFalse($method->invoke($obj, []));

            $this->assertTrue($method->invoke($obj, [
                [],
                [
                    'data' => 1,
                    'size' => 1,
                    'filename' => 1,
                    'extension' => 1,
                    'contenttype' => 1,
                ],
            ]));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj, []);
        }

        /**
         * @throws \ReflectionException
         */
        public function testProcessTicketAttachmentsReturnsTrue()
        {
            $obj = $this->getMocked();
            $method = $this->getMethod($obj, 'ProcessTicketAttachments');

            $this->assertFalse($method->invoke($obj));

            $obj->Ticket->method('GetAttachments')->willReturn([[]]);
            $this->assertTrue($method->invoke($obj));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj);
        }

        /**
         * @throws \ReflectionException
         */
        public function testProcessPostAttachmentsReturnsTrue()
        {
            $obj = $this->getMocked();
            $method = $this->getMethod($obj, 'ProcessPostAttachments');

            $this->assertFalse($method->invoke($obj, 'field'));

            $_FILES['field'] = [
                'name' => ['', 'file.txt'],
                'size' => ['0', '1'],
                'type' => ['', 'text/plain'],
                'tmp_name' => ['', '/tmp/file.txt'],
            ];
            $this->assertTrue($method->invoke($obj, 'field'));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj, 'field');
        }

        /**
         * @throws \ReflectionException
         */
        public function testProcessPostListReturnsTrue()
        {
            $obj = $this->getMocked();
            $method = $this->getMethod($obj, 'ProcessPostList');

            $this->assertTrue($method->invoke($obj, true));

            static::$_prop['isprivate'] = 0;
            $this->assertTrue($method->invoke($obj, false));

            static::$_prop['creator'] = 5;
            static::$_prop['isthirdparty'] = 2;
            $this->assertTrue($method->invoke($obj, false));

            static::$_prop['creator'] = 3;
            $this->assertTrue($method->invoke($obj, false));

            static::$_prop['creator'] = 4;
            $this->assertTrue($method->invoke($obj, false));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj, true);
        }

        /**
         * @throws \ReflectionException
         */
        public function testPrepareReturnsTrue()
        {
            $obj = $this->getMocked();
            $method = $this->getMethod($obj, 'Prepare');

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => [1],
                        'tgroupid' => 1,
                        'languageid' => 1,
                    ],
                    2 => [
                        'tgroupid' => 2,
                        'languageid' => 2,
                    ],
                ];
            };

            $this->assertTrue($method->invoke($obj));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj);
        }

        /**
         * @throws \ReflectionException
         */
        public function testLoadTemplateVariablesReturnsTrue()
        {
            $obj = $this->getMocked();
            $method = $this->getMethod($obj, 'LoadTemplateVariables');

            static::$_prop['ownerstaffid'] = 0;
            $obj->Ticket->method('GetDataStore')->willReturnCallback(function () {
                return [
                    'tgroupid' => 1,
                    'ticketid' => 1,
                    'iswatched' => 0,
                    'lastpostid' => 0,
                    'departmentid' => 1,
                    'userid' => 1,
                    'ticketpostid' => 1,
                    'linktype' => 1,
                    'trasholddepartmentid' => 0,
                    'ticketstatusid' => 1,
                    'ownerstaffid' => &static::$_prop['ownerstaffid'],
                    'priorityid' => 1,
                    'tickettypeid' => 1,
                    'totalreplies' => 0,
                    'lastactivity' => 0,
                    'fullname' => 'fullname',
                    'email' => 'email',
                    'ratingid' => 1,
                    'iseditable' => 1,
                    'isclientonly' => 1,
                    'staffid' => 1,
                    'tickettimetrackid' => 1,
                    'tickettimetracknoteid' => 1,
                    'timeworked' => 0,
                    'timebilled' => 0,
                    'wasreopened' => 0,
                    'bayescategoryid' => 0,
                    'duetime' => 1,
                    'resolutionduedateline' => 1,
                    'userorganizationid' => 0,
                    'charset' => 'UTF-8',
                    'subject' => 'subject',
                    'searchstoreid' => 1,
                    'emailqueueid' => 1,
                ];
            });
            $this->assertTrue($method->invoke($obj, 1));

            $mock1 = $this->getMockBuilder('Base\Models\User\SWIFT_User')
                ->disableOriginalConstructor()
                ->getMock();
            $mock1->method('GetIsClassLoaded')->willReturn(true);
            $mock2 = $this->getMockBuilder('Base\Models\User\SWIFT_UserOrganization')
                ->disableOriginalConstructor()
                ->getMock();
            $mock2->method('GetIsClassLoaded')->willReturn(true);
            $mock3 = $this->getMockBuilder('Base\Models\User\SWIFT_UserGroup')
                ->disableOriginalConstructor()
                ->getMock();
            $mock3->method('GetIsClassLoaded')->willReturn(true);
            $obj->Ticket->method('GetUserObject')->willReturn($mock1);
            $obj->Ticket->method('GetUserOrganizationObject')->willReturn($mock2);
            $obj->Ticket->method('GetUserGroupObject')->willReturn($mock3);
            static::$_prop['ownerstaffid'] = 1;
            static::$_prop['resolutionduedateline'] = DATENOW + 100000;
            static::$_prop['duetime'] = DATENOW + 100000;
            $this->assertTrue($method->invoke($obj, 1));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj, 1);
        }

        /**
         * @throws \ReflectionException
         */
        public function testDispatchReturnsTrue()
        {
            $obj = $this->getMocked();
            $method = $this->getMethod($obj, 'Dispatch');

            static::$databaseCallback['CacheGet'] = function ($x) {
                if ($x === 'queuecache') {
                    return [
                        'pointer' => ['me4@mail.com' => 1, 'no@mail.com' => 2],
                    ];
                }

                return [
                    1 => [1 => [1]],
                ];
            };

            SWIFT::Set('loopcontrol', false);

            SWIFT::Set('_ignoreCCEmail', ['no2@mail.com']);
            SWIFT::Set('_ignoreBCCEmail', ['no2@mail.com']);
            $this->assertTrue($method->invoke($obj, 1, 'contents', 'contents', 'from', 'me@mail.com', 'me@mail.com',
                'to',
                ['me2@mail.com', 'me@mail.com', 'no@mail.com', 'no2@mail.com'],
                ['me3@mail.com', 'me@mail.com', 'no@mail.com', 'no2@mail.com'], 1, 'subject', false));

            SWIFT::Set('loopcontrol', true);

            $this->assertFalse($method->invoke($obj, 1, 'contents', 'contents', 'from', 'me@mail.com', 'me@mail.com',
                'to',
                ['me2@mail.com'], ['me3@mail.com'], 1, 'subject', true));

            SWIFT::Set('loopcontrol', false);

            $this->assertFalse($method->invoke($obj, 1, 'contents', 'contents', 'from', 'me@mail.com', 'me4@mail.com',
                'to',
                ['me2@mail.com'], ['me3@mail.com'], 1, 'subject', true));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj, 1, 'contents', 'contents', 'from', 'me@mail.com', 'me@mail.com', 'to',
                ['me2@mail.com'],
                'me3@mail.com', 1, 'subject', true);
        }

        /**
         * @throws \ReflectionException
         */
        public function testgetLanguageEngineReturnsClass()
        {
            $obj = $this->getMocked();
            $method = $this->getMethod($obj, 'getLanguageEngine');

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'tgroupid' => 1,
                'languageid' => 1,
                'languageengineid' => 1,
                'linktype' => 1,
                'guestusergroupid' => 1,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'languageid' => '1',
                        'regusergroupid' => '1',
                        'departmentapp' => 'tickets',
                        'languagecode' => 'en-us',
                    ],
                ];
            };

            $this->assertInstanceOf('SWIFT_LanguageEngine', $method->invoke($obj));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testEmbedImageAttachmentsReturnsString(): void
        {
            $obj = $this->getMocked();

            $str = '<img src="test.gif"/>';
            $this->assertEquals($str, $obj->EmbedImageAttachments($str),
                'Returns html with images intact');
        }

        /**
         * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketEmailDispatchMock
         */
        private function getMocked()
        {
            $post = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_TicketPost')
                ->disableOriginalConstructor()
                ->getMock();

            $post->method('GetProperty')->willReturnCallback(function ($x) {
                if (isset(static::$_prop[$x])) {
                    return static::$_prop[$x];
                }

                return 1;
            });

            $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
                ->disableOriginalConstructor()
                ->getMock();

            $ticket->method('GetProperty')->willReturnCallback(function ($x) {
                if (isset(static::$_prop[$x])) {
                    return static::$_prop[$x];
                }

                return 1;
            });
            $ticket->method('GetIsClassLoaded')->willReturn(true);
            $ticket->method('GetTicketPosts')->willReturn([1 => $post]);

            $tpl = $this->getMockBuilder('SWIFT_TemplateEngine')
                ->disableOriginalConstructor()
                ->getMock();

            $mgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
                ->disableOriginalConstructor()
                ->getMock();

            $mgr->method('GetCustomFieldValue')->willReturn([]);

            $int = $this->getMockBuilder('SWIFT_Interface')
                ->disableOriginalConstructor()
                ->getMock();

            $int->method('GetInterface')->willReturn(60);

            $emoji = $this->getMockBuilder('SWIFT_Emoji')
                ->disableOriginalConstructor()
                ->getMock();

            $mail = $this->getMockBuilder('SWIFT_Mail')
                ->disableOriginalConstructor()
                ->getMock();

            $cookie = $this->getMockBuilder('SWIFT_Cookie')
                ->disableOriginalConstructor()
                ->getMock();
            $cookie->method('GetVariable')->willReturn(1);
            $cookie->method('Get')->willReturn('en-us');

            \SWIFT::GetInstance()->Cookie = $cookie;

            return $this->getMockObject('Tickets\Library\Ticket\SWIFT_TicketEmailDispatchMock', [
                'Ticket' => $ticket,
                'Interface' => $int,
                'Template' => $tpl,
                'Mail' => $mail,
                'Emoji' => $emoji,
                'CustomFieldManager' => $mgr,
            ]);
        }
    }

    class SWIFT_TicketEmailDispatchMock extends SWIFT_TicketEmailDispatch
    {
        public $Ticket;
        public $returnLanguage = true;

        public function __construct($services = [])
        {
            $this->Load = new LoaderMock();

            foreach ($services as $key => $service) {
                $this->$key = $service;
            }

            $this->SetIsClassLoaded(true);

            parent::__construct($this->Ticket);
        }

        public function Initialize()
        {
            // override
            return true;
        }

        protected function getLanguage($_SWIFT_TemplateGroupObject)
        {
            if ($this->returnLanguage) {
                return parent::getLanguage($_SWIFT_TemplateGroupObject);
            } else {
                return \SWIFT::GetInstance()->Language;
            }
        }

        protected function getLanguageEngine()
        {
            if ($this->returnLanguage) {
                return parent::getLanguageEngine();
            }

            return \SWIFT::GetInstance()->Language;
        }
    }
}
