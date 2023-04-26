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

namespace Tickets\Library\Notification;

use Archiver\Admin\PDOMock;
use Base\Models\User\SWIFT_UserOrganization;
use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class TicketNotificationTest
 * @group tickets
 * @group tickets-lib4
 */
class TicketNotificationTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Library\Notification\SWIFT_TicketNotification', $obj);

        $this->setExpectedException('SWIFT_Exception', SWIFT_CREATEFAILED);
        $this->getMocked([], false);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetTicketReturnsFalse()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'SetTicket');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertFalse($method->invoke($obj, $ticket));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $ticket);
    }

    public function testIsValidTypeReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj::IsValidType(-1));
    }

    public function testUpdateIsNotLoaded()
    {
        $obj = $this->getMocked();

        $this->assertClassNotLoaded($obj, 'Update', '', 1, 1);
    }

    public function testUpdateIsValidTitle()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Update', '', 1, 1);
    }

    public function testGetEmailListIsValidType()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetEmailList');

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, -1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetEmailListReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetEmailList');

        $this->setNextRecordNoLimit();

        $SWIFT = SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'ticketmaskid' => 0,
            'tgroupid' => 1,
            'regusergroupid' => 1,
            'usergroupid' => 1,
            'userid' => 1,
            'userdesignation' => '',
            'salutation' => '',
            'fullname' => 'fullname',
            'email' => 'me@mail.com',
            'oldeditemailaddress' => 'me@mail.com',
            'replyto' => '',
            'ticketslaplanid' => '0',
            'slaplanid' => '0',
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'ticketpostid' => 1,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'subject' => 'subject',
            'emailqueueid' => '0',
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 1,
            'isresolved' => 1,
            'dateline' => 0,
            'userorganizationid' => 0,
            'languageid' => 1,
            'flagtype' => 1,
            'title' => 'title',
            'ownerstaffid' => '1',

            'tickettimetrackid' => '1',
            'tickettimetracknoteid' => 1,
            'timeworked' => 0,
            'timebilled' => 0,
            'wasreopened' => 0,
            'bayescategoryid' => 0,
            'isthirdparty' => 0,
            'isprivate' => 0,
            'creator' => 1,
            'staffid' => &static::$_prop['staffid'],
            'isenabled' => 1,
            'staffgroupid' => &static::$_prop['staffgroupid'],

            'attachmentid' => 1,
            'filename' => 'file.txt',
            'storefilename' => 'file.txt',
            'attachmenttype' => 1,
            'filesize' => 1,
            'filetype' => 'file',
            'linktype' => 1,
        ];
        $SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, 'userid')) {
                static::$prop['userid'] = 1;
            }

            return $arr;
        });
        $SWIFT->Database->Record = $arr;
        $userOrg = $this->getMockBuilder(SWIFT_UserOrganization::class)
            ->disableOriginalConstructor()
            ->setMethods(['GetIsClassLoaded', 'GetUserOrganizationID'])
            ->getMock();
        $userOrg->method('GetIsClassLoaded')->willReturn(true);
        $userOrg->method('GetUserOrganizationID')->willReturn(1);
        $obj->Ticket->method('GetUserOrganizationObject')->willReturn($userOrg);
        $obj->Ticket->method('GetProperty')->willReturn(1);

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x === 'ticketcountcache') {
                return [];
            }

            if ($x === 'departmentcache') {
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    2 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    3 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                    4 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                ];
            }

            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                    ],
                    2 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '0',
                    ],
                ];
            }

            if ($x === 'groupassigncache') {
                return [
                    1 => [
                        1 => 1,
                        3 => 3,
                    ],
                ];
            }

            if ($x === 'tickettypecache' || $x === 'statuscache') {
                return [
                    1 => [
                        1 => 1,
                        'departmentid' => 2,
                    ],
                    2 => [
                        'departmentid' => 0,
                    ],
                ];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                ],
            ];
        };

        static::$_prop['staffid'] = 1;
        static::$_prop['staffgroupid'] = 1;
        foreach ([1, 2, 3, 4, 5, 6] as $type) {
            $this->assertInternalType('array', $method->invoke($obj, $type));
        }

        static::$_prop['staffid'] = 0;
        static::$_prop['staffgroupid'] = 0;
        $this->assertFalse($method->invoke($obj, 3));
        $this->assertFalse($method->invoke($obj, 4));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetBaseContentReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetBaseContent');

        $obj->Ticket->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            if (in_array($x, ['departmentid', 'ownerstaffid'], true)) {
                return 0;
            }

            return 1;
        });

        $userOrg = $this->getMockBuilder('SWIFT_UserOrganization')
            ->disableOriginalConstructor()
            ->setMethods(['GetIsClassLoaded', 'GetUserOrganizationID'])
            ->getMock();
        $userOrg->method('GetIsClassLoaded')->willReturn(true);
        $userOrg->method('GetUserOrganizationID')->willReturn(1);
        $obj->Ticket->method('GetUserOrganizationObject')->willReturn($userOrg);
        $obj->Ticket->method('GetTicketID')->willReturn(1);
        $obj->Ticket->method('Get')->willReturn(1);

        $this->setNextRecordNoLimit();
        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ratingid' => &static::$_prop['ratingid'],
            'typeid' => &static::$_prop['typeid'],
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $_SWIFT->Database->Record3 = [
            'ratingvisibility' => 'public',
            'ratingid' => 1,
            'departmentid' => 1,
            'iseditable' => 0,
        ];

        static::$_prop['ratingid'] = 1;
        static::$_prop['typeid'] = 1;
        $this->assertNotEmpty($method->invoke($obj, 3));

        static::$_prop['departmentid'] = 1;
        $this->assertNotEmpty($method->invoke($obj, 3));

        static::$_prop['typeid'] = 2;
        $this->assertNotEmpty($method->invoke($obj, 3));

        static::$_prop['typeid'] = 1;
        static::$_prop['ratingid'] = 2;
        $this->assertNotEmpty($method->invoke($obj, 3));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTitleReturnsFalse()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetTitle');

        $this->assertFalse($method->invoke($obj, 'a', 'aa'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testPrepareReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'Prepare');

        $_SWIFT = SWIFT::GetInstance();
        $_SWIFT->Staff = false;
        $obj->_updateContainer = ['t' => [0, 1, 2, 3]];
        $this->assertNotEmpty($method->invoke($obj, 1, 'a'));

        $obj->_updateContainer = ['t' => [0, '', 2, '']];
        $this->assertNotEmpty($method->invoke($obj, 1, 'a'));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 'a');
    }

    public function testDispatchThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Dispatch', -1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDispatchReturnsTrue()
    {
        $mockMgr = $this->getMockBuilder('SWIFT_CustomFieldManager')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['GetCustomFieldValue', 'Check'])
            ->getMock();
        $mockMgr->method('Check')->willReturn([1 => [1]]);
        $obj = $this->getMocked([
            'CustomFieldManager' => $mockMgr,
        ]);

        $obj->Ticket->method('GetNoAlerts')->willReturnOnConsecutiveCalls(true, false, false);
        $this->assertTrue($obj->Dispatch(1));

        $this->setNextRecordNoLimit();

        $SWIFT = SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'ticketmaskid' => 0,
            'tgroupid' => 1,
            'regusergroupid' => 1,
            'usergroupid' => 1,
            'userid' => 1,
            'userdesignation' => '',
            'salutation' => '',
            'fullname' => 'fullname',
            'email' => 'me@mail.com',
            'oldeditemailaddress' => 'me@mail.com',
            'replyto' => '',
            'ticketslaplanid' => '0',
            'slaplanid' => '0',
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'ticketpostid' => 1,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'subject' => 'subject',
            'emailqueueid' => '0',
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 1,
            'isresolved' => 1,
            'dateline' => 0,
            'userorganizationid' => 0,
            'languageid' => 1,
            'flagtype' => 1,
            'title' => 'title',
            'ownerstaffid' => '1',

            'tickettimetrackid' => '1',
            'tickettimetracknoteid' => 1,
            'timeworked' => 0,
            'timebilled' => 0,
            'wasreopened' => 0,
            'bayescategoryid' => 0,
            'isthirdparty' => 0,
            'isprivate' => 0,
            'creator' => 1,
            'staffid' => 1,
            'isenabled' => 1,
            'staffgroupid' => 1,
            'creationmode' => 3,

            'attachmentid' => 1,
            'filename' => 'file.txt',
            'storefilename' => 'file.txt',
            'attachmenttype' => 1,
            'filesize' => 1,
            'filetype' => 'file',
            'linktype' => 1,

            'forstaffid' => 1,
            'ticketnoteid' => 1,
        ];
        $SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, 'userid')) {
                static::$prop['userid'] = 1;
            }

            return $arr;
        });
        $SWIFT->Database->Record = $arr;
        $userOrg = $this->getMockBuilder('SWIFT_UserOrganization')
            ->disableOriginalConstructor()
            ->setMethods(['GetIsClassLoaded', 'GetUserOrganizationID'])
            ->getMock();
        $userOrg->method('GetIsClassLoaded')->willReturn(true);
        $userOrg->method('GetUserOrganizationID')->willReturn(1);
        $obj->Ticket->method('RetrieveFromEmailWithSuffix')->willReturn('me@mail.com');
        $obj->Ticket->method('GetUserOrganizationObject')->willReturn($userOrg);
        $obj->Ticket->method('GetTicketID')->willReturn(1);
        $obj->Ticket->method('GetNotificationAttachments')->willReturn([
            [
                'filesize' => 1,
                'size' => 1,
                'data' => 1,
                'filetype' => 'file',
                'contenttype' => 'text/plain',
                'storefilename' => 'file.txt',
                'attachmenttype' => 0,
                'contentid' => 0,
            ],
            [
                'filename' => 'file2.txt',
                'extension' => 'txt',
                'filesize' => 1024,
                'size' => 1024,
                'data' => 1,
                'filetype' => 'file2',
                'contenttype' => 'text/plain',
                'storefilename' => 'file2.txt',
                'attachmenttype' => 0,
                'contentid' => 1,
            ],
        ]);
        $obj->Ticket->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            if (false !== strpos($x, 'mail')) {
                return 'me@mail.com';
            }

            return 1;
        });

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x === 'ticketcountcache') {
                return [];
            }

            if ($x === 'departmentcache') {
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    2 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    3 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                    4 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                ];
            }

            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                        'email' => 'me@mail.com'
                    ],
                    2 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '0',
                    ],
                ];
            }

            if ($x === 'groupassigncache') {
                return [
                    1 => [
                        1 => 1,
                        3 => 3,
                    ],
                ];
            }

            if ($x === 'tickettypecache' || $x === 'statuscache') {
                return [
                    1 => [
                        1 => 1,
                        'departmentid' => 2,
                    ],
                    2 => [
                        'departmentid' => 0,
                    ],
                ];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                ],
            ];
        };

        \SWIFT::Set('loopcontrol', true);

        $this->assertTrue($obj->Dispatch(1, ['me2@mail.com'], 'subject', 'contents', 'from', 'from@mail.com', true, 'newstaffreply'));

        $mockInt = $this->getMockBuilder('SWIFT_Interface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockInt->method('GetInterface')->willReturn(210); // staffapi
        $SWIFT->Interface = $mockInt;

        $this->assertTrue($obj->Dispatch(1, ['me2@mail.com'], 'subject', 'contents', 'from', 'from@mail.com', true, 'newticketnotes'));

        $this->assertClassNotLoaded($obj, 'Dispatch', 1);
    }

    /**
     * @param array $services
     * @param bool $isLoaded
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketNotificationMock
     */
    private function getMocked(array $services = [], $isLoaded = true)
    {
        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn($isLoaded);

        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Library\Notification\SWIFT_TicketNotificationMock',
            array_merge($services, [
                'Ticket' => $ticket,
                'Emoji' => $mockEmoji,
                'Template' => $mockTpl,
            ]));
    }
}

class SWIFT_TicketNotificationMock extends SWIFT_TicketNotification
{
    public $Ticket;
    public $_updateContainer = [];

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
}

