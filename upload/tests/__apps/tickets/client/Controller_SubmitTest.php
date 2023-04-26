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

namespace {

    // This allow us to configure the behavior of the "global mock"
    global $mockIsUploadedFile;
    $mockIsUploadedFile = false;
}

namespace Tickets\Client {

    use Base\Models\Template\SWIFT_TemplateGroup;
    use Knowledgebase\Admin\LoaderMock;
    use SWIFT_Exception;

    /**
     * Class Controller_SubmitTest
     * @group tickets
     * @group tickets-client
     */
    class Controller_SubmitTest extends \SWIFT_TestCase
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
            $this->assertInstanceOf('Tickets\Client\Controller_Submit', $obj);

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        'appname' => 'tickets',
                        'widgetname' => 'submitticket',
                        'isenabled' => '1',
                    ],
                ];
            };

            $obj = $this->getMocked();
            $this->assertInstanceOf('Tickets\Client\Controller_Submit', $obj);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testIndexReturnsTrue()
        {
            $obj = $this->getMocked();

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    2 => [
                        1 => 1,
                        'departmentid' => 2,
                        'markasresolved' => 1,
                        'departmenttype' => 'public',
                        'type' => 'public',
                        'statustype' => 'public',
                        'department' => 1,
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => 0,
                        'uservisibilitycustom' => 0,
                        'title' => 1,
                    ],
                    1 => [
                        1 => 1,
                        'departmentid' => 1,
                        'markasresolved' => 1,
                        'departmenttype' => 'public',
                        'type' => 'public',
                        'statustype' => 'public',
                        'department' => 1,
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => 2,
                        'uservisibilitycustom' => 0,
                        'title' => 1,
                    ],
                ];
            };

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'priorityid' => 1,
                'usergroupid' => 1,
                'ticketstatusid' => 1,
                'toassignid' => 1,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;
            $obj->Database->Record = $arr;

            $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

            $this->assertTrue($obj->Index(),
                'Returns true without errors');

            static::$databaseCallback['GetLinked'] = function () {
                return 0;
            };
            $_POST['departmentid'] = 1;
            $this->assertTrue($obj->Index(1),
                'Returns true without errors');

            $this->assertClassNotLoaded($obj, 'Index');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testCheckForValidAttachmentsReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertTrue($obj::CheckForValidAttachments(1));

            $_FILES['ticketattachments']['tmp_name'] = [''];
            $this->assertFalse($obj::CheckForValidAttachments(1));

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    1 => [
                        1 => 1,
                        'extension' => 'txt',
                        'txt' => [
                            'acceptsupportcenter' => 1,
                            'maxsize' => 1,
                        ],
                    ],
                ];
            };

            $_FILES['ticketattachments']['tmp_name'] = ['file.txt'];
            $_FILES['ticketattachments']['name'] = ['file.txt'];
            $_FILES['ticketattachments']['size'] = [2048];
            $this->assertCount(2, $obj::CheckForValidAttachments(1));

            static::$databaseCallback['SettingsGet'] = function ($x) {
                if ($x === 'tickets_resattachments') {
                    return 0;
                }

                return 1;
            };

            $this->assertTrue($obj::CheckForValidAttachments(1));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testConfirmationReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertFalse($obj->Confirmation(),
                'Returns false with invalid user');

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'uservisibilitycustom' => 0,
                'departmentid' => 1,
                'userconsentid' => 1,
                'flagtype' => 1,
                'userid' => 1,
                'userdesignation' => '',
                'salutation' => '',
                'isresolved' => 1,
                'templategrouptitle' => 'Default',
                'name' => 'submitticket_form',
                'fullname' => 'fullname',
                'ticketslaplanid' => 0,
                'slaplanid' => 0,
                'ticketpostid' => 1,
                'averageresponsetimehits' => 0,
                'firstresponsetime' => 0,
                'totalreplies' => 0,
                'duetime' => 0,
                'resolutionduedateline' => 0,
                'ticketmaskid' => 0,
                'emailqueueid' => 0,
                'dateline' => 0,
                'languageid' => 1,
                'languageengineid' => 1,
                'tickettypeid' => 1,
                'ticketstatusid' => 1,
                'priorityid' => 1,
                'ticketpriorityid' => 1,
                'replyto' => '',
                'useremailid' => 1,
                'isthirdparty' => 0,
                'creator' => 0,
                'isprivate' => 0,
                'contents' => '<html>contents</html>',
                'ishtml' => 1,
                'userorganizationid' => 0,
                'guestusergroupid' => 0,
                'hasattachments' => 0,
                'subject' => 'subject',
                'email' => 'me@mail.com',
                'regusergroupid' => 1,
                'usergroupid' => 1,
                'linktype' => 1,
                'title' => 'title',
                'ownerstaffid' => 1,
                'tgroupid' => 1,
                'linktypeid' => 1,
                'tickethash' => 'hash',
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                if (false !== strpos($x, 'userconsents')) {
                    if (!isset(static::$_prop['consent'])) {
                        static::$_prop['consent'] = 1;
                    } else {
                        static::$_prop['consent']++;
                        if (static::$_prop['consent'] === 3) {
                            return false;
                        }
                    }
                }

                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->setNextRecordNoLimit();

            $_POST['_csrfhash'] = 'csrfhash';

            $user = $this->getMockBuilder('Base\Models\User\SWIFT_User')
                ->disableOriginalConstructor()
                ->getMock();
            $user->method('GetProperty')->willReturnArgument(0);
            $user->method('GetEmailList')->willReturn(['me@mail.com']);
            $user->method('GetIsClassLoaded')->willReturnCallback(function () {
                return static::$_prop['isloaded'];
            });

            static::$_prop['isloaded'] = 1;

            $_SWIFT->User = null;

            $this->assertFalse($obj->Confirmation(),
                'Returns false without POST');

            $_POST['ticketfullname'] = '<script></script>';
            $_POST['ticketmessage'] = 'message';
            $_POST['ticketsubject'] = 'subject';
            $_POST['ticketemail'] = '@';
            $this->assertFalse($obj->Confirmation(),
                'Returns false with invalid name');

            $_POST['ticketfullname'] = 'ticketfullname';
            $this->assertFalse($obj->Confirmation(),
                'Returns false with invalid mail');

            $_POST['ticketemail'] = 'me@mail.com';
            $this->assertFalse($obj->Confirmation(),
                'Returns false without consent');

            $_SWIFT = \SWIFT::GetInstance();
            $_SWIFT->User = $user;

            $_POST['registrationconsent'] = 'i do';
            $this->assertFalse($obj->Confirmation(),
                'Returns false without log in');

            static::$databaseCallback['SettingsGet'] = function ($x) {
                if ($x === 'security_captchatype') {
                    return 'no';
                }

                if (false !== strpos($x, 'email')) {
                    return 'me@mail.com';
                }

                return 1;
            };
            $this->assertFalse($obj->Confirmation(),
                'Returns false without departmentid and valid captcha');

            $_SWIFT->Session->method('IsLoggedIn')->willReturn(true);
            $this->assertFalse($obj->Confirmation(),
                'Returns false without departmentid');

            // WATCH OUT for the customfieldmanager->check method in the mock at the bottom
            $_POST['departmentid'] = 1;
            $this->assertFalse($obj->Confirmation(),
                'Returns false without custom field check');

            static::$databaseCallback['CacheGet'] = function ($x) {
                if($x == 'languagecache')
                    return [1 => ['languagecode' => 'en-us']];

                if($x == 'templategroupcache')
                    return [1 => ['languageid' => 1, 'tgroupid' => 1]];

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

          //  $this->expectOutputRegex('/.*/');

            \SWIFT::Set('loopcontrol', true);

            $_FILES['ticketattachments']['tmp_name'] = ['file.txt'];
            $_FILES['ticketattachments']['name'] = ['file.txt'];
            $_FILES['ticketattachments']['size'] = [2048];

            $this->assertFalse($obj->Confirmation(1),
                'Returns false with invalid attachment');

            $_POST['ticketpriorityid'] = 2;
            unset($_FILES['ticketattachments']);
            $this->assertFalse($obj->Confirmation(),
                'Returns false without ticketpriorityid');

            static::$_prop['isloaded'] = 0;
            $_POST['ticketpriorityid'] = 1;
            $_POST['tickettypeid'] = 2;
            $_POST['ticketemail'] = 'me@mail.com';
            $this->assertFalse($obj->Confirmation(),
                'Returns false without tickettypeid');

            $_POST['tickettypeid'] = 1;
            $_POST['ticketsubject'] = '<script></script>';
            $_POST['ticketmessage'] = '<script></script>';

            $tg = $this->getMockBuilder(SWIFT_TemplateGroup::class)
                ->disableOriginalConstructor()
                ->getMock();
            $tg->method('GetTemplateGroupID')->willReturn(1);
            $_SWIFT->TemplateGroup = $tg;
            $this->assertFalse($obj->Confirmation(),
                'Returns false with invalid HTML. It is replaced with blank string');

            $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);

            static::$databaseCallback['NextRecord'] = function () {
                if (isset(static::$_prop['stop'])) {
                    return false;
                }
                return static::$nextRecordCount % 2;
            };

            static::$databaseCallback['Query'] = function ($x) {
                if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                    static::$_prop['stop'] = true;
                }
            };

            $_POST['ticketsubject'] = 'subject';
            $_POST['ticketmessage'] = 'message';
            $_POST['ticketcc'] = 'me@mail.com, me2@mail.com';
            \Base\Models\User\SWIFT_User::$_permissionCache['perm_sendautoresponder'] = 0;
            $this->assertTrue($obj->Confirmation());

            \Base\Models\User\SWIFT_User::$_permissionCache['perm_sendautoresponder'] = 1;
            $_POST['ticketfullname'] = '<xml></xml>';
            $this->assertFalse($obj->Confirmation());

            $this->assertClassNotLoaded($obj, 'Confirmation');
        }

        public function testConfirmationMessageThrowsException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'ConfirmationMessage', 1, 'hash');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testConfirmationMessageReturnsTrue()
        {
            $obj = $this->getMocked();

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'firstpostid' => 1,
                'ticketpostid' => 1,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'tickethash' => 'hash',
                'contents' => 'contents',
                'fullname' => 'fullname',
                'email' => 'me@mail.com',
                'subject' => 'subject',
                'ticketmaskid' => 0,
                'priorityid' => 1,
                'tickettypeid' => 1,
                'ticketstatusid' => 1,
                'ticketrecipientid' => 2,
                'ticketemailid' => 1,
                'recipienttype' => 2,
                'ishtml' => 1,
                'creator' => 1,
                'hasattachments' => 0,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->setNextRecordType(static::NEXT_RECORD_NO_LIMIT);

            $this->assertTrue($obj->ConfirmationMessage(1, 'hash', 1),
                'Returns true without errors');

            $this->assertClassNotLoaded($obj, 'ConfirmationMessage', 1, 'hash');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testRenderFormReturnsTrue()
        {
            $rdr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldRendererClient')
                ->disableOriginalConstructor()
                ->getMock();
            $obj = $this->getMocked([
                'CustomFieldRendererClient' => $rdr,
            ]);

            $_POST['departmentid'] = 1;

            $this->assertFalse($obj->RenderForm(),
                'Returns true without errors');

            static::$databaseCallback['CacheGet'] = function ($x) {
                return [
                    2 => [
                        1 => 1,
                        'departmentid' => 2,
                        'markasresolved' => 1,
                        'departmenttype' => 'public',
                        'type' => 'public',
                        'statustype' => 'public',
                        'department' => 1,
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => 0,
                        'uservisibilitycustom' => 0,
                        'title' => 1,
                    ],
                    1 => [
                        1 => 1,
                        'departmentid' => 1,
                        'markasresolved' => 1,
                        'departmenttype' => 'public',
                        'type' => 'public',
                        'statustype' => 'public',
                        'department' => 1,
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => 2,
                        'uservisibilitycustom' => 0,
                        'title' => 1,
                    ],
                ];
            };

            $user = $this->getMockBuilder('Base\Models\User\SWIFT_User')
                ->disableOriginalConstructor()
                ->getMock();
            $user->method('GetIsClassLoaded')->willReturn(true);

            \SWIFT::GetInstance()->User = $user;

            $_SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'uservisibilitycustom' => 0,
            ];
            $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
                return $arr;
            });
            $_SWIFT->Database->Record = $arr;

            $this->setNextRecordType(static::NEXT_RECORD_QUERY_RESET);

            $this->assertTrue($obj->RenderForm(1),
                'Returns true without errors');

            $_POST['ticketfullname'] = 'fullname';
            $_POST['ticketemail'] = 'me@mail.com';
            $_POST['ticketsubject'] = 'subject';
            $_POST['ticketmessage'] = 'message';
            $_POST['tickettypeid'] = '1';
            $_POST['ticketpriorityid'] = '1';
            $this->assertTrue($obj->RenderForm(1),
                'Returns true without errors');

            $this->assertClassNotLoaded($obj, 'RenderForm');
        }

        /**
         * @throws \ReflectionException
         */
        public function testGetTicketObjectOnHashReturnsFalse()
        {
            $obj = $this->getMocked();
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod('_GetTicketObjectOnHash');
            $method->setAccessible(true);

            $this->assertFalse($method->invoke($obj, 0, 'hash'));
            $this->assertFalse($method->invoke($obj, 'no', 'hash'));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj, 1, 'hash');
        }

        /**
         * @throws \ReflectionException
         */
        public function testGetTicketTypeContainerReturnsArray()
        {
            $obj = $this->getMocked();
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod('_GetTicketTypeContainer');
            $method->setAccessible(true);

            $this->assertInternalType('array', $method->invoke($obj));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj);
        }

        /**
         * @throws \ReflectionException
         */
        public function testGetTicketPriorityContainerReturnsArray()
        {
            $obj = $this->getMocked();
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod('_GetTicketPriorityContainer');
            $method->setAccessible(true);

            $this->assertInternalType('array', $method->invoke($obj));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj);
        }

        /**
         * @param array $services
         * @return \PHPUnit_Framework_MockObject_MockObject|Controller_SubmitMock
         */
        private function getMocked(array $services = [])
        {
            $tgroup = $this->getMockBuilder('Base\Models\Template\SWIFT_TemplateGroup')
                ->disableOriginalConstructor()
                ->getMock();
            $tgroup->method('GetProperty')->willReturn(1);
            $mockInput = $this->getMockBuilder('SWIFT_Input')
                ->disableOriginalConstructor()
                ->getMock();
            $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
                ->disableOriginalConstructor()
                ->getMock();
            $rtr = $this->getMockBuilder('SWIFT_Router')
                ->disableOriginalConstructor()
                ->getMock();
            $rtr->method('GetCurrentURL')->willReturn('http://localhost');
            $mgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
                ->disableOriginalConstructor()
                ->getMock();
            $mgr->method('Check')->willReturnCallback(function ($x) {
                // TODO: this check is very fragil
                return [static::$nextRecordCount - 64, [1]];
            });
            return $this->getMockObject('Tickets\Client\Controller_SubmitMock', array_merge($services, [
                'TemplateGroup' => $tgroup,
                'Input' => $mockInput,
                'Emoji' => $mockEmoji,
                'CustomFieldManager' => $mgr,
                'Router' => $rtr,
            ]));
        }
    }

    class Controller_SubmitMock extends Controller_Submit
    {
        public $Database;
        public $Cache;

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
}
